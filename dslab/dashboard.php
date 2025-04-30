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
</head>
<body>
    <div class="dashboard-container">
      <div class="logo">
        <?php include 'icons/dsB.svg'; ?>
      </div>
      <div class="welcome-text">
        <span>Welcome</span>
        <div class="username"><?php echo htmlspecialchars($_SESSION['username']); ?></div>
      </div>
      <a class="main-action" href="labday.php">My Lab Day</a>
      <div class="action-row">
        <a class="action-btn" href="request-status.php">Request Status</a>
        <a class="action-btn" href="pending-return.php">Pending Return</a>
      </div>
      <div class="action-row">
        <a class="action-btn" href="direct-request.php">Direct Request</a>
        <a class="action-btn" href="equipment-info.php">Equipment Info</a>
      </div>
    </div>
    <ul>
        <li><a href="notif.php">Notifications</a></li>
        <li><a href="history.php">History</a></li>
        <li><a class="active" href="dashboard.php">Home</a></li>
        <li><a href="profile.php">Profile</a></li>
        <li><a href="more.php">More</a></li>
    </ul>
</body>
</html>
