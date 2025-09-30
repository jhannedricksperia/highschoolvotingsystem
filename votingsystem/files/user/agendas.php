<?php
session_start();
error_log("=== Agendas Page Accessed ===");
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

// Get unique election names
$elections_query = "SELECT DISTINCT ElectionID, ElectionName FROM election ORDER BY ElectionName";
$elections_result = $conn->query($elections_query);
$elections = [];
while ($row = $elections_result->fetch_assoc()) {
    $elections[] = $row;
}

// Get selected position and election from POST
$selected_position = isset($_POST['position']) ? $_POST['position'] : 'ALL';
$selected_election = isset($_POST['election']) ? $_POST['election'] : 'ALL';

// Get candidates with their agendas
$candidates_query = "SELECT c.*, e.ElectionName, GROUP_CONCAT(a.Agenda) as Agendas 
                    FROM candidate c 
                    JOIN election e ON c.ElectionID = e.ElectionID 
                    LEFT JOIN agenda a ON c.CandidateID = a.CandidateID 
                    WHERE 1=1";

if ($selected_position && $selected_position !== 'ALL') {
    $candidates_query .= " AND c.Position = ?";
}
if ($selected_election && $selected_election !== 'ALL') {
    $candidates_query .= " AND c.ElectionID = ?";
}

$candidates_query .= " GROUP BY c.CandidateID 
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
                        c.Name";

$stmt = $conn->prepare($candidates_query);
if ($selected_position !== 'ALL' && $selected_election !== 'ALL') {
    $stmt->bind_param("ss", $selected_position, $selected_election);
} elseif ($selected_position !== 'ALL') {
    $stmt->bind_param("s", $selected_position);
} elseif ($selected_election !== 'ALL') {
    $stmt->bind_param("s", $selected_election);
}

$stmt->execute();
$candidates_result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>

<head>
    <title>Candidate Agendas</title>
    <link rel="icon" type="image/png" href="/votingsystem/images/logo.png">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="/votingsystem/files/maincss/main.css">
    <link rel="stylesheet" type="text/css" href="/votingsystem/files/user/user.css">
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

        .candidate-container {
            background-color: rgba(0, 0, 0, 0.75);
            padding: 20px;
            border-radius: 10px;
            margin: 20px auto;
            max-width: 800px;
            color: white;
        }

        .candidate-card {
            background-color: rgba(255, 255, 255, 0.1);
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .candidate-name {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #36AE66;
        }

        .candidate-info {
            margin-bottom: 15px;
            color: #ccc;
        }

        .agenda-list {
            list-style-type: disc;
            margin-left: 20px;
            color: #fff;
        }

        .agenda-list li {
            margin-bottom: 8px;
        }

        .no-candidates {
            text-align: center;
            color: #ccc;
            padding: 20px;
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
        <div class="filter-container">
            <h1 class="page-title">CANDIDATE AGENDAS</h1>
            <form method="post" id="filterForm">
                <div class="filter-controls">
                    <div class="filter-group">
                        <label class="filter-label">Position:</label>
                        <select name="position" id="position" onchange="document.getElementById('filterForm').submit();">
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
                        <select name="election" id="election" onchange="document.getElementById('filterForm').submit();">
                            <option value="ALL">ALL ELECTIONS</option>
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

        <div class="candidate-container">
            <?php if ($candidates_result->num_rows > 0): ?>
                <?php while ($candidate = $candidates_result->fetch_assoc()): ?>
                    <div class="candidate-card">
                        <div class="candidate-name"><?php echo htmlspecialchars($candidate['Name']); ?></div>
                        <div class="candidate-info">
                            <strong>Position:</strong> <?php echo htmlspecialchars($candidate['Position']); ?><br>
                            <strong>Partylist:</strong> <?php echo htmlspecialchars($candidate['PartylistName']); ?><br>
                            <strong>Election:</strong> <?php echo htmlspecialchars($candidate['ElectionName']); ?>
                        </div>
                        <div class="agendas">
                            <strong>Agendas:</strong>
                            <ul class="agenda-list">
                                <?php
                                if ($candidate['Agendas']) {
                                    $agendas = explode(',', $candidate['Agendas']);
                                    foreach ($agendas as $agenda) {
                                        echo "<li>" . htmlspecialchars($agenda) . "</li>";
                                    }
                                } else {
                                    echo "<li>No agendas available</li>";
                                }
                                ?>
                            </ul>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="no-candidates">
                    No candidates found for the selected filters.
                </div>
            <?php endif; ?>
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