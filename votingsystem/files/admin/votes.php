<?php
session_start();
error_log("=== Home Page Accessed ===");
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

$query = "SELECT * FROM admin WHERE Username = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

error_log("Account query result rows: " . $result->num_rows);

if ($result->num_rows === 0) {
    error_log("Account not found in database, clearing session");
    session_destroy();
    header("Location: ../intro/login.php");
    exit();
}

error_log("Account verified, proceeding to display home page");

// Handle success and error messages
$successMessage = '';
$errorMessage = '';
if (isset($_GET['success'])) {
    switch($_GET['success']) {
        case '1': $successMessage = "Vote data loaded successfully!"; break;
        case '2': $successMessage = "Vote data exported successfully!"; break;
        default: $successMessage = "Operation completed successfully!"; break;
    }
}
if (isset($_GET['error'])) {
    switch($_GET['error']) {
        case '1': $errorMessage = "Failed to load vote data. Please try again."; break;
        case '2': $errorMessage = "No vote data available."; break;
        case '3': $errorMessage = "Failed to export vote data."; break;
        default: $errorMessage = "An error occurred. Please try again."; break;
    }
}

?>
<html>
  <head>
    <title>Home</title>
    <link rel="icon" type="image/png" href="/votingsystem/images/logo.png">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="/votingsystem/files/maincss/main.css">
    <link rel="stylesheet" type="text/css" href="/votingsystem/files/admin/admin.css">
    <link rel="stylesheet" type="text/css" href="/votingsystem/files/admin/management-table.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/snapalert@1.0.6/dist/snapalert.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/snapalert@1.0.6/dist/snapalert.min.css">
    <style>
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
          <a   href="home.php">Home</a>
          <a href="generatereport.php">Report</a>
          <div class="dropdown">
            <a class="active">Management</a>
            <div class="dropdown-content">
            <div class="dropdown-submenu">
                <a>Logged Management ▸</a>
                <div class="dropdown-subcontent">
                  <a href="adminloggedmanagement.php">Admin</a>
                  <a href="userloggedmanagement.php">User</a>
                </div>
              </div>
              <a href="votes.php">Votes</a>
              <a href="usermanagement.php">User Management</a>  
              <a href="adminmanagement.php">Admin Management</a>
              <a href="candidatemanagement.php">Candidate Management</a>
              <a href="votingsession.php">Voting Session</a>
              <a href="votingresults.php">Voting Results</a>
            </div>
          </div>
          <div class="dropdown">
          <a>Account</a>
            <div class="dropdown-content">
              <a href="accountmanagement.php">Manage</a>
                             <a href="/votingsystem/files/intro/logout.php">Logout</a>
            </div>
          </div>
        </div>
      </div>
    </header>

    <div class="centerMain">
      <div class="management-container">
        <div class="management-header">
          <h2>Votes</h2>
        </div>
        <div class="search-filter">
          <input type="text" id="searchVote" class="search-input" placeholder="Search...">
        </div>
        <div class="table-container">
          <table class="management-table">
            <thead>
              <tr>
                <th>VoteID</th>
                <th>AccountID</th>
                <th>CandidateID</th>
                <th>ElectionID</th>
                <th>Date Voted</th>
              </tr>
            </thead>
            <tbody>
              <?php
                $sql = "SELECT VoteID, AccountID, CandidateID, ElectionID, DateTime FROM vote";
                $result = $conn->query($sql);
                if ($result && $result->num_rows > 0) {
                  while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['VoteID']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['AccountID']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['CandidateID']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['ElectionID']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['DateTime']) . "</td>";
                    echo "</tr>";
                  }
                } else {
                  echo '<tr><td colspan="5" style="text-align:center;">No votes were made yet</td></tr>';
                }
              ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
    <script>
      document.getElementById('searchVote').addEventListener('keyup', function() {
        const search = this.value.toUpperCase();
        const table = document.querySelector('.management-table');
        const trs = table.getElementsByTagName('tr');
        for (let i = 1; i < trs.length; i++) {
          const tds = trs[i].getElementsByTagName('td');
          let show = false;
          for (let j = 0; j < tds.length; j++) {
            if (tds[j].textContent.toUpperCase().includes(search)) {
              show = true;
              break;
            }
          }
          trs[i].style.display = show ? "" : "none";
        }
      });
    </script>

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

    <script>
        // Show success or error messages using SnapAlert
        <?php if (!empty($successMessage)): ?>
        SnapAlert.success({ 
            title: 'Success', 
            message: '<?php echo addslashes($successMessage); ?>' 
        });
        <?php endif; ?>
        
        <?php if (!empty($errorMessage)): ?>
        SnapAlert.error({ 
            title: 'Error', 
            message: '<?php echo addslashes($errorMessage); ?>' 
        });
        <?php endif; ?>
    </script>

  </body>
</html>