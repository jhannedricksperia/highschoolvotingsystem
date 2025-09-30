<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accountType'])) {
    $_SESSION['selectedAccountType'] = $_POST['accountType'];
    echo 'success';
} else {
    echo 'error';
}
?> 