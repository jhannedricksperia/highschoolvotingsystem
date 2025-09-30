<?php
session_start();
error_log("=== Vote Receipt Page Accessed ===");
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

$query = "SELECT * FROM Account WHERE Username = ?";
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

$account = $result->fetch_assoc();

// Get all elections where the account has voted
$elections_query = "SELECT DISTINCT e.* 
                   FROM Election e 
                   INNER JOIN Vote v ON e.ElectionID = v.ElectionID 
                   WHERE v.AccountID = ? 
                   ORDER BY e.StartDateTime DESC";
$stmt = $conn->prepare($elections_query);
$stmt->bind_param("i", $account['AccountID']);
$stmt->execute();
$elections_result = $stmt->get_result();
$elections = [];
while ($election = $elections_result->fetch_assoc()) {
    $elections[] = $election;
}

// Get selected election's votes
$selected_election = null;
$votes = [];
$selected_election_id = isset($_POST['election_id']) ? $_POST['election_id'] : '';
if ($selected_election_id) {
    // Get election details
    $election_query = "SELECT * FROM Election WHERE ElectionID = ?";
    $stmt = $conn->prepare($election_query);
    $stmt->bind_param("s", $selected_election_id);
    $stmt->execute();
    $selected_election = $stmt->get_result()->fetch_assoc();

    if ($selected_election) {
        // Get votes for this election
        $votes_query = "SELECT v.*, c.Name, c.Position, c.PartylistName, c.GradeLevel 
                       FROM Vote v 
                       INNER JOIN Candidate c ON v.CandidateID = c.CandidateID 
                       WHERE v.AccountID = ? AND v.ElectionID = ?";
        $stmt = $conn->prepare($votes_query);
        $stmt->bind_param("is", $account['AccountID'], $selected_election_id);
        $stmt->execute();
        $votes_result = $stmt->get_result();

        // Group votes by position
        while ($vote = $votes_result->fetch_assoc()) {
            $votes[$vote['Position']][] = $vote;
        }
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Vote Receipt</title>
    <link rel="icon" type="image/png" href="/votingsystem/images/logo.png">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="/votingsystem/files/maincss/main.css">
    <link rel="stylesheet" type="text/css" href="/votingsystem/files/user/user.css">
    <style>
        .receipt-container {
            background-color: rgba(0, 0, 0, 0.75);
            padding: 20px;
            border-radius: 10px;
            margin: 20px auto;
            max-width: 800px;
            color: white;
        }

        .receipt-title {
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 20px;
            color: #36AE66;
        }

        .election-selector {
            margin-bottom: 30px;
            text-align: center;
        }

        .election-selector select {
            padding: 8px 15px;
            border-radius: 5px;
            border: 1px solid #36AE66;
            background-color: white;
            color: black;
            font-size: 16px;
            cursor: pointer;
        }

        .election-selector select:focus {
            outline: none;
            border-color: #36AE66;
        }

        .election-info {
            text-align: left;
            margin-bottom: 20px;
            color: #ccc;
            padding: 0px 20px;
        }

        .election-dates {
            margin-top: 10px;
            font-size: 14px;
            color: #ccc;
        }

        .position-section {
            margin-bottom: 30px;
            padding: 0px 20px;
        }

        .position-title {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 15px;
            color: #36AE66;
        }

        .candidate-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .candidate-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 5px;
        }

        .candidate-info {
            display: flex;
            align-items: center;
            gap: 15px;
            width: 100%;
        }

        .candidate-details {
            display: flex;
            flex-direction: column;
        }

        .candidate-name {
            font-weight: bold;
        }

        .candidate-party {
            color: #ccc;
            font-size: 14px;
        }

        .divider {
            height: 1px;
            background-color: rgba(255, 255, 255, 0.2);
            margin: 20px 0px;
        }

        .no-votes {
            text-align: center;
            color: #ccc;
            font-style: italic;
            padding: 0px;
            margin: 0px;
        }

        .timestamp {
            text-align: center;
            color: #ccc;
            font-size: 14px;
            margin-top: 20px;
        }

        .election-label {
            font-weight: bold;
            color: rgb(255, 255, 255);
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
        <div class="receipt-container">
            <h1 class="receipt-title">VOTE RECEIPT</h1>
            <form method="post" id="electionForm">
                <div class="election-selector">
                    <select name="election_id" id="electionSelect"
                        onchange="document.getElementById('electionForm').submit();">
                        <option value="">Select Election</option>
                        <?php foreach ($elections as $election): ?>
                            <option value="<?php echo htmlspecialchars($election['ElectionID']); ?>" <?php echo ($selected_election_id == $election['ElectionID']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($election['ElectionName']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </form>

            <?php if ($selected_election): ?>
                <div class="election-info">
                    <span class="election-label">Election:</span>
                    <?php echo htmlspecialchars($selected_election['ElectionName']); ?>
                    <div class="election-dates">
                        <span class="election-label">Start Date:</span> <?php
                        $start_date = new DateTime($selected_election['StartDateTime']);
                        echo $start_date->format('F j, Y');
                        ?>
                        <br>
                        <span class="election-label">End Date:</span> <?php
                        $end_date = new DateTime($selected_election['EndDateTime']);
                        echo $end_date->format('F j, Y');
                        ?>
                    </div>
                </div>

                <?php
                $positions = [
                    'PRESIDENT',
                    'VICE PRESIDENT',
                    'SECRETARY',
                    'TREASURER',
                    'AUDITOR',
                    'PUBLIC INFORMATION OFFICER',
                    'PROTOCOL OFFICER',
                    'GRADE 8 REPRESENTATIVE',
                    'GRADE 9 REPRESENTATIVE',
                    'GRADE 10 REPRESENTATIVE',
                    'GRADE 11 REPRESENTATIVE',
                    'GRADE 12 REPRESENTATIVE'
                ];

                // Add initial divider
                echo '<div class="divider"></div>';

                foreach ($positions as $position) {
                    echo '<div class="position-section">';
                    echo '<div class="position-title">' . htmlspecialchars($position) . '</div>';
                    echo '<div class="candidate-list">';

                    if (isset($votes[$position]) && !empty($votes[$position])) {
                        foreach ($votes[$position] as $vote) {
                            echo '<div class="candidate-item">';
                            echo '<div class="candidate-info">';
                            echo '<div class="candidate-details">';
                            echo '<div class="candidate-name">' . htmlspecialchars($vote['Name']) . '</div>';
                            echo '<div class="candidate-party">' . htmlspecialchars($vote['PartylistName']) . '</div>';
                            echo '</div>';
                            echo '</div>';
                            echo '</div>';
                        }
                    } else {
                        echo '<div class="candidate-item">';
                        echo '<div class="candidate-info">';
                        echo '<div class="no-votes">ABSTAIN</div>';
                        echo '</div>';
                        echo '</div>';
                    }

                    echo '</div>';
                    echo '</div>';

                    if ($position !== end($positions)) {
                        echo '<div class="divider"></div>';
                    }
                }
                ?>

                <?php if (!empty($votes)): ?>
                    <div class="timestamp">
                        Voted on: <?php
                        $first_vote = reset($votes[array_key_first($votes)]);
                        $date = new DateTime($first_vote['DateTime']);
                        $date->setTimezone(new DateTimeZone('Asia/Manila'));
                        echo $date->format('F j, Y g:i A');
                        ?>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="no-votes">
                    Please select an election to view your vote receipt.
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