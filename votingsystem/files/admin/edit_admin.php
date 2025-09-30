<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: ../intro/login.php");
    exit();
}

require_once '../connect/connect.php';

$adminId = isset($_GET['id']) ? $_GET['id'] : 0;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $securityKey = $_POST['securityKey'];
    $status = $_POST['status'];
    
    // Password validation (only if password is provided)
    if (!empty($password)) {
        $passwordErrors = [];
        if (strlen($password) < 8) {
            $passwordErrors[] = "Password must be at least 8 characters long";
        }
        if (!preg_match('/[a-z]/', $password)) {
            $passwordErrors[] = "Password must contain at least one lowercase letter";
        }
        if (!preg_match('/[A-Z]/', $password)) {
            $passwordErrors[] = "Password must contain at least one uppercase letter";
        }
        if (!preg_match('/[0-9]/', $password)) {
            $passwordErrors[] = "Password must contain at least one number";
        }
        if (!preg_match('/[^a-zA-Z0-9]/', $password)) {
            $passwordErrors[] = "Password must contain at least one symbol";
        }
        
        if (!empty($passwordErrors)) {
            $error = "Password requirements not met:<br>" . implode("<br>", $passwordErrors);
        }
    }
    
    if (!isset($error)) {
        // Check if username already exists (excluding current admin)
        $checkQuery = "SELECT * FROM admin WHERE Username = ? AND AdminID != ?";
        $checkStmt = $conn->prepare($checkQuery);
        $checkStmt->bind_param("si", $username, $adminId);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        
        if ($checkResult->num_rows > 0) {
            $error = "Username already exists!";
        } else {
            // Get current admin ID for ModifiedBy
            $currentAdminQuery = "SELECT AdminID FROM admin WHERE Username = ?";
        $currentAdminStmt = $conn->prepare($currentAdminQuery);
        $currentAdminStmt->bind_param("s", $_SESSION['username']);
        $currentAdminStmt->execute();
        $currentAdminResult = $currentAdminStmt->get_result();
        $currentAdmin = $currentAdminResult->fetch_assoc();
        $modifiedBy = $currentAdmin['AdminID'];
        
        // Set Philippine timezone
        date_default_timezone_set('Asia/Manila');
        $modifiedDate = date('Y-m-d H:i:s');
        
        if (!empty($password)) {
            // Update with new password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $updateQuery = "UPDATE admin SET Username = ?, Password = ?, SecurityKey = ?, Status = ?, ModifiedBy = ?, ModifiedDate = ? WHERE AdminID = ?";
            $updateStmt = $conn->prepare($updateQuery);
            $updateStmt->bind_param("ssssssi", $username, $hashedPassword, $securityKey, $status, $modifiedBy, $modifiedDate, $adminId);
        } else {
            // Update without changing password
            $updateQuery = "UPDATE admin SET Username = ?, SecurityKey = ?, Status = ?, ModifiedBy = ?, ModifiedDate = ? WHERE AdminID = ?";
            $updateStmt = $conn->prepare($updateQuery);
            $updateStmt->bind_param("sssssi", $username, $securityKey, $status, $modifiedBy, $modifiedDate, $adminId);
        }
        
        if ($updateStmt->execute()) {
            header("Location: adminmanagement.php?success=2");
            exit();
        } else {
            $error = "Error updating admin: " . $conn->error;
        }
        }
    }
}

// Fetch admin data
$query = "SELECT * FROM admin WHERE AdminID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $adminId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: adminmanagement.php");
    exit();
}

$admin = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Admin</title>
    <link rel="icon" type="image/png" href="/votingsystem/images/logo.png">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
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
    <header>
        <div class="header">
            <div class="header-left">
                <div class="logo"></div>
                <div class="header-title">
                    <span class="main-title">StudentsVoice: Voting System</span>
                </div>
            </div>
            <div class="topnav">
                <a href="home.php">Home</a>
                <a href="generatereport.php">Report</a>
                <div class="dropdown">
                    <a class="active">Management</a>
                    <div class="dropdown-content">
                        <a href="votes.php">Votes</a>
                        <a href="usermanagement.php">User Management</a>  
                        <a href="adminmanagement.php">Admin Management</a>
                        <a href="candidatemanagement.php">Candidate Management</a>
                        <a href="votingsession.php">Voting Session</a>
                        <a href="votingresults.php">Voting Results</a>
                    </div>
                </div>
                <div class="dropdown">
                    <a>Account</a>
                    <div class="dropdown-content">
                        <a href="accountmanagement.php">Manage</a>
                        <a href="/votingsystem/files/intro/logout.php">Logout</a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="edit-container">
        <h2>Edit Admin</h2>
        
        <?php if (isset($error)): ?>
            <div class="error-message" style="color: #f44336; margin-bottom: 20px; padding: 10px; background: rgba(244, 67, 54, 0.1); border-radius: 4px;"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <label>Username:
                <input type="text" name="username" value="<?php echo htmlspecialchars($admin['Username']); ?>" required>
            </label>
            
            <label>New Password: (leave blank to keep current)
                <input type="password" name="password">
                <div style="font-size: 12px; color: #888; margin-top: 5px; line-height: 1.4;">
                    Password must contain:<br>
                    • At least 8 characters<br>
                    • At least one lowercase letter (a-z)<br>
                    • At least one uppercase letter (A-Z)<br>
                    • At least one number (0-9)<br>
                    • At least one symbol (!@#$%^&* etc.)
                </div>
            </label>
            
            <label>Security Key:
                <input type="text" name="securityKey" value="<?php echo htmlspecialchars($admin['SecurityKey']); ?>" required>
            </label>
            
            <label>Status:
                <select name="status" required>
                    <option value="ACTIVE" <?php echo ($admin['Status'] == 'ACTIVE') ? 'selected' : ''; ?>>ACTIVE</option>
                    <option value="BLOCKED" <?php echo ($admin['Status'] == 'BLOCKED') ? 'selected' : ''; ?>>BLOCKED</option>
                </select>
            </label>
            
            <div class="form-actions">
                <a href="adminmanagement.php" class="btn btn-cancel">Cancel</a>
                <button type="submit" class="btn">Save Changes</button>
            </div>
        </form>
    </div>
</body>
</html> 