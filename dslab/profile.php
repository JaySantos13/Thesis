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
  <link rel="stylesheet" href="sidenav.css">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

</head>
<body style="background: #fff;">
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
  
  <div class="profile-main">
    <div class="profile-logo-area" style="display: flex; flex-direction: column; align-items: center; margin-bottom: 18px;">
      <?php include 'icons/dsB.svg'; ?>
    </div>
    
    <div class="profile-photo-container">
      <div class="profile-photo">
        <?php 
        $photoPath = 'uploads/profile/' . $user_id . '.jpg';
        if (file_exists($photoPath)) {
          echo '<img src="' . $photoPath . '?v=' . time() . '" alt="Profile Photo">';
        } else {
          echo '<div class="profile-photo-placeholder">' . strtoupper(substr(($user['first_name'] ?? 'U'), 0, 1)) . '</div>';
        }
        ?>
      </div>
      <form action="upload_photo.php" method="post" enctype="multipart/form-data" id="photoForm">
        <label for="photoUpload" class="photo-upload-btn">Change Photo</label>
        <input type="file" id="photoUpload" name="photo" accept="image/*" style="display: none;">
      </form>
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
  
  <!-- Bottom Navigation Removed -->
  
  <!-- JavaScript for menu toggle and photo upload -->    
  <script>
      document.addEventListener('DOMContentLoaded', function() {
          const menuToggle = document.getElementById('menuToggle');
          const sideNav = document.getElementById('sideNav');
          const photoUpload = document.getElementById('photoUpload');
          const photoForm = document.getElementById('photoForm');
          
          // Menu toggle functionality
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
          
          // Photo upload functionality
          if (photoUpload) {
              photoUpload.addEventListener('change', function() {
                  if (this.files && this.files[0]) {
                      // Show loading indicator or preview if desired
                      // Submit the form automatically when a file is selected
                      photoForm.submit();
                  }
              });
          }
          
          // Display upload status message if present in URL
          const urlParams = new URLSearchParams(window.location.search);
          const photoStatus = urlParams.get('photo');
          const photoMsg = urlParams.get('msg');
          
          if (photoStatus && photoMsg) {
              const statusClass = photoStatus === 'success' ? 'success-message' : 'error-message';
              const messageDiv = document.createElement('div');
              messageDiv.className = statusClass;
              messageDiv.textContent = decodeURIComponent(photoMsg);
              
              document.querySelector('.profile-photo-container').appendChild(messageDiv);
              
              // Remove message after 5 seconds
              setTimeout(() => {
                  messageDiv.remove();
                  // Clean up URL
                  const url = new URL(window.location);
                  url.searchParams.delete('photo');
                  url.searchParams.delete('msg');
                  window.history.replaceState({}, '', url);
              }, 5000);
          }
      });
  </script>
</body>
</html>
