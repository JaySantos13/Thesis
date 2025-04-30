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
</head>
<body class="notif-bg">
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
  <!-- Bottom Navigation Bar (matches dashboard.php) -->
  <ul>
    <li><a class="active" href="notif.php">Notifications</a></li>
    <li><a href="history.php">History</a></li>
    <li><a href="dashboard.php">Home</a></li>
    <li><a href="profile.php">Profile</a></li>
    <li><a href="more.php">More</a></li>
  </ul>
</body>
</html>