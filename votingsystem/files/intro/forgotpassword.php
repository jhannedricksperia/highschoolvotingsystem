<?php 
session_start();

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

  buttons.forEach(button => {
    button.addEventListener("click", () => {
      buttons.forEach(btn => btn.classList.remove("active"));
      button.classList.add("active");
    });
  });
  
  // Password toggle functionality
  const toggleNewPassword = document.getElementById('toggleNewPassword');
  const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
  const newPasswordInput = document.getElementById('newPassword');
  const confirmPasswordInput = document.getElementById('confirmPassword');
  const newPasswordIcon = document.getElementById('newPasswordIcon');
  const confirmPasswordIcon = document.getElementById('confirmPasswordIcon');

  toggleNewPassword.addEventListener('click', function() {
    const type = newPasswordInput.getAttribute('type') === 'password' ? 'text' : 'password';
    newPasswordInput.setAttribute('type', type);
    newPasswordIcon.src = type === 'password' ? '/votingsystem/images/password_notsee.svg' : '/votingsystem/images/password_see.svg';
  });

  toggleConfirmPassword.addEventListener('click', function() {
    const type = confirmPasswordInput.getAttribute('type') === 'password' ? 'text' : 'password';
    confirmPasswordInput.setAttribute('type', type);
    confirmPasswordIcon.src = type === 'password' ? '/votingsystem/images/password_notsee.svg' : '/votingsystem/images/password_see.svg';
  });
});

function validateForm() {
    const newPassword = document.getElementById("newPassword").value;
    const confirmPassword = document.getElementById("confirmPassword").value;
    const passwordError = document.getElementById("passwordError");
    let isValid = true;

    // Reset error styles
    document.getElementById("newPassword").style.marginBottom = "15px";
    document.getElementById("confirmPassword").style.marginBottom = "15px";
    passwordError.innerHTML = "";
    // Check if passwords match
    if (newPassword !== confirmPassword) {
        passwordError.innerHTML = "Passwords do not match";
        document.getElementById("confirmPassword").style.marginBottom = "0px";
        isValid = false;
    }else{
    // Password validation checks
        if (newPassword.length < 8) {
            passwordError.innerHTML = "Password must be at least 8 characters long";
            document.getElementById("confirmPassword").style.marginBottom = "0px";
            isValid = false;
        }
        else if (!/[A-Z]/.test(newPassword)) {
            passwordError.innerHTML = "Password must contain at least one uppercase letter";
            document.getElementById("confirmPassword").style.marginBottom = "0px";
            isValid = false;
        }
        else if (!/[a-z]/.test(newPassword)) {
            passwordError.innerHTML = "Password must contain at least one lowercase letter";
            document.getElementById("confirmPassword").style.marginBottom = "0px";
            isValid = false;
        }
        else if (!/[0-9]/.test(newPassword)) {
            passwordError.innerHTML = "Password must contain at least one number";
            document.getElementById("confirmPassword").style.marginBottom = "0px";
            isValid = false;
        }
        else if (!/[!@#$%^&*()_+\-=\[\]{};':\"\\|,.<>\/?]/.test(newPassword)) {
            passwordError.innerHTML = "Password must contain at least one special character (!@#$%^&*()_+-=[]{}|;':\",./<>?)";
            document.getElementById("confirmPassword").style.marginBottom = "0px";
            isValid = false;
        }
    }



    if (isValid) {
        document.querySelector("form").submit();
    }
    return isValid;
}
</script>
<div class="content-wrapper">
    <form action="updatepassword.php" method="post">
        <div class="login-box">
            <div class="left-panel">
                <div class="header">
                    <img src="/votingsystem/images/logo.png" alt="School Logo" class="logo" />
                    <div class="school-info">
                        <h2>StudentsVoice: Voting System</h2>
                        <p>MARIA ESTRELLA NATIONAL HIGH SCHOOL</p>
                        <p id="schoolAddress">Guinhawa, City of Malolos, Bulacan</p>
                    </div>
                </div>

                <div class="lalagyan" id="forgot">
                    <h1>Forgot Password</h1>
                    <div class="password-container" style="position: relative; width: 100%; max-width: 350px; margin-bottom: 15px;">
                        <input type="password" 
                               name="newPassword" 
                               id="newPassword" 
                               placeholder="New Password" 
                               value="<?php echo isset($_SESSION['newPassword']) ? $_SESSION['newPassword'] : ''; ?>" 
                               style="padding-right: 50px;" />

                        <button type="button" 
                                id="toggleNewPassword" 
                                style="position: absolute; right: 15px; top: 21px; transform: translateY(-50%); 
                                       background: none; border: none; cursor: pointer; padding: 0; margin: 0;">
                            <img src="/votingsystem/images/password_notsee.svg" 
                                 id="newPasswordIcon" 
                                 alt="Toggle password visibility" 
                                 style="width: 16px; height: 16px; opacity: 0.7;">
                        </button>
                    </div>
                    
                    <div class="password-container" style="position: relative; width: 100%; max-width: 350px; margin-bottom: 15px;">
                        <input type="password" 
                               name="confirmPassword" 
                               id="confirmPassword" 
                               placeholder="Confirm Password" 
                               value="<?php echo isset($_SESSION['confirmPassword']) ? $_SESSION['confirmPassword'] : ''; ?>" 
                               style="padding-right: 50px;" />

                        <button type="button" 
                                id="toggleConfirmPassword" 
                                style="position: absolute; right: 15px; top: 21px; transform: translateY(-50%); 
                                       background: none; border: none; cursor: pointer; padding: 0; margin: 0;">
                            <img src="/votingsystem/images/password_notsee.svg" 
                                 id="confirmPasswordIcon" 
                                 alt="Toggle password visibility" 
                                 style="width: 16px; height: 16px; opacity: 0.7;">
                        </button>
                    </div>
                    <span id="passwordError" class="error"><?php 
                        if(isset($_SESSION['passerror'])) {
                            echo "<script>document.getElementById('confirmPassword').style.marginBottom = '0px';</script>";                     
                            echo $_SESSION['passerror'];
                            unset($_SESSION['passerror']);
                        }
                    ?></span>
                    <div class="confAndBack">
                        <a href="/votingsystem/php session/logout.php"><button  type="button" class = "btn" id="backBtn">Back</button></a>
                        <button type="button" onclick="validateForm()" class="btn" id="confirmBtn">Confirm</button>
                    </div>    
                </div>

            </div>

            <div class="right-panel">
                <img src="/votingsystem/images/student.jpg" alt="Students">
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
