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

// Redirect admins to the admin version of this page
if ($is_admin) {
    header("Location: admin-direct-request-details.php");
    exit();
}

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

// For demo purposes, we're using hardcoded data
// In a real implementation, you would fetch this from the database using an ID passed in the URL
$request_data = [
    'title' => 'Direct Request',
    'subject' => 'Fundamentals to Electronics Circuits',
    'date' => 'March 11, 2025',
    'time' => '4:15 PM',
    'borrower' => 'Karl Paolo Cabanlugan',
    'equipment' => [
        ['name' => 'Multimeter', 'qty' => 1, 'due_return' => '03/18/25']
    ]
];

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Direct Request Details - DS Lab</title>
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
    
    /* Regular user styles */
    body {
      display: flex;
    }
    
    .content-container {
      flex: 1;
      display: flex;
      flex-direction: column;
    }
    
    .logo-container {
      background-color: white;
      padding: 10px 0;
      text-align: center;
    }
    
    .logo-container img {
      height: 60px;
    }
    
    .details-content {
      flex: 1;
      background-color: #ff7f1a;
      padding: 20px;
    }
    
    .info-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 20px;
      background-color: #ff7f1a;
    }
    
    .info-table td {
      padding: 8px 10px;
      border: 1px solid #e55a00;
    }
    
    .info-table td:first-child {
      font-weight: bold;
      width: 120px;
    }
    
    .equipment-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
      margin-bottom: 20px;
      background-color: #ff7f1a;
    }
    
    .equipment-table th {
      background-color: #ff7f1a;
      color: black;
      text-align: left;
      padding: 10px;
      border: 1px solid #e55a00;
    }
    
    .equipment-table td {
      padding: 10px;
      border: 1px solid #e55a00;
      background-color: white;
    }
    
    .action-buttons {
      display: flex;
      justify-content: flex-end;
      gap: 10px;
      margin-top: 20px;
    }
    
    .btn-reject {
      background-color: #f44336;
      color: white;
      border: none;
      padding: 8px 15px;
      border-radius: 5px;
      cursor: pointer;
      font-weight: 500;
    }
    
    .btn-approve {
      background-color: #4CAF50;
      color: white;
      border: none;
      padding: 8px 15px;
      border-radius: 5px;
      cursor: pointer;
      font-weight: 500;
    }
    
    .info-icon {
      vertical-align: middle;
      margin-left: 5px;
    }
  </style>
</head>
<body>
  <!-- Side Navigation -->
  <div class="sidenav" id="sideNav">
    <ul>
      <li><a href="notif.php"><span class="icon"><?php include 'icons/bell.svg'; ?></span> Notifications</a></li>
      <li><a href="history.php"><span class="icon"><?php include 'icons/clock.svg'; ?></span> History</a></li>
      <li><a href="dashboard.php"><span class="icon"><?php include 'icons/home.svg'; ?></span> Home</a></li>
      <li><a href="profile.php"><span class="icon"><?php include 'icons/profile.svg'; ?></span> Profile</a></li>
      <li><a href="more.php"><span class="icon"><?php include 'icons/more.svg'; ?></span> More</a></li>
    </ul>
  </div>
  
  <div class="content-container">
    <div class="logo-container">
      <img src="images/dslab-logo.png" alt="DS Lab Logo">
    </div>
    
    <div class="details-content">
      <table class="info-table">
        <tr>
          <td>Title</td>
          <td><?php echo $request_data['title']; ?></td>
        </tr>
        <tr>
          <td>Subject</td>
          <td><?php echo $request_data['subject']; ?></td>
        </tr>
        <tr>
          <td>Date</td>
          <td><?php echo $request_data['date']; ?></td>
        </tr>
        <tr>
          <td>Time</td>
          <td><?php echo $request_data['time']; ?></td>
        </tr>
        <tr>
          <td>Borrower</td>
          <td><?php echo $request_data['borrower']; ?></td>
        </tr>
      </table>
      
      <table class="equipment-table">
        <thead>
          <tr>
            <th>Qty.</th>
            <th>Equipment Name</th>
            <th>Due Return</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($request_data['equipment'] as $item): ?>
          <tr>
            <td><?php echo $item['qty']; ?></td>
            <td><?php echo $item['name']; ?></td>
            <td><?php echo $item['due_return']; ?> <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" class="info-icon"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      
      <div class="action-buttons">
        <button class="btn-reject">Reject</button>
        <button class="btn-approve">Approve</button>
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
      
      // Button actions
      document.querySelector('.btn-approve').addEventListener('click', function() {
        alert('Request approved!');
        window.location.href = 'borrowing.php';
      });
      
      document.querySelector('.btn-reject').addEventListener('click', function() {
        alert('Request rejected!');
        window.location.href = 'borrowing.php';
      });
    });
  </script>
</body>
</html>
