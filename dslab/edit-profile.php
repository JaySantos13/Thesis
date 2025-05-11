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
<body class="edit-profile-bg">
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
      <li><a href="dashboard.php"><span class="icon"><?php include 'icons/home.svg'; ?></span> Home</a></li>
      <li><a class="active" href="profile.php"><span class="icon"><?php include 'icons/profile.svg'; ?></span> Profile</a></li>
      <li><a href="more.php"><span class="icon"><?php include 'icons/more.svg'; ?></span> More</a></li>
    </ul>
  </div>
  
  <div class="edit-profile-main">
    <div class="edit-logo-area">
      <?php include 'icons/dsB.svg'; ?>
    </div>
    
    <div class="edit-profile-title">Edit Profile</div>
    <div class="edit-profile-card">
      <form class="edit-form" method="post" action="" style="text-align: center;">
        <input class="edit-input" type="text" name="first_name" placeholder="Firstname" value="<?php echo htmlspecialchars($user['first_name'] ?? ''); ?>" required />
        <input class="edit-input" type="text" name="last_name" placeholder="Last name" value="<?php echo htmlspecialchars($user['last_name'] ?? ''); ?>" required />
        <input class="edit-input" type="email" name="email" placeholder="Email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required />
        <input class="edit-input" type="text" name="course" placeholder="Course" value="<?php echo htmlspecialchars($user['program'] ?? ''); ?>" />
        <input class="edit-input" type="text" name="student_no" placeholder="ID No." value="<?php echo htmlspecialchars($user['student_no'] ?? ''); ?>" />
        
        <a href="change-password.php" class="edit-btn change">Change Password</a>
        
        <a href="enable-2fa.php" class="edit-btn change">Enable 2FA</a>
        
        <div class="edit-btn-row">
          <div style="display: flex; align-items: center;">
            <a href="profile.php" class="edit-btn cancel">Cancel</a>
          </div>
          <div style="display: flex; align-items: center;">
            <button type="submit" class="edit-btn save">Save</button>
          </div>
        </div>
      </form>
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
