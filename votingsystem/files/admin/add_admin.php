<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: ../intro/login.php");
    exit();
}

require_once '../connect/connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $securityKey = $_POST['securityKey'];
    $status = $_POST['status'];
    
    // Password validation
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
    } else {
        // Check if username already exists
        $checkQuery = "SELECT * FROM admin WHERE Username = ?";
        $checkStmt = $conn->prepare($checkQuery);
        $checkStmt->bind_param("s", $username);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        
        if ($checkResult->num_rows > 0) {
            $error = "Username already exists!";
        } else {
            // Hash the password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            // Get current admin ID for CreatedBy
            $currentAdminQuery = "SELECT AdminID FROM admin WHERE Username = ?";
            $currentAdminStmt = $conn->prepare($currentAdminQuery);
            $currentAdminStmt->bind_param("s", $_SESSION['username']);
            $currentAdminStmt->execute();
            $currentAdminResult = $currentAdminStmt->get_result();
            $currentAdmin = $currentAdminResult->fetch_assoc();
            $createdBy = $currentAdmin['AdminID'];
            
            // Set Philippine timezone
            date_default_timezone_set('Asia/Manila');
            $createdDate = date('Y-m-d H:i:s');
            
            // Insert new admin with all fields
            $insertQuery = "INSERT INTO admin (Username, Password, SecurityKey, Status, CreatedBy, CreatedDate) VALUES (?, ?, ?, ?, ?, ?)";
            $insertStmt = $conn->prepare($insertQuery);
            $insertStmt->bind_param("ssssss", $username, $hashedPassword, $securityKey, $status, $createdBy, $createdDate);
            
            if ($insertStmt->execute()) {
                header("Location: adminmanagement.php?success=1");
                exit();
            } else {
                $error = "Error adding admin: " . $conn->error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add New Admin</title>
    <link rel="icon" type="image/png" href="/votingsystem/images/logo.png">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
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

    <div class="add-container">
        <h2>Add New Admin</h2>
        
        <?php if (isset($error)): ?>
            <div class="error-message" style="color: #f44336; margin-bottom: 20px; padding: 10px; background: rgba(244, 67, 54, 0.1); border-radius: 4px;"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <label>Username:
                <input type="text" name="username" required>
            </label>
            
            <label>Password:
                <input type="password" name="password" required>
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
                <input type="text" name="securityKey" required>
            </label>
            
            <label>Status:
                <select name="status" required>
                    <option value="Active">Active</option>
                    <option value="Blocked">Blocked</option>
                </select>
            </label>
            
            <div class="form-actions">
                <a href="adminmanagement.php" class="btn btn-cancel">Cancel</a>
                <button type="submit" class="btn">Add Admin</button>
            </div>
        </form>
    </div>
</body>
</html> 