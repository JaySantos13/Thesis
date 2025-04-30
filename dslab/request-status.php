<?php
session_start();
include 'db.php';

$requests = [];
$sql = "SELECT * FROM requests ORDER BY schedule_date, start_time";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $requests[] = $row;
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="requeststat.css">
  <link rel="stylesheet" href="sidenav.css">
  <title>Request Status</title>
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
    
    .request-card {
      background: #fff;
      border-radius: 18px;
      max-width: 420px;
      width: 100%;
      margin: 0 auto;
      box-shadow: 0 2px 12px rgba(0,0,0,0.1);
      padding: 20px;
      box-sizing: border-box;
      display: flex;
      flex-direction: column;
      max-height: calc(100vh - 120px);
      overflow: hidden;
    }
    
    .request-top-row {
      display: flex;
      align-items: center;
      margin-bottom: 15px;
      position: relative;
      width: 100%;
    }
    
    .request-back {
      position: absolute;
      left: 0;
      top: 0;
      background: none;
      border: none;
      font-size: 24px;
      cursor: pointer;
      color: #444;
      width: 40px;
      height: 40px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: background 0.2s;
      z-index: 10;
    }
    
    .request-back:hover {
      background: #f0f0f0;
    }
    
    .request-logo-center {
      text-align: center;
      margin-bottom: 10px;
    }
    
    .request-logo-center svg,
    .request-logo-center img {
      max-width: 120px;
      height: auto;
    }
    
    .request-header {
      text-align: center;
      font-size: 1.25em;
      font-weight: 400;
      letter-spacing: 0.5px;
      margin-bottom: 18px;
      color: #222;
    }
    
    .request-search-row {
      display: flex;
      gap: 10px;
      margin-bottom: 15px;
      flex-shrink: 0;
    }
    
    .request-search-row input {
      flex: 1;
      padding: 10px 15px;
      border: 1px solid #ddd;
      border-radius: 20px;
      font-size: 1em;
      outline: none;
    }
    
    .request-filters {
      background: #f0f0f0;
      border: none;
      padding: 10px 15px;
      border-radius: 20px;
      cursor: pointer;
      font-size: 0.9em;
      white-space: nowrap;
    }
    
    .request-list {
      overflow-y: auto;
      flex: 1;
      padding-bottom: 10px;
      scrollbar-width: thin;
      scrollbar-color: #ff7f1a #ffe5d0;
    }
    
    .request-list::-webkit-scrollbar {
      width: 8px;
    }
    
    .request-list::-webkit-scrollbar-track {
      background: #ffe5d0;
      border-radius: 10px;
    }
    
    .request-list::-webkit-scrollbar-thumb {
      background-color: #ff7f1a;
      border-radius: 10px;
    }
    
    .request-item {
      display: flex;
      background: #fff;
      border-radius: 14px;
      margin-bottom: 15px;
      padding: 15px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.05);
      border-left: 4px solid #ff7f1a;
      flex-wrap: wrap;
    }
    
    .request-icon {
      font-size: 1.5em;
      margin-right: 15px;
      color: #ff7f1a;
      display: flex;
      align-items: center;
    }
    
    .request-details {
      flex: 1;
      min-width: 0;
    }
    
    .request-subject {
      font-weight: 600;
      color: #333;
      font-size: 1.1em;
    }
    
    .request-course {
      color: #666;
      font-size: 0.95em;
    }
    
    .request-date {
      text-align: right;
      color: #888;
      font-size: 0.9em;
      white-space: nowrap;
      margin-left: 10px;
    }
    
    .request-role {
      background: #ff7f1a;
      color: #fff;
      border-radius: 8px;
      padding: 4px 10px;
      margin-left: 10px;
      align-self: center;
      font-size: 0.9em;
    }
    
    @media screen and (max-width: 768px) {
      .request-card {
        border-radius: 12px;
        padding: 15px;
        max-height: calc(100vh - 100px);
      }
      
      .request-logo-center svg,
      .request-logo-center img {
        max-width: 100px;
      }
    }
    
    @media screen and (max-width: 480px) {
      .request-card {
        border-radius: 0;
        max-width: 100%;
        padding: 12px;
      }
      
      .request-search-row {
        flex-direction: column;
      }
      
      .request-filters {
        text-align: center;
      }
      
      .request-item {
        padding: 12px 10px;
      }
      
      .request-role {
        margin-top: 5px;
        margin-left: auto;
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
  
  <div class="request-card">
      <div class="request-top-row">
        <button class="request-back" onclick="window.location.href='dashboard.php'">&#8592;</button>
      </div>
      <div class="request-logo-center" style="position: relative; z-index: 5;">
        <?php include 'icons/dsB.svg'; ?>
      </div>
      <header class="request-header">Request Status</header>
        <form class="request-search-row" method="get" action="">
          <input type="text" name="search" placeholder="Search" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
          <button type="button" class="request-filters">&#9881; Filters</button>
        </form>
        <div class="request-list">
          <?php if (empty($requests)): ?>
            <!-- Demo item if no data -->
            <div class="request-item">
              <span class="request-icon">&#128209;</span>
              <div class="request-details">
                <span class="request-subject">Diode & Circuits</span><br>
                <span class="request-course">Fundametals to Electronics Circuits</span>
              </div>
              <div class="request-date">March 11, 2025<br>4:30PM-7:30PM</div>
              <span class="request-role">Borrower</span>
            </div>
            <div class="request-item">
              <span class="request-icon">&#128209;</span>
              <div class="request-details">
                <span class="request-subject">Diode & Circuits</span><br>
                <span class="request-course">Fundametals to Electronics Circuits</span>
              </div>
              <div class="request-date">March 20, 2025<br>5:30PM-7:30PM</div>
              <span class="request-role">Member</span>
            </div>
          <?php else: ?>
            <?php foreach ($requests as $req): ?>
              <div class="request-item">
                <span class="request-icon">&#128209;</span>
                <div class="request-details">
                  <span class="request-subject"><?php echo htmlspecialchars($req['subject']); ?></span><br>
                  <span class="request-course"><?php echo htmlspecialchars($req['course']); ?></span>
                </div>
                <div class="request-date">
                  <?php echo date('M j, Y', strtotime($req['schedule_date'])); ?><br>
                  <?php echo date('g:iA', strtotime($req['start_time'])) . '-' . date('g:iA', strtotime($req['end_time'])); ?>
                </div>
                <span class="request-role">
                  <?php echo htmlspecialchars($req['role']); ?>
                </span>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
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
