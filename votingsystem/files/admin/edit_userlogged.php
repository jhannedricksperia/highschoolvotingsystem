<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: ../intro/login.php");
    exit();
}

require_once '../connect/connect.php';

$loggedId = isset($_GET['id']) ? $_GET['id'] : 0;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $loggedIn = $_POST['loggedIn'];
    $loggedOut = $_POST['loggedOut'];
    
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
    
    // Update the logged record
    $updateQuery = "UPDATE userloggedrecord SET LoggedIn = ?, LoggedOut = ?, ModifiedBy = ?, ModifiedDate = ? WHERE LoggedID = ?";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param("ssssi", $loggedIn, $loggedOut, $modifiedBy, $modifiedDate, $loggedId);
    
    if ($updateStmt->execute()) {
        header("Location: userloggedmanagement.php?success=1");
        exit();
    } else {
        $error = "Error updating logged record: " . $conn->error;
    }
}

// Fetch logged record data
$query = "SELECT ul.*, ac.Username AS AccountUsername 
          FROM userloggedrecord ul
          LEFT JOIN account ac ON ul.AccountID = ac.AccountID
          WHERE ul.LoggedID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $loggedId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: userloggedmanagement.php");
    exit();
}

$loggedRecord = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit User Logged Record</title>
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
        .error-message { color: #f44336; margin-bottom: 20px; padding: 10px; background: rgba(244, 67, 54, 0.1); border-radius: 4px; }
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
                    <div class="dropdown-submenu">
                        <a class="active">Logged Management ▸</a>
                        <div class="dropdown-subcontent">
                          <a href="adminloggedmanagement.php">Admin</a>
                          <a href="userloggedmanagement.php">User</a>
                        </div>
                      </div>
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
        <h2>Edit User Logged Record</h2>
        
        <?php if (isset($error)): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <label>Logged ID:
                <input type="text" value="<?php echo htmlspecialchars($loggedRecord['LoggedID']); ?>" disabled>
            </label>
            
            <label>Account Username:
                <input type="text" value="<?php echo htmlspecialchars($loggedRecord['AccountUsername'] ?? 'N/A'); ?>" disabled>
            </label>
            
            <label>Logged In:
                <input type="datetime-local" name="loggedIn" value="<?php echo $loggedRecord['LoggedIn'] ? date('Y-m-d\TH:i', strtotime($loggedRecord['LoggedIn'])) : ''; ?>" required>
            </label>
            
            <label>Logged Out:
                <input type="datetime-local" name="loggedOut" value="<?php echo $loggedRecord['LoggedOut'] ? date('Y-m-d\TH:i', strtotime($loggedRecord['LoggedOut'])) : ''; ?>">
                <small style="color: #888; font-size: 12px;">Leave empty if still logged in</small>
            </label>
            
            <div class="form-actions">
                <a href="userloggedmanagement.php" class="btn btn-cancel">Cancel</a>
                <button type="submit" class="btn">Save Changes</button>
            </div>
        </form>
    </div>
</body>
</html> 