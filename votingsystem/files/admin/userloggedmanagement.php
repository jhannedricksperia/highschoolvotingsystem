<?php
session_start();
if (!isset($_SESSION['username'])) {
	header("Location: ../intro/login.php");
	exit();
}

require_once '../connect/connect.php';

// Validate admin
$username = $_SESSION['username'];
$stmt = $conn->prepare("SELECT AdminID, Username FROM admin WHERE Username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$adminResult = $stmt->get_result();
if ($adminResult->num_rows === 0) {
	session_destroy();
	header("Location: ../intro/login.php");
	exit();
}

// Handle success messages
$successMessage = '';
$errorMessage = '';
if (isset($_GET['success'])) {
    switch($_GET['success']) {
        case '1': $successMessage = "Logged record updated successfully!"; break;
        case '2': $successMessage = "Logged record deleted successfully!"; break;
        default: $successMessage = "Operation completed successfully!"; break;
    }
}
if (isset($_GET['error'])) {
    switch($_GET['error']) {
        case '1': $errorMessage = "Failed to update logged record. Please try again."; break;
        case '2': $errorMessage = "Failed to delete logged record. Please try again."; break;
        case '3': $errorMessage = "Record not found."; break;
        default: $errorMessage = "An error occurred. Please try again."; break;
    }
}

?>
<html>
  <head>
    <title>User Logged Management</title>
    <link rel="icon" type="image/png" href="/votingsystem/images/logo.png">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="/votingsystem/files/maincss/main.css">
    <link rel="stylesheet" type="text/css" href="/votingsystem/files/admin/admin.css">
    <link rel="stylesheet" type="text/css" href="/votingsystem/files/admin/management-table.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/snapalert@1.0.6/dist/snapalert.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/snapalert@1.0.6/dist/snapalert.min.css">
    <style>
      .dataTables_wrapper .dataTables_scrollBody { overflow-x: auto !important; }
      #userLoggedTable { width: 100% !important; }
      .management-table th, .management-table td { white-space: nowrap; }
      /* Transparent black background for DataTables "Show entries" combobox */
      .dataTables_wrapper .dataTables_length select {
        background-color: rgba(0, 0, 0, 0.5);
        color: #fff;
        border: 1px solid #333;
        border-radius: 4px;
        padding: 2px 6px;
      }
      .dataTables_wrapper .dataTables_length label { color: #fff; }
      .dataTables_wrapper .dataTables_length select option { color: #000; }
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
                <a class="active">Logged Management ▸</a>
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
          <h2>User Logged Records</h2>
        </div>
        <div class="table-container">
          <table id="userLoggedTable" class="management-table display nowrap" style="width:100%">
            <thead>
              <tr>
                <th>Logged ID</th>
                <th>Account Username</th>
                <th>Logged In</th>
                <th>Logged Out</th>
                <th>Modified By (Admin)</th>
                <th>Modified Date</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php
                $sql = "SELECT ul.LoggedID, ul.AccountID, ac.Username AS AccountUsername, ul.LoggedIn, ul.LoggedOut, 
                               ad.Username AS ModifiedByUsername, ul.ModifiedDate
                        FROM userloggedrecord ul
                        LEFT JOIN account ac ON ul.AccountID = ac.AccountID
                        LEFT JOIN admin ad ON ul.ModifiedBy = ad.AdminID
                        ORDER BY ul.LoggedID ASC";
                $result = $conn->query($sql);
                if ($result && $result->num_rows > 0) {
                  while ($row = $result->fetch_assoc()) {
                    echo '<tr>';
                    echo '<td>' . htmlspecialchars($row['LoggedID']) . '</td>';
                    echo '<td>' . htmlspecialchars($row['AccountUsername'] ?? 'N/A') . '</td>';
                    echo '<td>' . htmlspecialchars($row['LoggedIn'] ?? '-') . '</td>';
                    $loggedOutDisplay = (!empty($row['LoggedOut']) && $row['LoggedOut'] !== '0000-00-00 00:00:00') ? $row['LoggedOut'] : '-';
                    echo '<td>' . htmlspecialchars($loggedOutDisplay) . '</td>';
                    $modifiedByDisplay = !empty($row['ModifiedByUsername']) ? $row['ModifiedByUsername'] : '--';
                    echo '<td>' . htmlspecialchars($modifiedByDisplay) . '</td>';
                    $modifiedDateDisplay = (!empty($row['ModifiedDate']) && $row['ModifiedDate'] !== '0000-00-00 00:00:00') ? $row['ModifiedDate'] : '--';
                    echo '<td>' . htmlspecialchars($modifiedDateDisplay) . '</td>';
                    $accountIdForEdit = isset($row['AccountID']) ? (int)$row['AccountID'] : 0;
                    $editHref = $accountIdForEdit > 0 ? ('edit_userlogged.php?id=' . urlencode($row['LoggedID'])) : ('edit_userlogged.php?id=' . urlencode($row['LoggedID']));
                    echo '<td>';
                    echo '<button class="btn-edit" onclick="window.location.href=\'' . $editHref . '\'">Edit</button> ';
                    echo '<button class="btn-delete" onclick="deleteUserLogged(' . (int)$row['LoggedID'] . ')">Delete</button>';
                    echo '</td>';
                    echo '</tr>';
                  }
                } else {
                  echo '<tr>';
                  echo '<td style="text-align:center;">No user logged records found</td>';
                  echo '<td></td><td></td><td></td><td></td><td></td><td></td>';
                  echo '</tr>';
                }
              ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <script>
      $(function(){
        const table = $('#userLoggedTable').DataTable({
          scrollX: true,
          autoWidth: false,
          pageLength: 25,
          lengthMenu: [10,25,50,100],
          order: [[0,'asc']]
        });
        
        // Show success message if exists
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
      });
      
      function deleteUserLogged(id) {
        console.log('Delete function called with ID:', id);
        if (typeof SnapAlert === 'undefined') {
          console.error('SnapAlert is not loaded!');
          if (confirm('Are you sure you want to delete this user logged record?')) {
            window.location.href = 'delete_userlogged.php?id=' + encodeURIComponent(id);
          }
          return;
        }
        SnapAlert.confirm({
          title: 'Delete Record',
          message: 'Are you sure you want to delete this user logged record?',
          okText: 'Delete',
          cancelText: 'Cancel',
          okBtnClass: 'btn btn-danger'
        }).then(function(confirmed){
          console.log('SnapAlert confirmed:', confirmed);
          if (confirmed) {
            window.location.href = 'delete_userlogged.php?id=' + encodeURIComponent(id);
          }
        });
      }
    </script>
  </body>
</html>

