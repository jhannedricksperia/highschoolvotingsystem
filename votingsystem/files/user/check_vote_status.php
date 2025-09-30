<?php
session_start();
require_once '../connect/connect.php';

if (!isset($_SESSION['username'])) {
    header("Location: ../intro/login.php");
    exit();
}

$username = $_SESSION['username'];
date_default_timezone_set('Asia/Manila');
$current_date = date('Y-m-d H:i:s');

error_log("=== Check Vote Status Debug ===");
error_log("Current datetime: " . $current_date);


$debug_query = "SELECT * FROM Election";
$debug_result = $conn->query($debug_query);
error_log("All elections in database:");
while ($row = $debug_result->fetch_assoc()) {
    error_log("Election: " . $row['ElectionName'] .
        " Start: " . $row['StartDateTime'] .
        " End: " . $row['EndDateTime']);
}

$election_query = "SELECT * FROM Election 
                  WHERE StartDateTime <= ? 
                  AND EndDateTime >= ?";
error_log("Election Query: " . $election_query);
error_log("Parameters: " . $current_date . ", " . $current_date);

$stmt = $conn->prepare($election_query);
$stmt->bind_param("ss", $current_date, $current_date);
$stmt->execute();
$election_result = $stmt->get_result();

error_log("Number of active elections found: " . $election_result->num_rows);

if ($election_result->num_rows === 0) {
    error_log("No active election found - redirecting to noscheduledelelection.php");
    $_SESSION['error_message'] = 'There is no active election at this time.';
    header("Location: ../messages/noscheduledelelection.php");
    exit();
}

// Get account details
$account_query = "SELECT * FROM Account WHERE Username = ?";
$stmt = $conn->prepare($account_query);
$stmt->bind_param("s", $username);
$stmt->execute();
$account = $stmt->get_result()->fetch_assoc();

// Get active election details
$active_election = $election_result->fetch_assoc();
error_log("Active election found - Start: " . $active_election['StartDateTime'] . ", End: " . $active_election['EndDateTime']);

// Check for overlapping elections
$overlap_query = "SELECT * FROM Election 
                 WHERE ((StartDateTime <= ? AND EndDateTime >= ?) 
                 OR (StartDateTime <= ? AND EndDateTime >= ?) 
                 OR (StartDateTime >= ? AND EndDateTime <= ?))";
$stmt = $conn->prepare($overlap_query);
$stmt->bind_param(
    "ssssss",
    $current_date,
    $current_date,  // For current datetime overlap
    $active_election['StartDateTime'],
    $active_election['EndDateTime'],  // For election date range
    $active_election['StartDateTime'],
    $active_election['EndDateTime']   // For contained elections
);
$stmt->execute();
$overlapping_elections = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

error_log("Number of overlapping elections: " . count($overlapping_elections));

// Check if user has already voted in the active election
$voted_query = "SELECT * FROM Vote 
                WHERE AccountID = ? 
                AND ElectionID = ?";
$stmt = $conn->prepare($voted_query);
$stmt->bind_param("is", $account['AccountID'], $active_election['ElectionID']);
$stmt->execute();
$vote_result = $stmt->get_result();
$has_voted = $vote_result->num_rows > 0;

error_log("User has voted: " . ($has_voted ? "Yes" : "No"));

// Determine where to redirect
if (count($overlapping_elections) > 1) {
    error_log("Multiple overlapping elections found - redirecting to overlappingelection.php");
    $_SESSION['error_message'] = 'Multiple overlapping elections detected.';
    header("Location: ../messages/overlappingelection.php");
    exit();
} 

if ($has_voted) {
    error_log("User has already voted - redirecting to alreadyvoted.php");
    $_SESSION['error_message'] = 'You have already voted in this election.';
    header("Location: ../messages/alreadyvoted.php");
    exit();
} else {
    error_log("All checks passed - redirecting to dataprivacy.php");
    header("Location: dataprivacy.php");
    exit();
}
?>
