<?php
session_start();
error_log("=== Account Management Page Accessed ===");
error_log("Session contents: " . print_r($_SESSION, true));

if (!isset($_SESSION['username'])) {
    error_log("No username in session, redirecting to login");
    header("Location: ../intro/login.php");
    exit();
}

require_once '../connect/connect.php';
$username = $_SESSION['username'];
error_log("Checking account for username: " . $username);

$query = "SELECT * FROM account WHERE Username = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$account = $result->fetch_assoc();

if (!$account) {
    error_log("Account not found in database, redirecting to home");
    header("Location: home.php");
    exit();
}

error_log("Account verified, proceeding to display account management page");
?>

<!DOCTYPE html>
<html>

<head>
    <title>Account Management</title>
    <link rel="icon" type="image/png" href="/votingsystem/images/logo.png">
    <link rel="stylesheet" type="text/css" href="/votingsystem/files/maincss/main.css">
    <link rel="stylesheet" type="text/css" href="/votingsystem/files/user/user.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

        .form-center-content {
            background-color: rgba(0, 0, 0, 0.75);
            padding: 2rem;
            border-radius: 10px;
            margin: 2rem auto;
            max-width: 800px;
            color: #fff;
            font-family: 'Inter', sans-serif;
        }

        .account-form {
            position: relative;
        }

        .account-form input[type="text"],
        .account-form input[type="password"],
        .account-form select {
            width: 100%;
            padding: 10px;
            margin: 5px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
            background-color: rgba(255, 255, 255, 0.9);
            color: #333;
            box-sizing: border-box;
            height: 45px;
            font-family: 'Inter', sans-serif;
            font-size: 14px;
        }

        .account-form select {
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 10px center;
            background-size: 1em;
            padding-right: 30px;
        }

        .account-form select:focus {
            outline: none;
            border-color: #36AE66;
            box-shadow: 0 0 0 2px rgba(54, 174, 102, 0.2);
        }

        .account-form label {
            color: #fff;
            font-weight: 600;
            margin-bottom: 5px;
            display: block;
            font-family: 'Inter', sans-serif;
            font-size: 14px;
        }

        .account-form .form-text {
            color: #ccc;
            font-size: 13px;
            margin-top: 5px;
            font-family: 'Inter', sans-serif;
        }

        .account-form .form-text ul {
            margin-left: 20px;
            margin-top: 5px;
        }

        .account-form .form-text li {
            margin-bottom: 3px;
        }

        .btn-update {
            background: #36AE66;
            color: white;
            border: none;
            border-radius: 8px;
            padding: 14px 32px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(54, 174, 102, 0.2);
            height: 50px;
            width: 160px;
            display: inline-block;
            font-family: 'Inter', sans-serif;
        }

        .btn-update:hover {
            background-color: #45a049;
        }

        .alert {
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 24px;
            font-size: 15px;
            display: flex;
            align-items: center;
            gap: 12px;
            clear: both;
            font-family: 'Inter', sans-serif;
        }

        .alert.success {
            background: rgba(54, 174, 102, 0.1);
            border: 1px solid #36AE66;
            color: #36AE66;
        }

        .alert.error {
            background: rgba(255, 59, 48, 0.1);
            border: 1px solid #ff3b30;
            color: #ff3b30;
        }

        .alert::before {
            content: '';
            display: inline-block;
            width: 20px;
            height: 20px;
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
        }

        .alert.success::before {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%2336AE66'%3E%3Cpath d='M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z'/%3E%3C/svg%3E");
        }

        .alert.error::before {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%23ff3b30'%3E%3Cpath d='M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z'/%3E%3C/svg%3E");
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 20px;
            margin-top: 20px;
            width: 100%;
        }

        .btn-back {
            background: #6c757d;
            color: white;
            border: none;
            border-radius: 8px;
            padding: 14px 32px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(108, 117, 125, 0.2);
            height: 50px;
            width: 160px;
            display: inline-block;
            font-family: 'Inter', sans-serif;
        }

        .btn-back:hover {
            background: #5a6268;
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(108, 117, 125, 0.3);
        }

        .btn-back:active {
            transform: translateY(0);
            box-shadow: 0 2px 8px rgba(108, 117, 125, 0.2);
        }

        @media (max-width: 768px) {
            .form-actions {
                flex-direction: row;
                justify-content: center;
                gap: 10px;
            }

            .btn-back,
            .btn-update {
                width: 140px;
                padding: 12px 20px;
            }
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
                <form action="home.php" method="post" style="display:inline;"><button type="submit" class="topnav-link">Home</button></form>
                <div class="dropdown">
                    <button type="button" class="topnav-link">Vote</button>
                    <div class="dropdown-content">
                        <form action="check_vote_status.php" method="post" style="display:inline;"><button type="submit"
                                class="dropdown-link">Vote Now</button></form>
                        <form action="votereceipt.php" method="post" style="display:inline;"><button type="submit"
                                class="dropdown-link">Vote Receipt</button></form>
                        <form action="agendas.php" method="post" style="display:inline;"><button type="submit"
                                class="dropdown-link">Agendas</button></form>
                        <form action="results.php" method="post" style="display:inline;"><button type="submit"
                                class="dropdown-link">Results</button></form>
                    </div>
                </div>
                <div class="dropdown">
                    <button type="button" class="topnav-link active">Account</button>
                    <div class="dropdown-content">
                        <form action="accountmanagement.php" method="post" style="display:inline;"><button type="submit"
                                class="dropdown-link">Manage</button></form>
                        <form action="/votingsystem/files/intro/logout.php" method="post"><button type="submit"
                                class="dropdown-link">Logout</button></form>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="centerMain">
        <div class="form-center-content">
            <form method="POST" action="account_process.php" class="account-form">
                <h2 style="text-align: center;">Account Management</h2><br>

                <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="alert error"><?php echo $_SESSION['error_message'];
                    unset($_SESSION['error_message']); ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="alert success">
                        <?php echo $_SESSION['success_message'];
                        unset($_SESSION['success_message']); ?>
                    </div>
                <?php endif; ?>

                <div>
                    <label for="AccountID">Account ID</label>
                    <input type="text" id="AccountID" name="AccountID"
                        value="<?php echo htmlspecialchars($account['AccountID']); ?>" disabled>
                </div><br>

                <div>
                    <label for="Name">Name</label>
                    <input type="text" id="Name" name="Name" value="<?php echo htmlspecialchars($account['Name']); ?>"
                        required>
                </div><br>

                <div>
                    <label for="GradeLevel">Grade Level</label>
                    <select id="GradeLevel" name="GradeLevel" required>
                        <option value="">Select Grade Level</option>
                        <?php
                        $grade_levels = ['7', '8', '9', '10', '11', '12'];
                        foreach ($grade_levels as $grade) {
                            $selected = $account['GradeLevel'] === $grade ? 'selected' : '';
                            echo "<option value='$grade' $selected>$grade</option>";
                        }
                        ?>
                    </select>
                </div><br>

                <div>
                    <label for="Username">Username</label>
                    <input type="text" id="Username" name="Username"
                        value="<?php echo htmlspecialchars($account['Username']); ?>" required>
                </div><br>

                <div>
                    <label for="Password">Password</label>
                    <input type="password" id="Password" name="Password"
                        placeholder="Enter new password (leave blank to keep current)">
                    <small class="form-text"><br>
                        <p>Password must meet the following requirements:</p>
                        <ul>
                            <li>At least 8 characters long</li>
                            <li>Contains at least one uppercase letter</li>
                            <li>Contains at least one lowercase letter</li>
                            <li>Contains at least one number</li>
                            <li>Contains at least one special character</li>
                        </ul>
                    </small>
                </div>

                <div class="form-actions">
                    <button type="button" onclick="window.location.href='home.php'" class="btn-back">Back</button>
                    <button type="submit" class="btn-update">Update</button>
                </div>
            </form>
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