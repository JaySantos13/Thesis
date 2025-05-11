<?php
session_start();
include 'db.php';

// Allow both admin and regular users to access this page
// We'll just check if a session exists
if (!isset($_SESSION['admin_id']) && !isset($_SESSION['user_id'])) {
    // If neither admin nor user session exists, redirect to login
    header("Location: login.php");
    exit();
}

// Check if user is admin to determine which navigation to show
$is_admin = isset($_SESSION['admin_id']) && isset($_SESSION['is_admin']);

// Get admin information and permissions if admin
$permissions = [];
if ($is_admin) {
    $admin_id = $_SESSION['admin_id'];
    $stmt = $conn->prepare("SELECT * FROM admins WHERE id = ?");
    $stmt->bind_param("i", $admin_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $admin = $result->fetch_assoc();

    // Get admin permissions
    $perm_stmt = $conn->prepare("SELECT * FROM admin_permissions WHERE admin_id = ?");
    $perm_stmt->bind_param("i", $admin_id);
    $perm_stmt->execute();
    $perm_result = $perm_stmt->get_result();
    $permissions = $perm_result->fetch_assoc();
}

// Fetch request options
$request_options = [
    ['id' => 'lab_day', 'name' => 'Lab day', 'date' => 'Mar 11, 2025'],
    ['id' => 'direct_request', 'name' => 'Direct Request', 'date' => 'Mar 11, 2025']
];

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Borrowing & Return Requests</title>
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="sidenav.css">
  <link rel="stylesheet" href="borrowing.css">
  <style>
    /* Common styles */
    body, html {
      height: 100vh;
      margin: 0;
      padding: 0;
      overflow-x: hidden;
      background: #f7f7f7;
      font-family: 'Segoe UI', Arial, sans-serif;
    }
    
    /* Admin dashboard styles */
    .admin-container {
      display: flex;
      min-height: 100vh;
    }
    
    .admin-sidenav {
      width: 250px;
      background-color: #444;
      color: white;
      padding-top: 20px;
      position: fixed;
      height: 100%;
      z-index: 1;
      transition: all 0.3s;
      left: 0;
      top: 0;
    }
    
    .admin-sidenav h2 {
      color: #ff7f1a;
      padding: 0 20px;
      margin-bottom: 30px;
    }
    
    .admin-sidenav ul {
      list-style: none;
      padding: 0;
    }
    
    .admin-sidenav li {
      margin-bottom: 5px;
    }
    
    .admin-sidenav a {
      display: flex;
      align-items: center;
      padding: 10px 20px;
      color: white;
      text-decoration: none;
      transition: background-color 0.3s;
    }
    
    .admin-sidenav a:hover, .admin-sidenav a.active {
      background-color: #e55a00;
    }
    
    .admin-sidenav .icon {
      margin-right: 10px;
      display: inline-flex;
    }
    
    /* Regular user styles */
    body {
      display: flex;
      flex-direction: column;
      align-items: center;
    }
    
    .borrowing-card {
      background: white;
      border-radius: 15px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.1);
      width: 100%;
      max-width: 800px;
      margin: 20px auto;
      overflow: hidden;
      display: flex;
      flex-direction: column;
      height: calc(100vh - 40px);
    }
    
    /* Admin view specific styles */
    .admin-view .borrowing-card {
      margin-left: 270px;
      width: calc(100% - 290px);
    }
    
    .borrowing-header {
      background-color: #ff7f1a;
      color: white;
      padding: 15px 20px;
      font-size: 1.2em;
      font-weight: 500;
    }
    
    .borrowing-content {
      flex: 1;
      padding: 20px;
      overflow-y: auto;
    }
    
    .search-container {
      margin-bottom: 15px;
      position: relative;
    }
    
    .search-input {
      width: 100%;
      padding: 10px 15px;
      border: 1px solid #ddd;
      border-radius: 20px;
      font-size: 0.9em;
      background-color: #f5f5f5;
    }
    
    .search-input::placeholder {
      color: #999;
    }
    
    .filter-container {
      display: flex;
      align-items: center;
      margin-bottom: 15px;
      gap: 10px;
    }
    
    .filter-label {
      background-color: #f0f0f0;
      color: #666;
      padding: 5px 10px;
      border-radius: 15px;
      font-size: 0.8em;
      display: inline-flex;
      align-items: center;
    }
    
    .filter-button {
      background-color: #f0f0f0;
      border: none;
      color: #666;
      padding: 5px 10px;
      border-radius: 15px;
      font-size: 0.8em;
      cursor: pointer;
      margin-left: auto;
    }
    
    .request-list {
      display: flex;
      flex-direction: column;
      gap: 10px;
    }
    
    .request-item {
      background-color: #f9f9f9;
      border-radius: 10px;
      padding: 15px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      cursor: pointer;
      transition: background-color 0.2s;
    }
    
    .request-item:hover {
      background-color: #f0f0f0;
    }
    
    .request-info {
      flex: 1;
    }
    
    .request-title {
      font-weight: 500;
      color: #444;
      margin-bottom: 3px;
    }
    
    .request-subtitle {
      font-size: 0.8em;
      color: #777;
    }
    
    .request-date {
      color: #666;
      font-size: 0.9em;
      text-align: right;
    }
    
    @media (max-width: 768px) {
      .borrowing-card {
        border-radius: 0;
        margin: 0;
        height: 100vh;
        max-width: 100%;
      }
      
      /* Admin mobile styles */
      .admin-view .borrowing-card {
        margin: 0;
        width: 100%;
      }
      
      .admin-sidenav {
        transform: translateX(-100%);
        transition: transform 0.3s ease;
      }
      
      .admin-sidenav.active {
        transform: translateX(0);
      }
      
      .admin-view .menu-toggle {
        display: block;
        z-index: 1000;
        position: fixed;
        top: 10px;
        left: 10px;
      }
    }
  </style>
</head>
<body class="<?php echo $is_admin ? 'admin-view' : ''; ?>">
<?php if ($is_admin): ?>
  <!-- Admin Menu Toggle Button for Mobile -->    
  <button class="menu-toggle" id="adminMenuToggle">
    <span class="icon">
      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
        <path d="M3 18h18v-2H3v2zm0-5h18v-2H3v2zm0-7v2h18V6H3z"/>
      </svg>
    </span>
  </button>
  
  <!-- Admin Side Navigation -->
  <div class="admin-sidenav" id="adminSideNav">
    <h2>DS Lab Admin</h2>
    <ul>
      <li><a href="admin-dashboard.php">
        <span class="icon">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
            <path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/>
          </svg>
        </span>
        Dashboard
      </a></li>
      <?php if (isset($permissions['can_manage_users']) && $permissions['can_manage_users']): ?>
      <li><a href="admin-users.php">
        <span class="icon">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
            <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
          </svg>
        </span>
        Manage Users
      </a></li>
      <?php endif; ?>
      <?php if (isset($permissions['can_manage_equipment']) && $permissions['can_manage_equipment']): ?>
      <li><a href="admin-equipment.php">
        <span class="icon">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
            <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 15.5h-1.5V14h-1v3H8v-3H7v4.5H5.5v-5c0-.55.45-1 1-1H11c.55 0 1 .45 1 1v5zm3.5 0H14v-6h3.5c.55 0 1 .45 1 1V16c0 .55-.45 1-1 1h-2v1.5zm-1-8c0 .55-.45 1-1 1H10V10h3V9h-2c-.55 0-1-.45-1-1V6.5c0-.55.45-1 1-1h2.5c.55 0 1 .45 1 1v4zm1 3.5H17v1.5h-1.5z"/>
          </svg>
        </span>
        Inventory Management
      </a></li>
      <?php endif; ?>
      <?php if (isset($permissions['can_approve_requests']) && $permissions['can_approve_requests']): ?>
      <li><a href="borrowing.php" class="active">
        <span class="icon">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
            <path d="M18 2H6c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zM9 4h2v5l-1-.75L9 9V4zm9 16H6V4h1v9l3-2.25L13 13V4h5v16z"/>
          </svg>
        </span>
        Borrowing & Return Requests
      </a></li>
      <?php endif; ?>
      <?php if (isset($permissions['can_view_reports']) && $permissions['can_view_reports']): ?>
      <li><a href="admin-reports.php">
        <span class="icon">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
            <path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/>
          </svg>
        </span>
        Reports
      </a></li>
      <?php endif; ?>
      <li><a href="admin-settings.php">
        <span class="icon">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
            <path d="M19.14 12.94c.04-.3.06-.61.06-.94 0-.32-.02-.64-.07-.94l2.03-1.58c.18-.14.23-.41.12-.61l-1.92-3.32c-.12-.22-.37-.29-.59-.22l-2.39.96c-.5-.38-1.03-.7-1.62-.94l-.36-2.54c-.04-.24-.24-.41-.48-.41h-3.84c-.24 0-.43.17-.47.41l-.36 2.54c-.59.24-1.13.57-1.62.94l-2.39-.96c-.22-.08-.47 0-.59.22L2.74 8.87c-.12.21-.08.47.12.61l2.03 1.58c-.05.3-.09.63-.09.94s.02.64.07.94l-2.03 1.58c-.18.14-.23.41-.12.61l1.92 3.32c.12.22.37.29.59.22l2.39-.96c.5.38 1.03.7 1.62.94l.36 2.54c.05.24.24.41.48.41h3.84c.24 0 .44-.17.47-.41l.36-2.54c.59-.24 1.13-.56 1.62-.94l2.39.96c.22.08.47 0 .59-.22l1.92-3.32c.12-.22.07-.47-.12-.61l-2.01-1.58zM12 15.6c-1.98 0-3.6-1.62-3.6-3.6s1.62-3.6 3.6-3.6 3.6 1.62 3.6 3.6-1.62 3.6-3.6 3.6z"/>
          </svg>
        </span>
        Settings
      </a></li>
      <li><a href="adminlogout.php">
        <span class="icon">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
            <path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17l5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z"/>
          </svg>
        </span>
        Logout
      </a></li>
    </ul>
  </div>
<?php else: ?>
  <!-- Regular Menu Toggle Button for Mobile -->    
  <button class="menu-toggle" id="menuToggle">
    <span class="icon">
      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
        <path d="M3 18h18v-2H3v2zm0-5h18v-2H3v2zm0-7v2h18V6H3z"/>
      </svg>
    </span>
  </button>
  
  <!-- Regular Side Navigation -->
  <div class="sidenav" id="sideNav">
    <ul>
      <li><a href="notif.php"><span class="icon"><?php include 'icons/bell.svg'; ?></span> Notifications</a></li>
      <li><a href="history.php"><span class="icon"><?php include 'icons/clock.svg'; ?></span> History</a></li>
      <li><a href="dashboard.php"><span class="icon"><?php include 'icons/home.svg'; ?></span> Home</a></li>
      <li><a href="profile.php"><span class="icon"><?php include 'icons/profile.svg'; ?></span> Profile</a></li>
      <li><a href="more.php"><span class="icon"><?php include 'icons/more.svg'; ?></span> More</a></li>
    </ul>
  </div>
<?php endif; ?>
  
  <div class="borrowing-card">
    <div class="borrowing-header">
      Borrowing & Return Requests
    </div>
    
    <div class="borrowing-content">
      <div class="search-container">
        <input type="text" placeholder="Search" class="search-input">
      </div>
      
      <div class="filter-container">
        <div class="filter-label">University</div>
        <button class="filter-button">Filter</button>
      </div>
      
      <!-- Debug info: Admin status = <?php echo $is_admin ? 'true' : 'false'; ?> -->
      <div class="request-list">
        <!-- Lab Day Request Option -->
        <a href="labday-details.php" style="text-decoration: none; color: inherit;">
          <div class="request-item">
            <div class="request-info">
              <div class="request-name">Lab day</div>
              <div class="request-desc">Schedule laboratory session</div>
            </div>
            <div class="request-date">Mar 11, 2025</div>
            <div class="request-arrow">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                <path d="M8.59 16.59L13.17 12 8.59 7.41 10 6l6 6-6 6-1.41-1.41z"/>
              </svg>
            </div>
          </div>
        </a>
        
        <!-- Direct Request Option -->
        <?php if ($is_admin): ?>
        <a href="admin-direct-request-details.php" style="text-decoration: none; color: inherit;">
        <?php else: ?>
        <a href="direct-request-details.php" style="text-decoration: none; color: inherit;">
        <?php endif; ?>
          <div class="request-item">
            <div class="request-info">
              <div class="request-name">Direct Request</div>
              <div class="request-desc">Request equipment directly</div>
            </div>
            <div class="request-date">Mar 11, 2025</div>
            <div class="request-arrow">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                <path d="M8.59 16.59L13.17 12 8.59 7.41 10 6l6 6-6 6-1.41-1.41z"/>
              </svg>
            </div>
          </div>
        </a>
      </div>
    </div>
  </div>
  
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Regular menu toggle functionality
      const menuToggle = document.getElementById('menuToggle');
      const sideNav = document.getElementById('sideNav');
      
      if (menuToggle && sideNav) {
        menuToggle.addEventListener('click', function() {
          sideNav.classList.toggle('active');
        });
        
        // Close regular menu when clicking outside on small screens
        document.addEventListener('click', function(event) {
          const isSmallScreen = window.matchMedia('(max-width: 768px)').matches;
          if (isSmallScreen && !sideNav.contains(event.target) && !menuToggle.contains(event.target)) {
            sideNav.classList.remove('active');
          }
        });
      }
      
      // Admin menu toggle functionality
      const adminMenuToggle = document.getElementById('adminMenuToggle');
      const adminSideNav = document.getElementById('adminSideNav');
      
      if (adminMenuToggle && adminSideNav) {
        adminMenuToggle.addEventListener('click', function() {
          adminSideNav.classList.toggle('active');
        });
        
        // Close admin menu when clicking outside on small screens
        document.addEventListener('click', function(event) {
          const isSmallScreen = window.matchMedia('(max-width: 768px)').matches;
          if (isSmallScreen && !adminSideNav.contains(event.target) && !adminMenuToggle.contains(event.target)) {
            adminSideNav.classList.remove('active');
          }
        });
      }
      
      // Request type selection
      const requestTypeOptions = document.querySelectorAll('.request-type-option');
      const requestForms = document.querySelectorAll('.request-form');
      
      requestTypeOptions.forEach(option => {
        option.addEventListener('click', function() {
          const type = this.getAttribute('data-type');
          
          // Update active class on options
          requestTypeOptions.forEach(opt => opt.classList.remove('active'));
          this.classList.add('active');
          
          // Show the selected form
          requestForms.forEach(form => form.classList.remove('active'));
          document.getElementById(type + '_form').classList.add('active');
        });
      });
      
      // Set default active option
      if (requestTypeOptions.length > 0) {
        requestTypeOptions[0].click();
      }
      
      // Equipment selection
      const equipmentCheckboxes = document.querySelectorAll('.equipment-checkbox');
      const selectedItems = document.getElementById('selected_items');
      const cartItemsContainer = document.getElementById('cart_items_container');
      
      function updateSelectedItems() {
        let hasSelectedItems = false;
        cartItemsContainer.innerHTML = '';
        
        equipmentCheckboxes.forEach(checkbox => {
          if (checkbox.checked) {
            hasSelectedItems = true;
            const itemContainer = checkbox.closest('.equipment-item');
            const itemName = itemContainer.querySelector('.equipment-name').textContent;
            const itemQuantity = itemContainer.querySelector('.equipment-quantity').value;
            
            const cartItem = document.createElement('div');
            cartItem.className = 'cart-item';
            cartItem.innerHTML = `
              <span class="cart-item-name">${itemName}</span>
              <span class="cart-item-quantity">Qty: ${itemQuantity}</span>
              <button type="button" class="cart-item-remove" data-id="${checkbox.id}">&times;</button>
            `;
            
            cartItemsContainer.appendChild(cartItem);
          }
        });
        
        selectedItems.style.display = hasSelectedItems ? 'block' : 'none';
        
        // Add event listeners to remove buttons
        document.querySelectorAll('.cart-item-remove').forEach(button => {
          button.addEventListener('click', function() {
            const checkboxId = this.getAttribute('data-id');
            document.getElementById(checkboxId).checked = false;
            updateSelectedItems();
          });
        });
      }
      
      equipmentCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateSelectedItems);
      });
      
      // Update quantity display when changed
      document.querySelectorAll('.equipment-quantity').forEach(input => {
        input.addEventListener('change', updateSelectedItems);
      });
    });
  </script>
</body>
</html>
