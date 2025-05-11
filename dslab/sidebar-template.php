<?php
// This is a reusable sidebar component that matches the screenshot
?>
<!-- Side Navigation -->    
<div class="sidenav" id="sideNav">
  <ul>
    <li><a href="notifications.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'notifications.php' || basename($_SERVER['PHP_SELF']) == 'notif.php') ? 'class="active"' : ''; ?>>
      <span class="icon">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
          <path d="M12 22c1.1 0 2-.9 2-2h-4c0 1.1.9 2 2 2zm6-6v-5c0-3.07-1.64-5.64-4.5-6.32V4c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5v.68C7.63 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2z"/>
        </svg>
      </span> 
      Notifications
    </a></li>
    <li><a href="history.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'history.php') ? 'class="active"' : ''; ?>>
      <span class="icon">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
          <path d="M13 3c-4.97 0-9 4.03-9 9H1l3.89 3.89.07.14L9 12H6c0-3.87 3.13-7 7-7s7 3.13 7 7-3.13 7-7 7c-1.93 0-3.68-.79-4.94-2.06l-1.42 1.42C8.27 19.99 10.51 21 13 21c4.97 0 9-4.03 9-9s-4.03-9-9-9zm-1 5v5l4.28 2.54.72-1.21-3.5-2.08V8H12z"/>
        </svg>
      </span> 
      History
    </a></li>
    <li><a href="dashboard.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'dashboard.php' || basename($_SERVER['PHP_SELF']) == 'index.php') ? 'class="active"' : ''; ?>>
      <span class="icon">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
          <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/>
        </svg>
      </span> 
      Home
    </a></li>
    <li><a href="profile.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'profile.php') ? 'class="active"' : ''; ?>>
      <span class="icon">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
          <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
        </svg>
      </span> 
      Profile
    </a></li>
    <li><a href="more.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'more.php') ? 'class="active"' : ''; ?>>
      <span class="icon">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
          <path d="M6 10c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm12 0c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm-6 0c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"/>
        </svg>
      </span> 
      More
    </a></li>
  </ul>
</div>

<!-- Menu Toggle Button for Mobile -->    
<button class="menu-toggle" id="menuToggle">
  <span class="icon">
    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
      <path d="M3 18h18v-2H3v2zm0-5h18v-2H3v2zm0-7v2h18V6H3z"/>
    </svg>
  </span>
</button>

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
