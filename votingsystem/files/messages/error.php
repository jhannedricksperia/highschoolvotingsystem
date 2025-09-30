<?php
session_start();
error_log("=== Error Page Accessed ===");
error_log("Session contents: " . print_r($_SESSION, true));

if (!isset($_SESSION['username'])) {
    error_log("No username in session, redirecting to login");
    header("Location: ../intro/login.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Error</title>
    <link rel="icon" type="image/png" href="/votingsystem/images/logo.png">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="/votingsystem/files/maincss/main.css">
    <link rel="stylesheet" type="text/css" href="/votingsystem/files/user/user.css">
    <style>
        .message-container {
            background-color: rgba(0, 0, 0, 0.75);
            padding: 20px;
            border-radius: 10px;
            margin: 20px auto;
            max-width: 800px;
            color: white;
            text-align: center;
        }

        .message-title {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 20px;
            color: #ff4444;
        }

        .message-text {
            margin-bottom: 30px;
            color: #ccc;
        }

        .button-container {
            display: flex;
            justify-content: center;
            margin-top: 30px;
        }

        .button {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            transition: background-color 0.3s;
            background-color: #36AE66;
            color: white;
            text-decoration: none;
            display: inline-block;
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
            <h1 class="message-title">Error Processing Vote</h1>
            <p class="message-text">
                <?php 
                if (isset($_SESSION['vote_error'])) {
                    echo htmlspecialchars($_SESSION['vote_error']);
                    unset($_SESSION['vote_error']);
                } else {
                    echo "There was an error processing your vote. Please try again or contact the administrator if the problem persists.";
                }
                ?>
            </p>
            <div class="button-container">
                <a href="../user/voting.php" class="button">Return to Voting</a>
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