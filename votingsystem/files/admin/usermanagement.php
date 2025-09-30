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
    <title>User Management</title>
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
        /* Message Modal Styles */
        .modal-message {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            animation: fadeIn 0.3s ease-in-out;
        }

        .modal-message-content {
            background-color: #222;
            margin: 15% auto;
            padding: 30px;
            border-radius: 15px;
            width: 400px;
            text-align: center;
            position: relative;
            border: 2px solid #333;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
            animation: slideIn 0.3s ease-out;
        }

        .close-message {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            position: absolute;
            right: 20px;
            top: 15px;
            cursor: pointer;
            transition: color 0.3s;
        }

        .close-message:hover {
            color: #fff;
        }

        #messageIcon {
            width: 60px;
            height: 60px;
            margin: 0 auto 20px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 30px;
        }

        .success-icon {
            background-color: #4caf50;
            color: white;
        }

        .error-icon {
            background-color: #f44336;
            color: white;
        }

        #messageTitle {
            color: #fff;
            margin: 0 0 15px 0;
            font-size: 24px;
            font-weight: 600;
        }

        #messageText {
            color: #ccc;
            margin: 0 0 25px 0;
            font-size: 16px;
            line-height: 1.5;
        }

        #messageOkBtn {
            background-color: #36AE66;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        #messageOkBtn:hover {
            background-color: #2d8f54;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideIn {
            from { transform: translateY(-50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        /* Table adjustments for additional columns */
        .table-container {
            overflow-x: auto;
        }
        
        .management-table {
            min-width: 1000px;
            table-layout: fixed;
        }
        
        .management-table th,
        .management-table td {
            padding: 6px 4px;
            font-size: 10px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        /* Ensure uniform font size across all record content */
        .management-table,
        .management-table th,
        .management-table td,
        .management-table td div,
        .management-table button {
            font-size: 12px !important;
        }
        
        /* Actions column sizing and overflow */
        .management-table th:nth-child(13),
        .management-table td:nth-child(13) {
            width: 240px;
            min-width: 240px;
            white-space: nowrap;
            overflow: visible;
        }
        
        /* Transparent black background for DataTables "Show entries" combobox */
        .dataTables_wrapper .dataTables_length select {
            background-color: rgba(255, 255, 255, 0.5);
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
                <h2>User Management</h2>
            </div>
            
            <?php 
            $successMessage = '';
            $errorMessage = '';
            
            if (isset($_GET['success'])) {
                switch($_GET['success']) {
                    case '1': $successMessage = "User added successfully!"; break;
                    case '2': $successMessage = "User updated successfully!"; break;
                    case '3': $successMessage = "User deleted successfully!"; break;
                    default: $successMessage = "Operation completed successfully!"; break;
                }
            }
            
            if (isset($_GET['error'])) {
                switch($_GET['error']) {
                    case 'cannot_delete_self': $errorMessage = "You cannot delete your own account!"; break;
                    case 'delete_failed': $errorMessage = "Failed to delete user account."; break;
                    case 'user_not_found': $errorMessage = "User account not found."; break;
                    case 'invalid_id': $errorMessage = "Invalid user ID."; break;
                    case 'duplicate_username': $errorMessage = "Username already exists!"; break;
                    case 'add_failed': $errorMessage = "Failed to add user account."; break;
                    case 'update_failed': $errorMessage = "Failed to update user account."; break;
                    default: $errorMessage = "An error occurred."; break;
                }
            }
            ?>

            <div class="search-filter">
                <a href="add_user.php" class="btn-add">Add New User</a>
                <div class="search-filter-controls">
                    <select id="filterGradeLevel" onchange="searchAndFilterUsers()">
                        <option value="all">All Grade Levels</option>
                        <option value="Grade 7">Grade 7</option>
                        <option value="Grade 8">Grade 8</option>
                        <option value="Grade 9">Grade 9</option>
                        <option value="Grade 10">Grade 10</option>
                        <option value="Grade 11">Grade 11</option>
                        <option value="Grade 12">Grade 12</option>
                    </select>
                    <select id="filterStatus" onchange="searchAndFilterUsers()">
                        <option value="all">All Account Status</option>
                        <option value="Active">Active</option>
                        <option value="Blocked">Blocked</option>
                    </select>
                    <select id="filterVoteStatus" onchange="searchAndFilterUsers()">
                        <option value="all">All Vote Status</option>
                        <option value="Not Voted">Not Voted</option>
                        <option value="Voted">Voted</option>
                    </select>
                </div>
            </div>

            <div class="table-container">
                <table id="userTable" class="management-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Grade Level</th>
                            <th>Name</th>
                            <th>Username</th>
                            <th>Account Status</th>
                            <th>Vote Status</th>
                            <th>Created By</th>
                            <th>Created Date</th>
                            <th>Modified By</th>
                            <th>Modified Date</th>
                            <th>Admin Modified By</th>
                            <th>Admin Modified Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Fetch users from database with join to get creator and modifier names
                        $query = "SELECT a.*, 
                                        c.Username as CreatedByUsername,
                                        m.Username as ModifiedByUsername,
                                        am.Username as AdminModifiedByUsername
                                 FROM account a 
                                 LEFT JOIN admin c ON a.CreatedBy = c.AdminID 
                                 LEFT JOIN account m ON a.ModifiedBy = m.AccountID 
                                 LEFT JOIN admin am ON a.AdminModifiedBy = am.AdminID 
                                 ORDER BY a.AccountID ASC";
                        $result = $conn->query($query);

                        if ($result->num_rows > 0) {
                            while($row = $result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>" . $row['AccountID'] . "</td>";
                                echo "<td>" . $row['GradeLevel']. "</td>";
                                echo "<td>" . $row['Name'] . "</td>";
                                echo "<td>" . $row['Username']. "</td>";  
                                $status = $row['AccountStatus'] ?: 'ACTIVE';
                                $statusBgColor = ($status == 'ACTIVE') ? '#4caf50' : '#f44336';
                                echo "<td><div style='background-color: $statusBgColor; color: white; padding: 2px 6px; border-radius: 8px; font-size: 9px; font-weight: 600; display: inline-block; min-width: 50px; text-align: center;'>$status</div></td>";  
                                $voteStatus = $row['VoteStatus'] ?: 'NOT VOTED';
                                $vStatusBgColor = ($voteStatus == 'NOT VOTED') ? '#ff9800' : '#4caf50';
                                echo "<td><div style='background-color: $vStatusBgColor; color: white; padding: 2px 6px; border-radius: 8px; font-size: 9px; font-weight: 600; display: inline-block; min-width: 50px; text-align: center;'>$voteStatus</div></td>";

                                echo "<td>" . ($row['CreatedByUsername'] ?: 'System') . "</td>";
                                echo "<td>" . ($row['CreatedDate'] ?: 'N/A') . "</td>";
                                echo "<td>" . ($row['ModifiedByUsername'] ?: 'N/A') . "</td>";
                                echo "<td>" . ($row['ModifiedDate'] ?: 'N/A') . "</td>";
                                echo "<td>" . ($row['AdminModifiedByUsername'] ?: 'N/A') . "</td>";
                                echo "<td>" . ($row['AdminModifiedDate'] ?: 'N/A') . "</td>";
                                echo "<td>";
                                echo "<span class='action-buttons'>";
                                echo "<button class='btn-edit' onclick='editUser(" . $row['AccountID'] . ")' style='font-size: 9px; padding: 3px 6px; margin: 1px;'><i class='fas fa-edit'></i> Edit</button>";
                                echo "<button class='btn-delete' onclick='deleteUser(" . $row['AccountID'] . ")' style='font-size: 9px; padding: 3px 6px; margin: 1px;'><i class='fas fa-trash'></i> Delete</button>";
                                echo "<button class='btn-show-key' onclick=\"showSecurityKey('" . htmlspecialchars(addslashes($row['SecurityKey'])) . "')\" style='font-size: 9px; padding: 3px 6px; margin: 1px;'>Show Key</button>";
                                echo "</span>";
                                echo "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='13' class='no-data'>No users found</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Success/Error Popup Modal -->
    <div id="messageModal" class="modal-message">
        <div class="modal-message-content">
            <span class="close-message" onclick="closeMessageModal()">&times;</span>
            <div id="messageIcon"></div>
            <h3 id="messageTitle"></h3>
            <p id="messageText"></p>
            <button id="messageOkBtn" onclick="closeMessageModal()">OK</button>
        </div>
    </div>

    <div id="securityKeyModal" class="modal-key">
        <div class="modal-key-content">
            <span class="close-key" onclick="closeSecurityKeyModal()">&times;</span>
            <h3>Security Key</h3>
            <div id="securityKeyValue" style="font-size: 1.3em; margin: 20px 0;"></div>
            <button id="copyKeyBtn" class="btn-show-key" onclick="copySecurityKey()">Copy</button>
            <div id="copiedMsg" style="color:#36AE66; margin-top:10px; display:none;">Copied!</div>
        </div>
    </div>


    <script>
let dataTableFilterRegistered = false;

function initDataTable() {
    if (window.jQuery && $.fn.dataTable && !$.fn.dataTable.isDataTable('#userTable')) {
        const table = $('#userTable').DataTable({
            scrollX: true,
            autoWidth: false,
            pageLength: 25,
            lengthMenu: [10, 25, 50, 100],
            order: [[0, 'asc']],
            searching: true,
            columnDefs: [
                { targets: 12, orderable: false, searchable: false, width: '240px' }
            ]
        });
        table.columns.adjust();

        if (!dataTableFilterRegistered) {
            $.fn.dataTable.ext.search.push(function(settings, data) {
                const gradeLevelFilter = (document.getElementById('filterGradeLevel').value || '').toUpperCase();
                const accountStatusFilter = (document.getElementById('filterStatus').value || '').toUpperCase();
                const voteStatusFilter = (document.getElementById('filterVoteStatus').value || '').toUpperCase();

                const gradeLevel = (data[1] || '').toUpperCase();
                const accountStatus = (data[4] || '').toUpperCase();
                const voteStatus = (data[5] || '').toUpperCase();

                let gradeLevelMatch = true;
                if (gradeLevelFilter !== 'ALL') {
                    const filterMatch = gradeLevelFilter.match(/\d+/);
                    const rowMatch = gradeLevel.match(/\d+/);
                    const filterNum = filterMatch ? parseInt(filterMatch[0]) : null;
                    const rowNum = rowMatch ? parseInt(rowMatch[0]) : null;
                    gradeLevelMatch = filterNum === rowNum;
                }

                const accountStatusMatch = (accountStatusFilter === 'ALL') || (accountStatus.trim() === accountStatusFilter);
                const voteStatusMatch = (voteStatusFilter === 'ALL') || (voteStatus.trim() === voteStatusFilter);

                return gradeLevelMatch && accountStatusMatch && voteStatusMatch;
            });
            dataTableFilterRegistered = true;
        }

        return table;
    }
}

function searchAndFilterUsers() {
    if (window.jQuery && $.fn.dataTable && $.fn.dataTable.isDataTable('#userTable')) {
        const table = $('#userTable').DataTable();
        table.draw();
    } else {
        // If DataTable isn't ready, try to initialize it
        initDataTable();
    }
}

        function editUser(id) {
            // Implement edit functionality
            window.location.href = 'edit_user.php?id=' + id;
        }

        function deleteUser(id) {
            SnapAlert.confirm({
                title: 'Delete User',
                message: 'Are you sure you want to delete this user? This action cannot be undone.',
                okText: 'Delete',
                cancelText: 'Cancel',
                okBtnClass: 'btn btn-danger'
            }).then(function(confirmed){
                if (confirmed) {
                    window.location.href = 'delete_user.php?id=' + id;
                }
            });
        }

        function showSecurityKey(key) {
            document.getElementById('securityKeyValue').textContent = key;
            document.getElementById('securityKeyModal').style.display = 'block';
            document.getElementById('copiedMsg').style.display = 'none';
        }

        function closeSecurityKeyModal() {
            document.getElementById('securityKeyModal').style.display = 'none';
        }

        function copySecurityKey() {
            const key = document.getElementById('securityKeyValue').textContent;
            navigator.clipboard.writeText(key).then(function() {
                document.getElementById('copiedMsg').style.display = 'block';
                setTimeout(function() {
                    document.getElementById('copiedMsg').style.display = 'none';
                }, 1500);
            });
        }

        // Close modal when clicking outside or pressing Escape
        window.onclick = function(event) {
            const modal = document.getElementById('addUserModal');
            const keyModal = document.getElementById('securityKeyModal');
            const messageModal = document.getElementById('messageModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
            if (event.target == keyModal) {
                keyModal.style.display = 'none';
            }
            if (event.target == messageModal) {
                messageModal.style.display = 'none';
            }
        }

        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeSecurityKeyModal();
                closeMessageModal();
            }
        });

        // Message Modal Functions
        function showMessageModal(type, title, message) {
            const modal = document.getElementById('messageModal');
            const icon = document.getElementById('messageIcon');
            const titleEl = document.getElementById('messageTitle');
            const textEl = document.getElementById('messageText');
            
            // Set icon and styling based on type
            if (type === 'success') {
                icon.className = 'success-icon';
                icon.innerHTML = '✓';
            } else {
                icon.className = 'error-icon';
                icon.innerHTML = '✕';
            }
            
            titleEl.textContent = title;
            textEl.textContent = message;
            modal.style.display = 'block';
            
            // Auto-hide after 5 seconds
            setTimeout(() => {
                if (modal.style.display === 'block') {
                    closeMessageModal();
                }
            }, 5000);
        }

        function closeMessageModal() {
            document.getElementById('messageModal').style.display = 'none';
        }

        // Check for messages on page load and initialize DataTable
        document.addEventListener('DOMContentLoaded', function() {
            initDataTable();
            searchAndFilterUsers();
            
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