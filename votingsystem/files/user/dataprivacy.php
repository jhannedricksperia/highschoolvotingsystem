<?php
session_start();
error_log("=== Data Privacy Page Accessed ===");
error_log("Session contents: " . print_r($_SESSION, true));

if (!isset($_SESSION['username'])) {
  error_log("No username in session, redirecting to login");
  header("Location: ../intro/login.php");
  exit();
}

// Verify the account exists
require_once '../connect/connect.php';
$username = $_SESSION['username'];
error_log("Checking account for username: " . $username);

$query = "SELECT * FROM account WHERE Username = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
  error_log("Account not found in database, clearing session");
  session_destroy();
  header("Location: ../intro/login.php");
  exit();
}

error_log("Account verified, proceeding to display data privacy page");
?>
<html>

<head>
  <title>Data Privacy</title>
  <link rel="icon" type="image/png" href="/votingsystem/images/logo.png">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" type="text/css" href="/votingsystem/files/maincss/main.css">
  <link rel="stylesheet" type="text/css" href="/votingsystem/files/user/user.css">
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
        <form action="home.php" method="post" style="display:inline;"><button type="submit"
            class="topnav-link">Home</button></form>
        <div class="dropdown">
          <button type="button" class="topnav-link active">Vote</button>
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
          <button type="button" class="topnav-link">Account</button>
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
    <form action="voting.php" method="post">
      <div class="form-center-content">
        <div class="agreement">
          <h2>Agreement</h2>
          <div class="content-agreement">
            <p>By participating in this voting system, I understand and acknowledge that my personal information—such as
              my name, identification number, and other necessary details—may be collected, processed, and stored by the
              system administrators. This data will be used strictly for purposes related to the administration,
              verification, and security of the voting process.
              <br><br>
              I am aware that all personal data collected will be handled in accordance with the provisions of Republic
              Act No. 10173, also known as the Data Privacy Act of 2012, ensuring that my rights to privacy, security,
              and transparency are protected. I also understand that I have the right to access, correct, or withdraw my
              personal data at any time, as provided by law.
              With this understanding, I hereby make an informed choice regarding the use of my personal data in
              connection with this voting activity:
            </p>
          </div>
          <div class="checkbox">
            <div class="checkbox-option">
              <input type="radio" name="agreement_choice" id="agree" value="agree">
              <label for="agree">I agree - to the collection, processing, and use of my personal information for the
                purposes stated above.</label>
            </div>
            <div class="checkbox-option">
              <input type="radio" name="agreement_choice" id="disagree" value="disagree">
              <label for="disagree">I do not agree - to the collection, processing, and use of my personal information,
                and I understand that this may affect my ability to participate in the voting process.</label>
            </div>
            <div class="continue">
              <input type="submit" value="Continue">
            </div>
          </div>
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

  <script>
    // Only handle the data privacy agreement form
    document.querySelector("form[action='voting.php']").addEventListener("submit", function (e) {
      e.preventDefault();
      const agree = document.getElementById("agree").checked;
      const disagree = document.getElementById("disagree").checked;
      if (agree) {
        window.location.href = "voting.php";
      } else if (disagree) {
        window.location.href = "../thankyou/notvote.php";
      } else {
        alert("Please select an option to continue.");
      }
    });
  </script>
</body>

</html>