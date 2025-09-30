<?php
session_start();
require_once '../connect/connect.php';

if (!isset($_GET['id'])) {
    header('Location: usermanagement.php');
    exit();
}

$accountId = intval($_GET['id']);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $gradelevel = $_POST['gradelevel'];
    $name = $_POST['name'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $accountstatus = $_POST['accountstatus'];
    $votestatus = $_POST['votestatus'];
    $securitykey = $_POST['securitykey'];

    // Get current admin id for audit
    $adminUsername = $_SESSION['username'];
    $adminStmt = $conn->prepare("SELECT AdminID FROM admin WHERE Username = ?");
    $adminStmt->bind_param('s', $adminUsername);
    $adminStmt->execute();
    $adminResult = $adminStmt->get_result();
    $admin = $adminResult->fetch_assoc();
    $adminId = $admin ? intval($admin['AdminID']) : null;
    $adminStmt->close();

    // Set timezone to Manila and get current datetime
    date_default_timezone_set('Asia/Manila');
    $now = date('Y-m-d H:i:s');

    // Only update password if not empty
    if (!empty($password)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE account SET GradeLevel=?, Name=?, Username=?, Password=?, AccountStatus=?, VoteStatus=?, SecurityKey=?, AdminModifiedBy=?, AdminModifiedDate=? WHERE AccountID=?");
        // 7 strings, 1 int (admin), 1 string (date), 1 int (id)
        $stmt->bind_param('sssssssisi', $gradelevel, $name, $username, $hashed_password, $accountstatus, $votestatus, $securitykey, $adminId, $now, $accountId);
    } else {
        $stmt = $conn->prepare("UPDATE account SET GradeLevel=?, Name=?, Username=?, AccountStatus=?, VoteStatus=?, SecurityKey=?, AdminModifiedBy=?, AdminModifiedDate=? WHERE AccountID=?");
        // 6 strings, 1 int (admin), 1 string (date), 1 int (id)
        $stmt->bind_param('ssssssisi', $gradelevel, $name, $username, $accountstatus, $votestatus, $securitykey, $adminId, $now, $accountId);
    }
    $stmt->execute();
    $stmt->close();
    $conn->close();
    header('Location: usermanagement.php');
    exit();
}

// Fetch user data
$stmt = $conn->prepare("SELECT * FROM account WHERE AccountID=?");
$stmt->bind_param('i', $accountId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();
$conn->close();

if (!$user) {
    echo "User not found.";
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit User</title>
    <link rel="icon" type="image/png" href="/votingsystem/images/logo.png">
    <link rel="stylesheet" type="text/css" href="/votingsystem/files/maincss/main.css">
    <link rel="stylesheet" type="text/css" href="/votingsystem/files/admin/admin.css">
    <style>
        body { background: #111; color: #fff; font-family: 'Inter', sans-serif; }
        .edit-container { background: #222; color: #fff; max-width: 500px; margin: 40px auto; padding: 30px; border-radius: 10px; }
        label { display: block; margin-top: 15px; color: #fff; }
        input, select { width: 100%; padding: 8px; margin-top: 5px; border-radius: 4px; border: 1px solid #444; background: #333; color: #fff; }
        .form-actions { margin-top: 20px; display: flex; gap: 10px; justify-content: flex-end; }
        .btn { background: #36AE66; color: #fff; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; }
        .btn-cancel { background: #888; }
    </style>
</head>
<body>
    <div class="edit-container">
        <h2>Edit User</h2>
        <form method="POST">
            <label>Grade Level:
                <input type="text" name="gradelevel" value="<?php echo htmlspecialchars($user['GradeLevel']); ?>" required>
            </label>
            <label>Full Name:
                <input type="text" name="name" value="<?php echo htmlspecialchars($user['Name']); ?>" required>
            </label>
            <label>Username:
                <input type="text" name="username" value="<?php echo htmlspecialchars($user['Username']); ?>" required>
            </label>
            <label>Password (leave blank to keep current):
                <input type="password" name="password" value="">
            </label>
            <label>Account Status:
                <select name="accountstatus" required>
                    <option value="ACTIVE" <?php if($user['AccountStatus']==='ACTIVE') echo 'selected'; ?>>ACTIVE</option>
                    <option value="BLOCKED" <?php if($user['AccountStatus']==='BLOCKED') echo 'selected'; ?>>BLOCKED</option>
                </select>
            </label>
            <label>Vote Status:
                <select name="votestatus" required>
                    <option value="NOT VOTED" <?php if(strtoupper($user['VoteStatus'])==='NOT VOTED') echo 'selected'; ?>>NOT VOTED</option>
                    <option value="VOTED" <?php if(strtoupper($user['VoteStatus'])==='VOTED') echo 'selected'; ?>>VOTED</option>
                </select>
            </label>
            <label>Security Key:
                <input type="text" name="securitykey" value="<?php echo htmlspecialchars($user['SecurityKey']); ?>">
            </label>
            <div class="form-actions">
                <a href="usermanagement.php" class="btn btn-cancel">Cancel</a>
                <button type="submit" class="btn">Save Changes</button>
            </div>
        </form>
    </div>
</body>
</html> 