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
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
  <div class="notif-logo-area" style="display: flex; flex-direction: column; align-items: center; justify-content: center; margin-bottom: 0;">
    <?php include 'icons/dsB.svg'; ?>
    <div class="notif-header-bar" style="margin-top: 4px; text-align: center; width: 100%; margin: 20px;">History</div>
  </div>
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
  <ul>
    <li><a href="notif.php">Notifications</a></li>
    <li><a class="active"href="history.php">History</a></li>
    <li><a href="dashboard.php">Home</a></li>
    <li><a href="profile.php">Profile</a></li>
    <li><a href="/more">More</a></li>
  </ul>
</body>
</html>
