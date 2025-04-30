<?php
session_start();

if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit();
}
?>

<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="sidenav.css">
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
    
    <!-- Main Content -->    
    <div class="dashboard-container">
      <div class="logo">
        <?php include 'icons/dsB.svg'; ?>
      </div>
      <div class="welcome-text">
        <span>Welcome</span>
        <div class="username"><?php echo htmlspecialchars($_SESSION['username']); ?></div>
      </div>
      <a class="main-action" href="labday.php"><span class="icon"><?php include 'icons/calendar.svg'; ?></span> My Lab Day</a>
      <div class="action-row">
        <a class="action-btn" href="request-status.php"><span class="icon"><?php include 'icons/check.svg'; ?></span> Request Status</a>
        <a class="action-btn" href="pending-return.php"><span class="icon"><?php include 'icons/return.svg'; ?></span> Pending Return</a>
      </div>
      <div class="action-row">
        <a class="action-btn" href="direct-request.php"><span class="icon"><?php include 'icons/request.svg'; ?></span> Direct Request</a>
        <a class="action-btn" href="equipment-info.php"><span class="icon"><?php include 'icons/equipment.svg'; ?></span> Equipment Info</a>
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
