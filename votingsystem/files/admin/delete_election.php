<?php
session_start();
require_once '../connect/connect.php';

if (!isset($_GET['id'])) {
    $_SESSION['snap_error'] = "Invalid election ID provided.";
    header('Location: votingsession.php');
    exit();
}

$electionId = $_GET['id'];

try {
    // Delete votes associated with candidates in this election
    $stmt = $conn->prepare("DELETE FROM vote WHERE CandidateID IN (SELECT CandidateID FROM candidate WHERE ElectionID=?)");
    $stmt->bind_param('s', $electionId);
    $stmt->execute();
    $stmt->close();

    // Delete candidates associated with this election
    $stmt = $conn->prepare("DELETE FROM candidate WHERE ElectionID=?");
    $stmt->bind_param('s', $electionId);
    $stmt->execute();
    $stmt->close();

    // Delete the election
    $stmt = $conn->prepare("DELETE FROM election WHERE ElectionID=?");
    $stmt->bind_param('s', $electionId);
    $result = $stmt->execute();
    $stmt->close();
    
    if ($result) {
        $_SESSION['snap_success'] = "Election and all associated candidates and votes deleted successfully.";
    } else {
        $_SESSION['snap_error'] = "Failed to delete election. Please try again.";
    }
} catch (Exception $e) {
    $_SESSION['snap_error'] = "Error deleting election: " . $e->getMessage();
}

$conn->close();
header('Location: votingsession.php');
exit();
?>