<?php
session_start();
require_once '../connect/connect.php';

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

    // Resolve current admin id
    $adminUsername = $_SESSION['username'];
    $adminStmt = $conn->prepare("SELECT AdminID FROM admin WHERE Username = ?");
    $adminStmt->bind_param('s', $adminUsername);
    $adminStmt->execute();
    $adminRes = $adminStmt->get_result();
    $admin = $adminRes->fetch_assoc();
    $createdBy = $admin ? intval($admin['AdminID']) : null;
    $adminStmt->close();

    // Manila time
    date_default_timezone_set('Asia/Manila');
    $createdDate = date('Y-m-d H:i:s');

    $stmt = $conn->prepare("INSERT INTO candidate (ElectionID, PartylistName, Name, Position, GradeLevel, CreatedBy, CreatedDate) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('sssssis', $electionID, $partylist, $name, $position, $gradelevel, $createdBy, $createdDate);
    if ($stmt->execute()) {
        $_SESSION['snap_success'] = 'Candidate added successfully';
    } else {
        $_SESSION['snap_error'] = 'Failed to add candidate';
    }
    $stmt->close();
    $conn->close();
    header('Location: candidatemanagement.php');
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add Candidate</title>
    <link rel="icon" type="image/png" href="/votingsystem/images/logo.png">
    <link rel="stylesheet" type="text/css" href="/votingsystem/files/maincss/main.css">
    <link rel="stylesheet" type="text/css" href="/votingsystem/files/admin/admin.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/snapalert@1.0.6/dist/snapalert.min.css">
    <style>
        body { background: #111; color: #fff; font-family: 'Inter', sans-serif; }
        .add-container { background: #222; color: #fff; max-width: 500px; margin: 40px auto; padding: 30px; border-radius: 10px; }
        label { display: block; margin-top: 15px; color: #fff; }
        input, select { width: 100%; padding: 8px; margin-top: 5px; border-radius: 4px; border: 1px solid #444; background: #333; color: #fff; }
        .form-actions { margin-top: 20px; display: flex; gap: 10px; justify-content: flex-end; }
        .btn { background: #36AE66; color: #fff; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; }
        .btn-cancel { background: #888; }
    </style>
</head>
<body>
    <div class="add-container">
        <h2>Add New Candidate</h2>
        <form method="POST">
            <label>Name:
                <input type="text" name="name" required>
            </label>
            <label>Partylist:
                <input type="text" name="partylist" required>
            </label>
            <label>Position:
                <select name="position" required>
                    <option value="PRESIDENT">PRESIDENT</option>
                    <option value="VICE PRESIDENT">VICE PRESIDENT</option>
                    <option value="SECRETARY">SECRETARY</option>
                    <option value="TREASURER">TREASURER</option>
                    <option value="AUDITOR">AUDITOR</option>
                    <option value="PUBLIC INFORMATION OFFICER">PUBLIC INFORMATION OFFICER</option>
                    <option value="PROTOCOL OFFICER">PROTOCOL OFFICER</option>
                    <option value="GRADE 8 REPRESENTATIVE">GRADE 8 REPRESENTATIVE</option>
                    <option value="GRADE 9 REPRESENTATIVE">GRADE 9 REPRESENTATIVE</option>
                    <option value="GRADE 10 REPRESENTATIVE">GRADE 10 REPRESENTATIVE</option>
                    <option value="GRADE 11 REPRESENTATIVE">GRADE 11 REPRESENTATIVE</option>
                    <option value="GRADE 12 REPRESENTATIVE">GRADE 12 REPRESENTATIVE</option>
                </select>
            </label>
            <label>Grade Level:
                <select name="gradelevel" required>
                    <option value="ALL">All</option>
                    <option value="7">Grade 7</option>
                    <option value="8">Grade 8</option>
                    <option value="9">Grade 9</option>
                    <option value="10">Grade 10</option>
                    <option value="11">Grade 11</option>
                    <option value="12">Grade 12</option>
                </select>
            </label>
            <label>Election:
                <select name="election_id" required>
                    <option value="">Select Election</option>
                    <?php foreach ($elections as $e): ?>
                        <option value="<?php echo $e['ElectionID']; ?>"><?php echo htmlspecialchars($e['ElectionID']); ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <div class="form-actions">
                <a href="candidatemanagement.php" class="btn btn-cancel">Cancel</a>
                <button type="submit" class="btn">Add Candidate</button>
            </div>
        </form>
    </div>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/snapalert@1.0.6/dist/snapalert.min.js"></script>
</body>
</html>