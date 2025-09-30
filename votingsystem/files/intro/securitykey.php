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
<script>
    function validateForm() {
        const un = document.getElementById("username").value;
        const securityKey = document.getElementById("security_key").value;
        const unError = document.getElementById("unError");
        const keyError = document.getElementById("keyError");
        let isValid = true;

        // Reset error messages
        unError.innerHTML = "";
        keyError.innerHTML = "";

        if (un === "") {
            unError.innerHTML = "Username is required";
            document.getElementById("username").style.marginBottom = "0px";
            isValid = false;
        }else {
            document.getElementById("username").style.marginBottom = "15px";
        }

        // Password validation  
        if (securityKey === "") {
            document.getElementById("security_key").style.marginBottom = "0px";
            keyError.innerHTML = "Security key is required";
            isValid = false;
        } else {
            document.getElementById("security_key").style.marginBottom = "15px";
        }

        if (isValid) {
            document.querySelector("form").submit();
        }

        return isValid;
    }
</script>
<body>
<div class="content-wrapper">
    <form action="securitykeyvalidation.php" method="post">
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

                
                <div class="lalagyan" id="forgot">
                    <h1>Authentication</h1>

                    <input type="text" id="username" name="username" placeholder="Username" value="<?php echo isset($_SESSION['username']) ? $_SESSION['username'] : ''; ?>" />
                    <span id="unError" class="error"><?php 
                    if(isset($_SESSION['usererror'])) {
                        echo "<script>document.getElementById('username').style.marginBottom = '0px';</script>";                     
                        echo $_SESSION['usererror'];
                        unset($_SESSION['usererror']);
                    }
                    ?></span>
                    <input type="text" id="security_key" name="security_key" placeholder="Enter Security Key" />                
                    <span id="keyError" class="error"><?php 
                        if(isset($_SESSION['keyerror'])) {
                            echo "<script>document.getElementById('security_key').style.marginBottom = '0px';</script>";                     
                            echo $_SESSION['keyerror'];
                            unset($_SESSION['keyerror']);
                        }
                    ?></span>
                    <div class="confAndBack">
                        <a href="/votingsystem/php session/logout.php"><button  type="button" class = "btn" id="backBtn">Back</button></a>
                        <button type="button" class="btn" onclick="validateForm()" id="confirmBtn">Confirm</button>
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
