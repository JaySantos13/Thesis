<?php
// This file contains the navigation template for professor pages
// Usage: include 'nav_template_professor.php';
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
    <li><a href="professor-dashboard.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'professor-dashboard.php') ? 'class="active"' : ''; ?>><span class="icon"><?php include 'icons/home.svg'; ?></span> Dashboard</a></li>
    <li><a href="professor-lab-schedule.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'professor-lab-schedule.php') ? 'class="active"' : ''; ?>><span class="icon"><?php include 'icons/calendar.svg'; ?></span> Lab Schedule</a></li>
    <li><a href="professor-equipment.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'professor-equipment.php') ? 'class="active"' : ''; ?>><span class="icon"><?php include 'icons/equipment.svg'; ?></span> Equipment</a></li>
    <li><a href="professor-requests.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'professor-requests.php') ? 'class="active"' : ''; ?>><span class="icon"><?php include 'icons/request.svg'; ?></span> Requests</a></li>
    <li><a href="professor-profile.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'professor-profile.php') ? 'class="active"' : ''; ?>><span class="icon"><?php include 'icons/profile.svg'; ?></span> Profile</a></li>
    <li><a href="professor-logout.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'professor-logout.php') ? 'class="active"' : ''; ?>><span class="icon"><?php include 'icons/logout.svg'; ?></span> Logout</a></li>
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
