<?php
session_start();

// Allow both admin and regular users to access this page
// We'll just check if a session exists
if (!isset($_SESSION['admin_id']) && !isset($_SESSION['user_id'])) {
    // If neither admin nor user session exists, redirect to login
    header("Location: login.php");
    exit();
}

// Check if user is admin to determine which navigation to show
$is_admin = isset($_SESSION['admin_id']) && isset($_SESSION['is_admin']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Lab Day Details</title>
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="sidenav.css">
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
      height: 100vh;
      margin: 0;
      padding: 0;
      overflow: hidden;
      background: #f7f7f7;
      font-family: 'Segoe UI', Arial, sans-serif;
      display: flex;
      flex-direction: column;
      align-items: center;
    }
    
    .labday-card {
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
    .admin-view .labday-card {
      margin-left: 270px;
      width: calc(100% - 290px);
    }
    
    .labday-header {
      background-color: #ff7f1a;
      color: white;
      padding: 15px 20px;
      font-size: 1.2em;
      font-weight: 500;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    
    .back-button {
      background: none;
      border: none;
      color: white;
      font-size: 1.5em;
      cursor: pointer;
      padding: 0;
      margin-right: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    
    .labday-content {
      flex: 1;
      padding: 0;
      overflow-y: auto;
      background-color: #ff9f4a;
    }
    
    .info-table {
      width: 100%;
      border-collapse: collapse;
    }
    
    .info-table td {
      padding: 8px 15px;
      border: 1px solid #e55a00;
    }
    
    .info-table td:first-child {
      width: 120px;
      font-weight: 500;
      background-color: rgba(255, 255, 255, 0.1);
    }
    
    .equipment-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
    }
    
    .equipment-table th {
      background-color: #ff7f1a;
      color: white;
      text-align: left;
      padding: 10px 15px;
      font-weight: 500;
    }
    
    .equipment-table td {
      padding: 10px 15px;
      border-bottom: 1px solid #e55a00;
      background-color: white;
    }
    
    .equipment-table tr:last-child td {
      border-bottom: none;
    }
    
    .qty-column {
      width: 60px;
      text-align: center;
    }
    
    .due-column {
      width: 120px;
      text-align: center;
    }
    
    .edit-column {
      width: 50px;
      text-align: center;
    }
    
    .edit-icon {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 24px;
      height: 24px;
      border-radius: 50%;
      background-color: #f0f0f0;
      cursor: pointer;
    }
    
    .edit-icon:hover {
      background-color: #e0e0e0;
    }
    
    .action-buttons {
      display: flex;
      justify-content: flex-end;
      gap: 10px;
      padding: 15px;
      background-color: white;
    }
    
    .btn {
      background-color: #ff7f1a;
      color: white;
      border: none;
      padding: 12px 20px;
      border-radius: 8px;
      cursor: pointer;
      font-size: 1em;
      font-weight: 500;
      transition: background-color 0.2s;
    }
    
    .btn-reject {
      background-color: #dc3545;
    }
    
    .btn-reject:hover {
      background-color: #c82333;
    }
    
    .btn-approve {
      background-color: #28a745;
    }
    
    .btn-approve:hover {
      background-color: #218838;
    }
    
    @media (max-width: 768px) {
      .labday-card {
        border-radius: 0;
        margin: 0;
        height: 100vh;
        max-width: 100%;
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
      <li><a href="admin-users.php">
        <span class="icon">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
            <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
          </svg>
        </span>
        Manage Users
      </a></li>
      <li><a href="admin-equipment.php">
        <span class="icon">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
            <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 15.5h-1.5V14h-1v3H8v-3H7v4.5H5.5v-5c0-.55.45-1 1-1H11c.55 0 1 .45 1 1v5zm3.5 0H14v-6h3.5c.55 0 1 .45 1 1V16c0 .55-.45 1-1 1h-2v1.5zm-1-8c0 .55-.45 1-1 1H10V10h3V9h-2c-.55 0-1-.45-1-1V6.5c0-.55.45-1 1-1h2.5c.55 0 1 .45 1 1v4zm1 3.5H17v1.5h-1.5z"/>
          </svg>
        </span>
        Inventory Management
      </a></li>
      <li><a href="borrowing.php" class="active">
        <span class="icon">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
            <path d="M18 2H6c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zM9 4h2v5l-1-.75L9 9V4zm9 16H6V4h1v9l3-2.25L13 13V4h5v16z"/>
          </svg>
        </span>
        Borrowing & Return Requests
      </a></li>
      <li><a href="admin-reports.php">
        <span class="icon">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
            <path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/>
          </svg>
        </span>
        Reports
      </a></li>
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
  
  <div class="labday-card">
    <div class="labday-header">
      <button class="back-button" onclick="window.location.href='borrowing.php'">&larr;</button>
      <span>Lab Day Details</span>
    </div>
    
    <div class="labday-content">
      <table class="info-table">
        <tr>
          <td>Title</td>
          <td>Lab day</td>
        </tr>
        <tr>
          <td>Subject</td>
          <td>Fundamentals to Electronics Circuits</td>
        </tr>
        <tr>
          <td>Date</td>
          <td>March 11, 2025</td>
        </tr>
        <tr>
          <td>Time</td>
          <td>4:30PM-7:30PM</td>
        </tr>
        <tr>
          <td>Borrower</td>
          <td>Dave Daryl Basatingo</td>
        </tr>
        <tr>
          <td>Group No.</td>
          <td>5</td>
        </tr>
        <tr>
          <td>Members</td>
          <td>
            Karl Paolo Cabarlitasan<br>
            Jay Michael Santos<br>
            Ray Christian Reynaldo<br>
            Joaquin Alejandro Ortiz
          </td>
        </tr>
      </table>
      
      <table class="equipment-table">
        <thead>
          <tr>
            <th class="qty-column">Qty.</th>
            <th>Equipment Name</th>
            <th class="due-column">Due Return</th>
            <th class="edit-column"></th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td class="qty-column">1</td>
            <td>Oscilloscope</td>
            <td class="due-column">03/18/25</td>
            <td class="edit-column">
              <div class="edit-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="#666">
                  <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/>
                </svg>
              </div>
            </td>
          </tr>
          <tr>
            <td class="qty-column">2</td>
            <td>Multimeter</td>
            <td class="due-column">03/18/25</td>
            <td class="edit-column">
              <div class="edit-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="#666">
                  <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/>
                </svg>
              </div>
            </td>
          </tr>
          <tr>
            <td class="qty-column">1</td>
            <td>Soldering Iron</td>
            <td class="due-column">03/18/25</td>
            <td class="edit-column">
              <div class="edit-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="#666">
                  <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/>
                </svg>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
      
      <form method="post" class="action-buttons">
        <button type="submit" name="reject" class="btn btn-reject" onclick="return confirm('Are you sure you want to reject this lab day request?')">Reject</button>
        <button type="submit" name="approve" class="btn btn-approve">Approve</button>
      </form>
    </div>
  </div>
  
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Menu toggle functionality
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
