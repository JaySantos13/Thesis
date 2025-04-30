<?php
session_start();
require_once 'db.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
$user_id = $_SESSION['user_id'];
// Fetch user info
$user_sql = "SELECT first_name, last_name, email FROM users WHERE id=?";
$user_stmt = $conn->prepare($user_sql);
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user = $user_stmt->get_result()->fetch_assoc();
// Fetch enrolled courses
$courses_sql = "SELECT code, title, section, class_no FROM enrollments WHERE user_id=?";
$courses_stmt = $conn->prepare($courses_sql);
$courses_stmt->bind_param("i", $user_id);
$courses_stmt->execute();
$courses = $courses_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Profile</title>
  <link rel="stylesheet" href="notif.css">
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="profile.css">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

</head>
<body style="background: #fff;">
  <div class="profile-main">
    <div style="display: flex; flex-direction: column; align-items: center; margin-bottom: 18px;">
      <?php include 'icons/dsB.svg'; ?>
    </div>
    <div class="profile-card">
    <h2><?php echo htmlspecialchars(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')); ?></h2>
    <div class="profile-email"><?php echo htmlspecialchars($user['email'] ?? ''); ?></div>
      <a href="edit-profile.php" class="profile-edit-btn">EDIT PROFILE</a>
    </div>
    <table class="profile-courses-table">
      <tr><th colspan="2">COURSE ENROLLED</th></tr>
      <?php if (empty($courses)): ?>
        <tr><td colspan="2" style="text-align:center; color:#888;">No enrolled courses.</td></tr>
      <?php else: ?>
        <?php foreach ($courses as $c): ?>
        <tr class="profile-course-row">
          <td style="width:45%;"><div style="font-size:.95em; color:#8f3b00;"><strong><?php echo htmlspecialchars($c['code'] . ($c['section'] ? $c['section'] : '')); ?></strong></div></td>
          <td>
            <div><strong><?php echo htmlspecialchars($c['title']); ?></strong></div>
            <div style="font-size:.97em; color:#222b; margin-top:2px;">Class No: <?php echo htmlspecialchars($c['class_no']); ?></div>
          </td>
        </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </table>
  </div>
  <ul>
    <li><a href="notif.php">Notifications</a></li>
    <li><a href="history.php">History</a></li>
    <li><a href="dashboard.php">Home</a></li>
    <li><a class="active" href="profile.php">Profile</a></li>
    <li><a href="more.php">More</a></li>
  </ul>
</body>
</html>
