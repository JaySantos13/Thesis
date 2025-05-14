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

// Skip activity logging to avoid errors
// We'll just continue with the user management functionality

// Sample user data based on the image
$users = [
    ['name' => 'Basiliyo, Dave Daryl', 'id_number' => '00-0000-000', 'role' => 'Student'],
    ['name' => 'Cabangbang, Karl Paolo', 'id_number' => '00-0000-000', 'role' => 'Student'],
    ['name' => 'Reynaldo, Rey Christian', 'id_number' => '00-0000-000', 'role' => 'Student'],
    ['name' => 'Santos, Jay Michael', 'id_number' => '00-0000-000', 'role' => 'Student'],
    ['name' => 'Ortiz, Joaquin Alejandro', 'id_number' => '00-0000-000', 'role' => 'Student'],
    ['name' => 'Smith, John', 'id_number' => '00-1111-000', 'role' => 'Professor'],
    ['name' => 'Johnson, Emily', 'id_number' => '00-1112-000', 'role' => 'Professor'],
    ['name' => 'Garcia, Maria', 'id_number' => '00-1113-000', 'role' => 'Professor']
];

// Handle search and filter functionality
$search_query = '';
$filter_category = isset($_GET['category']) ? $_GET['category'] : 'all';

// First apply category filter
$category_filtered = $users;
if ($filter_category !== 'all') {
    $category_filtered = array_filter($users, function($user) use ($filter_category) {
        return strtolower($user['role']) === strtolower($filter_category);
    });
}

// Then apply search filter on top of category filter
$filtered_users = $category_filtered;
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search_query = $_GET['search'];
    $filtered_users = array_filter($category_filtered, function($user) use ($search_query) {
        return (stripos($user['name'], $search_query) !== false || 
                stripos($user['id_number'], $search_query) !== false || 
                stripos($user['role'], $search_query) !== false);
    });
}

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
        
        .user-management-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .user-management-header h2 {
            color: #444;
            margin: 0;
        }
        
        .filter-buttons {
            display: flex;
            gap: 10px;
        }
        
        .filter-btn {
            padding: 8px 15px;
            background-color: #f0f0f0;
            color: #444;
            border-radius: 5px;
            text-decoration: none;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        
        .filter-btn.active {
            background-color: #444;
            color: white;
        }
        
        .filter-btn:hover {
            background-color: #e55a00;
            color: white;
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
        
        .user-link {
            color: #444;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }
        
        .user-link:hover {
            color: #e55a00;
            text-decoration: underline;
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
                <li><a href="admin-manage-users.php" class="active">
                    <span class="icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                        </svg>
                    </span>
                    Manage Users
                </a></li>
                <?php endif; ?>
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
                    <div class="filter-buttons">
                        <a href="?category=all" class="filter-btn <?php echo $filter_category === 'all' ? 'active' : ''; ?>">All</a>
                        <a href="?category=student" class="filter-btn <?php echo $filter_category === 'student' ? 'active' : ''; ?>">Students</a>
                        <a href="?category=professor" class="filter-btn <?php echo $filter_category === 'professor' ? 'active' : ''; ?>">Professors</a>
                    </div>
                </div>
                
                <form action="" method="GET" class="search-container">
                    <input type="hidden" name="category" value="<?php echo htmlspecialchars($filter_category); ?>">
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
                            if (count($filtered_users) > 0) {
                                foreach ($filtered_users as $index => $user) {
                                    $user_id = $index + 1;
                                    echo "<tr>";
                                    echo "<td><a href='admin-user-detail.php?id={$user_id}' class='user-link'>" . htmlspecialchars($user['name']) . "</a></td>";
                                    echo "<td>" . htmlspecialchars($user['id_number']) . "</td>";
                                    echo "<td>" . htmlspecialchars($user['role']) . "</td>";
                                    echo "<td class='action-buttons'>
                                            <button class='edit-btn' onclick=\"location.href='admin-user-detail.php?id={$user_id}'\">View</button>
                                            <button class='delete-btn' onclick=\"if(confirm('Are you sure you want to delete this user?')) location.href='admin-delete-user.php?id={$user_id}'\">Delete</button>
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
