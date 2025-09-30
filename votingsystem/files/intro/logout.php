<?php
session_start();

// Log logout time before clearing session
if (isset($_SESSION['username']) && isset($_SESSION['accountType'])) {
	include '../connect/connect.php';
	
	date_default_timezone_set('Asia/Manila');
	$logoutTime = date('Y-m-d H:i:s');
	
	if ($_SESSION['accountType'] === 'Voter' && isset($_SESSION['accountId'])) {
		// Find latest open record for this voter
		$selectStmt = $conn->prepare("SELECT LoggedID FROM userloggedrecord WHERE AccountID = ? AND LoggedOut IS NULL ORDER BY LoggedID DESC LIMIT 1");
		$selectStmt->bind_param("i", $_SESSION['accountId']);
		$selectStmt->execute();
		$selectStmt->bind_result($latestLoggedId);
		if ($selectStmt->fetch()) {
			$selectStmt->close();
			// Update that specific record
			$updateStmt = $conn->prepare("UPDATE userloggedrecord SET LoggedOut = ? WHERE LoggedID = ?");
			$updateStmt->bind_param("si", $logoutTime, $latestLoggedId);
			if ($updateStmt->execute()) {
				error_log("Logout time logged successfully for voter " . $_SESSION['username']);
			} else {
				error_log("Failed to log logout time (voter): " . $conn->error);
			}
			$updateStmt->close();
		} else {
			$selectStmt->close();
			error_log("No open userloggedrecord found to update for voter " . $_SESSION['username']);
		}
	} elseif ($_SESSION['accountType'] === 'Admin' && isset($_SESSION['adminId'])) {
		// Find latest open record for this admin
		$selectStmt = $conn->prepare("SELECT LoggedID FROM adminloggedrecord WHERE AdminID = ? AND LoggedOut IS NULL ORDER BY LoggedID DESC LIMIT 1");
		$selectStmt->bind_param("i", $_SESSION['adminId']);
		$selectStmt->execute();
		$selectStmt->bind_result($latestLoggedId);
		if ($selectStmt->fetch()) {
			$selectStmt->close();
			// Update that specific record
			$updateStmt = $conn->prepare("UPDATE adminloggedrecord SET LoggedOut = ? WHERE LoggedID = ?");
			$updateStmt->bind_param("si", $logoutTime, $latestLoggedId);
			if ($updateStmt->execute()) {
				error_log("Logout time logged successfully for admin " . $_SESSION['username']);
			} else {
				error_log("Failed to log logout time (admin): " . $conn->error);
			}
			$updateStmt->close();
		} else {
			$selectStmt->close();
			error_log("No open adminloggedrecord found to update for admin " . $_SESSION['username']);
		}
	}
	
	$conn->close();
}

$_SESSION = array(); // Clear all session variables
if (ini_get("session.use_cookies")) {
	$params = session_get_cookie_params();
	setcookie(session_name(), '', time() - 42000,
		$params["path"], $params["domain"],
		$params["secure"], $params["httponly"]
	);
}
session_destroy(); // Destroy the session
header("Location: logoutloading.php"); // Redirect to loading screen first
exit();
?>
