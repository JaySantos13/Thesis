<?php
// This file contains the navigation template to be included in all pages
// Usage: include 'nav_template.php';
?>
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
    <li><a href="notif.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'notif.php') ? 'class="active"' : ''; ?>><span class="icon"><?php include 'icons/bell.svg'; ?></span> Notifications</a></li>
    <li><a href="history.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'history.php') ? 'class="active"' : ''; ?>><span class="icon"><?php include 'icons/clock.svg'; ?></span> History</a></li>
    <li><a href="dashboard.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'dashboard.php') ? 'class="active"' : ''; ?>><span class="icon"><?php include 'icons/home.svg'; ?></span> Home</a></li>
    <li><a href="profile.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'profile.php') ? 'class="active"' : ''; ?>><span class="icon"><?php include 'icons/profile.svg'; ?></span> Profile</a></li>
    <li><a href="more.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'more.php') ? 'class="active"' : ''; ?>><span class="icon"><?php include 'icons/more.svg'; ?></span> More</a></li>
  </ul>
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
