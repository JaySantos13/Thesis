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
        $count_stmt = $conn->query("SELECT COUNT(*) as count FROM requests WHERE status = 'pending' AND user_id = $professor_id");
        if ($count_stmt) {
            $pending_requests = $count_stmt->fetch_assoc()['count'];
        }
    } else {
        // If status column doesn't exist, just count all requests
        $count_stmt = $conn->query("SELECT COUNT(*) as count FROM requests WHERE user_id = $professor_id");
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
        
        .mobile-header {
            display: flex;
            align-items: center;
            padding: 10px 16px;
            background: white;
            margin-bottom: 10px;
            gap: 12px;
            justify-content: center;
            border-radius: 0;
            position: relative;
            z-index: 1;
            width: 320px;
            margin-left: auto;
            margin-right: auto;
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
            margin-top: 10px;
            text-align: center;
            max-width: 320px;
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 8px;
            width: 100%;
            margin-left: auto;
            margin-right: auto;
        }
        
        .professor-actions a:not(:first-child) {
            height: 60px;
        }
        
        .professor-actions a:first-child {
            grid-column: 1 / span 2;
            height: 60px;
        }
        
        .action-row {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .action-button {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background-color: #ff7f1a;
            color: black;
            text-decoration: none;
            padding: 8px 5px;
            border-radius: 8px;
            transition: all 0.3s ease;
            text-align: center;
            width: 100%;
            height: 100%;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            font-size: 0.8em;
            font-weight: 500;
        }
        
        .action-button:hover {
            background-color: #e55a00;
            transform: translateY(-3px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.15);
        }
        
        .action-button .icon {
            margin-bottom: 6px;
        }
        
        .action-button .icon svg {
            width: 24px;
            height: 24px;
            fill: black;
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
    <style>
    .professor-main {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        min-height: 100vh;
        margin-left: 160px; /* same as .sidenav width */
        padding: 0;
        background-color: #f5f5f5;
    }
    @media (max-width: 768px) {
        .professor-main {
            margin-left: 0;
            padding-top: 0;
            padding-bottom: 0;
        }
    }
    </style>
</head>
<body>
<div class="sidenav" id="sideNav">
        <ul>
            <li><a href="professor-notifications.php"><span class="icon"><?php include_once __DIR__ . '/icons/bell.svg'; ?></span> Notifications</a></li>
            <li><a href="professor-history.php"><span class="icon"><?php include_once __DIR__ . '/icons/clock.svg'; ?></span> History</a></li>
            <li><a href="professor-dashboard.php"><span class="icon"><?php include_once __DIR__ . '/icons/home.svg'; ?></span> Home</a></li>
            <li><a href="professor-profile.php"><span class="icon"><?php include_once __DIR__ . '/icons/profile.svg'; ?></span> Profile</a></li>
            <li><a href="professor-more.php"><span class="icon"><?php include_once __DIR__ . '/icons/more.svg'; ?></span> More</a></li>
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
        <div class="professor-main">
            <?php
            // Check if professor data exists
            if (isset($professor) && !empty($professor)) {
            ?>
                <div class="mobile-header">
                    <div class="logo">
                        <?php 
                        if (file_exists(__DIR__ . '/icons/dsB.svg')) {
                            include_once __DIR__ . '/icons/dsB.svg';
                        } else {
                            echo '<div style="display:flex;align-items:center;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24" style="fill:#ff6b00;">
                                    <path d="M13.5.67s.74 2.65.74 4.8c0 2.06-1.35 3.73-3.41 3.73-2.07 0-3.63-1.67-3.63-3.73l.03-.36C5.21 7.51 4 10.62 4 14c0 4.42 3.58 8 8 8s8-3.58 8-8C20 8.61 17.41 3.8 13.5.67zM11.71 19c-1.78 0-3.22-1.4-3.22-3.14 0-1.62 1.05-2.76 2.81-3.12 1.77-.36 3.6-1.21 4.62-2.58.39 1.29.59 2.65.59 4.04 0 2.65-2.15 4.8-4.8 4.8z"/>
                                </svg>
                                <span style="color:#000;font-weight:bold;margin-left:5px;font-size:0.9em;">DS LAB</span>
                            </div>';
                        }
                        ?>
                    </div>
                    <div class="welcome-text" style="text-align:center;">
                        <p style="margin:0;font-size:0.7em;color:#666;">Welcome</p>
                        <h2 style="margin:0;font-size:1em;font-weight:600;"><?php echo htmlspecialchars($professor['name'] ?? 'Professor'); ?></h2>
                    </div>
                </div>
                
                <!-- Quick Actions -->
                <div class="professor-actions" style="margin-top:10px;">
                    <a href="professor-labday.php" class="action-button">
                        <span class="icon">
                        <?php 
                        if (file_exists(__DIR__ . '/icons/calendar.svg')) {
                            include_once __DIR__ . '/icons/calendar.svg';
                        } else {
                            echo '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M4 11h5V5H4v6zm0 7h5v-6H4v6zm6 0h5v-6h-5v6zm6 0h5v-6h-5v6zm-6-7h5V5h-5v6zm6-6v6h5V5h-5z"/></svg>';
                        }
                        ?>
                        </span>
                        <span style="font-weight:bold;">Assign Lab Day</span>
                    </a>
                    <a href="professor-lab-schedule.php" class="action-button">
                        <span class="icon">
                        <?php 
                        if (file_exists(__DIR__ . '/icons/schedule.svg')) {
                            include_once __DIR__ . '/icons/schedule.svg';
                        } else {
                            echo '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/></svg>';
                        }
                        ?>
                        </span>
                        <span style="font-weight:bold;">Lab Schedule</span>
                    </a>
                    <a href="professor-direct-request.php" class="action-button">
                        <span class="icon">
                        <?php 
                        if (file_exists(__DIR__ . '/icons/edit.svg')) {
                            include_once __DIR__ . '/icons/edit.svg';
                        } else {
                            echo '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>';
                        }
                        ?>
                        </span>
                        <span style="font-weight:bold;">Direct Request</span>
                    </a>
                    <a href="professor-equipment.php" class="action-button">
                        <span class="icon">
                        <?php 
                        if (file_exists(__DIR__ . '/icons/info.svg')) {
                            include_once __DIR__ . '/icons/info.svg';
                        } else {
                            echo '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm-1 7V3.5L18.5 9H13z"/></svg>';
                        }
                        ?>
                        </span>
                        <span style="font-weight:bold;">Equipment Info</span>
                    </a>
                    <a href="professor-send-notification.php" class="action-button">
                        <span class="icon">
                        <?php 
                        if (file_exists(__DIR__ . '/icons/send.svg')) {
                            include_once __DIR__ . '/icons/send.svg';
                        } else {
                            echo '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M19 7v4H5.83l3.58-3.59L8 6l-6 6 6 6 1.41-1.41L5.83 13H21V7z"/></svg>';
                        }
                        ?>
                        </span>
                        <span style="font-weight:bold;">Send Schedule</span>
                    </a>
                </div>
            <?php
            } else {
                echo '<div class="error-message">Unable to load professor information. Please try again.</div>';
            }
            ?>
        </div>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
            const menuToggle = document.getElementById('professorMenuToggle');
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
