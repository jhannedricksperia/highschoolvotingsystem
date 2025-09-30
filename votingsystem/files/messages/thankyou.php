<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: ../intro/login.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Thank You</title>
    <link rel="icon" type="image/png" href="/votingsystem/images/logo.png">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="/votingsystem/files/maincss/main.css">
    <link rel="stylesheet" type="text/css" href="/votingsystem/files/user/user.css">
    <style>
        .message-container {
            background-color: rgba(0, 0, 0, 0.75);
            padding: 40px;
            border-radius: 10px;
            margin: 20px auto;
            max-width: 600px;
            text-align: center;
            color: white;
        }

        .message-logo {
            width: 150px;
            height: 150px;
            margin: 0 auto 20px;
            background-image: url('/votingsystem/images/logo.png');
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
        }

        .message-title {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 20px;
            color: #36AE66;
        }

        .message-text {
            font-size: 18px;
            line-height: 1.5;
            margin-bottom: 30px;
        }

        .button-container {
            display: flex;
            justify-content: center;
            gap: 20px;
        }

        .button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #36AE66;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            transition: background-color 0.3s;
        }

        .button:hover {
            opacity: 0.9;
        }
    </style>
</head>
<body>
    <header>
        <div class="header">
            <div class="header-left">
                <div class="logo"></div>
                <div class="header-title">
                    <span class="main-title">StudentsVoice: Voting System</span>
                </div>
            </div>
            <div class="topnav">
                <a href="../user/home.php">Home</a>
                <div class="dropdown">
                    <a class="active">Vote</a>
                    <div class="dropdown-content">
                        <a href="../user/dataprivacy.php">Vote Now</a>  
                        <a href="../user/votereceipt.php">Vote Receipt</a>
                        <a href="../user/agendas.php">Agendas</a>
                        <a href="../user/results.php">Results</a>
                    </div>
                </div>
                <div class="dropdown">
                    <a>Account</a>
                    <div class="dropdown-content">
                        <a href="../user/accountmanagement.php">Manage</a>
                        <a href="../intro/login.php">Logout</a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="centerMain">
        <div class="message-container">
            <div class="message-logo"></div>
            <h1 class="message-title">Thank You!</h1>
            <p class="message-text">
                Your vote has been successfully recorded.<br>
                Thank you for participating in this election!
            </p>
            <div class="button-container">
                <a href="../user/votereceipt.php" class="button">View Vote Receipt</a>
                <a href="../user/home.php" class="button">Back to Home</a>
            </div>
        </div>
    </div>

    <footer>
        <div class="footer">
            <div class="footer-content">
                <div class="footer-section">
                    <div class="footer-title">StudentsVoice: Voting System</div>
                    <p>In partnership of:</p>
                    <p>Maria Estrella National High School</p>
                    <p>Guinhawa, City of Malolos, Bulacan</p>
                </div>
                <div class="footer-section">
                    <div class="footer-title">Developed by:</div>
                    <ul class="footer-list">
                        <li>Peria, Jhann Edrick S.</li>
                        <li>Moreno, Czar Serafin</li>
                        <li>Dela Pena, MJ R.</li>
                        <li>Antonio, James Ian S.</li>
                        <li>Estrella, Maria Patrisha</li>
                        <li>Miranda, Lheriza A.</li>
                        <li>Pulido, Keziah</li>
                        <li>Santiago, Jennelyn B.</li>
                    </ul>
                </div>
                <div class="footer-section">
                    <div class="footer-title">Contact us:</div>
                    <p>Pat'z Solutions</p>
                    <p>Address: Guinhawa, City of Malolos, Bulacan</p>
                    <p>Contact Number: 09121212345</p>
                </div>
                <div class="footer-section">
                    <div class="footer-title">About</div>
                    <p>ver 1.0.0.1a</p>
                    <p>© 2025</p>
                </div>
            </div>
        </div>
    </footer>
</body>
</html>
