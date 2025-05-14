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

// Get counts for dashboard
$user_count = 0;
$equipment_count = 0;
$pending_requests = 0;

// Count users
$count_stmt = $conn->query("SELECT COUNT(*) as count FROM users");
if ($count_stmt) {
    $user_count = $count_stmt->fetch_assoc()['count'];
}

// Check if equipment table exists and count equipment
$equipment_check = $conn->query("SHOW TABLES LIKE 'equipment'");
if ($equipment_check->num_rows > 0) {
    $count_stmt = $conn->query("SELECT COUNT(*) as count FROM equipment");
    if ($count_stmt) {
        $equipment_count = $count_stmt->fetch_assoc()['count'];
    }
}

// Check if requests table exists and if it has a status column
$requests_check = $conn->query("SHOW TABLES LIKE 'requests'");
if ($requests_check->num_rows > 0) {
    // Check if status column exists in requests table
    $column_check = $conn->query("SHOW COLUMNS FROM requests LIKE 'status'");
    if ($column_check->num_rows > 0) {
        // Only query with status column if it exists
        $count_stmt = $conn->query("SELECT COUNT(*) as count FROM requests WHERE status = 'pending'");
        if ($count_stmt) {
            $pending_requests = $count_stmt->fetch_assoc()['count'];
        }
    } else {
        // If status column doesn't exist, just count all requests
        $count_stmt = $conn->query("SELECT COUNT(*) as count FROM requests");
        if ($count_stmt) {
            $pending_requests = $count_stmt->fetch_assoc()['count'];
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
    <title>Admin Dashboard - DS Lab</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="admin-sidenav.css">
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
        
        .admin-logo {
            display: flex;
            align-items: center;
            padding: 0 20px;
            margin-bottom: 30px;
        }
        
        .logo-img {
            width: 60px;
            height: 60px;
            margin-right: 10px;
        }
        
        .admin-sidenav h2 {
            color: #ff7f1a;
            margin: 0;
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
        }
        
        .admin-user-info {
            display: flex;
            align-items: center;
        }
        
        .admin-user-info span {
            margin-right: 15px;
            color: #444;
        }
        
        .logout-btn {
            background-color: #e55a00;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .dashboard-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .card {
            background-color: white;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            padding: 20px;
        }
        
        .card h3 {
            margin-top: 0;
            color: #444;
        }
        
        .card .count {
            font-size: 2rem;
            font-weight: bold;
            color: #e55a00;
        }
        
        .admin-actions {
            margin-top: 40px;
            text-align: center;
            max-width: 800px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .admin-actions h2 {
            color: #444;
            margin-top: 0;
            margin-bottom: 30px;
            text-align: center;
            font-size: 24px;
        }
        
        .action-buttons {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
            margin-top: 20px;
        }
        
        .action-row {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-bottom: 20px;
            width: 100%;
        }
        
        .action-btn {
            background-color: #ff7f1a;
            border: none;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            color: white;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            width: 200px;
            height: 120px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .action-btn:hover {
            background-color: #e55a00;
            transform: translateY(-3px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.15);
        }
        
        .action-btn .icon {
            margin-bottom: 15px;
            font-size: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .action-btn svg {
            width: 40px;
            height: 40px;
        }
        
        /* Mobile responsiveness */
        @media (max-width: 768px) {
            .admin-sidenav {
                transform: translateX(-100%);
            }
            
            .admin-sidenav.active {
                transform: translateX(0);
            }
            
            .admin-content {
                margin-left: 0;
            }
            
            .menu-toggle {
                display: block;
                position: fixed;
                top: 10px;
                left: 10px;
                z-index: 2;
                background-color: #444;
                color: white;
                border: none;
                border-radius: 5px;
                padding: 8px;
                cursor: pointer;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <?php include 'admin-sidenav-updated.php'; ?>
        
        <!-- Admin Content Area -->
        <div class="admin-content">
            <div class="admin-header">
                <h1>Admin Dashboard</h1>
                <div class="admin-user-info">
                    <span>Welcome, <?php echo htmlspecialchars($admin['full_name']); ?></span>
                    <a href="adminlogin.php" class="logout-btn">Logout</a>
                </div>
            </div>
            
            <!-- Dashboard Cards -->
            <div class="dashboard-cards">
                <div class="card">
                    <h3>Total Users</h3>
                    <div class="count"><?php echo $user_count; ?></div>
                </div>
                <div class="card">
                    <h3>Equipment Items</h3>
                    <div class="count"><?php echo $equipment_count; ?></div>
                </div>
                <div class="card">
                    <h3>Pending Requests</h3>
                    <div class="count"><?php echo $pending_requests; ?></div>
                </div>
                <div class="card">
                    <h3>Last Login</h3>
                    <div style="color: #444;"><?php echo $admin['last_login'] ? date('M d, Y H:i', strtotime($admin['last_login'])) : 'First login'; ?></div>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="admin-actions">
                <h2>Quick Actions</h2>
                
                <div class="action-buttons">
                    <!-- First Row -->
                    <div class="action-row">
                        <?php if ($permissions['can_manage_users']): ?>
                        <a href="admin-manage-users.php" class="action-btn">
                            <span class="icon">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                                </svg>
                            </span>
                            User Management
                        </a>
                        <?php endif; ?>
                        
                        <?php if ($permissions['can_manage_equipment']): ?>
                        <a href="inventory.php" class="action-btn">
                            <span class="icon">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 15.5h-1.5V14h-1v3H8v-3H7v4.5H5.5v-5c0-.55.45-1 1-1H11c.55 0 1 .45 1 1v5zm3.5 0H14v-6h3.5c.55 0 1 .45 1 1V16c0 .55-.45 1-1 1h-2v1.5zm-1-8c0 .55-.45 1-1 1H10V10h3V9h-2c-.55 0-1-.45-1-1V6.5c0-.55.45-1 1-1h2.5c.55 0 1 .45 1 1v4zm1 3.5H17v1.5h-1.5z"/>
                                </svg>
                            </span>
                            Inventory Management
                        </a>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Second Row -->
                    <div class="action-row">
                        <?php if ($permissions['can_approve_requests']): ?>
                        <a href="borrowing.php" class="action-btn">
                            <span class="icon">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M18 2H6c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zM9 4h2v5l-1-.75L9 9V4zm9 16H6V4h1v9l3-2.25L13 13V4h5v16z"/>
                                </svg>
                            </span>
                            Borrowing & Return Requests
                        </a>
                        <?php endif; ?>
                        
                        <?php if ($permissions['can_view_reports']): ?>
                        <a href="admin-reports.php" class="action-btn">
                            <span class="icon">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/>
                                </svg>
                            </span>
                            Reports & Logs Panel
                        </a>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Third Row -->
                    <div class="action-row">
                        <a href="admin-notifications.php" class="action-btn">
                            <span class="icon">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M19.14 12.94c.04-.3.06-.61.06-.94 0-.32-.02-.64-.07-.94l2.03-1.58c.18-.14.23-.41.12-.61l-1.92-3.32c-.12-.22-.37-.29-.59-.22l-2.39.96c-.5-.38-1.03-.7-1.62-.94l-.36-2.54c-.04-.24-.24-.41-.48-.41h-3.84c-.24 0-.43.17-.47.41l-.36 2.54c-.59.24-1.13.57-1.62.94l-2.39-.96c-.22-.08-.47 0-.59.22L2.74 8.87c-.12.21-.08.47.12.61l2.03 1.58c-.05.3-.09.63-.09.94s.02.64.07.94l-2.03 1.58c-.18.14-.23.41-.12.61l1.92 3.32c.12.22.37.29.59.22l2.39-.96c.5.38 1.03.7 1.62.94l.36 2.54c.05.24.24.41.48.41h3.84c.24 0 .44-.17.47-.41l.36-2.54c.59-.24 1.13-.56 1.62-.94l2.39.96c.22.08.47 0 .59-.22l1.92-3.32c.12-.22.07-.47-.12-.61l-2.01-1.58zM12 15.6c-1.98 0-3.6-1.62-3.6-3.6s1.62-3.6 3.6-3.6 3.6 1.62 3.6 3.6-1.62 3.6-3.6 3.6z"/>
                                </svg>
                            </span>
                            System Notifications
                        </a>
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
        });
    </script>
</body>
</html>
