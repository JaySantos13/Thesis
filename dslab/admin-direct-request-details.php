<?php
session_start();
include 'db.php';

// Check if user is logged in as admin
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['is_admin'])) {
    header("Location: adminlogin.php");
    exit();
}

// Get admin ID for later use
$admin_id = $_SESSION['admin_id'];

// Get admin information
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
    <title>Direct Request Details - DS Lab Admin</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="sidenav.css">
    <style>
        /* Admin Dashboard Specific Styles */
        body, html {
            margin: 0;
            padding: 0;
            overflow-x: hidden;
            font-family: 'Segoe UI', Arial, sans-serif;
            background-color: white;
            height: 100%;
        }
        
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
        
        /* Mobile menu toggle */
        .menu-toggle {
            display: none;
            background: none;
            border: none;
            color: #444;
            font-size: 1.5em;
            cursor: pointer;
            z-index: 1000;
            position: fixed;
            top: 10px;
            left: 10px;
        }
        
        @media (max-width: 768px) {
            .admin-sidenav {
                transform: translateX(-100%);
                width: 250px;
            }
            
            .admin-content {
                margin-left: 0;
            }
            
            .menu-toggle {
                display: block;
            }
            
            .admin-sidenav.active {
                transform: translateX(0);
            }
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
        
        .admin-content {
            flex: 1;
            margin-left: 250px;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            min-height: 100vh;
            transition: margin-left 0.3s;
            position: relative;
        }
        
        .request-details-card {
            background: #ff9f1a;
            border-radius: 0;
            overflow: hidden;
            width: 750px;
            max-width: 100%;
            border: none;
            box-shadow: none;
            margin-top: 0;
            padding-top: 60px;
        }
        
        .ds-lab-logo {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            width: 100%;
            background-color: white;
            text-align: center;
            padding: 10px 0;
            border-bottom: 1px solid #ddd;
        }
        
        .ds-lab-logo img {
            height: 40px;
        }
        
        .card-header {
            display: none;
        }
        
        .card-content {
            padding: 0;
        }
        
        .info-table {
            width: 100%;
            border-collapse: collapse;
            background-color: #ff9f1a;
            color: black;
            table-layout: fixed;
            border: 1px solid black;
        }
        
        .info-table td {
            padding: 5px 10px;
            border: 1px solid black;
        }
        
        .info-table td:first-child {
            width: 25%;
            font-weight: normal;
            background-color: #ff9f1a;
        }
        
        .info-table td:last-child {
            width: 75%;
            background-color: #ff9f1a;
        }
        
        .equipment-label {
            background-color: #ff9f1a;
            padding: 12px 15px;
            font-weight: 500;
            color: #444;
            border-bottom: 1px solid rgba(255,255,255,0.2);
        }
        
        .equipment-label {
            display: none;
        }
        
        .equipment-table {
            width: 100%;
            border-collapse: collapse;
            background-color: #ff9f1a;
            table-layout: fixed;
            border: 1px solid black;
            margin-top: 10px;
        }
        
        .equipment-table th {
            background-color: #ff9f1a;
            color: black;
            text-align: left;
            padding: 5px 10px;
            font-weight: normal;
            font-size: 0.9em;
            border: 1px solid black;
        }
        
        .equipment-table th:nth-child(1) {
            width: 15%;
        }
        
        .equipment-table th:nth-child(2) {
            width: 55%;
        }
        
        .equipment-table th:nth-child(3) {
            width: 30%;
        }
        
        .equipment-table td {
            padding: 5px 10px;
            color: black;
            background-color: white;
            border: 1px solid black;
        }
        
        .info-icon {
            display: inline-block;
            width: 16px;
            height: 16px;
            color: black;
            text-align: center;
            line-height: 16px;
            font-size: 14px;
            margin-left: 5px;
            cursor: help;
        }
        
        .action-buttons {
            display: flex;
            justify-content: flex-end;
            padding: 15px;
            gap: 10px;
            background-color: #ff9f1a;
        }
        
        .btn-reject {
            background-color: #f44336;
            color: white;
            border: none;
            padding: 5px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: normal;
            width: auto;
            text-transform: none;
            font-size: 0.9em;
        }
        
        .btn-approve {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 5px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: normal;
            width: auto;
            text-transform: none;
            font-size: 0.9em;
        }
    </style>
</head>
<body>
    <!-- Admin Menu Toggle Button for Mobile -->
    <button class="menu-toggle" id="adminMenuToggle">
        <span class="icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                <path d="M3 18h18v-2H3v2zm0-5h18v-2H3v2zm0-7v2h18V6H3z"/>
            </svg>
        </span>
    </button>
    
    <div class="admin-container">
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
                    Inventory
                </a></li>
                <li><a href="borrowing.php" class="active">
                    <span class="icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M18 2H6c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zM9 4h2v5l-1-.75L9 9V4zm9 16H6V4h1v9l3-2.25L13 13V4h5v16z"/>
                        </svg>
                    </span>
                    Requests
                </a></li>
                <li><a href="admin-reports.php">
                    <span class="icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/>
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
        
        <!-- Admin Content -->
        <div class="admin-content">
            <!-- DS Lab Logo -->
            <div class="ds-lab-logo">
                <img src="icons/dsB.svg" alt="DS Lab Logo">
            </div>
            
            <!-- Request Details Card -->
            <div class="request-details-card">
                <div class="card-content">
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
                                <th>Qty</th>
                                <th>Equipment Name</th>
                                <th>Due Return</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($request_data['equipment'] as $item): ?>
                            <tr>
                                <td><?php echo $item['qty']; ?></td>
                                <td><?php echo $item['name']; ?></td>
                                <td><?php echo $item['due_return']; ?> <span class="info-icon">ⓘ</span></td>
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
        </div>
    </div>
    
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
</body>
</html>
