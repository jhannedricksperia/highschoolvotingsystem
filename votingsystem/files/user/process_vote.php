<?php
session_start();
error_log("=== Process Vote Page Accessed ===");
error_log("Session contents: " . print_r($_SESSION, true));

if (!isset($_SESSION['username'])) {
    error_log("No username in session, redirecting to login");
    header("Location: ../intro/login.php");
    exit();
}

// Verify the account exists
require_once '../connect/connect.php';
$username = $_SESSION['username'];
error_log("Checking account for username: " . $username);

$query = "SELECT * FROM account WHERE Username = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    error_log("Account not found in database, clearing session");
    session_destroy();
    header("Location: ../intro/login.php");
    exit();
}

$account = $result->fetch_assoc();

// Start transaction
$conn->begin_transaction();

try {
    if (!isset($_POST['election_id'])) {
        throw new Exception("Election ID is missing");
    }

    $election_id = $_POST['election_id'];
    $current_datetime = date('Y-m-d H:i:s');

    error_log("=== Processing Votes ===");
    error_log("Election ID: " . $election_id);
    error_log("Account ID: " . $account['AccountID']);
    error_log("POST data: " . print_r($_POST, true));
    error_log("Account data: " . print_r($account, true));

    // Verify election exists and is active
    $election_check = "SELECT * FROM election WHERE ElectionID = ? AND StartDateTime <= ? AND EndDateTime >= ?";
    $stmt = $conn->prepare($election_check);
    if (!$stmt) {
        throw new Exception("Failed to prepare election check: " . $conn->error);
    }
    $stmt->bind_param("sss", $election_id, $current_datetime, $current_datetime);
    $stmt->execute();
    $election_result = $stmt->get_result();
    if ($election_result->num_rows === 0) {
        throw new Exception("Invalid or inactive election");
    }

    // Check if user has already voted in this election
    $vote_check = "SELECT COUNT(*) as vote_count FROM vote WHERE AccountID = ? AND ElectionID = ?";
    $stmt = $conn->prepare($vote_check);
    if (!$stmt) {
        throw new Exception("Failed to prepare vote check: " . $conn->error);
    }
    $stmt->bind_param("is", $account['AccountID'], $election_id);
    $stmt->execute();
    $vote_result = $stmt->get_result();
    $vote_count = $vote_result->fetch_assoc()['vote_count'];
    if ($vote_count > 0) {
        throw new Exception("You have already voted in this election");
    }

    // Process votes
    if (!isset($_POST['vote']) || !is_array($_POST['vote'])) {
        throw new Exception("No valid votes found in submission");
    }

    $vote_count = 0;
    foreach ($_POST['vote'] as $position => $candidate_ids) {
        error_log("Processing position: " . $position);
        error_log("Candidate IDs: " . print_r($candidate_ids, true));

        if (is_array($candidate_ids)) {
            foreach ($candidate_ids as $candidate_id) {
                if (!empty($candidate_id)) {
                    try {
                        error_log("Attempting to insert vote for candidate ID: " . $candidate_id);

                        // Verify candidate exists and belongs to the correct election
                        $candidate_check = "SELECT * FROM candidate WHERE CandidateID = ? AND ElectionID = ?";
                        $stmt = $conn->prepare($candidate_check);
                        if (!$stmt) {
                            throw new Exception("Failed to prepare candidate check: " . $conn->error);
                        }
                        $stmt->bind_param("is", $candidate_id, $election_id);
                        $stmt->execute();
                        if ($stmt->get_result()->num_rows === 0) {
                            throw new Exception("Invalid candidate ID: " . $candidate_id);
                        }

                        $vote_query = "INSERT INTO vote (AccountID, CandidateID, ElectionID, DateTime) VALUES (?, ?, ?, ?)";
                        $stmt = $conn->prepare($vote_query);
                        if (!$stmt) {
                            throw new Exception("Failed to prepare vote insert: " . $conn->error);
                        }

                        // Ensure correct data types for the Vote table
                        $account_id = (int) $account['AccountID'];
                        $candidate_id = (int) $candidate_id;
                        $election_id = (string) $election_id;
                        $current_datetime = date('Y-m-d H:i:s'); // Ensure proper DATETIME format

                        error_log("Inserting vote with values - AccountID: $account_id, CandidateID: $candidate_id, ElectionID: $election_id, DateTime: $current_datetime");

                        $stmt->bind_param("iiss", $account_id, $candidate_id, $election_id, $current_datetime);
                        if (!$stmt->execute()) {
                            throw new Exception("Failed to insert vote: " . $stmt->error);
                        }
                        $vote_count++;
                        error_log("Successfully inserted vote for candidate: " . $candidate_id);
                    } catch (Exception $e) {
                        error_log("Error inserting vote: " . $e->getMessage());
                        throw $e;
                    }
                }
            }
        } else if (!empty($candidate_ids)) {
            try {
                error_log("Attempting to insert single vote for candidate ID: " . $candidate_ids);

                // Verify candidate exists and belongs to the correct election
                $candidate_check = "SELECT * FROM candidate WHERE CandidateID = ? AND ElectionID = ?";
                $stmt = $conn->prepare($candidate_check);
                if (!$stmt) {
                    throw new Exception("Failed to prepare candidate check: " . $conn->error);
                }
                $stmt->bind_param("is", $candidate_ids, $election_id);
                $stmt->execute();
                if ($stmt->get_result()->num_rows === 0) {
                    throw new Exception("Invalid candidate ID: " . $candidate_ids);
                }

                $vote_query = "INSERT INTO vote (AccountID, CandidateID, ElectionID, DateTime) VALUES (?, ?, ?, ?)";
                $stmt = $conn->prepare($vote_query);
                if (!$stmt) {
                    throw new Exception("Failed to prepare vote insert: " . $conn->error);
                }

                // Ensure correct data types for the Vote table
                $account_id = (int) $account['AccountID'];
                $candidate_id = (int) $candidate_ids;
                $election_id = (string) $election_id;
                $current_datetime = date('Y-m-d H:i:s'); // Ensure proper DATETIME format

                error_log("Inserting vote with values - AccountID: $account_id, CandidateID: $candidate_id, ElectionID: $election_id, DateTime: $current_datetime");

                $stmt->bind_param("iiss", $account_id, $candidate_id, $election_id, $current_datetime);
                if (!$stmt->execute()) {
                    throw new Exception("Failed to insert vote: " . $stmt->error);
                }
                $vote_count++;
                error_log("Successfully inserted vote for candidate: " . $candidate_ids);
            } catch (Exception $e) {
                error_log("Error inserting vote: " . $e->getMessage());
                throw $e;
            }
        }
    }

    if ($vote_count === 0) {
        throw new Exception("No valid votes were submitted");
    }

    // Update account vote status
    $update_query = "UPDATE account SET VoteStatus = 'VOTED' WHERE AccountID = ?";
    $stmt = $conn->prepare($update_query);
    if (!$stmt) {
        throw new Exception("Failed to prepare account update: " . $conn->error);
    }
    $stmt->bind_param("i", $account['AccountID']);
    if (!$stmt->execute()) {
        throw new Exception("Failed to update account status: " . $stmt->error);
    }

    // Commit transaction
    $conn->commit();
    error_log("Successfully processed " . $vote_count . " votes");

    // Redirect to thank you page
    header("Location: ../messages/thankyou.php");
    exit();

} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    error_log("Error processing vote: " . $e->getMessage());
    $_SESSION['vote_error'] = $e->getMessage();
    header("Location: ../messages/error.php");
    exit();
}
?>