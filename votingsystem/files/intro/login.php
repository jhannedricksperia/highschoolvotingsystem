<?php
session_start();

// Uncomment and fix the redirect logic
if (isset($_SESSION['username']) && isset($_SESSION['accountType'])) {
    if($_SESSION['accountType'] == 'Voter') {
        header("Location: ../user/home.php");
    }
    else if($_SESSION['accountType'] == 'Admin') {
        header("Location: ../admin/home.php"); 
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="/votingsystem/images/logo.png">
    <link rel="stylesheet" type="text/css" href="/votingsystem/files/intro/styles.css">
    <title>Login</title>
</head>

<body>

<script>
    document.addEventListener("DOMContentLoaded", () => {
        const buttons = document.querySelectorAll(".toggle-buttons button");
        const voterBtn = document.getElementById('voterBtn');
        const adminBtn = document.getElementById('adminBtn');
        const accountTypeInput = document.getElementById('accountType');
        
        // Set initial state based on current selection
        let accType = accountTypeInput.value;

        buttons.forEach(button => {
            button.addEventListener("click", () => {
                buttons.forEach(btn => btn.classList.remove("active"));
                button.classList.add("active");
                accType = button.textContent; 
                accountTypeInput.value = accType;
                
                // Store the selected account type in session via AJAX
                fetch('storeAccountType.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'accountType=' + encodeURIComponent(accType)
                });
            });
        });
    });

    function validateForm() {
        const un = document.getElementById("username").value.trim();
        const password = document.getElementById("password").value;
        const unError = document.getElementById("unError");
        const passwordError = document.getElementById("passwordError");
        let isValid = true;

        // Reset error messages
        unError.innerHTML = "";
        passwordError.innerHTML = "";

        // Username validation
        if (un === "") {
            unError.innerHTML = "Username is required";
            document.getElementById("username").style.marginBottom = "0px";
            isValid = false;
        }else {
            document.getElementById("username").style.marginBottom = "15px";
        }

        // Password validation  
        if (password === "") {
            document.getElementById("password").style.marginBottom = "0px";
            passwordError.innerHTML = "Password is required";
            isValid = false;
        } else {
            document.getElementById("password").style.marginBottom = "15px";
        }

        if (isValid) {
            document.querySelector("form").submit();
        }

        return isValid;
    }

    // Password visibility toggle functionality
    document.addEventListener("DOMContentLoaded", function() {
        const togglePassword = document.getElementById('togglePassword');
        const password = document.getElementById('password');
        const passwordIcon = document.getElementById('passwordIcon');

        togglePassword.addEventListener('click', function() {
            // Toggle the type attribute
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            
            // Toggle the icon
            if (type === 'password') {
                passwordIcon.src = '/votingsystem/images/password_notsee.svg';
            } else {
                passwordIcon.src = '/votingsystem/images/password_see.svg';
            }
        });

        // Input change event listeners for error styling
        const usernameInput = document.getElementById('username');
        const passwordInput = document.getElementById('password');

        // Username input change listener
        usernameInput.addEventListener('input', function() {
            if (this.style.borderColor === 'red') {
                this.style.borderColor = '#4caf50'; // Revert to original green color
            }
        });

        // Password input change listener
        passwordInput.addEventListener('input', function() {
            if (this.style.borderColor === 'red') {
                this.style.borderColor = '#4caf50'; // Revert to original green color
            }
        });
    });
</script>

<div class="content-wrapper">
    <form action="loginvalidation.php" method="post">
        <div class="login-box">
            <div class="left-panel">
                <div class="header">
                    <img src="/votingsystem/images/logo.png" alt="School Logo" class="logo">
                    <div class="school-info">
                        <h2>StudentsVoice: Voting System</h2>
                        <p>MARIA ESTRELLA NATIONAL HIGH SCHOOL</p>
                        <p id="schoolAddress">Guinhawa, City of Malolos, Bulacan</p>
                    </div>
                </div>

                
                <div class="lalagyan">
                    <h1>Welcome</h1>
                    <div class="toggle-buttons">
                        <button type="button" id="voterBtn" class="<?php echo (!isset($_SESSION['selectedAccountType']) || $_SESSION['selectedAccountType'] == 'Voter') ? 'active' : ''; ?>">Voter</button>
                        <button type="button" id="adminBtn" class="<?php echo (isset($_SESSION['selectedAccountType']) && $_SESSION['selectedAccountType'] == 'Admin') ? 'active' : ''; ?>">Admin</button>
                    </div>
                    <input type="text" id="username" name="username" placeholder="Username" value="<?php echo isset($_SESSION['unplaceholder']) ? $_SESSION['unplaceholder'] : ''; ?>" />
                    <span id="unError" class="error" style="margin-bottom: 10px; margin-top: 1px;"><?php 
                    if(isset($_SESSION['usererror'])) {
                        echo "<script>document.getElementById('username').style.borderColor = 'red';</script>";
                        echo $_SESSION['usererror'];
                        unset($_SESSION['usererror']);
                    }
                    ?></span>
                    <div class="password-container" style="position: relative; width: 100%; max-width: 350px;">
                        <input type="password" 
                               id="password" 
                               name="password" 
                               placeholder="Password" 
                               value="<?php echo isset($_SESSION['passplaceholder']) ? $_SESSION['passplaceholder'] : ''; ?>" 
                               style="padding-right: 50px;" />

                        <button type="button" 
                                id="togglePassword" 
                                style="position: absolute; right: 15px; top: 21px; transform: translateY(-50%); 
                                       background: none; border: none; cursor: pointer; padding: 0; margin: 0;">
                            <img src="/votingsystem/images/password_notsee.svg" 
                                 id="passwordIcon" 
                                 alt="Toggle password visibility" 
                                 style="width: 16px; height: 16px; opacity: 0.7;">
                        </button>
                    </div>


                    <span id="passwordError" class="error" style="margin-bottom: 10px; margin-top: 0;"><?php 
                        if(isset($_SESSION['passerror'])) {
                            echo "<script>document.getElementById('password').style.borderColor = 'red';</script>";
                            echo $_SESSION['passerror'];
                            unset($_SESSION['passerror']);
                        }
                    ?></span>

                    <input type="hidden" id="accountType" name="accountType" value="<?php echo isset($_SESSION['selectedAccountType']) ? $_SESSION['selectedAccountType'] : 'Voter'; ?>">
                    <button type="button" onclick="validateForm()" class="btn" id="loginBtn">Login</button>
                    <a href="<?php 
                        if(isset($_SESSION['accountType']) && $_SESSION['accountType'] == 'Voter'){
                            echo 'securitykey.php';
                        }else{
                            session_destroy();
                            echo 'securitykey.php';
                        } ?>" class="forgot">Forgot password?
                    </a>
                </div>

                
            </div>

            <div class="right-panel">
                <img src="/votingsystem/images/student.jpg" alt="Students" />
            </div>
        </div>
    
    </form>

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
            <p>Address:Guinhawa, City of Malolos, Bulacan</p>
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
