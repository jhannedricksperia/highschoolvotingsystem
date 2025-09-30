<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: ../intro/login.php");
    exit();
}

require_once '../connect/connect.php';

// Ensure the user is an admin
$username = $_SESSION['username'];
$stmt = $conn->prepare("SELECT AdminID FROM admin WHERE Username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows === 0) {
    header("Location: ../intro/login.php");
    exit();
}

$loggedId = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($loggedId <= 0) {
    header("Location: userloggedmanagement.php?error=invalid_id");
    exit();
}

$del = $conn->prepare("DELETE FROM userloggedrecord WHERE LoggedID = ?");
$del->bind_param("i", $loggedId);
if ($del->execute()) {
    header("Location: userloggedmanagement.php?success=1");
} else {
    header("Location: userloggedmanagement.php?error=delete_failed");
}
exit();