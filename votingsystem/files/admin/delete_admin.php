<?php
session_start();
require_once '../connect/connect.php';

if (!isset($_GET['id'])) {
    $_SESSION['snap_error'] = "Invalid admin ID provided.";
    header('Location: adminmanagement.php');
    exit();
}

$adminId = intval($_GET['id']);

try {
    $stmt = $conn->prepare("DELETE FROM admin WHERE AdminID=?");
    $stmt->bind_param('i', $adminId);
    $result = $stmt->execute();
    $stmt->close();
    
    if ($result) {
        $_SESSION['snap_success'] = "Admin deleted successfully.";
    } else {
        $_SESSION['snap_error'] = "Failed to delete admin. Please try again.";
    }
} catch (Exception $e) {
    $_SESSION['snap_error'] = "Error deleting admin: " . $e->getMessage();
}

$conn->close();
header('Location: adminmanagement.php');
exit();
?> 