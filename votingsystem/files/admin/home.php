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

// Handle success and error messages
$successMessage = '';
$errorMessage = '';
if (isset($_GET['success'])) {
    switch($_GET['success']) {
        case '1': $successMessage = "Dashboard data loaded successfully!"; break;
        case '2': $successMessage = "System status updated successfully!"; break;
        case '3': $successMessage = "Settings saved successfully!"; break;
        default: $successMessage = "Operation completed successfully!"; break;
    }
}
if (isset($_GET['error'])) {
    switch($_GET['error']) {
        case '1': $errorMessage = "Failed to load dashboard data. Please try again."; break;
        case '2': $errorMessage = "No active election found."; break;
        case '3': $errorMessage = "Failed to update system status."; break;
        case '4': $errorMessage = "Failed to save settings."; break;
        default: $errorMessage = "An error occurred. Please try again."; break;
    }
}

// Fetch dashboard data
$dashboard_data = [];

// Get current active election
$election_query = "SELECT ElectionID, ElectionName FROM election ORDER BY ElectionID DESC LIMIT 1";
$election_result = $conn->query($election_query);
$current_election = $election_result->fetch_assoc();

if ($current_election) {
    $election_id = $current_election['ElectionID'];
    
    // Get total voters
    $total_voters_query = "SELECT COUNT(*) as total FROM account WHERE AccountStatus = 'ACTIVE'";
    $total_voters_result = $conn->query($total_voters_query);
    $total_voters = $total_voters_result->fetch_assoc()['total'];
    
    // Get total votes cast
    $total_votes_query = "SELECT COUNT(DISTINCT AccountID) as total FROM vote WHERE ElectionID = ?";
    $stmt = $conn->prepare($total_votes_query);
    $stmt->bind_param("s", $election_id);
    $stmt->execute();
    $total_votes_result = $stmt->get_result();
    $total_votes = $total_votes_result->fetch_assoc()['total'];
    
    // Get winning candidates by position
    $winners_query = "SELECT 
                        c.Position,
                        c.Name,
                        c.PartylistName,
                        COUNT(v.AccountID) as VoteCount
                     FROM candidate c
                     LEFT JOIN vote v ON c.CandidateID = v.CandidateID
                     WHERE c.ElectionID = ?
                     GROUP BY c.CandidateID, c.Position, c.Name, c.PartylistName
                     ORDER BY c.Position, VoteCount DESC";
    
    $stmt = $conn->prepare($winners_query);
    $stmt->bind_param("s", $election_id);
    $stmt->execute();
    $winners_result = $stmt->get_result();
    
    $winners_by_position = [];
    while ($row = $winners_result->fetch_assoc()) {
        $position = $row['Position'];
        if (!isset($winners_by_position[$position]) || $winners_by_position[$position]['VoteCount'] < $row['VoteCount']) {
            $winners_by_position[$position] = $row;
        }
    }
    
    // Get vote distribution by position for charts
    $chart_data_query = "SELECT 
                           c.Position,
                           c.Name,
                           c.PartylistName,
                           COUNT(v.AccountID) as VoteCount
                        FROM candidate c
                        LEFT JOIN vote v ON c.CandidateID = v.CandidateID
                        WHERE c.ElectionID = ?
                        GROUP BY c.CandidateID, c.Position, c.Name, c.PartylistName
                        ORDER BY c.Position, VoteCount DESC";
    
    $stmt = $conn->prepare($chart_data_query);
    $stmt->bind_param("s", $election_id);
    $stmt->execute();
    $chart_data_result = $stmt->get_result();
    
    $chart_data = [];
    while ($row = $chart_data_result->fetch_assoc()) {
        $position = $row['Position'];
        if (!isset($chart_data[$position])) {
            $chart_data[$position] = [];
        }
        $chart_data[$position][] = $row;
    }
    
    $dashboard_data = [
        'election' => $current_election,
        'total_voters' => $total_voters,
        'total_votes' => $total_votes,
        'voter_turnout' => $total_voters > 0 ? round(($total_votes / $total_voters) * 100, 1) : 0,
        'winners' => $winners_by_position,
        'chart_data' => $chart_data
    ];
}
?>
<html>
  <head>
    <title>Admin Dashboard - Home</title>
    <link rel="icon" type="image/png" href="/votingsystem/images/logo.png">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="/votingsystem/files/maincss/main.css">
    <link rel="stylesheet" type="text/css" href="/votingsystem/files/admin/admin.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/snapalert@1.0.6/dist/snapalert.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/snapalert@1.0.6/dist/snapalert.min.css">
    <style>
      .dashboard-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 20px;
      }
      
      .dashboard-header {
        background: rgba(0, 0, 0, 0.7);
        color: white;
        padding: 30px;
        border-radius: 15px;
        margin-bottom: 30px;
        text-align: center;
        box-shadow: 0 10px 30px rgba(0,0,0,0.3);
      }
      
      .dashboard-title {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 10px;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
      }
      
      .dashboard-subtitle {
        font-size: 1.2rem;
        opacity: 0.9;
        margin-bottom: 20px;
      }
      
      .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
      }
      
      .stat-card {
        background: rgba(0, 0, 0, 0.7);
        padding: 25px;
        border-radius: 15px;
        text-align: center;
        box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
      }
      
      .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.15);
      }
      
      .stat-number {
        font-size: 2.5rem;
        font-weight: 700;
        color: white;
        margin-bottom: 10px;
      }
      
      .stat-label {
        font-size: 1rem;
        color: white;
        font-weight: 500;
        opacity: 0.9;
      }
      
      .winners-section {
        background: rgba(0, 0, 0, 0.7);
        padding: 30px;
        border-radius: 15px;
        margin-bottom: 30px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.3);
      }
      
      .section-title {
        font-size: 1.8rem;
        font-weight: 600;
        color: white;
        margin-bottom: 25px;
        text-align: center;
      }
      
      .winners-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
        max-width: 1200px;
        margin: 0 auto;
      }
      
      @media (max-width: 1024px) {
        .winners-grid {
          grid-template-columns: repeat(2, 1fr);
        }
      }
      
      @media (max-width: 768px) {
        .winners-grid {
          grid-template-columns: 1fr;
        }
      }
      
      .winner-card {
        background: linear-gradient(135deg, rgba(76, 175, 80, 0.8) 0%, rgba(69, 160, 73, 0.8) 100%);
        color: white;
        padding: 20px;
        border-radius: 12px;
        text-align: center;
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        transition: transform 0.3s ease;
      }
      
      .winner-card.empty-position {
        background: linear-gradient(135deg, rgba(158, 158, 158, 0.8) 0%, rgba(117, 117, 117, 0.8) 100%);
      }
      
      .winner-card:hover {
        transform: scale(1.05);
      }
      
      .winner-position {
        font-size: 1.1rem;
        font-weight: 600;
        margin-bottom: 10px;
        opacity: 0.9;
      }
      
      .winner-name {
        font-size: 1.4rem;
        font-weight: 700;
        margin-bottom: 8px;
      }
      
      .winner-party {
        font-size: 1rem;
        opacity: 0.8;
        margin-bottom: 10px;
      }
      
      .winner-votes {
        font-size: 1.2rem;
        font-weight: 600;
        background: rgba(255,255,255,0.2);
        padding: 5px 15px;
        border-radius: 20px;
        display: inline-block;
      }
      
      .charts-section {
        background: rgba(0, 0, 0, 0.7);
        padding: 30px;
        border-radius: 15px;
        margin-bottom: 30px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.3);
      }
      
      .charts-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
        gap: 30px;
        margin-top: 25px;
      }
      
      .chart-container {
        background: white;
        padding: 20px;
        border-radius: 12px;
        box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        min-height: 400px;
        width: 100%;
        display: flex;
        flex-direction: column;
      }
      
      .chart-title {
        font-size: 1.2rem;
        font-weight: 600;
        color: #333;
        margin-bottom: 15px;
        text-align: center;
        flex-shrink: 0;
      }
      
      .chart-canvas-container {
        flex: 1;
        position: relative;
        min-height: 300px;
        width: 100%;
      }
      
      .position-selector {
        text-align: center;
        margin-bottom: 30px;
        padding: 20px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 10px;
      }
      
      .position-selector label {
        display: block;
        color: white;
        font-size: 1.1rem;
        font-weight: 600;
        margin-bottom: 15px;
      }
      
      .position-dropdown {
        padding: 12px 20px;
        font-size: 1rem;
        border: 2px solid #667eea;
        border-radius: 8px;
        background: white;
        color: #333;
        min-width: 250px;
        cursor: pointer;
        transition: all 0.3s ease;
      }
      
      .position-dropdown:focus {
        outline: none;
        border-color: #4CAF50;
        box-shadow: 0 0 10px rgba(76, 175, 80, 0.3);
      }
      
      .chart-display-area {
        display: flex;
        justify-content: center;
        align-items: stretch;
        min-height: 500px;
        width: 100%;
      }
      
      .no-position-selected {
        text-align: center;
        color: #666;
        font-size: 1.2rem;
        padding: 40px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 10px;
        border: 2px dashed #ccc;
      }
      
      .no-election {
        background: rgba(255, 255, 255, 0.95);
        padding: 50px;
        border-radius: 15px;
        text-align: center;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
      }
      
      .no-election h2 {
        color: #666;
        font-size: 1.5rem;
        margin-bottom: 15px;
      }
      
      .no-election p {
        color: #888;
        font-size: 1.1rem;
      }
      
      @media (max-width: 768px) {
        .dashboard-container {
          padding: 10px;
        }
        
        .dashboard-title {
          font-size: 2rem;
        }
        
        .stats-grid {
          grid-template-columns: 1fr;
        }
        
        .winners-grid {
          grid-template-columns: 1fr;
        }
        
        .charts-grid {
          grid-template-columns: 1fr;
        }
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
          <a class="active" href="home.php">Home</a>
          <a href="generatereport.php">Report</a>
          <div class="dropdown">
            <a >Management</a>
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
      <div class="dashboard-container">
        <?php if (!empty($dashboard_data)): ?>
          <!-- Dashboard Header -->
          <div class="dashboard-header">
            <h1 class="dashboard-title">Admin Dashboard</h1>
            <p class="dashboard-subtitle">
              <?php echo htmlspecialchars($dashboard_data['election']['ElectionName'] ?? 'Current Election'); ?>
            </p>
          </div>

          <!-- Statistics Cards -->
          <div class="stats-grid">
            <div class="stat-card">
              <div class="stat-number"><?php echo number_format($dashboard_data['total_voters']); ?></div>
              <div class="stat-label">Total Registered Voters</div>
            </div>
            <div class="stat-card">
              <div class="stat-number"><?php echo number_format($dashboard_data['total_votes']); ?></div>
              <div class="stat-label">Total Votes Cast</div>
            </div>
            <div class="stat-card">
              <div class="stat-number"><?php echo $dashboard_data['voter_turnout']; ?>%</div>
              <div class="stat-label">Voter Turnout</div>
            </div>
                         <div class="stat-card">
               <div class="stat-number"><?php echo $dashboard_data['total_votes'] > 0 ? count($dashboard_data['winners']) : 0; ?></div>
               <div class="stat-label">Positions Filled</div>
             </div>
          </div>

          <!-- Winning Candidates -->
          <div class="winners-section">
            <h2 class="section-title">Election Winners</h2>
            <div class="winners-grid">
              <?php
                             // Define the order of positions
               $position_order = [
                 'PRESIDENT', 'VICE PRESIDENT', 'SECRETARY',
                 'TREASURER', 'AUDITOR', 'PUBLIC INFORMATION OFFICER',
                 'PROTOCOL OFFICER', 'GRADE 8 REPRESENTATIVE',
                 'GRADE 9 REPRESENTATIVE', 'GRADE 10 REPRESENTATIVE', 'GRADE 11 REPRESENTATIVE',
                 'GRADE 12 REPRESENTATIVE'
               ];
              
              // Display winners in the specified order
              foreach ($position_order as $position):
                if (isset($dashboard_data['winners'][$position])):
                  $winner = $dashboard_data['winners'][$position];
              ?>
                <div class="winner-card">
                  <div class="winner-position"><?php echo htmlspecialchars($position); ?></div>
                  <div class="winner-name"><?php echo htmlspecialchars($winner['Name']); ?></div>
                  <div class="winner-party"><?php echo htmlspecialchars($winner['PartylistName']); ?></div>
                  <div class="winner-votes"><?php echo number_format($winner['VoteCount']); ?> votes</div>
                </div>
              <?php 
                else:
              ?>
                <div class="winner-card empty-position">
                  <div class="winner-position"><?php echo htmlspecialchars($position); ?></div>
                  <div class="winner-name">No Candidate</div>
                  <div class="winner-party">-</div>
                  <div class="winner-votes">0 votes</div>
                </div>
              <?php 
                endif;
              endforeach; 
              ?>
            </div>
          </div>

          <!-- Charts Section -->
          <div class="charts-section">
            <h2 class="section-title">Vote Distribution Charts</h2>
            
            <!-- Position Selector -->
            <div class="position-selector">
              <label for="positionSelect">Select Position:</label>
                             <select id="positionSelect" class="position-dropdown">
                 <option value="">-- Select Position --</option>
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
            </div>
            
            <!-- Chart Display Area -->
            <div class="chart-display-area">
              <div id="chartContainer" class="chart-container" style="display: none;">
                <h3 id="chartTitle" class="chart-title"></h3>
                <div class="chart-canvas-container">
                  <canvas id="selectedChart"></canvas>
                </div>
              </div>
              
              <div id="noPositionSelected" class="no-position-selected">
                <p>Please select a position from the dropdown above to view the vote distribution chart.</p>
              </div>
            </div>
          </div>

        <?php else: ?>
          <!-- No Active Election -->
          <div class="no-election">
            <h2>No Active Election</h2>
            <p>There is currently no active election to display results for.</p>
            <p>Please check the voting session management to start a new election.</p>
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

        <?php if (!empty($dashboard_data)): ?>
    <script>
      // Store chart data for all positions
      const chartData = <?php echo json_encode($dashboard_data['chart_data']); ?>;
      let currentChart = null;
      
      // Initialize position selector functionality
      document.addEventListener('DOMContentLoaded', function() {
        const positionSelect = document.getElementById('positionSelect');
        const chartContainer = document.getElementById('chartContainer');
        const chartTitle = document.getElementById('chartTitle');
        const noPositionSelected = document.getElementById('noPositionSelected');
        
        positionSelect.addEventListener('change', function() {
          const selectedPosition = this.value;
          
          if (selectedPosition && chartData[selectedPosition]) {
            // Show chart container and hide no position message
            chartContainer.style.display = 'flex';
            noPositionSelected.style.display = 'none';
            
            // Update chart title
            chartTitle.textContent = selectedPosition;
            
            // Destroy existing chart if it exists
            if (currentChart) {
              currentChart.destroy();
            }
            
            // Create new chart for selected position
            const ctx = document.getElementById('selectedChart').getContext('2d');
            const candidates = chartData[selectedPosition];
            
            currentChart = new Chart(ctx, {
              type: 'bar',
              data: {
                labels: candidates.map(c => c.Name),
                datasets: [{
                  label: 'Votes',
                  data: candidates.map(c => c.VoteCount),
                  backgroundColor: [
                    '#4CAF50',
                    '#45a049',
                    '#66BB6A',
                    '#81C784',
                    '#A5D6A7',
                    '#C8E6C9',
                    '#E8F5E8',
                    '#F1F8E9'
                  ],
                  borderColor: [
                    '#4CAF50',
                    '#45a049',
                    '#66BB6A',
                    '#81C784',
                    '#A5D6A7',
                    '#C8E6C9',
                    '#E8F5E8',
                    '#F1F8E9'
                  ],
                  borderWidth: 2
                }]
              },
                           options: {
               responsive: true,
               maintainAspectRatio: false,
               scales: {
                 y: {
                   beginAtZero: true,
                   ticks: {
                     stepSize: 1
                   }
                 }
               },
               plugins: {
                 legend: {
                   display: false
                 }
               }
             }
            });
          } else {
            // Hide chart container and show no position message
            chartContainer.style.display = 'none';
            noPositionSelected.style.display = 'block';
            
            // Destroy existing chart if it exists
            if (currentChart) {
              currentChart.destroy();
              currentChart = null;
            }
          }
        });
      });
    </script>
    <?php endif; ?>

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

  </body>
</html>