<?php
session_start();
$_SESSION = array(); // Clear all session variables
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
session_destroy(); // Destroy the session
?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logging out...</title>
    <link rel="icon" type="image/png" href="/votingsystem/images/logo.png">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: url('/votingsystem/images/mainBackground.png') center center no-repeat;
            background-size: cover;
            font-family: 'Inter', Arial, sans-serif;
        }
        .logo {
            width: 150px;
            height: auto;
            opacity: 0;
            animation: fadeIn 2s ease-in forwards;
            margin-bottom: 20px;
        }
        .loading-text {
            font-family: Arial, sans-serif;
            font-size: 18px;
            color: #ffffff;
            font-weight: bold;
            opacity: 0;
            animation: fadeIn 2s ease-in forwards;
        }
        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }
    </style>
</head>
<body>
    <img src="/votingsystem/images/logo.png" alt="School Logo" class="logo">
    <div class="loading-text">Logging out...</div>
    <script>
        setTimeout(function() {
            window.location.href = 'login.php';
        }, 2000);
    </script>
</body>
</html> 