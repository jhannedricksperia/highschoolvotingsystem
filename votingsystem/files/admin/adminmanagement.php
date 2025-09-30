<?php
session_start();
error_log("=== Admin Management Page Accessed ===");
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

error_log("Account verified, proceeding to display admin management page");
?>
<html>
  <head>
    <title>Admin Management</title>
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
        /* DataTables Show entries combobox transparent black */
        .dataTables_wrapper .dataTables_length select {
            background-color: rgba(0, 0, 0, 0.5);
            color: #fff;
            border: 1px solid #333;
            border-radius: 4px;
            padding: 2px 6px;
        }
        .dataTables_wrapper .dataTables_length label { color: #fff; }
        .dataTables_wrapper .dataTables_length select option { color: #000; }

        /* Table horizontal scroll and sizing */
        .dataTables_wrapper .dataTables_scrollBody { overflow-x: auto !important; }
        #adminTable { width: 100% !important; }

        /* Ensure all table content is left-aligned by default */
        #adminTable th,
        #adminTable td {
            text-align: left;
        }

        /* Actions column sizing and overflow */
        #adminTable th:nth-child(8),
        #adminTable td:nth-child(8) {
            width: 200px;
            min-width: 200px;
            white-space: nowrap;
            overflow: visible;
        }

        /* Ensure Actions column has proper spacing (match candidate config) */
        #adminTable th:last-child,
        #adminTable td:last-child {
            min-width: 220px !important;
            white-space: nowrap;
            text-align: center !important;
        }

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
        
        .management-table,
        #adminTable {
            min-width: 1000px;
            table-layout: fixed;
        }
        
        .management-table th,
        .management-table td,
        #adminTable th,
        #adminTable td {
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
        .management-table button,
        #adminTable,
        #adminTable th,
        #adminTable td,
        #adminTable td div,
        #adminTable button {
            font-size: 12px !important;
        }
        
        /* Actions column sizing and overflow */
        .management-table th:nth-child(8),
        .management-table td:nth-child(8),
        #adminTable th:nth-child(8),
        #adminTable td:nth-child(8) {
            width: 240px;
            min-width: 240px;
            white-space: nowrap;
            overflow: visible;
        }
        
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
                <h2>Admin Management</h2>
            </div>
            
            <?php 
            $successMessage = '';
            $errorMessage = '';
            
            if (isset($_GET['success'])) {
                switch($_GET['success']) {
                    case '1': $successMessage = "Admin added successfully!"; break;
                    case '2': $successMessage = "Admin updated successfully!"; break;
                    case '3': $successMessage = "Admin deleted successfully!"; break;
                    default: $successMessage = "Operation completed successfully!"; break;
                }
            }
            
            if (isset($_GET['error'])) {
                switch($_GET['error']) {
                    case 'cannot_delete_self': $errorMessage = "You cannot delete your own account!"; break;
                    case 'delete_failed': $errorMessage = "Failed to delete admin account."; break;
                    case 'admin_not_found': $errorMessage = "Admin account not found."; break;
                    case 'invalid_id': $errorMessage = "Invalid admin ID."; break;
                    default: $errorMessage = "An error occurred."; break;
                }
            }
            ?>

            <div class="search-filter">
                <a href="add_admin.php" class="btn-add">Add New Admin</a>
                <div class="search-filter-controls">
                    <select id="filterStatus" onchange="searchAndFilterAdmins()">
                        <option value="all">ALL</option>
                        <option value="ACTIVE">ACTIVE</option>
                        <option value="BLOCKED">BLOCKED</option>
                    </select>
                </div>
            </div>

            <div class="table-container">
                <table id="adminTable" class="management-table display nowrap" style="width:100%">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Status</th>
                            <th>Created By</th>
                            <th>Created Date</th>
                            <th>Modified By</th>
                            <th>Modified Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Fetch admins from database with join to get creator and modifier names
                        $query = "SELECT a.*, 
                                        c.Username as CreatedByUsername,
                                        m.Username as ModifiedByUsername
                                 FROM admin a 
                                 LEFT JOIN admin c ON a.CreatedBy = c.AdminID 
                                 LEFT JOIN admin m ON a.ModifiedBy = m.AdminID 
                                 ORDER BY a.AdminID ASC";
                        
                        $result = $conn->query($query);
                        if (!$result) {
                            echo "<tr><td colspan='8' class='no-data'>Error fetching admin data: " . $conn->error . "</td></tr>";
                        }

                        if ($result->num_rows > 0) {
                            while($row = $result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>" . $row['AdminID'] . "</td>";
                                echo "<td>" . $row['Username'] . "</td>";
                                $status = $row['Status'] ?: 'ACTIVE';
                                $statusBgColor = ($status == 'ACTIVE') ? '#4caf50' : '#f44336';
                                echo "<td><div style='background-color: $statusBgColor; color: white; padding: 4px 8px; border-radius: 12px; font-size: 12px; font-weight: 600; display: inline-block; min-width: 60px; text-align: center;'>$status</div></td>";
                                echo "<td>" . ($row['CreatedByUsername'] ?: 'System') . "</td>";
                                echo "<td>" . ($row['CreatedDate'] ?: 'N/A') . "</td>";
                                echo "<td>" . ($row['ModifiedByUsername'] ?: 'N/A') . "</td>";
                                echo "<td>" . ($row['ModifiedDate'] ?: 'N/A') . "</td>";
                                echo "<td>";
                                echo "<span class='action-buttons'>";
                                echo "<button class='btn-edit' onclick='editAdmin(" . $row['AdminID'] . ")'><i class='fas fa-edit'></i> Edit</button>";
                                echo "<button class='btn-delete' onclick='deleteAdmin(" . $row['AdminID'] . ")'><i class='fas fa-trash'></i> Delete</button>";
                                if ($row['SecurityKey']) {
                                    echo "<button class='btn-show-key' onclick=\"showSecurityKey('" . htmlspecialchars(addslashes($row['SecurityKey'])) . "')\">Show Security Key</button>";
                                }
                                echo "</span>";
                                echo "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='8' class='no-data'>No admins found</td></tr>";
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
$(function(){
    const table = $('#adminTable').DataTable({
        scrollX: true,
        autoWidth: false,
        fixedHeader: true,   // <--- keep header aligned
        columnDefs: [
            { targets: -1, orderable: false, searchable: false, width: '220px' }
        ]
    });

    table.columns.adjust().draw(); // ensure recalculation after load

    // Dynamically size columns to max record length (exclude Actions)
    function adjustColumnWidths() {
        const ctx = document.createElement('canvas').getContext('2d');
        const sampleCell = document.querySelector('#adminTable tbody td');
        const computed = sampleCell ? window.getComputedStyle(sampleCell) : null;
        ctx.font = computed ? `${computed.fontWeight} ${computed.fontSize} ${computed.fontFamily}` : '12px Inter, Arial';

        const paddingPx = 24;
        const minColPx = 80;
        const maxColPx = 360;
        const measure = (text) => ctx.measureText(String(text || '')).width;

        // indexes 0..6 (exclude 7 which is Actions)
        const targetIndexes = [0,1,2,3,4,5,6];

        targetIndexes.forEach(idx => {
            let maxPx = 0;
            const headerText = ($(table.column(idx).header()).text() || '').trim();
            maxPx = Math.max(maxPx, measure(headerText));
            table.column(idx, { search: 'applied', page: 'current' }).data().each(function(val){
                const text = (val || '').toString().replace(/<[^>]*>/g,'');
                maxPx = Math.max(maxPx, measure(text));
            });
            const widthPx = Math.min(Math.max(Math.ceil(maxPx) + paddingPx, minColPx), maxColPx);
            // Apply min-width (like candidate table)
            $(table.column(idx).header()).css({ minWidth: widthPx + 'px' });
            $(table.column(idx).nodes()).css({ minWidth: widthPx + 'px' });
        });
        table.columns.adjust();
    }

    let scheduled = false;
    table.on('draw', function(){
        if (scheduled) return;
        scheduled = true;
        requestAnimationFrame(function(){
            adjustColumnWidths();
            scheduled = false;
        });
    });
    // Also adjust on window resize for consistent header/body alignment
    let resizeTimer;
    $(window).on('resize', function(){
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function(){
            adjustColumnWidths();
        }, 150);
    });
    adjustColumnWidths();
});

function searchAndFilterAdmins() {
    const input = document.getElementById('searchAdmin');
    const searchFilter = input.value.toUpperCase();
    const statusFilter = document.getElementById('filterStatus').value.toUpperCase();

    const table = document.getElementById('adminTable');
    const tr = table.getElementsByTagName('tr');

    for (let i = 1; i < tr.length; i++) {
        const td = tr[i].getElementsByTagName('td');
        if (td.length >= 8) { // Updated to 8 for current columns
            const username = td[1].textContent || td[1].innerText;
            const status = td[2].textContent || td[2].innerText;

            const usernameMatch = username.toUpperCase().indexOf(searchFilter) > -1;
            const statusMatch = (statusFilter === 'ALL') || 
                               (statusFilter === 'ACTIVE' && status === 'ACTIVE') ||
                               (statusFilter === 'BLOCKED' && status === 'BLOCKED');

            if (usernameMatch && statusMatch) {
                tr[i].style.display = "";
            } else {
                tr[i].style.display = "none";
            }
        }
    }
}

function editAdmin(adminId) {
    window.location.href = 'edit_admin.php?id=' + adminId;
}

function deleteAdmin(adminId) {
    SnapAlert.confirm({
        title: 'Delete Admin',
        message: 'Are you sure you want to delete this admin? This action cannot be undone.',
        okText: 'Delete',
        cancelText: 'Cancel',
        okBtnClass: 'btn btn-danger'
    }).then(function(confirmed){
        if (confirmed) {
            window.location.href = 'delete_admin.php?id=' + adminId;
        }
    });
}

function showSecurityKey(securityKey) {
    document.getElementById('securityKeyValue').textContent = securityKey;
    document.getElementById('securityKeyModal').style.display = 'block';
}

function closeSecurityKeyModal() {
    document.getElementById('securityKeyModal').style.display = 'none';
}

function copySecurityKey() {
    const securityKey = document.getElementById('securityKeyValue').textContent;
    navigator.clipboard.writeText(securityKey).then(function() {
        document.getElementById('copiedMsg').style.display = 'block';
        setTimeout(function() {
            document.getElementById('copiedMsg').style.display = 'none';
        }, 2000);
    });
}

// Close modal when clicking outside of it
window.onclick = function(event) {
    const modal = document.getElementById('securityKeyModal');
    const messageModal = document.getElementById('messageModal');
    if (event.target == modal) {
        modal.style.display = 'none';
    }
    if (event.target == messageModal) {
        messageModal.style.display = 'none';
    }
}

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



// Check for messages on page load
document.addEventListener('DOMContentLoaded', function() {
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
  </body>
</html> 