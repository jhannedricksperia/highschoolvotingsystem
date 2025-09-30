<?php
session_start();
error_log("=== Generate Report Page Accessed ===");
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

error_log("Account verified, proceeding to display report page");

// Handle success and error messages
$successMessage = '';
$errorMessage = '';
if (isset($_GET['success'])) {
    switch($_GET['success']) {
        case '1': $successMessage = "Report generated successfully!"; break;
        case '2': $successMessage = "Report exported to PDF successfully!"; break;
        case '3': $successMessage = "Report data refreshed successfully!"; break;
        default: $successMessage = "Operation completed successfully!"; break;
    }
}
if (isset($_GET['error'])) {
    switch($_GET['error']) {
        case '1': $errorMessage = "Failed to generate report. Please try again."; break;
        case '2': $errorMessage = "No election data available."; break;
        case '3': $errorMessage = "Failed to export report to PDF."; break;
        case '4': $errorMessage = "No voting data available for the current election."; break;
        default: $errorMessage = "An error occurred. Please try again."; break;
    }
}

// Fetch report data
$report_data = [];

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
    
    // Get all candidates with vote counts for detailed results
    $detailed_results_query = "SELECT 
                                c.Position,
                                c.Name,
                                c.PartylistName,
                                COUNT(v.AccountID) as VoteCount
                             FROM candidate c
                             LEFT JOIN vote v ON c.CandidateID = v.CandidateID
                             WHERE c.ElectionID = ?
                             GROUP BY c.CandidateID, c.Position, c.Name, c.PartylistName
                             ORDER BY c.Position, VoteCount DESC";
    
    $stmt = $conn->prepare($detailed_results_query);
    $stmt->bind_param("s", $election_id);
    $stmt->execute();
    $detailed_results_result = $stmt->get_result();
    
    $detailed_results = [];
    while ($row = $detailed_results_result->fetch_assoc()) {
        $position = $row['Position'];
        if (!isset($detailed_results[$position])) {
            $detailed_results[$position] = [];
        }
        $detailed_results[$position][] = $row;
    }
    
         // Set timezone to Philippine Standard Time
     date_default_timezone_set('Asia/Manila');
     
     $report_data = [
         'election' => $current_election,
         'total_voters' => $total_voters,
         'total_votes' => $total_votes,
         'voter_turnout' => $total_voters > 0 ? round(($total_votes / $total_voters) * 100, 1) : 0,
         'winners' => $winners_by_position,
         'detailed_results' => $detailed_results,
         'generated_date' => date('F j, Y \a\t g:i A')
     ];
}
?>
<html>
  <head>
    <title>Election Report - <?php echo htmlspecialchars($report_data['election']['ElectionName'] ?? 'Voting System'); ?></title>
    <link rel="icon" type="image/png" href="/votingsystem/images/logo.png">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="/votingsystem/files/maincss/main.css">
    <link rel="stylesheet" type="text/css" href="/votingsystem/files/admin/admin.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/snapalert@1.0.6/dist/snapalert.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/snapalert@1.0.6/dist/snapalert.min.css">
    <style>
      .report-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
        background: white;
      }
      
      .report-header {
        text-align: center;
        margin-bottom: 30px;
        padding: 20px;
        border-bottom: 3px solid #333;
      }
      
      .report-title {
        font-size: 2.5rem;
        font-weight: 700;
        color: #333;
        margin-bottom: 10px;
      }
      
      .report-subtitle {
        font-size: 1.2rem;
        color: #666;
        margin-bottom: 15px;
      }
      
      .report-meta {
        font-size: 1rem;
        color: #888;
        margin-bottom: 20px;
      }
      
             .summary-section {
         margin-bottom: 40px;
         padding: 20px;
         border: 2px solid #333;
         border-radius: 10px;
         break-inside: avoid;
         page-break-inside: avoid;
       }
      
      .section-title {
        font-size: 1.8rem;
        font-weight: 600;
        color: #333;
        margin-bottom: 20px;
        text-align: center;
        border-bottom: 2px solid #333;
        padding-bottom: 10px;
      }
      
      .summary-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 20px;
      }
      
      .summary-item {
        text-align: center;
        padding: 15px;
        background: #f8f9fa;
        border-radius: 8px;
        border: 1px solid #dee2e6;
      }
      
      .summary-number {
        font-size: 2rem;
        font-weight: 700;
        color: #333;
        margin-bottom: 5px;
      }
      
      .summary-label {
        font-size: 0.9rem;
        color: #666;
        font-weight: 500;
      }
      
             .winners-section {
         margin-bottom: 40px;
         padding: 20px;
         border: 2px solid #333;
         border-radius: 10px;
         break-inside: avoid;
         page-break-inside: avoid;
       }
      
      .winners-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 20px;
      }
      
      .winner-card {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
        border: 1px solid #dee2e6;
        text-align: center;
      }
      
      .winner-position {
        font-size: 1.1rem;
        font-weight: 600;
        color: #333;
        margin-bottom: 10px;
      }
      
      .winner-name {
        font-size: 1.3rem;
        font-weight: 700;
        color: #28a745;
        margin-bottom: 8px;
      }
      
      .winner-party {
        font-size: 1rem;
        color: #666;
        margin-bottom: 10px;
      }
      
      .winner-votes {
        font-size: 1.2rem;
        font-weight: 600;
        color: #333;
        background: #e9ecef;
        padding: 8px 15px;
        border-radius: 20px;
        display: inline-block;
      }
      
      .no-winners-message,
      .no-results-message {
        text-align: center;
        padding: 40px 20px;
        background: #f8f9fa;
        border: 2px dashed #dee2e6;
        border-radius: 10px;
        color: #666;
      }
      
      .no-winners-message p,
      .no-results-message p {
        font-size: 1.1rem;
        margin: 0;
        font-style: italic;
      }
      
             .detailed-results {
         margin-bottom: 40px;
         padding: 20px;
         border: 2px solid #333;
         border-radius: 10px;
         break-inside: avoid;
         page-break-inside: avoid;
       }
      
             .position-results {
         margin-bottom: 30px;
         break-inside: avoid;
         page-break-inside: avoid;
       }
      
      .position-title {
        font-size: 1.4rem;
        font-weight: 600;
        color: #333;
        margin-bottom: 15px;
        padding: 10px;
        background: #f8f9fa;
        border-radius: 5px;
        border-left: 4px solid #007bff;
      }
      
      .candidate-list {
        list-style: none;
        padding: 0;
      }
      
      .candidate-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 15px;
        margin-bottom: 8px;
        background: white;
        border: 1px solid #dee2e6;
        border-radius: 5px;
      }
      
      .candidate-info {
        flex: 1;
      }
      
      .candidate-name {
        font-weight: 600;
        color: #333;
        margin-bottom: 3px;
      }
      
      .candidate-party {
        font-size: 0.9rem;
        color: #666;
      }
      
      .candidate-votes {
        font-weight: 600;
        color: #007bff;
        font-size: 1.1rem;
      }
      
      .winner-badge {
        background: #28a745;
        color: white;
        padding: 4px 8px;
        border-radius: 12px;
        font-size: 0.8rem;
        font-weight: 600;
        margin-left: 10px;
      }
      
      .action-buttons {
        text-align: center;
        margin: 30px 50px 0 50px;
        padding: 20px;
        background: #f8f9fa;
        border-radius: 10px;
        border: 1px solid #dee2e6;
      }
      
      .btn {
        display: inline-block;
        padding: 12px 24px;
        margin: 0 10px;
        font-size: 1rem;
        font-weight: 600;
        text-decoration: none;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.3s ease;
        border: none;
      }
      
      .btn-print {
        background: #007bff;
        color: white;
      }
      
      .btn-print:hover {
        background: #0056b3;
      }
      
      .btn-pdf {
        background: #dc3545;
        color: white;
      }
      
      .btn-pdf:hover {
        background: #c82333;
      }
      
      .btn-back {
        background: #6c757d;
        color: white;
      }
      
      .btn-back:hover {
        background: #545b62;
      }
      
      .no-election {
        text-align: center;
        padding: 50px;
        background: #f8f9fa;
        border-radius: 10px;
        border: 1px solid #dee2e6;
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
      
             @media print {
         header, footer, .action-buttons {
           display: none !important;
         }
         
         .report-container {
           padding: 0;
           margin: 0;
         }
         
         .summary-section, .winners-section, .detailed-results {
           border: 1px solid #000;
           break-inside: avoid;
           page-break-inside: avoid;
         }
         
         .winners-grid {
           grid-template-columns: repeat(2, 1fr);
         }
         
         .position-results {
           break-inside: avoid;
           page-break-inside: avoid;
         }
         
         .winner-card {
           break-inside: avoid;
           page-break-inside: avoid;
         }
         
         .candidate-item {
           break-inside: avoid;
           page-break-inside: avoid;
         }
         
         /* Reduce font sizes for print/PDF */
         .report-title {
           font-size: 1.8rem !important;
         }
         
         .report-subtitle {
           font-size: 0.9rem !important;
         }
         
         .report-meta {
           font-size: 0.8rem !important;
         }
         
         .section-title {
           font-size: 1.3rem !important;
         }
         
         .summary-number {
           font-size: 1.5rem !important;
         }
         
         .summary-label {
           font-size: 0.7rem !important;
         }
         
         .winner-position {
           font-size: 0.8rem !important;
         }
         
         .winner-name {
           font-size: 1.1rem !important;
         }
         
         .winner-party {
           font-size: 0.7rem !important;
         }
         
         .winner-votes {
           font-size: 0.9rem !important;
         }
         
         .position-title {
           font-size: 1.1rem !important;
         }
         
         .candidate-name {
           font-size: 0.8rem !important;
         }
         
         .candidate-party {
           font-size: 0.6rem !important;
         }
         
         .candidate-votes {
           font-size: 0.8rem !important;
         }
         
         .winner-badge {
           font-size: 0.6rem !important;
         }
         
         .no-winners-message p,
         .no-results-message p {
           font-size: 0.8rem !important;
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
          <a href="home.php">Home</a>
          <a class="active" href="generatereport.php">Report</a>
          <div class="dropdown">
            <a>Management</a>
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
      <div class="report-container" id="reportContent">
        <?php if (!empty($report_data)): ?>
          <!-- Report Header -->
          <div class="report-header">
            <h1 class="report-title">Election Report</h1>
            <p class="report-subtitle"><?php echo htmlspecialchars($report_data['election']['ElectionName']); ?></p>
            <p class="report-meta">Generated on: <?php echo $report_data['generated_date']; ?></p>
          </div>

          <!-- Summary Section -->
          <div class="summary-section">
            <h2 class="section-title">Election Summary</h2>
            <div class="summary-grid">
              <div class="summary-item">
                <div class="summary-number"><?php echo number_format($report_data['total_voters']); ?></div>
                <div class="summary-label">Total Registered Voters</div>
              </div>
              <div class="summary-item">
                <div class="summary-number"><?php echo number_format($report_data['total_votes']); ?></div>
                <div class="summary-label">Total Votes Cast</div>
              </div>
              <div class="summary-item">
                <div class="summary-number"><?php echo $report_data['voter_turnout']; ?>%</div>
                <div class="summary-label">Voter Turnout</div>
              </div>
              <div class="summary-item">
                <div class="summary-number"><?php echo $report_data['total_votes'] > 0 ? count($report_data['winners']) : 0; ?></div>
                <div class="summary-label">Positions Filled</div>
              </div>
            </div>
          </div>

          <!-- Winners Section -->
          <?php if ($report_data['total_votes'] > 0): ?>
            <div class="winners-section">
              <h2 class="section-title">Election Winners</h2>
              <div class="winners-grid">
                <?php
                $position_order = [
                  'PRESIDENT', 'VICE PRESIDENT', 'SECRETARY',
                  'TREASURER', 'AUDITOR', 'PUBLIC INFORMATION OFFICER',
                  'PROTOCOL OFFICER', 'GRADE 8 REPRESENTATIVE',
                  'GRADE 9 REPRESENTATIVE', 'GRADE 10 REPRESENTATIVE', 'GRADE 11 REPRESENTATIVE',
                  'GRADE 12 REPRESENTATIVE'
                ];
                
                foreach ($position_order as $position):
                  if (isset($report_data['winners'][$position])):
                    $winner = $report_data['winners'][$position];
                ?>
                  <div class="winner-card">
                    <div class="winner-position"><?php echo htmlspecialchars($position); ?></div>
                    <div class="winner-name"><?php echo htmlspecialchars($winner['Name']); ?></div>
                    <div class="winner-party"><?php echo htmlspecialchars($winner['PartylistName']); ?></div>
                    <div class="winner-votes"><?php echo number_format($winner['VoteCount']); ?> votes</div>
                  </div>
                <?php 
                  endif;
                endforeach; 
                ?>
              </div>
            </div>
          <?php else: ?>
            <div class="winners-section">
              <h2 class="section-title">Election Winners</h2>
              <div class="no-winners-message">
                <p>No winners to display. No votes have been cast in this election yet.</p>
              </div>
            </div>
          <?php endif; ?>

          <!-- Detailed Results Section -->
          <?php if ($report_data['total_votes'] > 0): ?>
            <div class="detailed-results">
              <h2 class="section-title">Detailed Results by Position</h2>
              <?php foreach ($position_order as $position): ?>
                <?php if (isset($report_data['detailed_results'][$position])): ?>
                  <div class="position-results">
                    <h3 class="position-title"><?php echo htmlspecialchars($position); ?></h3>
                    <ul class="candidate-list">
                      <?php 
                      $candidates = $report_data['detailed_results'][$position];
                      // Sort by vote count descending
                      usort($candidates, function($a, $b) {
                        return $b['VoteCount'] - $a['VoteCount'];
                      });
                      
                      foreach ($candidates as $candidate): 
                        $is_winner = isset($report_data['winners'][$position]) && 
                                    $report_data['winners'][$position]['Name'] === $candidate['Name'];
                      ?>
                        <li class="candidate-item">
                          <div class="candidate-info">
                            <div class="candidate-name"><?php echo htmlspecialchars($candidate['Name']); ?></div>
                            <div class="candidate-party"><?php echo htmlspecialchars($candidate['PartylistName']); ?></div>
                          </div>
                          <div class="candidate-votes">
                            <?php echo number_format($candidate['VoteCount']); ?> votes
                            <?php if ($is_winner): ?>
                              <span class="winner-badge">WINNER</span>
                            <?php endif; ?>
                          </div>
                        </li>
                      <?php endforeach; ?>
                    </ul>
                  </div>
                <?php endif; ?>
              <?php endforeach; ?>
            </div>
          <?php else: ?>
            <div class="detailed-results">
              <h2 class="section-title">Detailed Results by Position</h2>
              <div class="no-results-message">
                <p>No detailed results to display. No votes have been cast in this election yet.</p>
              </div>
            </div>
          <?php endif; ?>

        <?php else: ?>
          <!-- No Active Election -->
          <div class="no-election">
            <h2>No Active Election</h2>
            <p>There is currently no active election to generate a report for.</p>
            <p>Please check the voting session management to start a new election.</p>
          </div>
        <?php endif; ?>
      </div>

      <!-- Action Buttons -->
      <?php if (!empty($report_data)): ?>
        <div class="action-buttons">
          <button class="btn btn-print" onclick="window.print()">Print Report</button>
          <button class="btn btn-pdf" onclick="generatePDF()">Export as PDF</button>
          <a href="home.php" class="btn btn-back">← Back to Dashboard</a>
        </div>
      <?php endif; ?>
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

    <script>
             function generatePDF() {
         const element = document.getElementById('reportContent');
         const opt = {
           margin: 0.5,
           filename: 'election_report_<?php date_default_timezone_set('Asia/Manila'); echo date('Y-m-d_H-i-s'); ?>.pdf',
           image: { type: 'jpeg', quality: 0.9 },
           html2canvas: { scale: 1.5, useCORS: true, allowTaint: true },
           jsPDF: { 
             unit: 'in', 
             format: 'a4', 
             orientation: 'portrait',
             compress: true
           },
           pagebreak: { mode: ['avoid-all', 'css', 'legacy'] }
         };

        // Hide action buttons temporarily
        const actionButtons = document.querySelector('.action-buttons');
        if (actionButtons) actionButtons.style.display = 'none';

        html2pdf().set(opt).from(element).save().then(() => {
          // Show action buttons again
          if (actionButtons) actionButtons.style.display = 'block';
        });
      }
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
  </body>
</html>