<?php
session_start();
require_once '../connect/connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agenda_id'])) {
    $agendaId = intval($_POST['agenda_id']);
    
    // Get the candidate ID before deleting the agenda
    $candidateId = null;
    $candidateQuery = "SELECT CandidateID FROM agenda WHERE AgendaID = ?";
    $candidateStmt = $conn->prepare($candidateQuery);
    $candidateStmt->bind_param('i', $agendaId);
    $candidateStmt->execute();
    $candidateResult = $candidateStmt->get_result();
    if ($candidateResult->num_rows > 0) {
        $candidateRow = $candidateResult->fetch_assoc();
        $candidateId = $candidateRow['CandidateID'];
    }
    $candidateStmt->close();
    
    if ($candidateId) {
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
        
        // Delete the agenda
        $stmt = $conn->prepare("DELETE FROM agenda WHERE AgendaID=?");
        $stmt->bind_param('i', $agendaId);
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