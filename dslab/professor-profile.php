<?php
session_start();

// Check if professor is logged in
if (!isset($_SESSION['professor_id'])) {
  header("Location: professorlogin.php");
  exit();
}

include 'db.php';

// Get professor information
$professor_id = $_SESSION['professor_id'];
$sql = "SELECT * FROM professors WHERE id = $professor_id";
$result = $conn->query($sql);
$professor = $result->fetch_assoc();

// Handle logout
if (isset($_GET['logout'])) {
  session_destroy();
  header("Location: professorlogin.php");
  exit();
}

// For now, we'll use placeholder data for courses handled
// In the future, you may want to create a 'classes' or 'courses' table with professor_id field
$classes = [
  // Example placeholder data
  /*
  [
    'code' => 'BSCOME2207',
    'title' => 'Fundamentals to Electronics Circuits',
    'section' => '',
    'class_no' => '53104'
  ],
  [
    'code' => 'BSCOME2207L',
    'title' => 'Software Design',
    'section' => '',
    'class_no' => '53102'
  ]
  */
];

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Professor Profile</title>
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="profile.css">
  <link rel="stylesheet" href="sidenav.css">
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
      <li><a href="professor-notifications.php"><span class="icon"><?php include 'icons/bell.svg'; ?></span> Notifications</a></li>
      <li><a href="professor-history.php"><span class="icon"><?php include 'icons/clock.svg'; ?></span> History</a></li>
      <li><a href="professor-dashboard.php"><span class="icon"><?php include 'icons/home.svg'; ?></span> Home</a></li>
      <li><a class="active" href="professor-profile.php"><span class="icon"><?php include 'icons/profile.svg'; ?></span> Profile</a></li>
      <li><a href="professor-more.php"><span class="icon"><?php include 'icons/more.svg'; ?></span> More</a></li>
    </ul>
  </div>
  
  <div class="profile-main">
    <div class="profile-logo-area" style="display: flex; flex-direction: column; align-items: center; margin: 30px 0; width: 100%; max-width: 200px;">
      <div style="width: 100%; height: auto;">
        <?php include 'icons/dsB.svg'; ?>
      </div>
    </div>
    
    <div class="profile-photo-container">
      <div class="profile-photo">
        <?php 
        $photoPath = 'uploads/professors/' . $professor_id . '.jpg';
        if (file_exists($photoPath)) {
          echo '<img src="' . $photoPath . '?v=' . time() . '" alt="Profile Photo">';
        } else {
          echo '<div class="profile-photo-placeholder">' . strtoupper(substr(($professor['name'] ?? 'P'), 0, 1)) . '</div>';
        }
        ?>
      </div>
      <form action="upload_professor_photo.php" method="post" enctype="multipart/form-data" id="photoForm">
        <label for="photoUpload" class="photo-upload-btn">Change Photo</label>
        <input type="file" id="photoUpload" name="photo" accept="image/*" style="display: none;">
      </form>
    </div>
    
    <div class="profile-card">
      <h2><?php echo htmlspecialchars($professor['name'] ?? ''); ?></h2>
      <div class="profile-email"><?php echo htmlspecialchars($professor['email'] ?? ''); ?></div>
      <div style="font-size: 1.05em; margin-top: 5px; color: #333;">
        <strong>Department:</strong> <?php echo htmlspecialchars($professor['department'] ?? ''); ?>
      </div>
      <div style="font-size: 1.05em; margin-top: 5px; color: #333;">
        <strong>Phone:</strong> <?php echo htmlspecialchars($professor['phone'] ?? ''); ?>
      </div>
      <a href="professor-mobile-edit-profile.php" class="profile-edit-btn">EDIT PROFILE</a>
    </div>
    
    <table class="profile-courses-table">
      <tr><th colspan="2">COURSES HANDLED</th></tr>
      <?php if (empty($classes)): ?>
        <tr><td colspan="2" style="text-align:center; color:#888;">No courses assigned yet.</td></tr>
      <?php else: ?>
        <?php foreach ($classes as $class): ?>
        <tr class="profile-course-row">
          <td style="width:45%;"><div style="font-size:.95em; color:#8f3b00;"><strong><?php echo htmlspecialchars($class['code'] . ($class['section'] ? $class['section'] : '')); ?></strong></div></td>
          <td>
            <div><strong><?php echo htmlspecialchars($class['title']); ?></strong></div>
            <div style="font-size:.97em; color:#222b; margin-top:2px;">Class No: <?php echo htmlspecialchars($class['class_no']); ?></div>
          </td>
        </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </table>
  </div>
  
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
