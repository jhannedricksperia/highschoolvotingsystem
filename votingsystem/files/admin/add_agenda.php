<?php
session_start();
require_once '../connect/connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['candidate_id'], $_POST['agenda'])) {
    $candidateId = intval($_POST['candidate_id']);
    $agenda = trim($_POST['agenda']);
    
    if ($agenda !== '') {
        // Set timezone to Manila
        date_default_timezone_set('Asia/Manila');
        $currentDateTime = date('Y-m-d H:i:s');
        
        // Get the logged-in admin's AdminID
        $adminId = null;
        if (isset($_SESSION['username'])) {
            $username = $_SESSION['username'];
            $adminQuery = "SELECT AdminID FROM admin WHERE Username = ?";
            $adminStmt = $conn->prepare($adminQuery);
            $adminStmt->bind_param('s', $username);
            $adminStmt->execute();
            $adminResult = $adminStmt->get_result();
            if ($adminResult->num_rows > 0) {
                $adminRow = $adminResult->fetch_assoc();
                $adminId = $adminRow['AdminID'];
            }
            $adminStmt->close();
        }
        
        // Insert the agenda
        $stmt = $conn->prepare("INSERT INTO agenda (CandidateID, Agenda) VALUES (?, ?)");
        $stmt->bind_param('is', $candidateId, $agenda);
        $stmt->execute();
        $stmt->close();
        
        // Update candidate's audit fields
        if ($adminId) {
            $updateStmt = $conn->prepare("UPDATE candidate SET ModifiedBy = ?, ModifiedDate = ? WHERE CandidateID = ?");
            $updateStmt->bind_param('isi', $adminId, $currentDateTime, $candidateId);
            $updateStmt->execute();
            $updateStmt->close();
        }
    }
}
$conn->close();
echo "success";
?>