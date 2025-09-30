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

$query = "SELECT * FROM account WHERE Username = ?";
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

$account = $result->fetch_assoc();
$name = $account['Name'];

// Get current time for greeting
date_default_timezone_set('Asia/Manila');
$hour = date('G');
$greeting = '';
if ($hour >= 5 && $hour < 12) {
  $greeting = 'Good Morning';
} elseif ($hour >= 12 && $hour < 17) {
  $greeting = 'Good Afternoon';
} else {
  $greeting = 'Good Evening';
}

// Get current election
date_default_timezone_set('Asia/Manila');
$current_date = date('Y-m-d H:i:s');
$election_query = "SELECT * FROM Election 
                  WHERE StartDateTime <= ? 
                  AND EndDateTime >= ?";
$stmt = $conn->prepare($election_query);
$stmt->bind_param("ss", $current_date, $current_date);
$stmt->execute();
$election_result = $stmt->get_result();
$current_election = $election_result->fetch_assoc();

error_log("Home page - Current datetime: " . $current_date);
error_log("Home page - Active election found: " . ($current_election ? "Yes" : "No"));
if ($current_election) {
  error_log("Home page - Election details - Start: " . $current_election['StartDateTime'] . ", End: " . $current_election['EndDateTime']);
}

// Function to get the correct rep number based on position
function getRepNumber($position, $current_election)
{
  if (strpos($position, 'GRADE 8') !== false)
    return $current_election['G8RepNum'] ?? 1;
  if (strpos($position, 'GRADE 9') !== false)
    return $current_election['G9RepNum'] ?? 1;
  if (strpos($position, 'GRADE 10') !== false)
    return $current_election['G10RepNum'] ?? 1;
  if (strpos($position, 'GRADE 11') !== false)
    return $current_election['G11RepNum'] ?? 1;
  if (strpos($position, 'GRADE 12') !== false)
    return $current_election['G12RepNum'] ?? 1;
  return 1; // Default for non-representative positions
}

error_log("Account verified, proceeding to display home page");
?>
<html>

<head>
  <title>Home</title>
  <link rel="icon" type="image/png" href="/votingsystem/images/logo.png">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" type="text/css" href="/votingsystem/files/maincss/main.css">
  <link rel="stylesheet" type="text/css" href="/votingsystem/files/user/user.css">
  <style>
    .greeting {
      text-align: left;
      color: white;
      font-size: 48px;
      font-weight: bold;
      margin: 20px 0;
      text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
      padding-left: 20px;
    }

    .home-container {
      display: flex;
      justify-content: space-between;
      gap: 20px;
      margin: 20px 0;
      padding: 0;
      width: 100%;
    }

    .election-info {
      flex: 3;
      background-color: rgba(0, 0, 0, 0.75);
      padding: 20px;
      border-radius: 10px;
      color: white;
      min-width: 0;
      margin-left: 20px;
    }

    .election-info h2 {
      color: #36AE66;
      margin-bottom: 15px;
      font-size: 30px;
      text-align: center;
    }

    .election-info p {
      margin: 10px 0;
      font-size: 16px;
    }

    .results-timestamp {
      text-align: center;
      color: #ccc;
      font-size: 14px;
      margin: 10px 0 20px 0;
    }

    .results-container {
      margin-top: 20px;
      background-color: rgba(0, 0, 0, 0.75);
      padding: 20px;
      border-radius: 10px;
    }

    .position-title {
      font-size: 24px;
      font-weight: bold;
      margin: 20px 0;
      color: #36AE66;
      text-align: left;
      padding-left: 10px;
    }

    .divider {
      height: 2px;
      background-color: rgba(54, 174, 102, 0.3);
      margin: 15px 0;
    }

    .candidate-card {
      background-color: rgba(255, 255, 255, 0.1);
      padding: 15px 20px;
      border-radius: 8px;
      margin-bottom: 10px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      transition: all 0.3s ease;
      border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .candidate-card.winner {
      background-color: rgba(54, 174, 102, 0.3);
    }

    .candidate-info {
      flex: 1;
    }

    .candidate-name {
      font-size: 18px;
      font-weight: bold;
      margin-bottom: 5px;
    }

    .candidate-name.winner {
      font-size: 28px;
      color: #36AE66;
    }

    .candidate-party {
      color: #ccc;
      font-size: 14px;
    }

    .candidate-party.winner {
      font-size: 18px;
      color: #36AE66;
    }

    .vote-count {
      font-size: 18px;
      font-weight: bold;
      color: white;
    }

    .vote-count.winner {
      font-size: 28px;
      color: #36AE66;
    }

    .action-buttons {
      flex: 1;
      display: flex;
      flex-direction: column;
      gap: 15px;
      min-width: 250px;
      margin-right: 80px;
    }

    .action-button {
      background-color: #36AE66;
      color: white;
      padding: 15px 25px;
      border: none;
      border-radius: 8px;
      font-size: 18px;
      font-weight: bold;
      cursor: pointer;
      text-align: center;
      text-decoration: none;
      transition: background-color 0.3s ease;
      width: 100%;
      /* Make buttons full width of their container */
    }

    .action-button:hover {
      background-color: #2d8f52;
    }

    .no-election {
      text-align: center;
      font-size: 18px;
      color: #ccc;
      margin: 20px 0;
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
        <form action="home.php" method="post" style="display:inline;"><button type="submit"
            class="topnav-link active">Home</button></form>
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
    <div class="greeting">
      <?php echo htmlspecialchars($greeting . ', ' . $name . '!'); ?>
    </div>

    <div class="home-container">
      <div class="election-info">
        <h2><?php echo $current_election ? 'Current Election Results' : "We're Sorry"; ?></h2>
        <?php if ($current_election): ?>
          <p><strong>Election Name:</strong> <?php echo htmlspecialchars($current_election['ElectionName']); ?></p>
          <p><strong>Start Date:</strong>
            <?php echo date('F j, Y g:i A', strtotime($current_election['StartDateTime'])); ?></p>
          <p><strong>End Date:</strong> <?php echo date('F j, Y g:i A', strtotime($current_election['EndDateTime'])); ?>
          </p>

          <div class="results-timestamp">
            Results as of: <?php
            echo date('F j, Y g:i A');
            ?> (PST)
          </div>

          <div class="results-container">
            <?php
            // Get vote counts for candidates
            $results_query = "SELECT 
                                c.CandidateID,
                                c.Name,
                                c.Position,
                                c.PartylistName,
                                COUNT(v.VoteID) as VoteCount
                             FROM candidate c
                             LEFT JOIN vote v ON c.CandidateID = v.CandidateID
                             WHERE c.ElectionID = ?
                             GROUP BY c.CandidateID
                             ORDER BY 
                               CASE c.Position
                                   WHEN 'PRESIDENT' THEN 1
                                   WHEN 'VICE PRESIDENT' THEN 2
                                   WHEN 'SECRETARY' THEN 3
                                   WHEN 'TREASURER' THEN 4
                                   WHEN 'AUDITOR' THEN 5
                                   WHEN 'PUBLIC INFORMATION OFFICER' THEN 6
                                   WHEN 'PROTOCOL OFFICER' THEN 7
                                   WHEN 'GRADE 7 REPRESENTATIVE' THEN 8
                                   WHEN 'GRADE 8 REPRESENTATIVE' THEN 9
                                   WHEN 'GRADE 9 REPRESENTATIVE' THEN 10
                                   WHEN 'GRADE 10 REPRESENTATIVE' THEN 11
                                   WHEN 'GRADE 11 REPRESENTATIVE' THEN 12
                                   WHEN 'GRADE 12 REPRESENTATIVE' THEN 13
                                   ELSE 14
                               END,
                               VoteCount DESC,
                               c.Name";

            $stmt = $conn->prepare($results_query);
            $stmt->bind_param("s", $current_election['ElectionID']);
            $stmt->execute();
            $results = $stmt->get_result();

            $current_position = '';
            $position_count = 0;

            if ($results->num_rows > 0) {
              while ($row = $results->fetch_assoc()) {
                if ($current_position !== $row['Position']) {
                  if ($current_position !== '') {
                    echo '</div>'; // Close previous position group
                  }
                  $current_position = $row['Position'];
                  $position_count = 0;
                  echo '<div class="divider"></div>';
                  echo '<div class="position-title">' . htmlspecialchars($current_position) . '</div>';
                  echo '<div class="position-results">';
                }

                $is_winner = false;
                if (strpos($current_position, 'REPRESENTATIVE') !== false) {
                  $g_rep_num = getRepNumber($current_position, $current_election);
                  $is_winner = $position_count < $g_rep_num && $row['VoteCount'] > 0;
                } else {
                  $is_winner = $position_count === 0 && $row['VoteCount'] > 0;
                }

                echo '<div class="candidate-card' . ($is_winner ? ' winner' : '') . '">';
                echo '<div class="candidate-info">';
                echo '<div class="candidate-name' . ($is_winner ? ' winner' : '') . '">' . htmlspecialchars($row['Name']) . '</div>';
                echo '<div class="candidate-party' . ($is_winner ? ' winner' : '') . '">' . htmlspecialchars($row['PartylistName']) . '</div>';
                echo '</div>';
                echo '<div class="vote-count' . ($is_winner ? ' winner' : '') . '">' . number_format($row['VoteCount']) . '</div>';
                echo '</div>';

                $position_count++;
              }
              echo '</div>'; // Close last position group
            } else {
              echo '<div class="no-results">No results found for this election.</div>';
            }
            ?>
          </div>
        <?php else: ?>
          <p class="no-election">No Scheduled Elections Found</p>
        <?php endif; ?>
      </div>

      <div class="action-buttons">
        <?php if ($current_election): ?>
          <a href="check_vote_status.php" class="action-button">Vote Now</a>
        <?php endif; ?>
        <a href="votereceipt.php" class="action-button">Vote Receipt</a>
        <a href="agendas.php" class="action-button">View Agenda</a>
        <a href="accountmanagement.php" class="action-button">Manage Account</a>
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

  <script>
    // Remove the form event listener since there's no form on this page
    // The form handling is done in dataprivacy.php instead
  </script>
</body>

</html>