<?php 
session_start();

include '../connect/connect.php';

$un = $_SESSION['username'];
$security_key = $_SESSION['security_key'];
$account_type = $_SESSION['account_type']; // Get which table the user belongs to
$newPassword = $_POST['newPassword'];
$confirmPassword = $_POST['confirmPassword'];

// Determine which table to query based on account_type
if ($account_type === 'admin') {
    // Get the old password from admin table
    $stmt = $conn->prepare("SELECT Password FROM admin WHERE Username = ? AND SecurityKey = ?");
    $stmt->bind_param("ss", $un, $security_key);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $stmt->bind_result($oldPassword);
        $stmt->fetch();
        
        if (password_verify($newPassword, $oldPassword)) {
            $_SESSION['newPassword'] = $newPassword;
            $_SESSION['confirmPassword'] = $confirmPassword;
            $_SESSION['passerror'] = "Cannot use old password";
            header("Location: forgotpassword.php");
            exit();
        }

        // Hash the new password
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        $updateStmt = $conn->prepare("UPDATE admin SET Password = ? WHERE Username = ? AND SecurityKey = ?");
        $updateStmt->bind_param("sss", $hashedPassword, $un, $security_key);
        $updateStmt->execute();
        $updateStmt->close();

        $stmt->free_result();
        $stmt->close();
        
        $conn->close();

        // Show success message before redirecting
        echo "<script>
            alert('Password successfully changed!');
            window.location.href = 'login.php';
        </script>";

        session_destroy();
        exit();
    }
} else {
    // Default to account table (for backward compatibility)
    // Get the old password from account table
    $stmt = $conn->prepare("SELECT Password FROM account WHERE Username = ? AND SecurityKey = ?");
    $stmt->bind_param("ss", $un, $security_key);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $stmt->bind_result($oldPassword);
        $stmt->fetch();
        
        if (password_verify($newPassword, $oldPassword)) {
            $_SESSION['newPassword'] = $newPassword;
            $_SESSION['confirmPassword'] = $confirmPassword;
            $_SESSION['passerror'] = "Cannot use old password";
            header("Location: forgotpassword.php");
            exit();
        }

        // Hash the new password
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        $updateStmt = $conn->prepare("UPDATE account SET Password = ? WHERE Username = ? AND SecurityKey = ?");
        $updateStmt->bind_param("sss", $hashedPassword, $un, $security_key);
        $updateStmt->execute();
        $updateStmt->close();

        $stmt->free_result();
        $stmt->close();
        
        $conn->close();

        // Show success message before redirecting
        echo "<script>
            alert('Password successfully changed!');
            window.location.href = 'login.php';
        </script>";

        session_destroy();
        exit();
    }
}

// Clear stored result and close statement if we reach here
$stmt->free_result();
$stmt->close();
$conn->close();
?>
