<?php
session_start();

include '../connect/connect.php';

$security_key = $_POST['security_key'];
$un = $_POST['username'];

// First check account table
$stmt = $conn->prepare("SELECT SecurityKey, Username, 'account' as table_type FROM account WHERE Username = ?");
$stmt->bind_param("s", $un);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 1) {
    $stmt->bind_result($securityKeyFromDB, $usernameFromDB, $tableType);
    $stmt->fetch();

    // Check if username matches exactly (case sensitive)
    if ($un !== $usernameFromDB) {
        $_SESSION['usererror'] = "User not found";
        $_SESSION['username'] = $un;
        
        $stmt->free_result();
        $stmt->close();
        $conn->close();
        
        header("Location: securitykey.php");
        exit();
    }

    if ($security_key === $securityKeyFromDB) {
        // Success: Set session
        $_SESSION['security_key'] = $security_key;
        $_SESSION['username'] = $usernameFromDB; // Use username from DB to preserve case
        $_SESSION['account_type'] = 'account'; // Store which table the user belongs to
        
        // Clean up before redirect
        $stmt->free_result();
        $stmt->close();
        $conn->close();
        
        header("Location: forgotpassword.php"); 
        exit();
    } 
    else {
        $_SESSION['keyerror'] = "Security key is invalid";
        $_SESSION['username'] = $un;
        
        // Clean up before redirect
        $stmt->free_result();
        $stmt->close();
        $conn->close();
        
        header("Location: securitykey.php");
        exit();
    }
} else {
    // If not found in account table, check admin table
    $stmt->free_result();
    $stmt->close();
    
    $stmt = $conn->prepare("SELECT SecurityKey, Username, 'admin' as table_type FROM admin WHERE Username = ?");
    $stmt->bind_param("s", $un);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows === 1) {
        $stmt->bind_result($securityKeyFromDB, $usernameFromDB, $tableType);
        $stmt->fetch();

        // Check if username matches exactly (case sensitive)
        if ($un !== $usernameFromDB) {
            $_SESSION['usererror'] = "User not found";
            $_SESSION['username'] = $un;
            
            $stmt->free_result();
            $stmt->close();
            $conn->close();
            
            header("Location: securitykey.php");
            exit();
        }

        if ($security_key === $securityKeyFromDB) {
            // Success: Set session
            $_SESSION['security_key'] = $security_key;
            $_SESSION['username'] = $usernameFromDB; // Use username from DB to preserve case
            $_SESSION['account_type'] = 'admin'; // Store which table the user belongs to
            
            // Clean up before redirect
            $stmt->free_result();
            $stmt->close();
            $conn->close();
            
            header("Location: forgotpassword.php"); 
            exit();
        } 
        else {
            $_SESSION['keyerror'] = "Security key is invalid";
            $_SESSION['username'] = $un;
            
            // Clean up before redirect
            $stmt->free_result();
            $stmt->close();
            $conn->close();
            
            header("Location: securitykey.php");
            exit();
        }
    } else {
        $_SESSION['usererror'] = "User not found";
        $_SESSION['unplaceholder'] = $un;
        
        // Clean up before redirect
        $stmt->free_result();
        $stmt->close();
        $conn->close();
        
        header("Location: securitykey.php");
        exit();
    }
}
?>
