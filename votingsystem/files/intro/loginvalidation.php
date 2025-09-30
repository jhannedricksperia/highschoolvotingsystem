<?php
session_start();
error_log("=== Login Process Started ===");
error_log("POST data: " . print_r($_POST, true));

include '../connect/connect.php';
error_log("Database connection established");

$un = trim($_POST['username']);
$password = $_POST['password'];
$accountType = $_POST['accountType'];

error_log("Attempting login - Username: $un, Account Type: $accountType");

// Prepare SQL statement based on account type
if ($accountType === 'Voter') {
    $stmt = $conn->prepare("SELECT AccountID, Password, Name, Username FROM account WHERE Username = ?");
    error_log("Checking voter account");
} else {
    $stmt = $conn->prepare("SELECT AdminID, Password, Username, Status FROM admin WHERE Username = ?");
    error_log("Checking admin account");
}

$stmt->bind_param("s", $un);
$stmt->execute();
$stmt->store_result();

error_log("Query result rows: " . $stmt->num_rows);

if ($stmt->num_rows === 1) {
    if ($accountType === 'Voter') {
        $stmt->bind_result($accountId, $passwordFromDb, $name, $usernameFromDB);
    }
    else {
        $stmt->bind_result($adminId, $passwordFromDb, $usernameFromDB, $adminStatus);
    }

    $stmt->fetch();
    if(trim($usernameFromDB) !== $un){
        error_log("Username mismatch");
        $_SESSION['usererror'] = "User not found";
        $_SESSION['username'] = $un;
        $_SESSION['accountType'] = $accountType;
        
        // Clean up before redirect
        $stmt->free_result();
        $stmt->close();
        $conn->close();
        
        header("Location: login.php");
        exit();
    }

    if (password_verify($password, $passwordFromDb)) {
        // Check admin status if it's an admin account
        if ($accountType === 'Admin') {
            if ($adminStatus === 'BLOCKED') {
                error_log("Admin account is BLOCKED - Login denied");
                $_SESSION['usererror'] = "Account is blocked. Please contact administrator.";
                $_SESSION['unplaceholder'] = $un;
                
                // Clean up before redirect
                $stmt->free_result();
                $stmt->close();
                $conn->close();
                
                header("Location: login.php");
                exit();
            }
        }
        
        error_log("Password matches - Login successful");
        
        // Log the login time
        date_default_timezone_set('Asia/Manila');
        $loginTime = date('Y-m-d H:i:s');
        
        if ($accountType === 'Voter') {
            // Insert login record for voter (no Modified fields at creation)
            $logStmt = $conn->prepare("INSERT INTO userloggedrecord (AccountID, LoggedIn) VALUES (?, ?)");
            $logStmt->bind_param("is", $accountId, $loginTime);
        } else {
            // Insert login record for admin (no Modify fields at creation)
            $logStmt = $conn->prepare("INSERT INTO adminloggedrecord (AdminID, LoggedIn) VALUES (?, ?)");
            $logStmt->bind_param("is", $adminId, $loginTime);
        }
        
        if ($logStmt->execute()) {
            error_log("Login time logged successfully");
        } else {
            error_log("Failed to log login time: " . $conn->error);
        }
        $logStmt->close();
        
        // Success: Set session
        $_SESSION['username'] = $un;
        $_SESSION['accountType'] = $accountType;
        if ($accountType === 'Voter') {
            $_SESSION['name'] = $name;
            $_SESSION['accountId'] = $accountId;
        } else {
            $_SESSION['adminId'] = $adminId;
        }
        
        error_log("Session after setting: " . print_r($_SESSION, true));
        
        // Clean up before continuing
        $stmt->free_result();
        $stmt->close();
        $conn->close();
        
        error_log("Attempting redirect to home.php");
        if ($accountType === 'Voter') {
            header("Location: /votingsystem/files/intro/loading.php");
        } else {
            header("Location: /votingsystem/files/intro/loading.php");
        }
        exit();
    } else {
        error_log("Password mismatch");
        $_SESSION['passerror'] = "Incorrect password";
        $_SESSION['unplaceholder'] = $un;
        $_SESSION['passplaceholder'] = $password;
        
        // Clean up before redirect
        $stmt->free_result();
        $stmt->close();
        $conn->close();
        
        header("Location: login.php");
        exit();
    }
} else {
    error_log("User not found in database");
    $_SESSION['usererror'] = "User not found";
    $_SESSION['unplaceholder'] = $un;
    
    // Clean up before redirect
    $stmt->free_result();
    $stmt->close();
    $conn->close();
    
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Status</title>
</head>
<body>
    <form action="logout.php" method="post">
        <button type="submit">Logout</button>
    </form>
</body>
</html>