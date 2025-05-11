<?php
session_start();
require_once 'db.php';

// Check if professor is logged in
if (!isset($_SESSION['professor_id'])) {
  header("Location: professorlogin.php");
  exit();
}

$professor_id = $_SESSION['professor_id'];
$professor_sql = "SELECT name, email, department, phone FROM professors WHERE id=?";
$professor_stmt = $conn->prepare($professor_sql);
$professor_stmt->bind_param("i", $professor_id);
$professor_stmt->execute();
$professor = $professor_stmt->get_result()->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $department = $_POST['department'] ?? '';
    $phone = $_POST['phone'] ?? '';

    $update_sql = "UPDATE professors SET name = ?, email = ?, department = ?, phone = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("ssssi", $name, $email, $department, $phone, $professor_id);
    
    if ($update_stmt->execute()) {
        // Refresh professor data
        $professor_stmt->execute();
        $professor = $professor_stmt->get_result()->fetch_assoc();
        
        // Redirect to profile page with success message
        header("Location: professor-profile.php?update=success&msg=" . urlencode("Profile updated successfully"));
        exit();
    } else {
        $error_message = "Failed to update profile. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Professor Profile</title>
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="profile.css">
  <link rel="stylesheet" href="edit-profile.css">
  <link rel="stylesheet" href="sidenav.css">
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
      <li><a href="professor-notifications.php"><span class="icon"><?php include 'icons/bell.svg'; ?></span> Notifications</a></li>
      <li><a href="professor-history.php"><span class="icon"><?php include 'icons/clock.svg'; ?></span> History</a></li>
      <li><a href="professor-dashboard.php"><span class="icon"><?php include 'icons/home.svg'; ?></span> Home</a></li>
      <li><a class="active" href="professor-profile.php"><span class="icon"><?php include 'icons/profile.svg'; ?></span> Profile</a></li>
      <li><a href="professor-more.php"><span class="icon"><?php include 'icons/more.svg'; ?></span> More</a></li>
    </ul>
  </div>
  
  <div class="edit-profile-main">
    <div class="edit-logo-area">
      <?php include 'icons/dsB.svg'; ?>
    </div>
    
    <div class="edit-profile-title">Edit Profile</div>
    <div class="edit-profile-card">
      <?php if (isset($error_message)): ?>
        <div style="color: #721c24; background-color: #f8d7da; border: 1px solid #f5c6cb; padding: 10px; margin-bottom: 10px; border-radius: 8px;">
          <?php echo htmlspecialchars($error_message); ?>
        </div>
      <?php endif; ?>
      
      <form class="edit-form" method="post" action="" style="text-align: center;">
        <input class="edit-input" type="text" name="name" placeholder="Full Name" value="<?php echo htmlspecialchars($professor['name'] ?? ''); ?>" required />
        <input class="edit-input" type="email" name="email" placeholder="Email" value="<?php echo htmlspecialchars($professor['email'] ?? ''); ?>" required />
        <input class="edit-input" type="text" name="department" placeholder="Department" value="<?php echo htmlspecialchars($professor['department'] ?? ''); ?>" required />
        <input class="edit-input" type="text" name="phone" placeholder="Phone" value="<?php echo htmlspecialchars($professor['phone'] ?? ''); ?>" />
        
        <a href="professor-change-password.php" class="edit-btn change">Change Password</a>
        
        <a href="professor-enable-2fa.php" class="edit-btn change">Enable 2FA</a>
        
        <div class="edit-btn-row">
          <div style="display: flex; align-items: center;">
            <a href="professor-profile.php" class="edit-btn cancel">Cancel</a>
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
