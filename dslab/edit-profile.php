<?php
session_start();
require_once 'db.php';
$user_id = $_SESSION['user_id'] ?? 1; 
$user_sql = "SELECT first_name, last_name, email, program, student_no FROM users WHERE id=?";
$user_stmt = $conn->prepare($user_sql);
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user = $user_stmt->get_result()->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course = $_POST['course'] ?? '';
    $student_no = $_POST['student_no'] ?? '';

    $update_sql = "UPDATE users SET program = ?, student_no = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("ssi", $course, $student_no, $user_id);
    $update_stmt->execute();

    $user_stmt->execute();
    $user = $user_stmt->get_result()->fetch_assoc();

    echo "Updated Course: " . htmlspecialchars($user['program']) . "<br>";
    echo "Updated Student No: " . htmlspecialchars($user['student_no']) . "<br>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Profile</title>
  <link rel="stylesheet" href="notif.css">
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="profile.css">
  <link rel="stylesheet" href="edit-profile.css">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
  <div class="edit-profile-main">
    <div class="edit-logo-area" style="margin-bottom: 0;">
      <?php include 'icons/dsB.svg'; ?>
    </div>
    <div class="edit-profile-card">
      <div class="edit-profile-title">Edit Profile</div>
      <form class="edit-form" method="post" action="">
        <input class="edit-input" type="text" name="first_name" placeholder="First name" value="<?php echo htmlspecialchars($user['first_name'] ?? ''); ?>" required />
        <input class="edit-input" type="text" name="last_name" placeholder="Last name" value="<?php echo htmlspecialchars($user['last_name'] ?? ''); ?>" required />
        <input class="edit-input" type="email" name="email" placeholder="Email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required />
        <input class="edit-input" type="text" name="course" placeholder="Course" value="<?php echo htmlspecialchars($user['program'] ?? ''); ?>" />
        <input class="edit-input" type="text" name="student_no" placeholder="ID No." value="<?php echo htmlspecialchars($user['student_no'] ?? ''); ?>" />
        <div class="edit-btn-row">
          <a href="change-password.php" class="edit-btn change">Change Password</a>
          <a href="profile.php" class="edit-btn cancel">Cancel</a>
          <button type="submit" class="edit-btn save">Save</button>
        </div>
      </form>
    </div>
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
