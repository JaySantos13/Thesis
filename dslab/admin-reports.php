<?php
session_start();

// Check if user is logged in as admin
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['is_admin'])) {
    header("Location: adminlogin.php");
    exit();
}

include 'db.php';

// Get admin information
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

// Check if admin has permission to view reports
if (!$permissions['can_view_reports']) {
    header("Location: admin-dashboard.php");
    exit();
}

// Log this activity
$action_type = "view";
$action_details = "Viewed reports and logs panel";
$ip_address = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
$log_stmt = $conn->prepare("INSERT INTO admin_activity_log (admin_id, action_type, action_details, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)");
$log_stmt->bind_param("issss", $admin_id, $action_type, $action_details, $ip_address, $user_agent);
$log_stmt->execute();

// Get borrowing requests logs
$borrowing_logs = [];
$borrowing_query = "SELECT * FROM borrowing_requests 
                 ORDER BY created_at DESC 
                 LIMIT 20";
$borrowing_result = $conn->query($borrowing_query);
if ($borrowing_result && $borrowing_result->num_rows > 0) {
    while ($row = $borrowing_result->fetch_assoc()) {
        $borrowing_logs[] = $row;
    }
}

// Check if requests table exists before querying
$request_logs = [];
$requests_check = $conn->query("SHOW TABLES LIKE 'requests'");
if ($requests_check->num_rows > 0) {
    $request_query = "SELECT * FROM requests 
                    ORDER BY created_at DESC 
                    LIMIT 20";
    $request_result = $conn->query($request_query);
    if ($request_result && $request_result->num_rows > 0) {
        while ($row = $request_result->fetch_assoc()) {
            $request_logs[] = $row;
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports & Logs Panel - DS Lab Admin</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Admin Dashboard Specific Styles */
        body, html {
            margin: 0;
            padding: 0;
            overflow-x: hidden;
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
            padding: 20px;
            transition: margin-left 0.3s;
        }
        
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .admin-header h1 {
            color: #444;
            margin: 0;
        }
        
        .admin-menu-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #444;
        }
        
        /* Reports Panel Specific Styles */
        .reports-panel {
            background-color: #ff7f1a;
            padding: 15px;
            margin: 0 auto;
            box-shadow: none;
            max-width: 500px;
            border-radius: 0;
            overflow-y: auto;
        }
        
        .reports-panel h2 {
            color: #444;
            margin: 0 0 10px 0;
            font-size: 1.2rem;
            font-weight: bold;
        }
        
        .filter-controls {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 15px;
            background-color: white;
            border-radius: 5px;
            padding: 10px;
            position: relative;
        }
        
        .filter-controls label {
            color: #444;
            margin-right: 5px;
            font-size: 0.9rem;
        }
        
        .filter-controls input[type="date"] {
            padding: 5px;
            border: 1px solid #ccc;
            border-radius: 3px;
            margin-right: 5px;
            font-size: 0.9rem;
        }
        
        .filter-controls button {
            background-color: #444;
            color: white;
            border: none;
            border-radius: 0;
            padding: 5px 10px;
            cursor: pointer;
            font-size: 0.8rem;
            margin: 0 0 5px 0;
            width: 40px;
            display: block;
            transition: background-color 0.2s;
        }
        
        .filter-controls button:hover {
            background-color: #333;
        }
        
        .log-item {
            background-color: #f2f2f2;
            border: 1px solid #ddd;
            border-radius: 0;
            padding: 10px 15px;
            margin: 0 0 10px 0;
            box-shadow: none;
            text-align: left;
            cursor: pointer;
            transition: background-color 0.2s, transform 0.1s;
        }
        
        .log-item:hover {
            background-color: #e8e8e8;
            transform: translateY(-1px);
        }
        
        .log-item:active {
            background-color: #ddd;
            transform: translateY(0);
        }
        
        .log-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .log-item h3 {
            color: #ff5500;
            margin: 0;
            font-size: 1rem;
            font-weight: normal;
        }
        
        .log-item p {
            color: #444;
            margin: 5px 0;
        }
        
        .log-item .timestamp {
            color: #777;
            font-size: 0.9rem;
            text-align: right;
        }
        
        .action-buttons {
            display: flex;
            justify-content: space-between;
            margin-top: 0;
            gap: 10px;
            margin-bottom: 10px;
        }
        
        .action-buttons button {
            background-color: #444;
            color: white;
            border: none;
            border-radius: 0;
            padding: 8px 10px;
            width: 49%;
            cursor: pointer;
            font-size: 0.9rem;
            transition: background-color 0.2s, transform 0.1s;
        }
        
        .action-buttons button:hover {
            background-color: #555;
        }
        
        .action-buttons button:active {
            background-color: #333;
            transform: translateY(1px);
        }
        
        .action-buttons button:hover {
            background-color: #e55a00;
        }
        
        .search-box {
            padding: 8px 10px;
            border: 1px solid #ccc;
            border-radius: 20px;
            width: 100%;
            margin: 0 0 15px 0;
            font-size: 14px;
        }
        
        @media (max-width: 768px) {
            .admin-menu-toggle {
                display: block;
            }
            
            .admin-sidenav {
                transform: translateX(-100%);
                width: 80%;
                max-width: 250px;
            }
            
            .admin-sidenav.active {
                transform: translateX(0);
            }
            
            .admin-content {
                margin-left: 0;
            }
            
            .filter-controls {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .filter-controls input[type="date"],
            .search-box {
                width: 100%;
                margin-bottom: 10px;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .action-buttons button {
                margin-left: 0;
                margin-bottom: 10px;
                width: 100%;
            }
        }
    </style>
</head>
<body>
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
                <?php if ($permissions['can_manage_users']): ?>
                <li><a href="admin-users.php">
                    <span class="icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                        </svg>
                    </span>
                    Manage Users
                </a></li>
                <?php endif; ?>
                <?php if ($permissions['can_manage_equipment']): ?>
                <li><a href="admin-equipment.php">
                    <span class="icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M22 9V7h-2V5c0-1.1-.9-2-2-2H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2v-2h2v-2h-2v-2h2v-2h-2V9h2zm-4 10H4V5h14v14zM6 13h5v4H6zm6-6h4v3h-4zm0 4h4v6h-4zm-6-4h5v3H6z"/>
                        </svg>
                    </span>
                    Manage Equipment
                </a></li>
                <?php endif; ?>
                <?php if ($permissions['can_approve_requests']): ?>
                <li><a href="admin-requests.php">
                    <span class="icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M19 3h-4.18C14.4 1.84 13.3 1 12 1c-1.3 0-2.4.84-2.82 2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 0c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1zm2 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/>
                        </svg>
                    </span>
                    Requests
                </a></li>
                <?php endif; ?>
                <?php if ($permissions['can_view_reports']): ?>
                <li><a href="admin-reports.php" class="active">
                    <span class="icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/>
                        </svg>
                    </span>
                    Reports
                </a></li>
                <?php endif; ?>
                <?php if ($permissions['can_manage_admins'] && $admin['is_super_admin']): ?>
                <li><a href="admin-management.php">
                    <span class="icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm0 10.99h7c-.53 4.12-3.28 7.79-7 8.94V12H5V6.3l7-3.11v8.8z"/>
                        </svg>
                    </span>
                    Admin Management
                </a></li>
                <?php endif; ?>
                <li><a href="admin-logout.php">
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
            <div class="admin-header">
                <button id="adminMenuToggle" class="admin-menu-toggle">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M3 18h18v-2H3v2zm0-5h18v-2H3v2zm0-7v2h18V6H3z"/>
                    </svg>
                </button>
                <h1>Reports & Logs Panel</h1>
                <div class="admin-user">
                    <span>admin</span>
                </div>
            </div>
            
            <!-- Reports Panel -->
            <div class="reports-panel">
                <input type="text" class="search-box" placeholder="Search...">
                
                <div class="filter-controls" style="margin-top: 10px;">
                    <div>
                        <label>Date</label>
                        <input type="date" id="startDate" name="startDate" value="2025-04-30">
                        <span>to</span>
                        <input type="date" id="endDate" name="endDate" value="2025-05-01">
                    </div>
                </div>
                
                <div style="display: flex; justify-content: space-between; margin: 10px 0 20px 0;">
                    <button id="filterBtn" style="height: 30px; width: 49%; font-size: 12px; background-color: #444; color: white; border: none; border-radius: 5px; cursor: pointer; transition: background-color 0.2s; text-align: center; display: flex; justify-content: center; align-items: center;">Filter</button>
                    <button id="clearBtn" style="height: 30px; width: 49%; font-size: 12px; background-color: #444; color: white; border: none; border-radius: 5px; cursor: pointer; transition: background-color 0.2s; text-align: center; display: flex; justify-content: center; align-items: center;">Clear</button>
                </div>
                
                <div class="log-items">
                    <div class="log-item" style="margin-bottom: 5px; cursor: pointer; background-color: #fff; border-radius: 5px; padding: 10px;">
                        <h3 style="color: #ff5500; margin: 0;">Lab day</h3>
                        <div id="lab-day-logs" style="display: none; margin-top: 15px;"></div>
                    </div>
                    
                    <div class="log-item" style="margin-bottom: 5px; cursor: pointer; background-color: #fff; border-radius: 5px; padding: 10px;">
                        <h3 style="color: #ff5500; margin: 0;">Direct Request</h3>
                        <div id="direct-request-logs" style="display: none; margin-top: 15px;"></div>
                    </div>
                </div>
                
                <div style="text-align: center; margin-top: 20px; color: #fff;">
                    <p>No borrowing request logs found</p>
                    <p>No direct request logs found</p>
                </div>
                
                <div class="action-buttons" style="display: flex; justify-content: space-between; margin-top: 20px;">
                    <button id="exportBtn" style="height: 30px; width: 49%; font-size: 12px; background-color: #444; color: white; border: none; border-radius: 5px; cursor: pointer;">Export</button>
                    <button id="printBtn" style="height: 30px; width: 49%; font-size: 12px; background-color: #444; color: white; border: none; border-radius: 5px; cursor: pointer;">Print</button>
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
            
            // Initialize the log items with event listeners
            document.querySelector('.log-item:nth-child(1)').addEventListener('click', showLabDayLogs);
            document.querySelector('.log-item:nth-child(2)').addEventListener('click', showDirectRequestLogs);
            
            function showLabDayLogs() {
                const logsContainer = document.getElementById('lab-day-logs');
                
                // Toggle display
                if (logsContainer.style.display === '' || logsContainer.style.display === 'none') {
                    logsContainer.style.display = 'block';
                    
                    // Create lab day logs table exactly matching the screenshot
                    logsContainer.innerHTML = `
                        <div style="background-color: white; border-radius: 5px; overflow: hidden;">
                            <table style="width: 100%; border-collapse: collapse; background-color: white;">
                                <tr style="background-color: #ff7f1a; color: white;">
                                    <th style="padding: 8px 3px; text-align: center; width: 40px;">Qty</th>
                                    <th style="padding: 8px 3px; text-align: left;">Equipment Name</th>
                                    <th style="padding: 8px 3px; text-align: center;">Borrow Date</th>
                                    <th style="padding: 8px 3px; text-align: center;">Return Date</th>
                                    <th style="padding: 8px 3px; text-align: center;">Due Date</th>
                                    <th style="padding: 8px 3px; text-align: center;">Status</th>
                                    <th style="padding: 8px 3px; text-align: center;">Action</th>
                                </tr>
                                <tr>
                                    <td style="padding: 8px 3px; text-align: center; border-bottom: 1px solid #ddd;">1</td>
                                    <td style="padding: 8px 3px; border-bottom: 1px solid #ddd;">Oscilloscope</td>
                                    <td style="padding: 8px 3px; text-align: center; border-bottom: 1px solid #ddd;">Mar 10, 2025</td>
                                    <td style="padding: 8px 3px; text-align: center; border-bottom: 1px solid #ddd;">Mar 10, 2025</td>
                                    <td style="padding: 8px 3px; text-align: center; border-bottom: 1px solid #ddd;">Mar 11, 2025</td>
                                    <td style="padding: 8px 3px; text-align: center; border-bottom: 1px solid #ddd;"><span style="padding: 2px 5px; background-color: #28a745; color: white; border-radius: 3px; font-size: 10px;">Returned</span></td>
                                    <td style="padding: 8px 3px; text-align: center; border-bottom: 1px solid #ddd;"></td>
                                </tr>
                                <tr>
                                    <td style="padding: 8px 3px; text-align: center; border-bottom: 1px solid #ddd;">2</td>
                                    <td style="padding: 8px 3px; border-bottom: 1px solid #ddd;">Multimeter</td>
                                    <td style="padding: 8px 3px; text-align: center; border-bottom: 1px solid #ddd;">Mar 10, 2025</td>
                                    <td style="padding: 8px 3px; text-align: center; border-bottom: 1px solid #ddd;"></td>
                                    <td style="padding: 8px 3px; text-align: center; border-bottom: 1px solid #ddd;">Mar 11, 2025</td>
                                    <td style="padding: 8px 3px; text-align: center; border-bottom: 1px solid #ddd;"><span style="padding: 2px 5px; background-color: #dc3545; color: white; border-radius: 3px; font-size: 10px;">Overdue</span></td>
                                    <td style="padding: 8px 3px; text-align: center; border-bottom: 1px solid #ddd;">
                                        <button style="background-color: #ff7f1a; color: white; border: none; padding: 2px 5px; border-radius: 3px; cursor: pointer; font-size: 10px;">Add extension</button>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 8px 3px; text-align: center;">1</td>
                                    <td style="padding: 8px 3px;">Soldering Iron</td>
                                    <td style="padding: 8px 3px; text-align: center;">Mar 10, 2025</td>
                                    <td style="padding: 8px 3px; text-align: center;"></td>
                                    <td style="padding: 8px 3px; text-align: center;">Mar 11, 2025</td>
                                    <td style="padding: 8px 3px; text-align: center;"><span style="padding: 2px 5px; background-color: #dc3545; color: white; border-radius: 3px; font-size: 10px;">Overdue</span></td>
                                    <td style="padding: 8px 3px; text-align: center;">
                                        <button style="background-color: #ff7f1a; color: white; border: none; padding: 2px 5px; border-radius: 3px; cursor: pointer; font-size: 10px;">Add extension</button>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        
                        <div style="padding: 10px; font-size: 11px;">
                            <table style="width: 100%; border-collapse: collapse;">
                                <tr>
                                    <td style="vertical-align: top; width: 50%;">
                                        <strong>Title:</strong> Fundamentals to Electronics Circuits<br>
                                        <strong>Subject:</strong> Fundamentals to Electronics Circuits<br>
                                        <strong>Date:</strong> March 11, 2025<br>
                                        <strong>Time:</strong> 4:30PM-7:00PM
                                    </td>
                                    <td style="vertical-align: top; width: 50%;">
                                        <strong>Borrower:</strong> Dave Daryl Basatingo<br>
                                        <strong>Group No.:</strong> 5<br>
                                        <strong>Members:</strong> Karl Paolo Cabarlitasan, Jay Michael Santos, Ray Christian Reynaldo, Joaquin Alejandro Ortiz
                                    </td>
                                </tr>
                            </table>
                        </div>
                    `;
                } else {
                    logsContainer.style.display = 'none';
                }
            }
            
            function showDirectRequestLogs() {
                const logsContainer = document.getElementById('direct-request-logs');
                
                // Toggle display
                if (logsContainer.style.display === '' || logsContainer.style.display === 'none') {
                    logsContainer.style.display = 'block';
                    logsContainer.innerHTML = '<p>No direct request logs available at this time.</p>';
                } else {
                    logsContainer.style.display = 'none';
                }
            }
            
            // Search functionality
            const searchBox = document.querySelector('.search-box');
            searchBox.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                console.log(`Searching for: ${searchTerm}`);
                // In a real implementation, this would filter the logs in real-time
            });
            
            // Filter functionality
            document.getElementById('filterBtn').addEventListener('click', function() {
                const startDate = document.getElementById('startDate').value;
                const endDate = document.getElementById('endDate').value;
                const searchTerm = document.querySelector('.search-box').value.toLowerCase();
                
                // Visual feedback
                this.style.backgroundColor = '#333';
                setTimeout(() => {
                    this.style.backgroundColor = '';
                }, 200);
                
                console.log(`Filtering from ${startDate} to ${endDate} with search term: ${searchTerm}`);
                // In a real implementation, this would make an AJAX call or submit a form
            });
            
            // Clear filters
            document.getElementById('clearBtn').addEventListener('click', function() {
                document.querySelector('.search-box').value = '';
                
                // Visual feedback
                this.style.backgroundColor = '#333';
                setTimeout(() => {
                    this.style.backgroundColor = '';
                }, 200);
                
                console.log('Filters cleared');
                // In a real implementation, this would reload the page or reset the view
            });
            
            // Export functionality
            document.getElementById('exportBtn').addEventListener('click', function() {
                console.log('Exporting reports to CSV...');
                // In a real implementation, this would trigger a download
                
                // Visual feedback
                this.style.backgroundColor = '#555';
                setTimeout(() => {
                    this.style.backgroundColor = '';
                }, 200);
            });
            
            // Print functionality
            document.getElementById('printBtn').addEventListener('click', function() {
                console.log('Printing reports...');
                // Visual feedback before printing
                this.style.backgroundColor = '#555';
                setTimeout(() => {
                    this.style.backgroundColor = '';
                    window.print();
                }, 200);
            });
        });
    </script>
</body>
</html>
