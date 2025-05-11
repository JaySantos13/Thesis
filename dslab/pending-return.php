<?php
session_start();
include 'db.php';

$pending = [];
$sql = "SELECT * FROM pending_returns ORDER BY request_date DESC";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $pending[] = $row;
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pending Return List</title>
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="pending-return.css">
  <link rel="stylesheet" href="sidenav.css">
  <style>
    body {
      height: 100vh;
      margin: 0;
      overflow: hidden;
      background: #f7f7f7;
      font-family: 'Segoe UI', Arial, sans-serif;
      display: flex;
      flex-direction: column;
      align-items: center;
    }
    
    .pending-card {
      background: #fff;
      border-radius: 18px;
      max-width: 420px;
      width: 100%;
      margin: 0 auto;
      box-shadow: 0 2px 12px rgba(0,0,0,0.1);
      padding: 15px;
      box-sizing: border-box;
      display: flex;
      flex-direction: column;
      max-height: calc(100vh - 80px);
      overflow: hidden;
    }
    
    .pending-top-row {
      display: flex;
      align-items: center;
      margin-bottom: 5px;
      position: relative;
      width: 100%;
    }
    
    .pending-back {
      position: absolute;
      left: 0;
      top: 0;
      background: none;
      border: none;
      font-size: 24px;
      cursor: pointer;
      color: #444;
      width: 36px;
      height: 36px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: background 0.2s;
      z-index: 10;
    }
    
    .pending-back:hover {
      background: #f0f0f0;
    }
    
    .pending-logo-center {
      text-align: center;
      margin-bottom: 5px;
      position: relative;
      z-index: 5;
      width: 100%;
    }
    
    .pending-logo-center svg,
    .pending-logo-center img {
      max-width: 120px;
      height: auto;
    }
    
    .pending-header-bar {
      text-align: center;
      font-size: 1.15em;
      font-weight: 500;
      margin-bottom: 12px;
      margin-top: 5px;
      color: #333;
    }
    
    .pending-search-row {
      display: flex;
      gap: 10px;
      margin-bottom: 10px;
      flex-shrink: 0;
    }
    
    .pending-search-input {
      flex: 1;
      padding: 10px 15px;
      border: 1px solid #ddd;
      border-radius: 20px;
      font-size: 1em;
      outline: none;
    }
    
    .pending-filters {
      background: #f0f0f0;
      border: none;
      padding: 10px 15px;
      border-radius: 20px;
      cursor: pointer;
      font-size: 0.9em;
      white-space: nowrap;
    }
    
    .pending-list {
      overflow-y: auto;
      flex: 1;
      padding-bottom: 5px;
      scrollbar-width: thin;
      scrollbar-color: #ff7f1a #ffe5d0;
    }
    
    .pending-list::-webkit-scrollbar {
      width: 8px;
    }
    
    .pending-list::-webkit-scrollbar-track {
      background: #ffe5d0;
      border-radius: 10px;
    }
    
    .pending-list::-webkit-scrollbar-thumb {
      background-color: #ff7f1a;
      border-radius: 10px;
    }
    
    .pending-item {
      display: flex;
      background: #fff;
      border-radius: 12px;
      margin-bottom: 10px;
      padding: 12px 15px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.05);
      border-left: 4px solid #ff7f1a;
      transition: transform 0.2s, box-shadow 0.2s;
    }
    
    .pending-item:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    
    .pending-details {
      flex: 1;
      min-width: 0;
    }
    
    .pending-subject {
      font-weight: 600;
      color: #333;
      font-size: 1.1em;
    }
    
    .pending-course {
      color: #666;
      font-size: 0.95em;
    }
    
    .pending-date {
      text-align: right;
      color: #888;
      font-size: 0.9em;
      white-space: nowrap;
      margin-left: 10px;
    }
    
    @media screen and (max-width: 768px) {
      .pending-card {
        border-radius: 12px;
        padding: 15px;
        max-height: calc(100vh - 100px);
      }
      
      .pending-logo-center svg,
      .pending-logo-center img {
        max-width: 100px;
      }
    }
    
    @media screen and (max-width: 480px) {
      .pending-card {
        border-radius: 0;
        max-width: 100%;
        padding: 12px;
      }
      
      .pending-search-row {
        flex-direction: column;
      }
      
      .pending-filters {
        text-align: center;
      }
      
      .pending-item {
        padding: 12px 10px;
      }
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
      <li><a class="active" href="dashboard.php"><span class="icon"><?php include 'icons/home.svg'; ?></span> Home</a></li>
      <li><a href="profile.php"><span class="icon"><?php include 'icons/profile.svg'; ?></span> Profile</a></li>
      <li><a href="more.php"><span class="icon"><?php include 'icons/more.svg'; ?></span> More</a></li>
    </ul>
  </div>
  
  <div class="pending-card">
    <div class="pending-top-row">
      <button class="pending-back" onclick="window.location.href='dashboard.php'">&#8592;</button>
      <div class="pending-logo-center">
        <?php include 'icons/dsB.svg'; ?>
      </div>
    </div>
    <div class="pending-header-bar">Pending Request</div>
    <form class="pending-search-row" method="get" action="">
      <input type="text" class="pending-search-input" name="search" placeholder="Search" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
      <button type="button" class="pending-filters">&#128269; Filters</button>
    </form>
    <div class="pending-list">
      <?php if (empty($pending)): ?>
        <!-- Demo items -->
        <div class="pending-item" >
          <div class="pending-details">
            <div class="pending-subject">Diode & Circuits</div>
            <div class="pending-course">Fundametals to Electronics Circuits</div>
          </div>
          <div class="pending-date">Mar 11, 2025</div>
        </div>
        <div class="pending-item" >
          <div class="pending-details">
            <div class="pending-subject">Direct Request</div>
          </div>
          <div class="pending-date">Mar 11, 2025</div>
        </div>
        <div class="pending-item" >
          <div class="pending-details">
            <div class="pending-subject">Oscilloscope</div>
            <div class="pending-course">Digital Electronics Lab</div>
          </div>
          <div class="pending-date">Mar 15, 2025</div>
        </div>
      <?php else: ?>
          <?php foreach ($pending as $row): ?>
            <div class="pending-item" >
              <div class="pending-details">
                <div class="pending-subject"><?php echo htmlspecialchars($row['subject'] ?? ''); ?></div>
                <div class="pending-course"><?php echo htmlspecialchars($row['course'] ?? $row['request_type'] ?? ''); ?></div>
              </div>
              <div class="pending-date"><?php echo isset($row['request_date']) ? date('M j, Y', strtotime($row['request_date'])) : ''; ?></div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>
  
  <!-- JavaScript for menu toggle -->    
  <script>
      document.addEventListener('DOMContentLoaded', function() {
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
  </script>
</body>
</html>
