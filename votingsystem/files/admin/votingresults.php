<?php
session_start();
require_once '../connect/connect.php';

if (!isset($_SESSION['username'])) {
    header("Location: ../intro/login.php");
    exit();
}

// Get unique positions
$positions_query = "SELECT DISTINCT Position FROM candidate 
                   ORDER BY 
                     CASE Position
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
                     END";
$positions_result = $conn->query($positions_query);
$positions = [];
while ($row = $positions_result->fetch_assoc()) {
    $positions[] = $row['Position'];
}

// Get elections
$elections_query = "SELECT ElectionID, ElectionName, G8RepNum, G9RepNum, G10RepNum, G11RepNum, G12RepNum FROM election ORDER BY ElectionName";
$elections_result = $conn->query($elections_query);
$elections = [];
while ($row = $elections_result->fetch_assoc()) {
    $elections[] = $row;
}

// Get selected position and election from POST
$selected_position = isset($_POST['position']) ? $_POST['position'] : 'ALL';
$selected_election = isset($_POST['election']) ? $_POST['election'] : ($elections[0]['ElectionID'] ?? '');

// Get vote counts for candidates
$results_query = "SELECT 
                    c.CandidateID,
                    c.Name,
                    c.Position,
                    c.PartylistName,
                    COUNT(v.VoteID) as VoteCount
                 FROM candidate c
                 LEFT JOIN vote v ON c.CandidateID = v.CandidateID
                 WHERE c.ElectionID = ?";

if ($selected_position !== 'ALL') {
    $results_query .= " AND c.Position = ?";
}

$results_query .= " GROUP BY c.CandidateID
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
if ($selected_position !== 'ALL') {
    $stmt->bind_param("ss", $selected_election, $selected_position);
} else {
    $stmt->bind_param("s", $selected_election);
}
$stmt->execute();
$results = $stmt->get_result();

// Get current election's representative numbers
$current_election = array_filter($elections, function ($e) use ($selected_election) {
    return $e['ElectionID'] === $selected_election;
});
$current_election = reset($current_election);

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

// Handle success and error messages
$successMessage = '';
$errorMessage = '';
if (isset($_GET['success'])) {
    switch($_GET['success']) {
        case '1': $successMessage = "Results generated successfully!"; break;
        case '2': $successMessage = "Report exported successfully!"; break;
        default: $successMessage = "Operation completed successfully!"; break;
    }
}
if (isset($_GET['error'])) {
    switch($_GET['error']) {
        case '1': $errorMessage = "Failed to generate results. Please try again."; break;
        case '2': $errorMessage = "No data available for the selected criteria."; break;
        case '3': $errorMessage = "Failed to export report."; break;
        default: $errorMessage = "An error occurred. Please try again."; break;
    }
}

?>

<!DOCTYPE html>
<html>

<head>
    <title>Election Results</title>
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
        .page-title {
            text-align: center;
            color: white;
            font-size: 32px;
            font-weight: bold;
            margin: 0 0 20px 0;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
        }

        .filter-container {
            background-color: rgba(0, 0, 0, 0.75);
            padding: 20px;
            border-radius: 10px;
            margin: 20px auto;
            max-width: 800px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 20px;
        }

        .filter-controls {
            display: flex;
            gap: 20px;
            justify-content: center;
            width: 100%;
            align-items: center;
        }

        .filter-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .filter-label {
            color: white;
            font-weight: bold;
            font-size: 16px;
        }

        .filter-container select {
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
            background-color: white;
            min-width: 200px;
            font-family: 'Inter', sans-serif;
        }

        .results-container {
            background-color: rgba(0, 0, 0, 0.75);
            padding: 20px;
            border-radius: 10px;
            margin: 20px auto;
            max-width: 800px;
            color: white;
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

        .no-results {
            text-align: center;
            color: #ccc;
            padding: 20px;
        }

        .results-timestamp {
            text-align: center;
            color: #ccc;
            font-size: 14px;
            margin: 10px 0 20px 0;
        }
    </style>
</head>

<body>
<header>
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
        <div class="filter-container">
            <h1 class="page-title">ELECTION RESULTS</h1>
            <form method="post" id="filterForm">
                <div class="filter-controls">
                    <div class="filter-group">
                        <label class="filter-label">Position:</label>
                        <select name="position" id="position"
                            onchange="document.getElementById('filterForm').submit();">
                            <option value="ALL">ALL POSITIONS</option>
                            <?php foreach ($positions as $position): ?>
                                <option value="<?php echo htmlspecialchars($position); ?>" <?php echo $selected_position === $position ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($position); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label class="filter-label">Election:</label>
                        <select name="election" id="election"
                            onchange="document.getElementById('filterForm').submit();">
                            <?php foreach ($elections as $election): ?>
                                <option value="<?php echo htmlspecialchars($election['ElectionID']); ?>" <?php echo $selected_election === $election['ElectionID'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($election['ElectionName']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </form>
        </div>

        <div class="results-container">
            <div class="results-timestamp">
                Results as of: <?php
                date_default_timezone_set('Asia/Manila');
                echo date('F j, Y g:i A');
                ?> (PST)
            </div>
            <?php
            $current_position = '';
            $position_winners = [];
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
                echo '<div class="no-results">No results found for the selected filters.</div>';
            }
            ?>
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