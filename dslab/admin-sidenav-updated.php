<?php
// This is a reusable sidebar component for the admin dashboard
// Matches the professor sidebar structure but with admin-specific links
?>
<!-- Admin Side Navigation -->
<div class="admin-sidenav" id="adminSideNav">
    <div class="admin-logo">
        <img src="icons/dsB.svg" alt="DS Lab Logo" class="logo-img">
        <h2>Admin</h2>
    </div>
    <ul>
        <li><a href="admin-dashboard.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'admin-dashboard.php') ? 'class="active"' : ''; ?>>
            <span class="icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/>
                </svg>
            </span>
            Dashboard
        </a></li>



        <li><a href="admin-profile.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'admin-profile.php') ? 'class="active"' : ''; ?>>
            <span class="icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                </svg>
            </span>
            Profile
        </a></li>
        <li><a href="adminlogin.php">
            <span class="icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17l5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z"/>
                </svg>
            </span>
            Logout
        </a></li>
    </ul>
</div>

<!-- Menu Toggle Button for Mobile -->
<button class="menu-toggle" id="adminMenuToggle">
    <span class="icon">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
            <path d="M3 18h18v-2H3v2zm0-5h18v-2H3v2zm0-7v2h18V6H3z"/>
        </svg>
    </span>
</button>

<!-- JavaScript for menu toggle -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const menuToggle = document.getElementById('adminMenuToggle');
        const sideNav = document.getElementById('adminSideNav');
        
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
