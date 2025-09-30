<?php
session_start();
require_once '../connect/connect.php';

if (!isset($_GET['id'])) {
    header('Location: candidatemanagement.php');
    exit();
}

$candidateId = intval($_GET['id']);

// Fetch elections for dropdown
$elections = [];
$electionResult = $conn->query("SELECT ElectionID FROM election");
while ($row = $electionResult->fetch_assoc()) {
    $elections[] = $row;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $partylist = $_POST['partylist'];
    $position = $_POST['position'];
    $gradelevel = $_POST['gradelevel'];
    $electionID = $_POST['election_id'];

    // Resolve admin id for ModifiedBy
    $adminUsername = $_SESSION['username'];
    $adminStmt = $conn->prepare("SELECT AdminID FROM admin WHERE Username = ?");
    $adminStmt->bind_param('s', $adminUsername);
    $adminStmt->execute();
    $adminRes = $adminStmt->get_result();
    $admin = $adminRes->fetch_assoc();
    $modifiedBy = $admin ? intval($admin['AdminID']) : null;
    $adminStmt->close();

    // Manila time
    date_default_timezone_set('Asia/Manila');
    $modifiedDate = date('Y-m-d H:i:s');

    $stmt = $conn->prepare("UPDATE candidate SET ElectionID=?, PartylistName=?, Name=?, Position=?, GradeLevel=?, ModifiedBy=?, ModifiedDate=? WHERE CandidateID=?");
    $stmt->bind_param('sssssisi', $electionID, $partylist, $name, $position, $gradelevel, $modifiedBy, $modifiedDate, $candidateId);
    if ($stmt->execute()) {
        $_SESSION['snap_success'] = 'Candidate updated successfully';
    } else {
        $_SESSION['snap_error'] = 'Failed to update candidate';
    }
    $stmt->close();
    $conn->close();
    header('Location: candidatemanagement.php');
    exit();
}

// Fetch candidate data
$stmt = $conn->prepare("SELECT * FROM candidate WHERE CandidateID=?");
$stmt->bind_param('i', $candidateId);
$stmt->execute();
$result = $stmt->get_result();
$candidate = $result->fetch_assoc();
$stmt->close();
$conn->close();

if (!$candidate) {
    echo "Candidate not found.";
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Candidate</title>
    <link rel="icon" type="image/png" href="/votingsystem/images/logo.png">
    <link rel="stylesheet" type="text/css" href="/votingsystem/files/maincss/main.css">
    <link rel="stylesheet" type="text/css" href="/votingsystem/files/admin/admin.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/snapalert@1.0.6/dist/snapalert.min.css">
    <style>
        body { background: #111; color: #fff; font-family: 'Inter', sans-serif; }
        .edit-container { background: #222; color: #fff; max-width: 500px; margin: 40px auto; padding: 30px; border-radius: 10px; }
        label { display: block; margin-top: 15px; color: #fff; }
        input, select { width: 100%; padding: 8px; margin-top: 5px; border-radius: 4px; border: 1px solid #444; background: #333; color: #fff; }
        .form-actions { margin-top: 20px; display: flex; gap: 10px; justify-content: flex-end; }
        .btn { background: #36AE66; color: #fff; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; }
        .btn-cancel { background: #888; }
    </style>
</head>
<body>
    <div class="edit-container">
        <h2>Edit Candidate</h2>
        <form method="POST">
            <label>Name:
                <input type="text" name="name" value="<?php echo htmlspecialchars($candidate['Name']); ?>" required>
            </label>
            <label>Partylist:
                <input type="text" name="partylist" value="<?php echo htmlspecialchars($candidate['PartylistName']); ?>" required>
            </label>
            <label>Position:
                <select name="position" required>
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
                    foreach ($positions as $pos) {
                        $selected = ($candidate['Position'] === $pos) ? 'selected' : '';
                        echo "<option value=\"$pos\" $selected>$pos</option>";
                    }
                    ?>
                </select>
            </label>
            <label>Grade Level:
                <select name="gradelevel" required>
                    <option value="ALL" <?php if($candidate['GradeLevel'] === 'ALL') echo 'selected'; ?>>All Grade Levels</option>
                    <?php
                    for ($i = 7; $i <= 12; $i++) {
                        $selected = ($candidate['GradeLevel'] == $i) ? 'selected' : '';
                        echo "<option value=\"$i\" $selected>Grade $i</option>";
                    }
                    ?>
                </select>
            </label>
            <label>Election:
                <select name="election_id" required>
                    <option value="">Select Election</option>
                    <?php foreach ($elections as $e): ?>
                        <option value="<?php echo $e['ElectionID']; ?>" <?php if($candidate['ElectionID'] == $e['ElectionID']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($e['ElectionID']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <div class="form-actions">
                <a href="candidatemanagement.php" class="btn btn-cancel">Cancel</a>
                <button type="submit" class="btn">Save Changes</button>
            </div>
        </form>
    </div>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/snapalert@1.0.6/dist/snapalert.min.js"></script>
</body>
</html>