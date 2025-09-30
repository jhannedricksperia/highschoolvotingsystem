<?php
session_start();
error_log("=== Vote Summary Page Accessed ===");
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

$account = $result->fetch_assoc();

// Get election details
$election_id = $_POST['election_id'];
$election_query = "SELECT * FROM election WHERE ElectionID = ?";
$stmt = $conn->prepare($election_query);
$stmt->bind_param("s", $election_id);
$stmt->execute();
$election_result = $stmt->get_result();
$election = $election_result->fetch_assoc();

// Get selected candidates
$selected_votes = [];
error_log("=== Vote Summary Processing ===");
error_log("POST data: " . print_r($_POST['vote'], true));

if (isset($_POST['vote']) && is_array($_POST['vote'])) {
    foreach ($_POST['vote'] as $position => $candidate_ids) {
        error_log("Processing position: " . $position);
        error_log("Candidate IDs: " . print_r($candidate_ids, true));
        if (is_array($candidate_ids)) {
            $selected_votes[$position] = array_filter($candidate_ids);
        } else {
            $selected_votes[$position] = [$candidate_ids];
        }
    }
}

error_log("Processed selected votes: " . print_r($selected_votes, true));

// Get candidate details for selected votes
$candidate_ids = [];
foreach ($selected_votes as $votes) {
    if (is_array($votes)) {
        $candidate_ids = array_merge($candidate_ids, $votes);
    }
}

$candidates = [];
if (!empty($candidate_ids)) {
    $placeholders = str_repeat('?,', count($candidate_ids) - 1) . '?';
    $candidate_query = "SELECT * FROM candidate WHERE CandidateID IN ($placeholders)";
    $stmt = $conn->prepare($candidate_query);
    $stmt->bind_param(str_repeat('i', count($candidate_ids)), ...$candidate_ids);
    $stmt->execute();
    $candidate_result = $stmt->get_result();

    while ($candidate = $candidate_result->fetch_assoc()) {
        $candidates[$candidate['CandidateID']] = $candidate;
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Vote Summary</title>
    <link rel="icon" type="image/png" href="/votingsystem/images/logo.png">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="/votingsystem/files/maincss/main.css">
    <link rel="stylesheet" type="text/css" href="/votingsystem/files/user/user.css">
    <style>
        .summary-container {
            background-color: rgba(0, 0, 0, 0.75);
            padding: 20px;
            border-radius: 10px;
            margin: 20px auto;
            max-width: 800px;
            color: white;
        }

        .summary-title {
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 20px;
            color: #36AE66;
        }

        .election-info {
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

        .abstain-text {
            color: #ccc;
            font-style: italic;
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
            font-weight: bold;
            transition: background-color 0.3s;
        }

        .back-button {
            background-color: #666;
            color: white;
        }

        .submit-button {
            background-color: #36AE66;
            color: white;
        }

        .button:hover {
            opacity: 0.9;
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
        <div class="summary-container">
            <h1 class="summary-title">VOTE SUMMARY</h1>
            <div class="election-info">
                Election: <?php echo htmlspecialchars($election['ElectionName']); ?>
            </div>

            <form id="summaryForm" action="process_vote.php" method="POST">
                <input type="hidden" name="election_id" value="<?php echo $election_id; ?>">
                <?php
                error_log("=== Vote Summary Form Data ===");
                error_log("Election ID: " . $election_id);
                error_log("Selected Votes: " . print_r($selected_votes, true));

                // Add hidden inputs for each vote
                foreach ($selected_votes as $position => $candidate_ids) {
                    if (is_array($candidate_ids)) {
                        foreach ($candidate_ids as $candidate_id) {
                            echo '<input type="hidden" name="vote[' . htmlspecialchars($position) . '][]" value="' . htmlspecialchars($candidate_id) . '">';
                        }
                    } else {
                        echo '<input type="hidden" name="vote[' . htmlspecialchars($position) . ']" value="' . htmlspecialchars($candidate_ids) . '">';
                    }
                }
                ?>

                <?php
                $positions = [
                    'PRESIDENT',
                    'VICE PRESIDENT',
                    'SECRETARY',
                    'TREASURER',
                    'AUDITOR',
                    'PUBLIC INFORMATION OFFICER',
                    'PROTOCOL OFFICER'
                ];

                // Add representative position only for the account's grade level
                if ($account['GradeLevel'] == 7) {
                    $positions[] = 'GRADE 8 REPRESENTATIVE';
                } else {
                    $positions[] = 'GRADE ' . $account['GradeLevel'] . ' REPRESENTATIVE';
                }

                foreach ($positions as $position) {
                    echo '<div class="position-section">';
                    echo '<div class="position-title">' . htmlspecialchars($position) . '</div>';
                    echo '<div class="candidate-list">';

                    if (isset($selected_votes[$position]) && !empty($selected_votes[$position])) {
                        foreach ($selected_votes[$position] as $candidate_id) {
                            if (isset($candidates[$candidate_id])) {
                                $candidate = $candidates[$candidate_id];
                                echo '<div class="candidate-item">';
                                echo '<div class="candidate-info">';
                                echo '<div class="candidate-details">';
                                echo '<div class="candidate-name">' . htmlspecialchars($candidate['Name']) . '</div>';
                                echo '<div class="candidate-party">' . htmlspecialchars($candidate['PartylistName']) . '</div>';
                                echo '</div>';
                                echo '</div>';
                                echo '</div>';
                            }
                        }
                    } else {
                        echo '<div class="candidate-item">';
                        echo '<div class="abstain-text">ABSTAIN</div>';
                        echo '</div>';
                    }

                    echo '</div>';
                    echo '</div>';

                    if ($position !== end($positions)) {
                        echo '<div class="divider"></div>';
                    }
                }
                ?>

                <div class="button-container">
                    <button type="button" class="button back-button"
                        onclick="window.location.href='voting.php?from_summary=1'">Back</button>
                    <button type="submit" class="button submit-button">Submit Vote</button>
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
        function returnToVoting() {
            sessionStorage.setItem('fromSummary', 'true');
            window.location.href = 'voting.php';
        }

        document.getElementById('summaryForm').addEventListener('submit', function (e) {
            e.preventDefault();
            if (confirm('Are you sure you want to submit your vote? This action cannot be undone.')) {
                // Log the form data before submission
                const formData = new FormData(this);
                console.log("Submitting form data:");
                for (let [key, value] of formData.entries()) {
                    console.log(key + ": " + value);
                }
                this.submit();
            }
        });
    </script>
</body>

</html>