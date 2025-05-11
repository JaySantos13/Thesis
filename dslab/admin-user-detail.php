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

// Check if admin has permission to manage users
if (!$permissions['can_manage_users']) {
    echo "<script>alert('You do not have permission to access this page.'); window.location.href='admin-dashboard.php';</script>";
    exit();
}

// Sample user data based on the image
$users = [
    1 => [
        'name' => 'Dave Daryll Beatingo', 
        'id_number' => '00-000-000', 
        'role' => 'Student',
        'course' => 'Bachelor of Science in Computer Engineering',
        'email' => 'Davedaryll92@gmail.com',
        'password' => 'DOVthebest'
    ],
    2 => ['name' => 'Cabangbang, Karl Paolo', 'id_number' => '00-0000-000', 'role' => 'Student', 'course' => 'Bachelor of Science in Computer Engineering', 'email' => 'karlpaolo@example.com', 'password' => 'password123'],
    3 => ['name' => 'Reynaldo, Rey Christian', 'id_number' => '00-0000-000', 'role' => 'Student', 'course' => 'Bachelor of Science in Computer Engineering', 'email' => 'reychristian@example.com', 'password' => 'password123'],
    4 => ['name' => 'Santos, Jay Michael', 'id_number' => '00-0000-000', 'role' => 'Student', 'course' => 'Bachelor of Science in Computer Engineering', 'email' => 'jaymichael@example.com', 'password' => 'password123'],
    5 => ['name' => 'Ortiz, Joaquin Alejandro', 'id_number' => '00-0000-000', 'role' => 'Student', 'course' => 'Bachelor of Science in Computer Engineering', 'email' => 'joaquin@example.com', 'password' => 'password123'],
    6 => ['name' => 'Smith, John', 'id_number' => '00-1111-000', 'role' => 'Professor', 'department' => 'Computer Science Department', 'email' => 'john.smith@example.com', 'password' => 'password123'],
    7 => ['name' => 'Johnson, Emily', 'id_number' => '00-1112-000', 'role' => 'Professor', 'department' => 'Engineering Department', 'email' => 'emily.johnson@example.com', 'password' => 'password123'],
    8 => ['name' => 'Garcia, Maria', 'id_number' => '00-1113-000', 'role' => 'Professor', 'department' => 'Mathematics Department', 'email' => 'maria.garcia@example.com', 'password' => 'password123']
];

// Get user ID from URL parameter
$user_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Check if user exists
if (!isset($users[$user_id])) {
    echo "<script>alert('User not found.'); window.location.href='admin-manage-users.php';</script>";
    exit();
}

$user = $users[$user_id];

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['change_role'])) {
        // Handle role change
        echo "<script>alert('Role changed successfully.'); window.location.href='admin-user-detail.php?id=$user_id';</script>";
    } elseif (isset($_POST['reset_account'])) {
        // Handle account reset
        echo "<script>alert('Account reset successfully.'); window.location.href='admin-user-detail.php?id=$user_id';</script>";
    } elseif (isset($_POST['delete_account'])) {
        // Handle account deletion
        echo "<script>alert('Account deleted successfully.'); window.location.href='admin-manage-users.php';</script>";
    } elseif (isset($_POST['unlock_account'])) {
        // Handle account unlock
        echo "<script>alert('Account unlocked successfully.'); window.location.href='admin-user-detail.php?id=$user_id';</script>";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Details - DS Lab Admin</title>
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
            margin-bottom: 20px;
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
        
        /* User Detail Specific Styles */
        .user-detail-container {
            background-color: #ff7f1a;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            color: #444;
        }
        
        .user-detail-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 20px;
        }
        
        .user-info {
            flex: 1;
        }
        
        .user-name {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .user-course {
            font-size: 16px;
            margin-bottom: 15px;
        }
        
        .user-id {
            font-size: 14px;
            margin-bottom: 5px;
            text-align: right;
        }
        
        .user-role {
            font-size: 14px;
            margin-bottom: 15px;
            text-align: right;
        }
        
        .user-detail-content {
            margin-top: 20px;
        }
        
        .detail-row {
            margin-bottom: 10px;
        }
        
        .detail-label {
            font-weight: bold;
            margin-right: 10px;
        }
        
        .action-buttons {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-top: 20px;
            align-items: flex-end;
        }
        
        .action-btn {
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            background-color: #ccc;
            color: #444;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s;
            width: 150px;
            text-align: center;
        }
        
        .action-btn:hover {
            background-color: #444;
            color: white;
        }
        
        .delete-btn {
            background-color: #e55a00;
            color: white;
        }
        
        .back-link {
            display: inline-flex;
            align-items: center;
            color: #444;
            text-decoration: none;
            margin-bottom: 20px;
        }
        
        .back-link:hover {
            color: #e55a00;
        }
        
        .back-link .icon {
            margin-right: 5px;
        }
        
        @media (max-width: 768px) {
            .admin-menu-toggle {
                display: block;
            }
            
            .admin-sidenav {
                left: -250px;
            }
            
            .admin-sidenav.active {
                left: 0;
            }
            
            .admin-content {
                margin-left: 0;
            }
            
            .user-detail-header {
                flex-direction: column;
            }
            
            .user-id, .user-role {
                text-align: left;
            }
            
            .action-buttons {
                align-items: flex-start;
                margin-top: 20px;
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
                <li><a href="admin-manage-users.php" class="active">
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
                            <path d="M22 10V6c0-1.1-.9-2-2-2H4c-1.1 0-1.99.9-1.99 2v4c1.1 0 1.99.9 1.99 2s-.89 2-2 2v4c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2v-4c-1.1 0-2-.9-2-2s.9-2 2-2zm-2-1.46c-1.19.69-2 1.99-2 3.46s.81 2.77 2 3.46V18H4v-2.54c1.19-.69 2-1.99 2-3.46 0-1.48-.8-2.77-1.99-3.46L4 6h16v2.54z"/>
                            <path d="M11 15h2v2h-2zm0-8h2v6h-2z"/>
                        </svg>
                    </span>
                    Manage Equipment
                </a></li>
                <?php endif; ?>
                <?php if ($permissions['can_approve_requests']): ?>
                <li><a href="admin-requests.php">
                    <span class="icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M19 3h-4.18C14.4 1.84 13.3 1 12 1c-1.3 0-2.4.84-2.82 2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 0c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1zm-2 14l-4-4 1.41-1.41L10 14.17l6.59-6.59L18 9l-8 8z"/>
                        </svg>
                    </span>
                    Requests
                </a></li>
                <?php endif; ?>
                <?php if ($permissions['can_view_reports']): ?>
                <li><a href="admin-reports.php">
                    <span class="icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/>
                        </svg>
                    </span>
                    Reports
                </a></li>
                <?php endif; ?>
                <?php if ($permissions['can_manage_admins'] && $admin['is_super_admin']): ?>
                <li><a href="admin-manage-admins.php">
                    <span class="icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm0 10.99h7c-.53 4.12-3.28 7.79-7 8.94V12H5V6.3l7-3.11v8.8z"/>
                        </svg>
                    </span>
                    Manage Admins
                </a></li>
                <?php endif; ?>
                <li><a href="admin-logout.php">
                    <span class="icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M13 3h-2v10h2V3zm4.83 2.17l-1.42 1.42C17.99 7.86 19 9.81 19 12c0 3.87-3.13 7-7 7s-7-3.13-7-7c0-2.19 1.01-4.14 2.58-5.42L6.17 5.17C4.23 6.82 3 9.26 3 12c0 4.97 4.03 9 9 9s9-4.03 9-9c0-2.74-1.23-5.18-3.17-6.83z"/>
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
                <h1>User Details</h1>
            </div>
            
            <a href="admin-manage-users.php" class="back-link">
                <span class="icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"/>
                    </svg>
                </span>
                Back to User Management
            </a>
            
            <div class="user-detail-container">
                <div class="user-detail-header">
                    <div class="user-info">
                        <div class="user-name"><?php echo htmlspecialchars($user['name']); ?></div>
                        <?php if ($user['role'] === 'Student'): ?>
                        <div class="user-course"><?php echo htmlspecialchars($user['course']); ?></div>
                        <?php elseif ($user['role'] === 'Professor'): ?>
                        <div class="user-course"><?php echo htmlspecialchars($user['department']); ?></div>
                        <?php endif; ?>
                    </div>
                    <div>
                        <div class="user-id"><?php echo htmlspecialchars($user['id_number']); ?></div>
                        <div class="user-role"><?php echo htmlspecialchars($user['role']); ?></div>
                    </div>
                </div>
                
                <div class="user-detail-content">
                    <div class="detail-row">
                        <span class="detail-label">Email:</span>
                        <span><?php echo htmlspecialchars($user['email']); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Password:</span>
                        <span><?php echo htmlspecialchars($user['password']); ?></span>
                    </div>
                </div>
                
                <div class="action-buttons">
                    <form method="POST">
                        <button type="submit" name="change_role" class="action-btn">Change Role</button>
                    </form>
                    <form method="POST">
                        <button type="submit" name="reset_account" class="action-btn">Reset Account</button>
                    </form>
                    <form method="POST" onsubmit="return confirm('Are you sure you want to delete this account? This action cannot be undone.');">
                        <button type="submit" name="delete_account" class="action-btn delete-btn">Delete Account</button>
                    </form>
                    <form method="POST">
                        <button type="submit" name="unlock_account" class="action-btn">Unlock Account</button>
                    </form>
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
