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

$successFlash = isset($_SESSION['snap_success']) ? $_SESSION['snap_success'] : '';
$errorFlash = isset($_SESSION['snap_error']) ? $_SESSION['snap_error'] : '';
unset($_SESSION['snap_success']);
unset($_SESSION['snap_error']);

error_log("Account verified, proceeding to display home page");

// Handle success and error messages
$successMessage = '';
$errorMessage = '';
if (isset($_GET['success'])) {
    switch($_GET['success']) {
        case '1': $successMessage = "Candidate added successfully!"; break;
        case '2': $successMessage = "Candidate updated successfully!"; break;
        case '3': $successMessage = "Candidate deleted successfully!"; break;
        default: $successMessage = "Operation completed successfully!"; break;
    }
}
if (isset($_GET['error'])) {
    switch($_GET['error']) {
        case '1': $errorMessage = "Failed to add candidate. Please try again."; break;
        case '2': $errorMessage = "Failed to update candidate. Please try again."; break;
        case '3': $errorMessage = "Failed to delete candidate. Please try again."; break;
        case '4': $errorMessage = "Candidate already exists."; break;
        default: $errorMessage = "An error occurred. Please try again."; break;
    }
}

?>
<html>
  <head>
    <title>Candidate Management</title>
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
      /* Make table horizontally scrollable via DataTables */
      .dataTables_wrapper .dataTables_scrollBody { overflow-x: auto !important; }
      #candidateTable { width: 100% !important; }

      /* Agendas column: wrap and size based on content */
      #candidateTable th:nth-child(6),
      #candidateTable td:nth-child(6) {
        white-space: normal !important;
        overflow: visible !important;
        text-overflow: initial !important;
        width: auto !important;
        min-width: 200px; /* give some breathing room */
      }

      /* Agenda list items formatting */
      #candidateTable .agenda-list { margin: 0; padding-left: 18px; }
      #candidateTable .agenda-item { margin: 0 0 4px 0; }

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
          <h2>Candidate Management</h2>
          
        </div>
        <div class="search-filter">
          <a href="add_candidate.php" id="addCandidateBtn" class="btn-add">Add Candidate</a>
          <div class="search-filter-controls">
         
          <select id="filterElection" class="filter-select">
            <option value="ALL">All Elections</option>
            <?php
            // Fetch all elections for the dropdown with their names
            $electionQuery = "SELECT DISTINCT c.ElectionID, e.ElectionName 
                             FROM candidate c 
                             LEFT JOIN election e ON c.ElectionID = e.ElectionID 
                             ORDER BY c.ElectionID ASC";
            $electionResult = $conn->query($electionQuery);
            while ($electionRow = $electionResult->fetch_assoc()) {
                $electionName = $electionRow['ElectionName'] ?: 'Election ' . $electionRow['ElectionID'];
                echo "<option value='" . $electionRow['ElectionID'] . "'>" . htmlspecialchars($electionName) . "</option>";
            }
            ?>
          </select>
          <select id="filterGradeLevel" class="filter-select">
            <option value="ALL">All Grade Levels</option>
            <option value="7">Grade 7</option>
            <option value="8">Grade 8</option>
            <option value="9">Grade 9</option>
            <option value="10">Grade 10</option>
            <option value="11">Grade 11</option>
            <option value="12">Grade 12</option>
          </select>
          <select id="filterPosition" class="filter-select">
            <option value="ALL">All Positions</option>
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
            <!-- Add more positions as needed -->
          </select>
          </div>

        </div>
        <div class="table-container">
          <table id="candidateTable" class="management-table display nowrap" style="width:100%">
            <thead>
              <tr>
                <th>Election</th>
                <th>Name</th>
                <th>Partylist</th>
                <th>Position</th>
                <th>Grade Level</th>
                <th>Agendas</th>
                <th>Created By</th>
                <th>Created Date</th>
                <th>Modified By</th>
                <th>Modified Date</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $sql = "SELECT c.CandidateID, c.Name, c.PartylistName, c.Position, c.GradeLevel, 
                             c.ElectionID,
                             c.CreatedBy, c.CreatedDate, c.ModifiedBy, c.ModifiedDate,
                             cb.Username AS CreatedByUsername,
                             mb.Username AS ModifiedByUsername,
                             GROUP_CONCAT(a.Agenda SEPARATOR '|') as Agendas,
                             GROUP_CONCAT(a.AgendaID SEPARATOR '|') as AgendaIDs
                      FROM candidate c
                      LEFT JOIN agenda a ON c.CandidateID = a.CandidateID
                      LEFT JOIN admin cb ON c.CreatedBy = cb.AdminID
                      LEFT JOIN admin mb ON c.ModifiedBy = mb.AdminID
                      GROUP BY c.CandidateID";
                $result = $conn->query($sql);
                while ($row = $result->fetch_assoc()) {
                $agendas = $row['Agendas'] ? explode('|', $row['Agendas']) : [];
                echo "<tr>";
                echo "<td>{$row['ElectionID']}</td>";
                echo "<td>{$row['Name']}</td>";
                echo "<td>{$row['PartylistName']}</td>";
                echo "<td>{$row['Position']}</td>";
                echo "<td>{$row['GradeLevel']}</td>";
                echo "<td>";
                if (!empty($agendas)) {
                    echo "<ul class='agenda-list'>";
                    foreach ($agendas as $agenda) {
                        echo "<li class='agenda-item'>" . htmlspecialchars($agenda) . "</li>";
                    }
                    echo "</ul>";
                  }
                echo "<div class='agenda-actions'>
                        <button class='btn-agenda addAgendaBtn' data-candidate='{$row['CandidateID']}'>Add Agenda</button>
                        <button class='btn-agenda removeAgendaBtn' data-candidate='{$row['CandidateID']}' data-agendas='" . htmlspecialchars(json_encode(array_map(null, $agendas, explode('|', $row['AgendaIDs'])))) . "'>Remove Agenda</button>
                      </div>";
                echo  "</td>";
                echo "<td>" . ($row['CreatedByUsername'] !== null ? htmlspecialchars($row['CreatedByUsername']) : 'N/A') . "</td>";
                echo "<td>" . ($row['CreatedDate'] !== null ? htmlspecialchars($row['CreatedDate']) : 'N/A') . "</td>";
                echo "<td>" . ($row['ModifiedByUsername'] !== null ? htmlspecialchars($row['ModifiedByUsername']) : 'N/A') . "</td>";
                echo "<td>" . ($row['ModifiedDate'] !== null ? htmlspecialchars($row['ModifiedDate']) : 'N/A') . "</td>";
                echo "<td>
                    <span class='action-buttons'>
                    <button class='btn-edit editCandidateBtn' onclick='editCandidate({$row['CandidateID']})'>Edit</button>
                    <button class='btn-delete deleteCandidateBtn' onclick='deleteCandidate({$row['CandidateID']})'>Delete</button>
                    </span>
                    </td>";
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
    const table = $('#candidateTable').DataTable({
      scrollX: true,
      autoWidth: false,
      pageLength: 25,
      lengthMenu: [10,25,50,100],
      order: [[0,'asc']],
      columnDefs: [
        { targets: -1, orderable: false, searchable: false, width: 200 }
      ]
    });

    // Flash alerts
    const successMsg = <?php echo json_encode($successFlash); ?>;
    const errorMsg = <?php echo json_encode($errorFlash); ?>;
    if (successMsg) {
      SnapAlert.success({ title: 'Success', message: successMsg });
    }
    if (errorMsg) {
      SnapAlert.error({ title: 'Error', message: errorMsg });
    }

    // Dynamically size columns to max record length (exclude Agendas and Actions)
    function adjustColumnWidths() {
      const ctx = document.createElement('canvas').getContext('2d');
      // Attempt to match table body font
      const sampleCell = document.querySelector('#candidateTable tbody td');
      const computed = sampleCell ? window.getComputedStyle(sampleCell) : null;
      ctx.font = computed ? `${computed.fontWeight} ${computed.fontSize} ${computed.fontFamily}` : '12px Inter, Arial';

      const paddingPx = 24; // left+right padding allowance
      const minColPx = 80;  // minimal width
      const maxColPx = 360; // cap overly long columns
      const measure = (text) => ctx.measureText(String(text || '')).width;

      // Column indexes to process (based on current header order)
      const targetIndexes = [0,1,2,3,4,6,7,8,9];

      targetIndexes.forEach(idx => {
        let maxPx = 0;
        // include header text
        const headerText = ($(table.column(idx).header()).text() || '').trim();
        maxPx = Math.max(maxPx, measure(headerText));
        // scan visible page data for performance; fallback to all data if needed
        table.column(idx, { search: 'applied', page: 'current' }).data().each(function(val){
          const text = (val || '').toString().replace(/<[^>]*>/g,'');
          maxPx = Math.max(maxPx, measure(text));
        });
        const widthPx = Math.min(Math.max(Math.ceil(maxPx) + paddingPx, minColPx), maxColPx);
        // Apply to header and cells via inline style
        $(table.column(idx).header()).css({ minWidth: widthPx + 'px' });
        $(table.column(idx).nodes()).css({ minWidth: widthPx + 'px' });
      });
      table.columns.adjust();
    }

    // initial sizing and on draw (debounced)
    let sizeScheduled = false;
    table.on('draw', function(){
      if (sizeScheduled) return;
      sizeScheduled = true;
      requestAnimationFrame(function(){
        adjustColumnWidths();
        sizeScheduled = false;
      });
    });
    // initial call
    adjustColumnWidths();

    // Hook filters to DataTables
    function applyFilters(){
      const election = ($('#filterElection').val()||'ALL');
      const grade = ($('#filterGradeLevel').val()||'ALL').toUpperCase();
      const position = ($('#filterPosition').val()||'ALL').toUpperCase();

      $.fn.dataTable.ext.search = $.fn.dataTable.ext.search || [];
      $.fn.dataTable.ext.search = $.fn.dataTable.ext.search.filter(() => false);
      $.fn.dataTable.ext.search.push(function(settings, data){
        const elec = (data[0]||'').toString();
        const pos = (data[3]||'').toUpperCase();
        const grd = (data[4]||'').toUpperCase();
        let ok = true;
        if (election !== 'ALL' && elec !== election) ok = false;
        if (grade !== 'ALL' && grd !== grade) ok = false;
        if (position !== 'ALL' && pos !== position) ok = false;
        return ok;
      });
      table.draw(false);
    }

    $('#filterElection, #filterGradeLevel, #filterPosition').on('change', applyFilters);

    $('#addCandidateBtn').on('click', function(e){
      e.preventDefault();
      window.location.href = 'add_candidate.php';
    });
  });

function editCandidate(id) {
  window.location.href = 'edit_candidate.php?id=' + id;
}

function deleteCandidate(id) {
  SnapAlert.confirm({
    title: 'Delete Candidate',
    message: 'Delete this candidate and all their agendas?',
    okText: 'Delete',
    cancelText: 'Cancel',
    okBtnClass: 'btn btn-danger'
  }).then(function(confirmed){
    if (confirmed) {
      window.location.href = 'delete_candidate.php?id=' + id;
    }
  });
}

// Add Agenda Modal
  function showAddAgendaModal(candidateId) {
    // Create modal HTML if not exists
    let modal = document.getElementById('addAgendaModal');
    if (!modal) {
      modal = document.createElement('div');
      modal.id = 'addAgendaModal';
      modal.style.position = 'fixed';
      modal.style.top = '0';
      modal.style.left = '0';
      modal.style.width = '100vw';
      modal.style.height = '100vh';
      modal.style.background = 'rgba(0,0,0,0.6)';
      modal.style.display = 'flex';
      modal.style.alignItems = 'center';
      modal.style.justifyContent = 'center';
      modal.innerHTML = `
        <div style="background:#222;padding:30px;border-radius:10px;max-width:400px;width:100%;color:#fff;position:relative;">
          <h3>Add Agenda</h3>
          <form id="agendaForm">
            <input type="hidden" name="candidate_id" id="agendaCandidateId">
            <label>
              <textarea name="agenda" id="agendaText" rows="3" style="width:100%;margin-top:10px;" required></textarea>
            </label>
            <div style="margin-top:15px;display:flex;gap:10px;justify-content:flex-end;">
              <button type="button" id="cancelAgendaBtn" style="background:#888;color:#fff;border:none;padding:8px 16px;border-radius:5px;cursor:pointer;">Cancel</button>
              <button type="submit" style="background:#36AE66;color:#fff;border:none;padding:8px 16px;border-radius:5px;cursor:pointer;">Add</button>
            </div>
          </form>
        </div>
      `;
      document.body.appendChild(modal);
    }
    document.getElementById('agendaCandidateId').value = candidateId;
    document.getElementById('agendaText').value = '';
    modal.style.display = 'flex';

    // Cancel button
    document.getElementById('cancelAgendaBtn').onclick = function() {
      modal.style.display = 'none';
    };

    // Form submit
    document.getElementById('agendaForm').onsubmit = function(e) {
      e.preventDefault();
      const cid = document.getElementById('agendaCandidateId').value;
      const agenda = document.getElementById('agendaText').value;
      // AJAX to add_agenda.php
      fetch('add_agenda.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'candidate_id=' + encodeURIComponent(cid) + '&agenda=' + encodeURIComponent(agenda)
      })
      .then(res => res.text())
      .then(data => {
        modal.style.display = 'none';
        location.reload();
      });
    };
  }

document.querySelectorAll('.addAgendaBtn').forEach(btn => {
  btn.addEventListener('click', function() {
    const candidateId = this.getAttribute('data-candidate');
    showAddAgendaModal(candidateId);
  });
});

document.querySelectorAll('.removeAgendaBtn').forEach(btn => {
  btn.addEventListener('click', function() {
    const candidateId = this.getAttribute('data-candidate');
    let agendaPairs = [];
    try {
      agendaPairs = JSON.parse(this.getAttribute('data-agendas'));
    } catch (e) {}

    // Build modal HTML
    let modal = document.getElementById('deleteAgendaModal');
    if (!modal) {
      modal = document.createElement('div');
      modal.id = 'deleteAgendaModal';
      modal.style.position = 'fixed';
      modal.style.top = '0';
      modal.style.left = '0';
      modal.style.width = '100vw';
      modal.style.height = '100vh';
      modal.style.background = 'rgba(0,0,0,0.6)';
      modal.style.display = 'flex';
      modal.style.alignItems = 'center';
      modal.style.justifyContent = 'center';
      modal.innerHTML = `
        <div style="background:#222;padding:30px;border-radius:10px;max-width:400px;width:100%;color:#fff;position:relative;">
          <h3>Remove Agenda</h3>
          <form id="deleteAgendaForm">
            <label for="agendaSelect">Select agenda to remove:</label>
            <select id="agendaSelect" name="agenda_id" style="width:100%;margin-top:10px;" required></select>
            <div style="margin-top:15px;display:flex;gap:10px;justify-content:flex-end;">
              <button type="button" id="cancelDeleteAgendaBtn" style="background:#888;color:#fff;border:none;padding:8px 16px;border-radius:5px;cursor:pointer;">Cancel</button>
              <button type="submit" style="background:#e74c3c;color:#fff;border:none;padding:8px 16px;border-radius:5px;cursor:pointer;">Remove</button>
            </div>
          </form>
        </div>
      `;
      document.body.appendChild(modal);
    }

    // Populate select options
    const select = modal.querySelector('#agendaSelect');
    select.innerHTML = '';
    agendaPairs.forEach(pair => {
      if (pair && pair[0] && pair[1]) {
        const option = document.createElement('option');
        option.value = pair[1];
        option.textContent = pair[0];
        select.appendChild(option);
      }
    });

    modal.style.display = 'flex';

    // Cancel button
    modal.querySelector('#cancelDeleteAgendaBtn').onclick = function() {
      modal.style.display = 'none';
    };

    // Form submit
    modal.querySelector('#deleteAgendaForm').onsubmit = function(e) {
      e.preventDefault();
      const agendaId = select.value;
      if (!agendaId) return;
      fetch('delete_agenda.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'agenda_id=' + encodeURIComponent(agendaId)
      })
      .then(res => res.text())
      .then(data => {
        modal.style.display = 'none';
        location.reload();
      });
    };
  });
});

  </script>
    <script>
        // Show success or error messages using SnapAlert
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