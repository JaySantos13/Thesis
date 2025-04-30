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
</head>
<body class="pending-bg">
  <div class="pending-top-row">
    <button class="pending-back" id="pendingBackBtn" aria-label="Back">&#8592;</button>
    <div class="pending-logo-center">
      <?php include 'icons/dsB.svg'; ?>
    </div>
  </div>
  <div class="pending-main-card">
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
    <!-- Bottom Navigation Bar (matches dashboard.php) -->
    <ul>
      <li><a href="/notif">Notifications</a></li>
      <li><a href="/history">History</a></li>
      <li><a class="active" href="dashboard.php">Home</a></li>
      <li><a href="/profile">Profile</a></li>
      <li><a href="/more">More</a></li>
    </ul>
  </div>
</body>
<script>
document.getElementById('pendingBackBtn').addEventListener('click', function() {
  window.history.back();
});
</script>
</html>
