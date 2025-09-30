<?php
session_start();
require_once '../connect/connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $gradelevel = $_POST['gradelevel'];
    $name = $_POST['name'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $accountstatus = $_POST['accountstatus'];
    $votestatus = $_POST['votestatus'];
    $securitykey = $_POST['securitykey'];

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Get current admin id for CreatedBy
    $adminUsername = $_SESSION['username'];
    $adminStmt = $conn->prepare("SELECT AdminID FROM admin WHERE Username = ?");
    $adminStmt->bind_param('s', $adminUsername);
    $adminStmt->execute();
    $adminResult = $adminStmt->get_result();
    $admin = $adminResult->fetch_assoc();
    $createdBy = $admin ? intval($admin['AdminID']) : null;
    $adminStmt->close();

    // Set timezone to Manila and get current datetime
    date_default_timezone_set('Asia/Manila');
    $createdDate = date('Y-m-d H:i:s');

    // Insert with CreatedBy and CreatedDate
    $stmt = $conn->prepare("INSERT INTO account (GradeLevel, Name, Username, Password, AccountStatus, VoteStatus, SecurityKey, CreatedBy, CreatedDate) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('sssssssis', $gradelevel, $name, $username, $hashed_password, $accountstatus, $votestatus, $securitykey, $createdBy, $createdDate);
    $stmt->execute();
    $stmt->close();
    $conn->close();
    header('Location: usermanagement.php');
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add User</title>
    <link rel="icon" type="image/png" href="/votingsystem/images/logo.png">
    <link rel="stylesheet" type="text/css" href="/votingsystem/files/maincss/main.css">
    <link rel="stylesheet" type="text/css" href="/votingsystem/files/admin/admin.css">
    <style>
        body { background: #111; color: #fff; font-family: 'Inter', sans-serif; }
        .add-container { background: #222; color: #fff; max-width: 500px; margin: 40px auto; padding: 30px; border-radius: 10px; }
        label { display: block; margin-top: 15px; color: #fff; }
        input, select { width: 100%; padding: 8px; margin-top: 5px; border-radius: 4px; border: 1px solid #444; background: #333; color: #fff; }
        .form-actions { margin-top: 20px; display: flex; gap: 10px; justify-content: flex-end; }
        .btn { background: #36AE66; color: #fff; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; }
        .btn-cancel { background: #888; }
    </style>
</head>
<body>
    <div class="add-container">
        <h2>Add New User</h2>
        <form method="POST">
            <label>Grade Level:
                <input type="text" name="gradelevel" required>
            </label>
            <label>Full Name:
                <input type="text" name="name" required>
            </label>
            <label>Username:
                <input type="text" name="username" required>
            </label>
            <label>Password:
                <input type="password" name="password" required>
            </label>
            <label>Account Status:
                <select name="accountstatus" required>
                    <option value="ACTIVE">ACTIVE</option>
                    <option value="BLOCKED">BLOCKED</option>
                </select>
            </label>
            <label>Vote Status:
                <select name="votestatus" required>
                    <option value="NOT VOTED">NOT VOTED</option>
                    <option value="VOTED">VOTED</option>
                </select>
            </label>
            <label>Security Key:
                <input type="text" name="securitykey">
            </label>
            <div class="form-actions">
                <a href="usermanagement.php" class="btn btn-cancel">Cancel</a>
                <button type="submit" class="btn">Add User</button>
            </div>
        </form>
    </div>
</body>
</html> 