<?php
require_once 'db.php';
// Fetch history records from your database
$search = $_GET['search'] ?? '';
$sql = "SELECT * FROM history WHERE subject LIKE ? OR message LIKE ? ORDER BY date DESC";
$stmt = $conn->prepare($sql);
$like = "%$search%";
$stmt->bind_param("ss", $like, $like);
$stmt->execute();
$result = $stmt->get_result();
$history = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>History - DS Lab</title>
  <link rel="stylesheet" href="notif.css">
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="sidenav.css">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
      <li><a href="notif.php"><span class="icon"><?php include 'icons/bell.svg'; ?></span> Notifications</a></li>
      <li><a class="active" href="history.php"><span class="icon"><?php include 'icons/clock.svg'; ?></span> History</a></li>
      <li><a href="dashboard.php"><span class="icon"><?php include 'icons/home.svg'; ?></span> Home</a></li>
      <li><a href="profile.php"><span class="icon"><?php include 'icons/profile.svg'; ?></span> Profile</a></li>
      <li><a href="more.php"><span class="icon"><?php include 'icons/more.svg'; ?></span> More</a></li>
    </ul>
  </div>
  
  <div class="notif-top-card">
    <div class="notif-logo-center">
      <?php include 'icons/dsB.svg'; ?>
    </div>
  </div>
  <div class="notif-header-bar">History</div>
  <div class="notif-main-card">
    <form class="notif-search-row" method="get" action="">
      <div class="notif-search-group">
        <input type="text" class="notif-search-input" name="search" placeholder="Search" value="<?php echo htmlspecialchars($search); ?>">
        <button type="submit" class="notif-filter-btn" title="Search">
          <span class="notif-search-icon">&#128269;</span>
        </button>
      </div>
    </form>
    <div class="notif-list">
      <?php if (empty($history)): ?>
        <div class="notif-item empty">No history records found.</div>
      <?php else: ?>
        <?php foreach ($history as $row): ?>
          <div class="notif-item">
            <span class="notif-icon">
              <?php
                switch ($row['type'] ?? '') {
                  case 'return': echo '✅'; break;
                  case 'borrow': echo '📦'; break;
                  case 'reminder': echo '⏰'; break;
                  default: echo '📄';
                }
              ?>
            </span>
            <div class="notif-content">
              <div class="notif-subject"><?php echo htmlspecialchars($row['subject'] ?? ''); ?></div>
              <div class="notif-message"><?php echo nl2br(htmlspecialchars($row['message'] ?? '')); ?></div>
            </div>
            <span class="notif-date"><?php echo isset($row['date']) ? date('M j, Y', strtotime($row['date'])) : ''; ?></span>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
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
