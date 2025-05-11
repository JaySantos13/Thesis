<?php
session_start();

// Check if professor is logged in
if (!isset($_SESSION['professor_id'])) {
  header("Location: professorlogin.php");
  exit();
}

include 'db.php';

// Get professor information
$professor_id = $_SESSION['professor_id'];
$sql = "SELECT * FROM professors WHERE id = $professor_id";
$result = $conn->query($sql);
$professor = $result->fetch_assoc();

// Count classes, equipment, and pending requests for dashboard stats
$class_count = 0;
$equipment_count = 0;
$pending_requests = 0;

// Count classes (if table exists)
$class_check = $conn->query("SHOW TABLES LIKE 'classes'");
if ($class_check->num_rows > 0) {
    $count_stmt = $conn->query("SELECT COUNT(*) as count FROM classes WHERE professor_id = $professor_id");
    if ($count_stmt) {
        $class_count = $count_stmt->fetch_assoc()['count'];
    }
}

// Count equipment (if table exists)
$equipment_check = $conn->query("SHOW TABLES LIKE 'equipment'");
if ($equipment_check->num_rows > 0) {
    $count_stmt = $conn->query("SELECT COUNT(*) as count FROM equipment");
    if ($count_stmt) {
        $equipment_count = $count_stmt->fetch_assoc()['count'];
    }
}

// Count pending requests (if table exists)
$requests_check = $conn->query("SHOW TABLES LIKE 'requests'");
if ($requests_check->num_rows > 0) {
    // Check if status column exists in requests table
    $column_check = $conn->query("SHOW COLUMNS FROM requests LIKE 'status'");
    if ($column_check->num_rows > 0) {
        // Only query with status column if it exists
        $count_stmt = $conn->query("SELECT COUNT(*) as count FROM requests WHERE status = 'pending' AND professor_id = $professor_id");
        if ($count_stmt) {
            $pending_requests = $count_stmt->fetch_assoc()['count'];
        }
    } else {
        // If status column doesn't exist, just count all requests
        $count_stmt = $conn->query("SELECT COUNT(*) as count FROM requests WHERE professor_id = $professor_id");
        if ($count_stmt) {
            $pending_requests = $count_stmt->fetch_assoc()['count'];
        }
    }
}

// Handle logout
if (isset($_GET['logout'])) {
  session_destroy();
  header("Location: professorlogin.php");
  exit();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Professor Dashboard - DS Lab</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Professor Dashboard Specific Styles */
        body, html {
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }
        
        .professor-container {
            display: flex;
            min-height: 100vh;
        }
        
        .professor-sidenav {
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
        
        .professor-logo {
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
        
        .professor-sidenav h2 {
            color: #ff7f1a;
            margin: 0;
        }
        
        .professor-sidenav ul {
            list-style: none;
            padding: 0;
        }
        
        .professor-sidenav li {
            margin-bottom: 5px;
        }
        
        .professor-sidenav a {
            display: flex;
            align-items: center;
            padding: 10px 20px;
            color: white;
            text-decoration: none;
            transition: background-color 0.3s;
        }
        
        .professor-sidenav a:hover, .professor-sidenav a.active {
            background-color: #e55a00;
        }
        
        .professor-sidenav .icon {
            margin-right: 10px;
            display: inline-flex;
        }
        
        .professor-content {
            flex: 1;
            margin-left: 250px;
            padding: 20px;
        }
        
        .menu-toggle {
            display: none;
            position: fixed;
            top: 10px;
            right: 10px;
            z-index: 2;
            background-color: #444;
            color: white;
            border: none;
            border-radius: 5px;
            padding: 8px;
            cursor: pointer;
        }
        
        .stats-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 40px;
        }
        
        .stat-card {
            flex: 1;
            min-width: 200px;
            background-color: white;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            padding: 20px;
        }
        
        .stat-card h3 {
            margin-top: 0;
            color: #444;
        }
        
        .stat-card .count {
            font-size: 2rem;
            font-weight: bold;
            color: #e55a00;
        }
        
        .professor-actions {
            margin-top: 40px;
            text-align: center;
            max-width: 800px;
        }
        
        .action-row {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .action-btn {
            background-color: #ff7f1a;
            color: white;
            border-radius: 8px;
            text-decoration: none;
            transition: all 0.3s ease;
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
        }
        
        .welcome-section {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .welcome-section h2 {
            color: #e55a00;
            margin-top: 0;
        }
        
        .welcome-section p {
            color: #444;
            margin-bottom: 0;
        }
        
        @media (max-width: 768px) {
            .professor-sidenav {
                left: -250px;
            }
            
            .professor-sidenav.active {
                left: 0;
            }
            
            .professor-content {
                margin-left: 0;
            }
            
            .menu-toggle {
                display: block;
            }
        }
    </style>
</head>
<body>
    <div class="professor-container">
        <!-- Professor Side Navigation -->
        <div class="professor-sidenav" id="professorSideNav">
            <div class="professor-logo">
                <img src="icons/dsB.svg" alt="DS Lab Logo" class="logo-img">
                <h2>Professor</h2>
            </div>
            <ul>
                <li><a href="professor-dashboard.php" class="active">
                    <span class="icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/>
                        </svg>
                    </span>
                    Dashboard
                </a></li>
                <li><a href="professor-classes.php">
                    <span class="icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M18 2H6c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-6 2h2v5l-1-.75L12 9V4zM6 20V4h4v9l3-2.25L16 13V4h2v16H6z"/>
                        </svg>
                    </span>
                    Classes
                </a></li>
                <li><a href="professor-equipment.php">
                    <span class="icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M22 10V6c0-1.1-.9-2-2-2H4c-1.1 0-1.99.9-1.99 2v4c1.1 0 1.99.9 1.99 2s-.89 2-2 2v4c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2v-4c-1.1 0-2-.9-2-2s.9-2 2-2zm-2-1.46c-1.19.69-2 1.99-2 3.46s.81 2.77 2 3.46V18H4v-2.54c1.19-.69 2-1.99 2-3.46 0-1.48-.8-2.77-1.99-3.46L4 6h16v2.54z"/>
                            <path d="M11 15h2v2h-2zm0-8h2v6h-2z"/>
                        </svg>
                    </span>
                    Equipment
                </a></li>
                <li><a href="professor-requests.php">
                    <span class="icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M19 3h-4.18C14.4 1.84 13.3 1 12 1c-1.3 0-2.4.84-2.82 2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 0c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1zm2 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/>
                        </svg>
                    </span>
                    Requests
                </a></li>
                <li><a href="professor-profile.php">
                    <span class="icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                        </svg>
                    </span>
                    Profile
                </a></li>
                <li><a href="professor-dashboard.php?logout=1">
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
        <button class="menu-toggle" id="professorMenuToggle">
            <span class="icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M3 18h18v-2H3v2zm0-5h18v-2H3v2zm0-7v2h18V6H3z"/>
                </svg>
            </span>
        </button>
        
        <!-- Main Content -->
        <div class="professor-content">
            <div class="welcome-section">
                <h2>Welcome, <?php echo htmlspecialchars($professor['name']); ?>!</h2>
                <p>Department: <?php echo htmlspecialchars($professor['department'] ?? 'Not specified'); ?></p>
            </div>
            
            <!-- Stats Cards -->
            <div class="stats-container">
                <div class="stat-card">
                    <h3>My Classes</h3>
                    <div class="count"><?php echo $class_count; ?></div>
                </div>
                <div class="stat-card">
                    <h3>Available Equipment</h3>
                    <div class="count"><?php echo $equipment_count; ?></div>
                </div>
                <div class="stat-card">
                    <h3>Pending Requests</h3>
                    <div class="count"><?php echo $pending_requests; ?></div>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="professor-actions">
                <h3>Quick Actions</h3>
                
                <!-- First Row -->
                <div class="action-row">
                    <a href="professor-labday.php" class="action-btn">
                        <span class="icon">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11zM7 10h5v5H7z"/>
                            </svg>
                        </span>
                        Assign Lab Day
                    </a>
                    
                    <a href="professor-lab-schedule.php" class="action-btn">
                        <span class="icon">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M19 3h-4.18C14.4 1.84 13.3 1 12 1c-1.3 0-2.4.84-2.82 2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 0c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1zm-2 14l-4-4 1.41-1.41L10 14.17l6.59-6.59L18 9l-8 8z"/>
                            </svg>
                        </span>
                        Lab Schedule
                    </a>
                </div>
                
                <!-- Second Row -->
                <div class="action-row">
                    <a href="professor-direct-request.php" class="action-btn">
                        <span class="icon">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/>
                            </svg>
                        </span>
                        Direct Request
                    </a>
                    
                    <a href="professor-equipment.php" class="action-btn">
                        <span class="icon">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M22 10V6c0-1.1-.9-2-2-2H4c-1.1 0-1.99.9-1.99 2v4c1.1 0 1.99.9 1.99 2s-.89 2-2 2v4c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2v-4c-1.1 0-2-.9-2-2s.9-2 2-2zm-2-1.46c-1.19.69-2 1.99-2 3.46s.81 2.77 2 3.46V18H4v-2.54c1.19-.69 2-1.99 2-3.46 0-1.48-.8-2.77-1.99-3.46L4 6h16v2.54z"/>
                                <path d="M11 15h2v2h-2zm0-8h2v6h-2z"/>
                            </svg>
                        </span>
                        Equipment Info
                    </a>
                </div>
                
                <!-- Third Row -->
                <div class="action-row">
                    <a href="professor-send-notification.php" class="action-btn">
                        <span class="icon">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
                            </svg>
                        </span>
                        Send Notifications
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const menuToggle = document.getElementById('professorMenuToggle');
            const sideNav = document.getElementById('professorSideNav');
            
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
