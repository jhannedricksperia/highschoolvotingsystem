<?php
session_start();
require_once '../connect/connect.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ElectionName = $_POST['ElectionName'];
    $StartDateTime = $_POST['StartDateTime'];
    $EndDateTime = $_POST['EndDateTime'];
    $G8RepNum = $_POST['G8RepNum'];
    $G9RepNum = $_POST['G9RepNum'];
    $G10RepNum = $_POST['G10RepNum'];
    $G11RepNum = $_POST['G11RepNum'];
    $G12RepNum = $_POST['G12RepNum'];

    // Resolve current admin id for CreatedBy
    $adminUsername = $_SESSION['username'];
    $adminStmt = $conn->prepare("SELECT AdminID FROM admin WHERE Username = ?");
    $adminStmt->bind_param('s', $adminUsername);
    $adminStmt->execute();
    $adminRes = $adminStmt->get_result();
    $admin = $adminRes->fetch_assoc();
    $createdBy = $admin ? intval($admin['AdminID']) : null;
    $adminStmt->close();

    // Manila time
    date_default_timezone_set('Asia/Manila');
    $createdDate = date('Y-m-d H:i:s');

    $stmt = $conn->prepare("INSERT INTO election (ElectionName, StartDateTime, EndDateTime, G8RepNum, G9RepNum, G10RepNum, G11RepNum, G12RepNum, CreatedBy, CreatedDate) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('sssiiiiiss', $ElectionName, $StartDateTime, $EndDateTime, $G8RepNum, $G9RepNum, $G10RepNum, $G11RepNum, $G12RepNum, $createdBy, $createdDate);
    $stmt->execute();
    $stmt->close();
    $conn->close();
    header('Location: votingsession.php');
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add Election</title>
    <link rel="icon" type="image/png" href="/votingsystem/images/logo.png">
    <link rel="stylesheet" type="text/css" href="/votingsystem/files/maincss/main.css">
    <link rel="stylesheet" type="text/css" href="/votingsystem/files/admin/admin.css">
    <style>
        body { background: #111; color: #fff; font-family: 'Inter', sans-serif; margin: 0; }
        .edit-container { background: #222; color: #fff; max-width: 100%; width: 400px; margin: 40px auto; padding: 20px; border-radius: 10px; box-sizing: border-box; }
        label { display: block; margin-top: 15px; color: #fff; }
        input { width: 100%; padding: 8px; margin-top: 5px; border-radius: 4px; border: 1px solid #444; background: #333; color: #fff; box-sizing: border-box; }
        .form-actions { margin-top: 20px; display: flex; gap: 10px; justify-content: flex-end; flex-wrap: wrap; }
        .btn { background: #36AE66; color: #fff; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; }
        .btn-cancel { background: #888; }
        @media (max-width: 500px) {
            .edit-container { width: 95vw; padding: 10px; }
            .form-actions { flex-direction: column; gap: 8px; }
            .btn, .btn-cancel { width: 100%; }
        }
    </style>
</head>
<body>
    <div class="edit-container">
        <h2>Schedule New Election</h2>
        <form method="post">
            <label>Election Name:
                <input type="text" name="ElectionName" required>
            </label>
            <label>Start Date/Time:
                <input type="datetime-local" name="StartDateTime" required>
            </label>
            <label>End Date/Time:
                <input type="datetime-local" name="EndDateTime" required>
            </label>
            <label>G8 Rep:
                <input type="number" name="G8RepNum" value="0" min="0" required>
            </label>
            <label>G9 Rep:
                <input type="number" name="G9RepNum" value="0" min="0" required>
            </label>
            <label>G10 Rep:
                <input type="number" name="G10RepNum" value="0" min="0" required>
            </label>
            <label>G11 Rep:
                <input type="number" name="G11RepNum" value="0" min="0" required>
            </label>
            <label>G12 Rep:
                <input type="number" name="G12RepNum" value="0" min="0" required>
            </label>
            <div class="form-actions">
                <a href="votingsession.php" class="btn btn-cancel">Cancel</a>
                <button type="submit" class="btn">Add Election</button>
            </div>
        </form>
    </div>
</body>
</html>