<?php
session_start();
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['admin_id']) && !isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check if user is admin
$is_admin = isset($_SESSION['admin_id']) && isset($_SESSION['is_admin']);

// Get equipment ID from URL
if (!isset($_GET['id'])) {
    header("Location: inventory.php");
    exit();
}

$equipment_id = (int)$_GET['id'];

// Get equipment details
$stmt = $conn->prepare("SELECT * FROM equipment WHERE id = ?");
$stmt->bind_param("i", $equipment_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: inventory.php");
    exit();
}

$equipment = $result->fetch_assoc();

// Get borrowing history
$history_stmt = $conn->prepare("
    SELECT eb.*, CONCAT(u.first_name, ' ', u.last_name) as user_name 
    FROM equipment_borrowing eb
    JOIN users u ON eb.user_id = u.id
    WHERE eb.equipment_id = ?
    ORDER BY eb.borrow_date DESC
    LIMIT 5
");
$history_stmt->bind_param("i", $equipment_id);
$history_stmt->execute();
$history_result = $history_stmt->get_result();
$borrowing_history = [];
while ($row = $history_result->fetch_assoc()) {
    $borrowing_history[] = $row;
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($equipment['name']); ?> - DS Lab</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="sidenav.css">
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
        
        /* Admin dashboard styles */
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
        
        /* Equipment detail styles */
        .equipment-container {
            background-color: #ff7f1a;
            width: 100%;
            height: calc(100vh - 60px);
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }
        
        /* Admin view specific styles */
        .admin-view .equipment-container {
            margin-left: 0;
            width: calc(100% - 250px);
            margin-left: 250px;
        }
        
        .equipment-header {
            color: white;
            padding: 15px 20px;
            font-size: 1.2em;
            font-weight: 500;
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
        }
        
        .equipment-content {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        
        .equipment-detail-card {
            background: white;
            border-radius: 10px;
            padding: 0;
            width: 100%;
            max-width: 800px;
            overflow: hidden;
            margin-bottom: 20px;
        }
        
        .equipment-image-container {
            padding: 20px;
            display: flex;
            align-items: flex-start;
        }
        
        .equipment-image {
            width: 200px;
            height: 150px;
            background-color: #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 20px;
            border: 1px solid #ddd;
        }
        
        .equipment-info {
            flex: 1;
        }
        
        .equipment-name {
            font-size: 1.8em;
            font-weight: 500;
            margin-bottom: 10px;
            color: #333;
        }
        
        .equipment-category {
            font-size: 0.9em;
            color: #777;
            margin-bottom: 15px;
        }
        
        .equipment-description {
            margin-bottom: 15px;
            line-height: 1.5;
            color: #444;
        }
        
        .equipment-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .meta-item {
            background-color: #f5f5f5;
            padding: 8px 12px;
            border-radius: 5px;
            font-size: 0.9em;
            color: #555;
            display: flex;
            align-items: center;
        }
        
        .meta-item .icon {
            margin-right: 5px;
            color: #777;
        }
        
        .equipment-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        
        .action-btn {
            padding: 8px 15px;
            border-radius: 5px;
            font-size: 0.9em;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 5px;
            text-decoration: none;
        }
        
        .primary-btn {
            background-color: #444;
            color: white;
            border: none;
        }
        
        .secondary-btn {
            background-color: #f0f0f0;
            color: #444;
            border: 1px solid #ddd;
        }
        
        .equipment-items {
            padding: 15px 20px;
            background-color: #f0f0f0;
        }
        
        .item-list {
            display: flex;
            flex-direction: column;
            gap: 0;
            width: 100%;
        }
        
        .item {
            display: flex;
            align-items: center;
            padding: 10px 15px;
            border-radius: 0;
            background-color: #f0f0f0;
            border-bottom: 1px solid #ddd;
        }
        
        .item-id {
            width: 100px;
            color: #555;
            font-size: 0.9em;
        }
        
        .item-status {
            margin-left: auto;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 0.8em;
        }
        
        .status-available {
            background-color: #e6f7e6;
            color: #2e7d32;
        }
        
        .status-borrowed {
            background-color: #ffebee;
            color: #c62828;
        }
        
        .history-section {
            background: white;
            border-radius: 10px;
            padding: 20px;
            width: 100%;
            max-width: 800px;
        }
        
        .history-title {
            font-size: 1.2em;
            font-weight: 500;
            margin-bottom: 15px;
            color: #333;
        }
        
        .history-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        
        .history-item {
            display: flex;
            padding: 10px;
            border-radius: 5px;
            background-color: #f5f5f5;
            border: 1px solid #eee;
        }
        
        .history-user {
            width: 150px;
            font-weight: 500;
        }
        
        .history-date {
            width: 150px;
            color: #777;
            font-size: 0.9em;
        }
        
        .history-status {
            margin-left: auto;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 0.8em;
        }
        
        .back-link {
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        @media (max-width: 768px) {
            .equipment-container {
                height: auto;
                min-height: 100vh;
            }
            
            .admin-view .equipment-container {
                margin-left: 0;
                width: 100%;
            }
            
            .equipment-image-container {
                flex-direction: column;
            }
            
            .equipment-image {
                width: 100%;
                margin-right: 0;
                margin-bottom: 15px;
            }
            
            .equipment-meta {
                flex-direction: column;
                gap: 5px;
            }
        }
    </style>
</head>
<body class="<?php echo $is_admin ? 'admin-view' : ''; ?>">
    <?php if ($is_admin): ?>
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
            <?php if (isset($permissions['can_manage_users']) && $permissions['can_manage_users']): ?>
            <li><a href="admin-user-management.php">
                <span class="icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/>
                    </svg>
                </span>
                Manage Users
            </a></li>
            <?php endif; ?>
            <li><a href="inventory.php" class="active">
                <span class="icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M20 8h-3V4H3c-1.1 0-2 .9-2 2v11h2c0 1.66 1.34 3 3 3s3-1.34 3-3h6c0 1.66 1.34 3 3 3s3-1.34 3-3h2v-5l-3-4zM6 18.5c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zm13.5-9l1.96 2.5H17V9.5h2.5zm-1.5 9c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5z"/>
                    </svg>
                </span>
                Inventory Management
            </a></li>
            <li><a href="borrowing.php">
                <span class="icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M18 17H6v-2h12v2zm0-4H6v-2h12v2zm0-4H6V7h12v2zM3 22l1.5-1.5L6 22l1.5-1.5L9 22l1.5-1.5L12 22l1.5-1.5L15 22l1.5-1.5L18 22l1.5-1.5L21 22V2l-1.5 1.5L18 2l-1.5 1.5L15 2l-1.5 1.5L12 2l-1.5 1.5L9 2 7.5 3.5 6 2 4.5 3.5 3 2v20z"/>
                    </svg>
                </span>
                Borrowing & Return Requests
            </a></li>
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
    <?php else: ?>
    <!-- Regular User Navigation -->
    <?php include 'nav_template.php'; ?>
    <?php endif; ?>

    <!-- Equipment Detail Container -->
    <div class="equipment-container">
        <div class="equipment-header">
            <a href="inventory.php" class="back-link">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"/>
                </svg>
                Back to Inventory
            </a>
            <?php if ($is_admin): ?>
            <div class="equipment-actions">
                <a href="edit-equipment.php?id=<?php echo $equipment_id; ?>" class="action-btn secondary-btn">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/>
                    </svg>
                    Edit
                </a>
                <button class="action-btn primary-btn" id="addItemBtn">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/>
                    </svg>
                    Add
                </button>
            </div>
            <?php endif; ?>
        </div>
        <div class="equipment-content">
            <div class="equipment-detail-card">
                <div class="equipment-image-container">
                    <div class="equipment-image">
                        <?php if (!empty($equipment['image_path'])): ?>
                        <img src="<?php echo htmlspecialchars($equipment['image_path']); ?>" alt="<?php echo htmlspecialchars($equipment['name']); ?>" style="max-width: 100%; max-height: 100%;">
                        <?php else: ?>
                        <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="#aaa">
                            <path d="M22 9V7h-2V5c0-1.1-.9-2-2-2H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2v-2h2v-2h-2v-2h2v-2h-2V9h2zm-4 10H4V5h14v14zM6 13h5v4H6zm6-6h4v3h-4zm-6 0h5v5H6z"/>
                        </svg>
                        <?php endif; ?>
                    </div>
                    <div class="equipment-info">
                        <h1 class="equipment-name"><?php echo htmlspecialchars($equipment['name']); ?></h1>
                        <div class="equipment-category"><?php echo htmlspecialchars($equipment['category']); ?></div>
                        <div class="equipment-description">
                            <?php echo nl2br(htmlspecialchars($equipment['description'] ?? 'Oscilloscopes (or digital storage oscilloscopes) are devices that display and analyze the waveform of electronic signals. The signal is plotted on a graph which shows how the signal changes over time. The vertical (Y) axis represents voltage and the horizontal (X) axis represents time.')); ?>
                        </div>
                        <div class="equipment-meta">
                            <div class="meta-item">
                                <span class="icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14h-2V9h-2V7h4v10z"/>
                                    </svg>
                                </span>
                                Total: <?php echo (int)($equipment['quantity'] ?? 0); ?> units
                            </div>
                            <div class="meta-item">
                                <span class="icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
                                    </svg>
                                </span>
                                Available: <?php echo (int)($equipment['available_quantity'] ?? 0); ?> units
                            </div>
                            <?php if (!empty($equipment['location'])): ?>
                            <div class="meta-item">
                                <span class="icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                                    </svg>
                                </span>
                                Location: <?php echo htmlspecialchars($equipment['location']); ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php if (!$is_admin): ?>
                        <div class="equipment-actions">
                            <a href="borrow-equipment.php?id=<?php echo $equipment_id; ?>" class="action-btn primary-btn">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm-2 16c-2.05 0-3.81-1.24-4.58-3h1.71c.63.9 1.68 1.5 2.87 1.5 1.93 0 3.5-1.57 3.5-3.5S13.93 9.5 12 9.5c-1.35 0-2.52.78-3.1 1.9l1.6 1.6h-4V9l1.3 1.3C8.69 8.92 10.23 8 12 8c2.76 0 5 2.24 5 5s-2.24 5-5 5z"/>
                                </svg>
                                Borrow
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="equipment-items">
                    <div class="item-list">
                        <?php 
                        // Generate some sample item IDs
                        $prefix = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $equipment['name'] ?? '3D Printer'), 0, 3));
                        $quantity = (int)($equipment['quantity'] ?? 0);
                        $available_quantity = (int)($equipment['available_quantity'] ?? 0);
                        for ($i = 1; $i <= min($quantity, 3); $i++): 
                            $itemId = $prefix . '-' . str_pad($i, 3, '0', STR_PAD_LEFT);
                            $isAvailable = $i <= $available_quantity;
                        ?>
                        <div class="item">
                            <div class="item-id"><?php echo $itemId; ?></div>
                            <div class="item-status <?php echo $isAvailable ? 'status-available' : 'status-borrowed'; ?>">
                                <?php echo $isAvailable ? 'Available' : 'Borrowed'; ?>
                            </div>
                        </div>
                        <?php endfor; ?>
                    </div>
                </div>
            </div>
            
            <?php if (!empty($borrowing_history)): ?>
            <div class="history-section">
                <h2 class="history-title">Borrowing History</h2>
                <div class="history-list">
                    <?php foreach ($borrowing_history as $history): ?>
                    <div class="history-item">
                        <div class="history-user"><?php echo htmlspecialchars($history['user_name']); ?></div>
                        <div class="history-date">
                            <?php echo date('M d, Y', strtotime($history['borrow_date'])); ?>
                        </div>
                        <div class="history-status <?php echo $history['status'] === 'Returned' ? 'status-available' : 'status-borrowed'; ?>">
                            <?php echo htmlspecialchars($history['status']); ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Add Item Button
            const addItemBtn = document.getElementById('addItemBtn');
            if (addItemBtn) {
                addItemBtn.addEventListener('click', function() {
                    // Implement add item functionality
                    alert('Add item functionality will be implemented here');
                });
            }
        });
    </script>
</body>
</html>
