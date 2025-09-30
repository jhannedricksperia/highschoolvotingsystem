<?php
session_start();
error_log("=== Account Process Started ===");
error_log("Session contents: " . print_r($_SESSION, true));

if (!isset($_SESSION['username'])) {
    error_log("No username in session, redirecting to login");
    header("Location: ../intro/login.php");
    exit();
}

require_once '../connect/connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_SESSION['username'];
    
    // Get form data
    $Username = $_POST['Username'];
    $Password = $_POST['Password'];



    // Check username uniqueness
    $username_check = "SELECT COUNT(*) as count FROM admin WHERE Username = ? AND Username != ?";
    $username_stmt = $conn->prepare($username_check);
    $username_stmt->bind_param("ss", $Username, $username);
    $username_stmt->execute();
    $username_result = $username_stmt->get_result();
    
    if ($username_result->fetch_assoc()['count'] > 0) {
        $_SESSION['error_message'] = "Username already exists.";
        header("Location: accountmanagement.php");
        exit();
    }

    // Get current password if new password is not provided
    if (empty($Password)) {
        $current_query = "SELECT Password FROM admin WHERE Username = ?";
        $current_stmt = $conn->prepare($current_query);
        $current_stmt->bind_param("s", $username);
        $current_stmt->execute();
        $current_result = $current_stmt->get_result();
        $Password = $current_result->fetch_assoc()['Password'];
    } else {
        $Password = password_hash($Password, PASSWORD_DEFAULT);
    }

    // Get current admin ID for ModifiedBy
    $currentAdminQuery = "SELECT AdminID FROM admin WHERE Username = ?";
    $currentAdminStmt = $conn->prepare($currentAdminQuery);
    $currentAdminStmt->bind_param("s", $username);
    $currentAdminStmt->execute();
    $currentAdminResult = $currentAdminStmt->get_result();
    $currentAdmin = $currentAdminResult->fetch_assoc();
    $modifiedBy = $currentAdmin['AdminID'];
    
    // Set Philippine timezone
    date_default_timezone_set('Asia/Manila');
    $modifiedDate = date('Y-m-d H:i:s');

    // Update account with ModifiedBy and ModifiedDate
    $update_query = "UPDATE admin SET Username = ?, Password = ?, ModifiedBy = ?, ModifiedDate = ? WHERE Username = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("sssss", $Username, $Password, $modifiedBy, $modifiedDate, $username);
    
    if ($update_stmt->execute()) {
        $_SESSION['success_message'] = "Account updated successfully!";
        $_SESSION['username'] = $Username; // Update session username if changed
    } else {
        $_SESSION['error_message'] = "Error updating account.";
    }
}

header("Location: accountmanagement.php");
exit();
?> 