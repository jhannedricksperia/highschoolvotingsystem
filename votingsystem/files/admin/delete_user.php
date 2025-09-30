<?php
session_start();
require_once '../connect/connect.php';

if (!isset($_GET['id'])) {
    $_SESSION['snap_error'] = "Invalid user ID provided.";
    header('Location: usermanagement.php');
    exit();
}

$accountId = intval($_GET['id']);

try {
    $stmt = $conn->prepare("DELETE FROM account WHERE AccountID=?");
    $stmt->bind_param('i', $accountId);
    $result = $stmt->execute();
    $stmt->close();
    
    if ($result) {
        $_SESSION['snap_success'] = "User deleted successfully.";
    } else {
        $_SESSION['snap_error'] = "Failed to delete user. Please try again.";
    }
} catch (Exception $e) {
    $_SESSION['snap_error'] = "Error deleting user: " . $e->getMessage();
}

$conn->close();
header('Location: usermanagement.php');
exit();
?> 