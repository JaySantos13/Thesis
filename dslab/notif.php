<?php
session_start();
include 'db.php';

// Fetch notifications from DB
$filter = isset($_GET['unread']) ? "WHERE is_unread=1" : "";
$sql = "SELECT * FROM notifications $filter ORDER BY date DESC";
$result = $conn->query($sql);
$notifications = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $notifications[] = $row;
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Notification</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="notif.css">
  <link rel="stylesheet" href="sidenav.css">
</head>
<body class="notif-bg">
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
      <li><a class="active" href="notif.php"><span class="icon"><?php include 'icons/bell.svg'; ?></span> Notifications</a></li>
      <li><a href="history.php"><span class="icon"><?php include 'icons/clock.svg'; ?></span> History</a></li>
      <li><a href="dashboard.php"><span class="icon"><?php include 'icons/home.svg'; ?></span> Home</a></li>
      <li><a href="profile.php"><span class="icon"><?php include 'icons/profile.svg'; ?></span> Profile</a></li>
      <li><a href="more.php"><span class="icon"><?php include 'icons/more.svg'; ?></span> More</a></li>
    </ul>
  </div>
  
  <div class="notif-container">
    <div class="notif-top-card">
      <div class="notif-logo-center">
        <?php include 'icons/dsB.svg'; ?>
      </div>
    </div>
    <div class="notif-header-bar">Notification</div>
    <div class="notif-main-card">
    <form class="notif-search-row" method="get" action="">
      <div class="notif-search-group">
        <input type="text" class="notif-search-input" name="search" placeholder="Search" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
        <button type="submit" class="notif-filter-btn" title="Apply Search/Filter">
          <span class="notif-search-icon">&#128269;</span>
        </button>
      </div>
      <label class="notif-unread-toggle">
        <input type="checkbox" name="unread" value="1" <?php if(isset($_GET['unread'])) echo 'checked'; ?>>
        <span>Unread only</span>
      </label>
    </form>
    <div class="notif-list">
      <?php if (empty($notifications)): ?>
        <div class="notif-item empty">No notifications found.</div>
      <?php else: ?>
        <?php foreach ($notifications as $notif): ?>
          <div class="notif-item<?php echo $notif['is_unread'] ? ' unread' : ''; ?>">
            <span class="notif-icon">
              <?php
                // Choose icon based on type, or use $notif['icon']
                switch ($notif['icon']) {
                  case 'reminder': echo '⏳'; break;
                  case 'alert': echo '⚠️'; break;
                  case 'success': echo '✅'; break;
                  case 'info': echo '💡'; break;
                  default: echo '🔔';
                }
              ?>
            </span>
            <div class="notif-content">
              <div class="notif-subject"><?php echo htmlspecialchars($notif['subject']); ?></div>
              <div class="notif-message"><?php echo htmlspecialchars($notif['message']); ?></div>
            </div>
            <span class="notif-date"><?php echo date('M j', strtotime($notif['date'])); ?></span>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
  </div>
  
  <!-- Bottom Navigation Removed -->
  
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