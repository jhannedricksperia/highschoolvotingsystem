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
?>
<html>
  <head>
    <title>Voting Session</title>
    <link rel="icon" type="image/png" href="/votingsystem/images/logo.png">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="/votingsystem/files/maincss/main.css">
    <link rel="stylesheet" type="text/css" href="/votingsystem/files/admin/admin.css">
    <link rel="stylesheet" type="text/css" href="/votingsystem/files/admin/management-table.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/snapalert@1.0.6/dist/snapalert.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/snapalert@1.0.6/dist/snapalert.min.css">
    <style>
      .dataTables_wrapper .dataTables_scrollBody { overflow-x: auto !important; }
      #electionTable { width: 100% !important; }
      .dataTables_wrapper .dataTables_length select {
        background-color: rgba(0,0,0,0.5);
        color: #fff;
        border: 1px solid #333;
        border-radius: 4px;
        padding: 2px 6px;
      }
      .dataTables_wrapper .dataTables_length label { color: #fff; }
      .dataTables_wrapper .dataTables_length select option { color: #000; }
    </style>
    <script>
    // Edit button function: redirect to edit_election.php with the ElectionID as a GET parameter
    function editElection(electionId) {
        window.location.href = 'edit_election.php?id=' + encodeURIComponent(electionId);
    }

    // Delete button function: confirm and redirect to delete_election.php with the ElectionID as a GET parameter
    function deleteElection(electionId) {
        SnapAlert.confirm({
            title: 'Delete Election',
            message: 'Are you sure you want to delete this election? This will also delete all associated candidates and votes.',
            okText: 'Delete',
            cancelText: 'Cancel',
            okBtnClass: 'btn btn-danger'
        }).then(function(confirmed){
            if (confirmed) {
                window.location.href = 'delete_election.php?id=' + encodeURIComponent(electionId);
            }
        });
    }
    </script>
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
          <h2>Voting Sessions</h2>
          <br>
          <button class="btn-add" onclick="window.location.href='add_election.php'">Schedule another Election</button>
          <br><br>
        </div>
        <div class="table-container">
          <table id="electionTable" class="management-table display nowrap" style="width:100%">
            <thead>
              <tr>
                <th>Election ID</th>
                <th>Election Name</th>
                <th>Start Date/Time</th>
                <th>End Date/Time</th>
                <th>G8 Rep</th>
                <th>G9 Rep</th>
                <th>G10 Rep</th>
                <th>G11 Rep</th>
                <th>G12 Rep</th>
                <th>Created By</th>
                <th>Created Date</th>
                <th>Modified By</th>
                <th>Modified Date</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php
              // Fetch all elections with creator/modifier usernames
              $sql = "SELECT e.*, cb.Username AS CreatedByUsername, mb.Username AS ModifiedByUsername
                      FROM election e
                      LEFT JOIN admin cb ON e.CreatedBy = cb.AdminID
                      LEFT JOIN admin mb ON e.ModifiedBy = mb.AdminID";
              $result = $conn->query($sql);
              while ($row = $result->fetch_assoc()) {
                  echo "<tr>";
                  echo "<td>" . htmlspecialchars($row['ElectionID']) . "</td>";
                  echo "<td>" . htmlspecialchars($row['ElectionName']) . "</td>";
                  echo "<td>" . htmlspecialchars(date("M j, Y g:i A", strtotime($row['StartDateTime']))) . "</td>";
                  echo "<td>" . htmlspecialchars(date("M j, Y g:i A", strtotime($row['EndDateTime']))) . "</td>";
                  echo "<td>" . htmlspecialchars($row['G8RepNum']) . "</td>";
                  echo "<td>" . htmlspecialchars($row['G9RepNum']) . "</td>";
                  echo "<td>" . htmlspecialchars($row['G10RepNum']) . "</td>";
                  echo "<td>" . htmlspecialchars($row['G11RepNum']) . "</td>";
                  echo "<td>" . htmlspecialchars($row['G12RepNum']) . "</td>";
                  echo "<td>" . ($row['CreatedByUsername'] ? htmlspecialchars($row['CreatedByUsername']) : 'N/A') . "</td>";
                  echo "<td>" . ($row['CreatedDate'] ? htmlspecialchars(date("M j, Y g:i A", strtotime($row['CreatedDate']))) : 'N/A') . "</td>";
                  echo "<td>" . ($row['ModifiedByUsername'] ? htmlspecialchars($row['ModifiedByUsername']) : 'N/A') . "</td>";
                  echo "<td>" . ($row['ModifiedDate'] ? htmlspecialchars(date("M j, Y g:i A", strtotime($row['ModifiedDate']))) : 'N/A') . "</td>";
                  echo "<td>\n        <span class='action-buttons'>\n            <button class='btn-edit' onclick='editElection(\"" . htmlspecialchars($row['ElectionID']) . "\")'>Edit</button>\n            <button class='btn-delete' onclick='deleteElection(\"" . htmlspecialchars($row['ElectionID']) . "\")'>Delete</button>\n        </span>\n    </td>";
                  echo "</tr>";
              }
              ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <script>
      $(function(){
        // Flash alerts
        const successMsg = <?php echo json_encode(isset($_SESSION['snap_success']) ? $_SESSION['snap_success'] : ''); ?>;
        const errorMsg = <?php echo json_encode(isset($_SESSION['snap_error']) ? $_SESSION['snap_error'] : ''); ?>;
        if (successMsg) {
          SnapAlert.success({ title: 'Success', message: successMsg });
          <?php unset($_SESSION['snap_success']); ?>
        }
        if (errorMsg) {
          SnapAlert.error({ title: 'Error', message: errorMsg });
          <?php unset($_SESSION['snap_error']); ?>
        }

        const t = $('#electionTable').DataTable({
          scrollX: true,
          autoWidth: false,
          pageLength: 25,
          lengthMenu: [10,25,50,100],
          order: [[0,'asc']],
          columnDefs: [
            { targets: -1, orderable: false, searchable: false, width: 180 }
          ]
        });

        // Dynamically size columns to max record length (exclude Actions)
        function adjustColumnWidths() {
          const ctx = document.createElement('canvas').getContext('2d');
          const sampleCell = document.querySelector('#electionTable tbody td');
          const computed = sampleCell ? window.getComputedStyle(sampleCell) : null;
          ctx.font = computed ? `${computed.fontWeight} ${computed.fontSize} ${computed.fontFamily}` : '12px Inter, Arial';

          const paddingPx = 24;
          const minColPx = 80;
          const maxColPx = 360;
          const measure = (text) => ctx.measureText(String(text || '')).width;

          // indexes 0..12 (exclude 13 which is Actions)
          const targetIndexes = [0,1,2,3,4,5,6,7,8,9,10,11,12];

          targetIndexes.forEach(idx => {
            let maxPx = 0;
            const headerText = ($(t.column(idx).header()).text() || '').trim();
            maxPx = Math.max(maxPx, measure(headerText));
            t.column(idx, { search: 'applied', page: 'current' }).data().each(function(val){
              const text = (val || '').toString().replace(/<[^>]*>/g,'');
              maxPx = Math.max(maxPx, measure(text));
            });
            const widthPx = Math.min(Math.max(Math.ceil(maxPx) + paddingPx, minColPx), maxColPx);
            $(t.column(idx).header()).css({ minWidth: widthPx + 'px' });
            $(t.column(idx).nodes()).css({ minWidth: widthPx + 'px' });
          });
          t.columns.adjust();
        }

        let scheduled = false;
        t.on('draw', function(){
          if (scheduled) return;
          scheduled = true;
          requestAnimationFrame(function(){
            adjustColumnWidths();
            scheduled = false;
          });
        });
        adjustColumnWidths();
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
  </body>
</html>