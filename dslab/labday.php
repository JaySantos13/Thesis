<?php
session_start();
include 'db.php';

// Fetch schedules from the database (adjust table/column names as needed)
$schedules = [];
$sql = "SELECT * FROM schedules ORDER BY schedule_date, start_time";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $schedules[] = $row;
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
  <link rel="stylesheet" href="sidenav.css">
  <link rel="stylesheet" href="notif.css">
  <title>My Lab Day</title>
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
    
    .labday-card {
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
    
    .labday-top-row {
      display: flex;
      align-items: center;
      margin-bottom: 5px;
      position: relative;
      width: 100%;
    }
    
    .labday-back {
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
    
    .labday-back:hover {
      background: #f0f0f0;
    }
    
    .labday-logo-center {
      text-align: center;
      margin-bottom: 5px;
      position: relative;
      z-index: 5;
    }
    
    .labday-logo-center svg,
    .labday-logo-center img {
      max-width: 120px;
      height: auto;
    }
    
    .labday-search-row {
      display: flex;
      gap: 10px;
      margin-bottom: 10px;
      flex-shrink: 0;
    }
    
    .labday-search-row input {
      flex: 1;
      padding: 10px 15px;
      border: 1px solid #ddd;
      border-radius: 20px;
      font-size: 1em;
      outline: none;
    }
    
    .labday-filters {
      background: #f0f0f0;
      border: none;
      padding: 10px 15px;
      border-radius: 20px;
      cursor: pointer;
      font-size: 0.9em;
      white-space: nowrap;
    }
    
    .labday-list {
      overflow-y: auto;
      flex: 1;
      padding-bottom: 5px;
      scrollbar-width: thin;
      scrollbar-color: #ff7f1a #ffe5d0;
    }
    
    .labday-list::-webkit-scrollbar {
      width: 8px;
    }
    
    .labday-list::-webkit-scrollbar-track {
      background: #ffe5d0;
      border-radius: 10px;
    }
    
    .labday-list::-webkit-scrollbar-thumb {
      background-color: #ff7f1a;
      border-radius: 10px;
    }
    
    .labday-item {
      display: flex;
      background: #fff;
      border-radius: 12px;
      margin-bottom: 10px;
      padding: 12px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.05);
      border-left: 4px solid #ff7f1a;
    }
    
    .labday-icon {
      font-size: 1.5em;
      margin-right: 15px;
      color: #ff7f1a;
      display: flex;
      align-items: center;
    }
    
    .labday-details {
      flex: 1;
    }
    
    .labday-subject {
      font-weight: 600;
      color: #333;
      font-size: 1.1em;
    }
    
    .labday-course {
      color: #666;
      font-size: 0.95em;
    }
    
    .labday-date {
      text-align: right;
      color: #888;
      font-size: 0.9em;
      white-space: nowrap;
      margin-left: 10px;
    }
    
    .labday-section {
      text-align: center;
      font-size: 1.15em;
      font-weight: 500;
      margin-bottom: 12px;
      margin-top: 5px;
      color: #333;
    }
    
    @media screen and (max-width: 768px) {
      .labday-card {
        border-radius: 12px;
        padding: 15px;
        max-height: calc(100vh - 100px);
      }
      
      .labday-logo-center svg,
      .labday-logo-center img {
        max-width: 100px;
      }
    }
    
    @media screen and (max-width: 480px) {
      .labday-card {
        border-radius: 0;
        max-width: 100%;
        padding: 12px;
      }
      
      .labday-search-row {
        flex-direction: column;
      }
      
      .labday-filters {
        text-align: center;
      }
      
      .labday-item {
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
  
  <div class="labday-card">
      <div class="labday-top-row">
        <button class="labday-back" onclick="window.location.href='dashboard.php'">&#8592;</button>
      </div>
      <div class="labday-logo-center">
        <?php include 'icons/dsB.svg'; ?>
      </div>
      <h2 class="labday-section" style="text-align:center;font-size:1.15em;font-weight:500;margin-bottom:18px;margin-top:8px;">Laboratory Schedules</h2>
      <form class="labday-search-row" method="get" action="">
        <input type="text" name="search" placeholder="Search" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
        <button type="button" class="labday-filters">&#9881; Filters</button>
      </form>
      <div class="labday-list">
        <?php if (empty($schedules)): ?>
          <!-- Demo item if no data -->
          <div class="labday-item">
            <span class="labday-icon">&#128202;</span>
            <div class="labday-details">
              <span class="labday-subject">Diode & Circuits</span><br>
              <span class="labday-course">Fundametals to Electronics Circuits</span>
            </div>
            <div class="labday-date">March 11, 2025<br>4:30PM-7:30PM</div>
          </div>
        <?php else: ?>
          <?php foreach ($schedules as $sched): ?>
            <div class="labday-item">
              <span class="labday-icon">&#128202;</span>
              <div class="labday-details">
                <span class="labday-subject"><?php echo htmlspecialchars($sched['subject']); ?></span><br>
                <span class="labday-course"><?php echo htmlspecialchars($sched['course']); ?></span>
              </div>
              <div class="labday-date">
                <?php echo date('M j, Y', strtotime($sched['schedule_date'])); ?><br>
                <?php echo date('g:iA', strtotime($sched['start_time'])) . '-' . date('g:iA', strtotime($sched['end_time'])); ?>
              </div>
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
