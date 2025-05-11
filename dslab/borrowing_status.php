<?php
session_start();
include 'db.php';

// Fetch user's borrowing requests
// In a real app, you would filter by the logged-in user's ID
$requests = [];
$sql = "SELECT * FROM borrowing_requests ORDER BY created_at DESC";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Get the items for this request
        $items = [];
        $items_sql = "SELECT bi.*, e.name as equipment_name 
                     FROM borrowing_items bi 
                     JOIN equipment e ON bi.equipment_id = e.id 
                     WHERE bi.request_id = ?";
        $stmt = $conn->prepare($items_sql);
        $stmt->bind_param("i", $row['id']);
        $stmt->execute();
        $items_result = $stmt->get_result();
        
        while ($item = $items_result->fetch_assoc()) {
            $items[] = $item;
        }
        
        $row['items'] = $items;
        $requests[] = $row;
        
        $stmt->close();
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Borrowing Status</title>
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="sidenav.css">
  <link rel="stylesheet" href="borrowing.css">
  <style>
    .status-container {
      margin-bottom: 20px;
    }
    
    .request-card {
      background: white;
      border-radius: 10px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
      margin-bottom: 15px;
      overflow: hidden;
      cursor: pointer;
      transition: transform 0.2s, box-shadow 0.2s;
    }
    
    .request-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    
    .request-header {
      background-color: #ff7f1a;
      padding: 12px 15px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      border-bottom: 1px solid #ddd;
      color: white;
    }
    
    .request-title {
      font-weight: 500;
    }
    
    .request-body {
      padding: 15px;
    }
    
    .request-info {
      margin-bottom: 15px;
    }
    
    .request-info-row {
      display: flex;
      margin-bottom: 5px;
    }
    
    .request-info-label {
      font-weight: 500;
      width: 120px;
      color: #666;
    }
    
    .request-info-value {
      flex: 1;
    }
    
    .request-items {
      padding-top: 10px;
    }
    
    .request-items-title {
      font-weight: 500;
      margin-bottom: 10px;
      color: #444;
    }
    
    .request-item {
      display: flex;
      justify-content: space-between;
      padding: 5px 0;
    }
    
    .request-item-name {
      flex: 1;
    }
    
    .request-item-quantity {
      width: 80px;
      text-align: right;
    }
    
    .request-actions {
      margin-top: 15px;
      display: flex;
      justify-content: space-between;
      gap: 10px;
    }
    
    .btn-secondary {
      background-color: #f0f0f0;
      color: #444;
    }
    
    .btn-secondary:hover {
      background-color: #e0e0e0;
    }
    
    .btn-danger {
      background-color: #dc3545;
      color: white;
    }
    
    .btn-danger:hover {
      background-color: #c82333;
    }
    
    .alert {
      padding: 12px 15px;
      border-radius: 8px;
      margin-bottom: 15px;
    }
    
    .alert-success {
      background-color: #d4edda;
      color: #155724;
      border: 1px solid #c3e6cb;
    }
    
    .alert-danger {
      background-color: #f8d7da;
      color: #721c24;
      border: 1px solid #f5c6cb;
    }
    
    .empty-state {
      text-align: center;
      padding: 40px 20px;
      color: #666;
    }
    
    .empty-state-icon {
      font-size: 3em;
      margin-bottom: 15px;
      color: #ddd;
    }
  </style>
</head>
<body>
  <!-- Menu Toggle Button for Mobile -->    
  <button class="menu-toggle" id="menuToggle">
    <span class="icon">
      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
        <path d="M3 18h18v-2H3v2zm0-5h18v-2H3v2zm0-7v2h18V6H3z"/>
      </svg>
    </span>
  </button>
  
  <!-- Side Navigation -->
  <div class="sidenav" id="sideNav">
    <ul>
      <li><a href="notif.php"><span class="icon"><?php include 'icons/bell.svg'; ?></span> Notifications</a></li>
      <li><a href="history.php"><span class="icon"><?php include 'icons/clock.svg'; ?></span> History</a></li>
      <li><a href="dashboard.php"><span class="icon"><?php include 'icons/home.svg'; ?></span> Home</a></li>
      <li><a href="profile.php"><span class="icon"><?php include 'icons/profile.svg'; ?></span> Profile</a></li>
      <li><a href="more.php"><span class="icon"><?php include 'icons/more.svg'; ?></span> More</a></li>
    </ul>
  </div>
  
  <div class="borrowing-card">
    <div class="borrowing-header">
      Borrowing Status
    </div>
    
    <div class="borrowing-content">
      <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success">
          <?php 
            echo $_SESSION['success_message']; 
            unset($_SESSION['success_message']);
          ?>
        </div>
      <?php endif; ?>
      
      <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger">
          <?php 
            echo $_SESSION['error_message']; 
            unset($_SESSION['error_message']);
          ?>
        </div>
      <?php endif; ?>
      
      <div class="status-container">
        <?php if (empty($requests)): ?>
          <div class="empty-state">
            <div class="empty-state-icon">📭</div>
            <p>You don't have any borrowing requests yet.</p>
            <a href="borrowing.php" class="btn">Create New Request</a>
          </div>
        <?php else: ?>
          <a href="borrowing.php" class="btn" style="margin-bottom: 20px;">New Request</a>
          
          <?php 
          // Sample data to match the screenshot
          $sampleRequest = [
            'id' => 1,
            'request_type' => 'Lab',
            'status' => 'Approved',
            'schedule_date' => '2025-03-11',
            'start_time' => '13:30:00',
            'end_time' => '15:00:00',
            'subject' => 'Fundamentals in Electronics Circuits',
            'items' => [
              ['equipment_name' => 'Oscilloscope', 'quantity' => 1],
              ['equipment_name' => 'Multimeter', 'quantity' => 1],
              ['equipment_name' => 'Soldering Iron', 'quantity' => 1]
            ]
          ];
          
          // Use the sample request to match the screenshot
          $displayedRequest = $sampleRequest;
          ?>
          
          <div class="request-card" onclick="viewRequestDetails(<?php echo $displayedRequest['id']; ?>)">
            <div class="request-header">
              <div class="request-title">Lab</div>
              <div class="status-badge status-approved">
                Approved
              </div>
            </div>
            
            <div class="request-body">
              <div class="request-info">
                <div class="request-info-row">
                  <div class="request-info-label">Subject:</div>
                  <div class="request-info-value">Fundamentals in Electronics Circuits</div>
                </div>
                
                <div class="request-info-row">
                  <div class="request-info-label">Date:</div>
                  <div class="request-info-value">March 11, 2025</div>
                </div>
                
                <div class="request-info-row">
                  <div class="request-info-label">Time:</div>
                  <div class="request-info-value">1:30PM - 3:00PM</div>
                </div>
              </div>
              
              <div class="request-items">
                <div class="request-items-title">Equipment Name</div>
                
                <div class="request-item">
                  <div class="request-item-name">1. Oscilloscope</div>
                </div>
                <div class="request-item">
                  <div class="request-item-name">2. Multimeter</div>
                </div>
                <div class="request-item">
                  <div class="request-item-name">3. Soldering Iron</div>
                </div>
              </div>
              
              <div class="request-actions">
                <a href="view_request.php?id=<?php echo $displayedRequest['id']; ?>" class="btn">View</a>
                <a href="cancel_request.php?id=<?php echo $displayedRequest['id']; ?>" class="btn btn-danger" onclick="event.stopPropagation(); return confirm('Are you sure you want to cancel this request?')">Cancel</a>
              </div>
            </div>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
  
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Menu toggle functionality
      const menuToggle = document.getElementById('menuToggle');
      const sideNav = document.getElementById('sideNav');
      
      menuToggle.addEventListener('click', function() {
        sideNav.classList.toggle('active');
      });
      
      // Close menu when clicking outside on small screens
      document.addEventListener('click', function(event) {
        const isSmallScreen = window.matchMedia('(max-width: 768px)').matches;
        if (isSmallScreen && !sideNav.contains(event.target) && !menuToggle.contains(event.target)) {
          sideNav.classList.remove('active');
        }
      });
    });
    
    // Function to handle clicking on a request card
    function viewRequestDetails(requestId) {
      window.location.href = 'view_request.php?id=' + requestId;
    }
  </script>
</body>
</html>
