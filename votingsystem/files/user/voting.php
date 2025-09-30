<?php
session_start();
error_log("=== Voting Page Accessed ===");
error_log("Session contents: " . print_r($_SESSION, true));

if (!isset($_SESSION['username'])) {
    error_log("No username in session, redirecting to login");
    header("Location: ../intro/login.php");
    exit();
}

// Check if coming from summary page
if (isset($_GET['from_summary'])) {
    $_SESSION['from_summary'] = true;
    header("Location: voting.php");
    exit();
}

// Verify the account exists and check vote status
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

$account = $result->fetch_assoc();

// Check if user has already voted
if ($account['VoteStatus'] === 'VOTED') {
    header("Location: ../messages/alreadyvoted.php");
    exit();
}

// Check for active election
date_default_timezone_set('Asia/Manila');
$current_date = date('Y-m-d H:i:s');
$election_query = "SELECT * FROM Election 
                  WHERE StartDateTime <= ? 
                  AND EndDateTime >= ?";
$stmt = $conn->prepare($election_query);
$stmt->bind_param("ss", $current_date, $current_date);
$stmt->execute();
$election_result = $stmt->get_result();

if ($election_result->num_rows === 0) {
    header("Location: ../messages/noscheduledelelection.php");
    exit();
}

$election = $election_result->fetch_assoc();

// Calculate the grade level to vote for (next grade)
$voting_grade = intval($account['GradeLevel']) + 1;

// Get candidates for the active election
$candidates_query = "SELECT c.*, e.ElectionName, e.G8RepNum, e.G9RepNum, e.G10RepNum, e.G11RepNum, e.G12RepNum 
                    FROM candidate c 
                    JOIN election e ON c.ElectionID = e.ElectionID 
                    WHERE c.ElectionID = ? 
                    AND (
                        c.Position NOT LIKE 'GRADE%REPRESENTATIVE' 
                        OR (c.Position = 'GRADE " . $voting_grade . " REPRESENTATIVE' AND c.GradeLevel = ?)
                    )
                    GROUP BY c.Position, c.CandidateID
                    ORDER BY 
                        CASE c.Position
                            WHEN 'PRESIDENT' THEN 1
                            WHEN 'VICE PRESIDENT' THEN 2
                            WHEN 'SECRETARY' THEN 3
                            WHEN 'TREASURER' THEN 4
                            WHEN 'AUDITOR' THEN 5
                            WHEN 'PUBLIC INFORMATION OFFICER' THEN 6
                            WHEN 'PROTOCOL OFFICER' THEN 7
                            WHEN 'GRADE 8 REPRESENTATIVE' THEN 8
                            WHEN 'GRADE 9 REPRESENTATIVE' THEN 9
                            WHEN 'GRADE 10 REPRESENTATIVE' THEN 10
                            WHEN 'GRADE 11 REPRESENTATIVE' THEN 11
                            WHEN 'GRADE 12 REPRESENTATIVE' THEN 12
                            ELSE 13
                        END,
                        c.Name";
$stmt = $conn->prepare($candidates_query);
$stmt->bind_param("ss", $election['ElectionID'], $voting_grade);
$stmt->execute();
$candidates_result = $stmt->get_result();

// Debug logging for candidates
error_log("=== Candidates Retrieved ===");
while ($candidate = $candidates_result->fetch_assoc()) {
    error_log("Position: " . $candidate['Position'] . ", Name: " . $candidate['Name']);
}
$candidates_result->data_seek(0); // Reset the result pointer
?>

<!DOCTYPE html>
<html>

<head>
    <title>Online Ballot</title>
    <link rel="icon" type="image/png" href="/votingsystem/images/logo.png">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="/votingsystem/files/maincss/main.css">
    <link rel="stylesheet" type="text/css" href="/votingsystem/files/user/user.css">
    <style>
        .ballot-container {
            background-color: rgba(0, 0, 0, 0.75);
            padding: 20px;
            border-radius: 10px;
            margin: 20px auto;
            max-width: 800px;
            color: white;
        }

        .ballot-title {
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .ballot-directions {
            text-align: center;
            margin-bottom: 30px;
            color: #ccc;
        }

        .position-section {
            margin-bottom: 30px;
        }

        .position-title {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 15px;
            color: #36AE66;
        }

        .position-note {
            color: #ccc;
            font-size: 0.9em;
            margin-bottom: 15px;
            font-style: italic;
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
        }

        .candidate-number {
            font-weight: bold;
            min-width: 30px;
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
            font-size: 0.9em;
        }

        .vote-radio {
            margin-left: 20px;
        }

        .vote-radio input[type="radio"] {
            width: 20px;
            height: 20px;
            cursor: pointer;
            accent-color: #36AE66;
        }

        .vote-radio input[type="checkbox"] {
            width: 20px;
            height: 20px;
            cursor: pointer;
            accent-color: #36AE66;
        }

        .divider {
            height: 1px;
            background-color: rgba(255, 255, 255, 0.2);
            margin: 20px 0;
        }

        .button-container {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
        }

        .button {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 700;
            font-family: 'Inter', sans-serif;
            transition: background-color 0.3s;
        }

        .back-button {
            background-color: #666;
            color: white;
        }

        .proceed-button {
            background-color: #36AE66;
            color: white;
        }

        .button:hover {
            opacity: 0.9;
        }

        .ballot-info {
            background-color: rgba(255, 255, 255, 0.1);
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .info-row {
            display: flex;
            margin-bottom: 8px;
        }

        .info-row:last-child {
            margin-bottom: 0;
        }

        .info-label {
            font-weight: bold;
            min-width: 120px;
            color: #36AE66;
        }

        .info-value {
            color: #fff;
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
        <div class="ballot-container">
            <h1 class="ballot-title">ONLINE BALLOT</h1>

            <div class="ballot-info">
                <div class="info-row">
                    <span class="info-label">Account ID: </span>
                    <span class="info-value"> <?php echo htmlspecialchars($account['AccountID']); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Name: </span>
                    <span class="info-value"> <?php echo htmlspecialchars($account['Name']); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Election Name: </span>
                    <span class="info-value"> <?php echo htmlspecialchars($election['ElectionName']); ?></span>
                </div>
            </div>

            <p class="ballot-directions">Directions: Please follow the instructions provided. Each position allows only
                a specific number of votes.</p>

            <form id="ballotForm" action="votesummary.php" method="POST" <?php if (isset($_SESSION['from_summary'])) {
                unset($_SESSION['from_summary']);
                echo 'autocomplete="off"';
            } ?>>
                <input type="hidden" name="election_id" value="<?php echo $election['ElectionID']; ?>">

                <?php
                $current_position = '';
                $candidate_number = 1;

                while ($candidate = $candidates_result->fetch_assoc()) {
                    if ($current_position !== $candidate['Position']) {
                        if ($current_position !== '') {
                            echo '</div>'; // Close candidate-list
                            echo '</div>'; // Close position-section
                            echo '<div class="divider"></div>';
                        }
                        $current_position = $candidate['Position'];
                        $candidate_number = 1;
                        echo '<div class="position-section">';
                        echo '<div class="position-title">' . htmlspecialchars($current_position) . '</div>';

                        // Add note based on position
                        if (strpos($current_position, 'REPRESENTATIVE') !== false) {
                            $grade = explode(' ', $current_position)[1];
                            $repNum = $election['G' . $grade . 'RepNum'];
                            echo '<div class="position-note">Note: Please vote ' . $repNum . ' representative' . ($repNum > 1 ? 's' : '') . ' only.</div>';
                        } else {
                            echo '<div class="position-note">Note: Please vote one ' . strtolower($current_position) . ' only.</div>';
                        }

                        echo '<div class="candidate-list">';
                    }
                    ?>
                    <div class="candidate-item">
                        <div class="candidate-info">
                            <span class="candidate-number"><?php echo $candidate_number++; ?></span>
                            <div class="candidate-details">
                                <div class="candidate-name"><?php echo htmlspecialchars($candidate['Name']); ?></div>
                                <div class="candidate-party"><?php echo htmlspecialchars($candidate['PartylistName']); ?>
                                </div>
                            </div>
                        </div>
                        <div class="vote-radio">
                            <?php if (strpos($current_position, 'REPRESENTATIVE') !== false): ?>
                                <input type="checkbox" name="vote[<?php echo htmlspecialchars($current_position); ?>][]"
                                    value="<?php echo $candidate['CandidateID']; ?>" onclick="toggleVote(this)">
                            <?php else: ?>
                                <input type="radio" name="vote[<?php echo htmlspecialchars($current_position); ?>]"
                                    value="<?php echo $candidate['CandidateID']; ?>" onclick="toggleVote(this)">
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php
                }
                if ($current_position !== '') {
                    echo '</div>'; // Close candidate-list
                    echo '</div>'; // Close position-section
                }
                ?>

                <div class="button-container">
                    <button type="button" class="button back-button"
                        onclick="window.location.href='dataprivacy.php'">Back</button>
                    <button type="submit" class="button proceed-button">Proceed</button>
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

    <script>
        // Check if we're returning from summary page
        window.onload = function () {
            if (sessionStorage.getItem('fromSummary') === 'true') {
                document.getElementById('ballotForm').reset();
                sessionStorage.removeItem('fromSummary');
            }
        }

        function toggleVote(input) {
            if (input.type === 'radio') {
                // If clicking the same radio button again, uncheck it
                if (input.dataset.wasChecked === 'true') {
                    input.checked = false;
                    input.dataset.wasChecked = 'false';
                } else {
                    input.dataset.wasChecked = 'true';
                }
            } else if (input.type === 'checkbox') {
                // For checkboxes, we'll handle the validation in the form submission
                const position = input.name.match(/\[(.*?)\]/)[1];
                const maxVotes = getMaxVotesForPosition(position);
                const checkedBoxes = document.querySelectorAll(`input[name="${input.name}"]:checked`);

                if (checkedBoxes.length > maxVotes) {
                    input.checked = false;
                    alert(`You can only select ${maxVotes} candidate(s) for ${position}`);
                }
            }
        }

        function getMaxVotesForPosition(position) {
            const repPositions = {
                'GRADE 8 REPRESENTATIVE': <?php echo $election['G8RepNum']; ?>,
                'GRADE 9 REPRESENTATIVE': <?php echo $election['G9RepNum']; ?>,
                'GRADE 10 REPRESENTATIVE': <?php echo $election['G10RepNum']; ?>,
                'GRADE 11 REPRESENTATIVE': <?php echo $election['G11RepNum']; ?>,
                'GRADE 12 REPRESENTATIVE': <?php echo $election['G12RepNum']; ?>
            };
            return repPositions[position] || 1;
        }

        document.getElementById('ballotForm').addEventListener('submit', function (e) {
            e.preventDefault();

            // Get all positions
            const positions = {};
            const formData = new FormData(this);

            console.log("Form data before submission:");
            for (let [key, value] of formData.entries()) {
                console.log(key + ": " + value);
                if (key.startsWith('vote[')) {
                    const position = key.match(/\[(.*?)\]/)[1];
                    if (!positions[position]) {
                        positions[position] = [];
                    }
                    positions[position].push(value);
                }
            }

            // Check representative positions
            const repPositions = {
                'GRADE 8 REPRESENTATIVE': <?php echo $election['G8RepNum']; ?>,
                'GRADE 9 REPRESENTATIVE': <?php echo $election['G9RepNum']; ?>,
                'GRADE 10 REPRESENTATIVE': <?php echo $election['G10RepNum']; ?>,
                'GRADE 11 REPRESENTATIVE': <?php echo $election['G11RepNum']; ?>,
                'GRADE 12 REPRESENTATIVE': <?php echo $election['G12RepNum']; ?>
            };

            let isValid = true;
            let errorMessage = '';

            for (const [position, maxVotes] of Object.entries(repPositions)) {
                if (positions[position] && positions[position].length > maxVotes) {
                    isValid = false;
                    errorMessage = `You can only select ${maxVotes} candidate(s) for ${position}`;
                    break;
                }
            }

            if (!isValid) {
                alert(errorMessage);
                return;
            }

            // If validation passes, submit the form
            this.submit();
        });
    </script>
</body>

</html>