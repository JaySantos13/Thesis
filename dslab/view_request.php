<?php
session_start();
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['admin_id']) && !isset($_SESSION['user_id']) && !isset($_SESSION['professor_id'])) {
    header("Location: login.php");
    exit();
}

// Check if request ID is provided
if (!isset($_GET['id'])) {
    $_SESSION['error_message'] = "No request ID provided";
    header("Location: borrowing_status.php");
    exit();
}

$request_id = $_GET['id'];
$request = null;
$items = [];

// In a real app, you would fetch the request from the database
// For now, we'll use sample data to match the screenshot
$request = [
    'id' => 1,
    'request_type' => 'Lab',
    'status' => 'Approved',
    'schedule_date' => '2025-03-11',
    'start_time' => '13:30:00',
    'end_time' => '15:00:00',
    'subject' => 'Fundamentals in Electronics Circuits',
    'room' => 'Electronics Lab',
    'created_at' => '2025-03-01 10:00:00',
    'updated_at' => '2025-03-02 14:30:00',
    'items' => [
        ['equipment_name' => 'Oscilloscope', 'quantity' => 1],
        ['equipment_name' => 'Multimeter', 'quantity' => 1],
        ['equipment_name' => 'Soldering Iron', 'quantity' => 1]
    ]
];

// Check if user is admin
$is_admin = isset($_SESSION['admin_id']) && isset($_SESSION['is_admin']);
$is_professor = isset($_SESSION['professor_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Details</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="sidenav.css">
    <link rel="stylesheet" href="borrowing.css">
    <style>
        .request-details-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 800px;
            margin: 20px auto;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }
        
        .request-details-header {
            background-color: #ff7f1a;
            color: white;
            padding: 15px 20px;
            font-size: 1.2em;
            font-weight: 500;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .request-details-content {
            padding: 20px;
        }
        
        .request-section {
            margin-bottom: 25px;
        }
        
        .request-section-title {
            font-weight: 600;
            color: #444;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 1px solid #eee;
        }
        
        .request-info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }
        
        .request-info-item {
            margin-bottom: 10px;
        }
        
        .request-info-label {
            font-weight: 500;
            color: #666;
            margin-bottom: 5px;
        }
        
        .request-info-value {
            color: #333;
        }
        
        .equipment-list {
            border: 1px solid #eee;
            border-radius: 8px;
            overflow: hidden;
        }
        
        .equipment-list-header {
            background-color: #f5f5f5;
            padding: 10px 15px;
            font-weight: 500;
            color: #444;
            display: grid;
            grid-template-columns: 50px 1fr 80px;
        }
        
        .equipment-item {
            padding: 12px 15px;
            border-top: 1px solid #eee;
            display: grid;
            grid-template-columns: 50px 1fr 80px;
            align-items: center;
        }
        
        .equipment-number {
            color: #666;
            font-weight: 500;
        }
        
        .equipment-name {
            color: #333;
        }
        
        .equipment-quantity {
            color: #666;
            text-align: center;
        }
        
        .action-buttons {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }
        
        .back-button {
            display: inline-flex;
            align-items: center;
            color: #444;
            text-decoration: none;
            font-weight: 500;
        }
        
        .back-button svg {
            margin-right: 8px;
        }
        
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.9em;
            font-weight: 500;
        }
        
        @media (max-width: 768px) {
            .request-info-grid {
                grid-template-columns: 1fr;
            }
            
            .equipment-list-header, .equipment-item {
                grid-template-columns: 40px 1fr 60px;
            }
            
            .action-buttons {
                flex-direction: column;
                gap: 10px;
            }
            
            .action-buttons .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <!-- Menu Toggle Button for Mobile -->    
    <button class="menu-toggle" id="menuToggle">
        <span class="icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                <path d="M3 18h18v-2H3v2zm0-5h18v-2H3v2zm0-7v2h18V6H3z"/>
            </svg>
        </span>
    </button>
    
    <!-- Side Navigation -->
    <div class="sidenav" id="sideNav">
        <ul>
            <li><a href="dashboard.php"><span class="icon"><?php include 'icons/home.svg'; ?></span> Dashboard</a></li>
            <li><a href="notif.php"><span class="icon"><?php include 'icons/bell.svg'; ?></span> Notifications</a></li>
            <li><a href="history.php"><span class="icon"><?php include 'icons/clock.svg'; ?></span> History</a></li>
            <li><a href="profile.php"><span class="icon"><?php include 'icons/profile.svg'; ?></span> Profile</a></li>
            <li><a href="more.php"><span class="icon"><?php include 'icons/more.svg'; ?></span> More</a></li>
        </ul>
    </div>
    
    <div class="request-details-card">
        <div class="request-details-header">
            <div>Request Details</div>
            <div class="status-badge status-<?php echo strtolower($request['status']); ?>">
                <?php echo $request['status']; ?>
            </div>
        </div>
        
        <div class="request-details-content">
            <a href="borrowing_status.php" class="back-button">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M19 12H5M12 19l-7-7 7-7"/>
                </svg>
                Back to Requests
            </a>
            
            <div class="request-section">
                <h3 class="request-section-title">Request Information</h3>
                <div class="request-info-grid">
                    <div class="request-info-item">
                        <div class="request-info-label">Request Type</div>
                        <div class="request-info-value"><?php echo $request['request_type']; ?></div>
                    </div>
                    
                    <div class="request-info-item">
                        <div class="request-info-label">Subject</div>
                        <div class="request-info-value"><?php echo $request['subject']; ?></div>
                    </div>
                    
                    <div class="request-info-item">
                        <div class="request-info-label">Date</div>
                        <div class="request-info-value"><?php echo date('F j, Y', strtotime($request['schedule_date'])); ?></div>
                    </div>
                    
                    <div class="request-info-item">
                        <div class="request-info-label">Time</div>
                        <div class="request-info-value">
                            <?php echo date('g:i A', strtotime($request['start_time'])); ?> - 
                            <?php echo date('g:i A', strtotime($request['end_time'])); ?>
                        </div>
                    </div>
                    
                    <div class="request-info-item">
                        <div class="request-info-label">Room</div>
                        <div class="request-info-value"><?php echo $request['room']; ?></div>
                    </div>
                    
                    <div class="request-info-item">
                        <div class="request-info-label">Requested On</div>
                        <div class="request-info-value"><?php echo date('F j, Y g:i A', strtotime($request['created_at'])); ?></div>
                    </div>
                </div>
            </div>
            
            <div class="request-section">
                <h3 class="request-section-title">Equipment</h3>
                <div class="equipment-list">
                    <div class="equipment-list-header">
                        <div>#</div>
                        <div>Name</div>
                        <div>Qty</div>
                    </div>
                    
                    <?php foreach ($request['items'] as $index => $item): ?>
                        <div class="equipment-item">
                            <div class="equipment-number"><?php echo $index + 1; ?></div>
                            <div class="equipment-name"><?php echo $item['equipment_name']; ?></div>
                            <div class="equipment-quantity"><?php echo $item['quantity']; ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="action-buttons">
                <?php if ($request['status'] == 'Pending'): ?>
                    <a href="edit_request.php?id=<?php echo $request['id']; ?>" class="btn btn-secondary">Edit Request</a>
                    <a href="cancel_request.php?id=<?php echo $request['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to cancel this request?')">Cancel Request</a>
                <?php elseif ($request['status'] == 'Approved'): ?>
                    <a href="return_items.php?id=<?php echo $request['id']; ?>" class="btn">Return Items</a>
                    <a href="cancel_request.php?id=<?php echo $request['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to cancel this request?')">Cancel Request</a>
                <?php endif; ?>
            </div>
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
