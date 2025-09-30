<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loading...</title>
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
            animation: fadeIn 5s ease-in forwards;
            margin-bottom: 20px;
        }
        .loading-text {
            font-family: Arial, sans-serif;
            font-size: 18px;
            color: #ffffff;
            font-weight: bold;
            opacity: 0;
            animation: fadeIn 5s ease-in forwards;
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
    <div class="loading-text">Logging in...</div>
    <?php
    session_start();
    if (isset($_SESSION['accountType']) && $_SESSION['accountType'] === 'Admin') {
        $redirectUrl = '/votingsystem/files/admin/home.php';
    } else {
        $redirectUrl = '/votingsystem/files/user/home.php';
    }
    ?>
    <script>
        setTimeout(function() {
            window.location.href = '<?php echo $redirectUrl; ?>';
        }, 5000);
    </script>
</body>
</html> 