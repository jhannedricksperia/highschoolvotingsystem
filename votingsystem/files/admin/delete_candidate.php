<?php
session_start();
require_once '../connect/connect.php';

if (!isset($_GET['id'])) {
    $_SESSION['snap_error'] = "Invalid candidate ID provided.";
    header('Location: candidatemanagement.php');
    exit();
}

$candidateId = intval($_GET['id']);

try {
    // Delete agendas first
    $stmt = $conn->prepare("DELETE FROM agenda WHERE CandidateID=?");
    $stmt->bind_param('i', $candidateId);
    $stmt->execute();
    $stmt->close();

    // Delete candidate
    $stmt = $conn->prepare("DELETE FROM candidate WHERE CandidateID=?");
    $stmt->bind_param('i', $candidateId);
    $result = $stmt->execute();
    $stmt->close();
    
    if ($result) {
        $_SESSION['snap_success'] = "Candidate and all associated agendas deleted successfully.";
    } else {
        $_SESSION['snap_error'] = "Failed to delete candidate. Please try again.";
    }
} catch (Exception $e) {
    $_SESSION['snap_error'] = "Error deleting candidate: " . $e->getMessage();
}

$conn->close();
header('Location: candidatemanagement.php');
exit();
?>