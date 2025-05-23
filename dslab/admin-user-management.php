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

// Handle search functionality
$search_query = '';
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search_query = $_GET['search'];
    $sql = "SELECT * FROM users WHERE id LIKE ? OR id LIKE ?";
    $search_param = "%" . $search_query . "%";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $search_param, $search_param);
} else {
    // Default query to get all users
    $sql = "SELECT * FROM users";
    $stmt = $conn->prepare($sql);
}

$stmt->execute();
$result = $stmt->get_result();

// Log admin activity
$activity = "Viewed user management page";
$log_stmt = $conn->prepare("INSERT INTO admin_activity_log (admin_id, activity, timestamp) VALUES (?, ?, NOW())");
$log_stmt->bind_param("is", $admin_id, $activity);
$log_stmt->execute();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - DS Lab Admin</title>
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
        
        /* User Management Specific Styles */
        .user-management-container {
            background-color: #ff7f1a;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .user-management-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .user-management-header h2 {
            color: #444;
            margin: 0;
        }
        
        .search-container {
            display: flex;
            margin-bottom: 20px;
        }
        
        .search-container input[type="text"] {
            flex: 1;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px 0 0 5px;
            font-size: 16px;
        }
        
        .search-container button {
            background-color: #444;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 0 5px 5px 0;
            cursor: pointer;
        }
        
        .user-table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
            border-radius: 5px;
            overflow: hidden;
        }
        
        .user-table th, .user-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        .user-table th {
            background-color: #444;
            color: white;
        }
        
        .user-table tr:last-child td {
            border-bottom: none;
        }
        
        .user-table tr:hover {
            background-color: #f5f5f5;
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
        }
        
        .action-buttons button {
            padding: 8px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s;
        }
        
        .edit-btn {
            background-color: #444;
            color: white;
        }
        
        .delete-btn {
            background-color: #e55a00;
            color: white;
        }
        
        .action-buttons button:hover {
            opacity: 0.9;
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
                <li><a href="admin-user-management.php" class="active">
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
                <h1>User Management</h1>
            </div>
            
            <div class="user-management-container">
                <div class="user-management-header">
                    <h2>Management User</h2>
                </div>
                
                <form action="" method="GET" class="search-container">
                    <input type="text" name="search" placeholder="Search" value="<?php echo htmlspecialchars($search_query); ?>">
                    <button type="submit">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/>
                        </svg>
                    </button>
                </form>
                
                <div class="table-container">
                    <table class="user-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>ID Number</th>
                                <th>Role</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    $name = htmlspecialchars($row['name']);
                                    $id_number = htmlspecialchars($row['id_number']);
                                    $role = isset($row['role']) ? htmlspecialchars($row['role']) : 'Student';
                                    
                                    echo "<tr>";
                                    echo "<td>{$name}</td>";
                                    echo "<td>{$id_number}</td>";
                                    echo "<td>{$role}</td>";
                                    echo "<td class='action-buttons'>
                                            <button class='edit-btn' onclick=\"location.href='admin-edit-user.php?id={$row['id']}'\">Edit</button>
                                            <button class='delete-btn' onclick=\"if(confirm('Are you sure you want to delete this user?')) location.href='admin-delete-user.php?id={$row['id']}'\">Delete</button>
                                          </td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='4' style='text-align:center;'>No users found</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
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
