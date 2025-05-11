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

// Check if notifications table exists, create if not
$check_table = $conn->query("SHOW TABLES LIKE 'system_notifications'");
if ($check_table->num_rows == 0) {
    $create_table = "CREATE TABLE system_notifications (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        message TEXT NOT NULL,
        type VARCHAR(50) NOT NULL DEFAULT 'info',
        target VARCHAR(50) NOT NULL DEFAULT 'all',
        is_read TINYINT(1) NOT NULL DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        expires_at TIMESTAMP NULL
    )";
    $conn->query($create_table);
}

// Handle creating new notification
$success_message = "";
$error_message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_notification'])) {
    $title = trim($_POST['title']);
    $message = trim($_POST['message']);
    $type = $_POST['type'];
    $target = $_POST['target'];
    $expires_days = (int)$_POST['expires_days'];
    
    // Basic validation
    if (empty($title) || empty($message)) {
        $error_message = "Title and message are required fields.";
    } else {
        // Set expiration date if provided
        $expires_at = "NULL";
        if ($expires_days > 0) {
            $expires_at = "DATE_ADD(NOW(), INTERVAL $expires_days DAY)";
        }
        
        // Insert notification
        $insert_sql = "INSERT INTO system_notifications (title, message, type, target, expires_at) 
                      VALUES (?, ?, ?, ?, $expires_at)";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param("ssss", $title, $message, $type, $target);
        
        if ($insert_stmt->execute()) {
            $success_message = "Notification created successfully!";
        } else {
            $error_message = "Error creating notification: " . $conn->error;
        }
    }
}

// Handle marking notification as read
if (isset($_GET['mark_read']) && is_numeric($_GET['mark_read'])) {
    $notification_id = (int)$_GET['mark_read'];
    $mark_read_stmt = $conn->prepare("UPDATE system_notifications SET is_read = 1 WHERE id = ?");
    $mark_read_stmt->bind_param("i", $notification_id);
    $mark_read_stmt->execute();
    
    // Redirect to remove the GET parameter
    header("Location: admin-notifications.php");
    exit();
}

// Handle deleting notification
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $notification_id = (int)$_GET['delete'];
    $delete_stmt = $conn->prepare("DELETE FROM system_notifications WHERE id = ?");
    $delete_stmt->bind_param("i", $notification_id);
    
    if ($delete_stmt->execute()) {
        $success_message = "Notification deleted successfully!";
    } else {
        $error_message = "Error deleting notification: " . $conn->error;
    }
    
    // Redirect to remove the GET parameter
    header("Location: admin-notifications.php");
    exit();
}

// Get all notifications, ordered by newest first
$notifications_sql = "SELECT * FROM system_notifications 
                     WHERE (expires_at IS NULL OR expires_at > NOW()) 
                     ORDER BY created_at DESC";
$notifications_result = $conn->query($notifications_sql);
$notifications = [];

if ($notifications_result->num_rows > 0) {
    while ($row = $notifications_result->fetch_assoc()) {
        $notifications[] = $row;
    }
}

// Count unread notifications
$unread_count = 0;
foreach ($notifications as $notification) {
    if ($notification['is_read'] == 0) {
        $unread_count++;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Notifications - DS Lab Admin</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="admin-sidenav.css">
    <style>
        /* System Notifications Specific Styles */
        .notifications-container {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            padding: 30px;
            margin-bottom: 30px;
        }
        
        .notifications-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .notifications-header h2 {
            color: #444;
            margin: 0;
        }
        
        .notification-count {
            background-color: #e55a00;
            color: white;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
        
        .notification-list {
            margin-bottom: 40px;
        }
        
        .notification-item {
            border-left: 4px solid #ddd;
            padding: 15px;
            margin-bottom: 15px;
            background-color: #f9f9f9;
            border-radius: 0 4px 4px 0;
            position: relative;
        }
        
        .notification-item.unread {
            background-color: #fff8f0;
        }
        
        .notification-item.info {
            border-left-color: #17a2b8;
        }
        
        .notification-item.success {
            border-left-color: #28a745;
        }
        
        .notification-item.warning {
            border-left-color: #ffc107;
        }
        
        .notification-item.danger {
            border-left-color: #dc3545;
        }
        
        .notification-title {
            font-weight: bold;
            color: #444;
            margin-top: 0;
            margin-bottom: 5px;
            padding-right: 80px;
        }
        
        .notification-message {
            color: #666;
            margin-bottom: 10px;
        }
        
        .notification-meta {
            display: flex;
            justify-content: space-between;
            font-size: 0.85rem;
            color: #888;
        }
        
        .notification-actions {
            position: absolute;
            top: 15px;
            right: 15px;
            display: flex;
            gap: 10px;
        }
        
        .notification-actions a {
            color: #666;
            text-decoration: none;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
        }
        
        .notification-actions a:hover {
            color: #e55a00;
        }
        
        .notification-actions .icon {
            margin-right: 3px;
        }
        
        .notification-form {
            background-color: #f9f9f9;
            border-radius: 8px;
            padding: 20px;
        }
        
        .notification-form h3 {
            color: #e55a00;
            margin-top: 0;
            margin-bottom: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #444;
            font-weight: 500;
        }
        
        .form-group input, 
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        
        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }
        
        .form-row {
            display: flex;
            gap: 20px;
        }
        
        .form-row .form-group {
            flex: 1;
        }
        
        .btn-primary {
            background-color: #e55a00;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
        }
        
        .btn-primary:hover {
            background-color: #ff7f1a;
        }
        
        .alert {
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #666;
        }
        
        .empty-state svg {
            width: 60px;
            height: 60px;
            color: #ddd;
            margin-bottom: 15px;
        }
        
        .empty-state p {
            margin: 0;
        }
        
        @media (max-width: 768px) {
            .form-row {
                flex-direction: column;
                gap: 0;
            }
            
            .notification-actions {
                position: static;
                margin-top: 10px;
                justify-content: flex-end;
            }
            
            .notification-title {
                padding-right: 0;
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
                <h1>System Notifications</h1>
                <div class="admin-user-info">
                    <span>Welcome, <?php echo htmlspecialchars($admin['full_name']); ?></span>
                </div>
            </div>
            
            <?php if (!empty($success_message)): ?>
            <div class="alert alert-success">
                <?php echo $success_message; ?>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger">
                <?php echo $error_message; ?>
            </div>
            <?php endif; ?>
            
            <div class="notifications-container">
                <div class="notifications-header">
                    <h2>All Notifications</h2>
                    <?php if ($unread_count > 0): ?>
                    <div class="notification-count"><?php echo $unread_count; ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="notification-list">
                    <?php if (count($notifications) > 0): ?>
                        <?php foreach ($notifications as $notification): ?>
                            <div class="notification-item <?php echo $notification['type']; ?> <?php echo $notification['is_read'] ? '' : 'unread'; ?>">
                                <h3 class="notification-title"><?php echo htmlspecialchars($notification['title']); ?></h3>
                                <div class="notification-message"><?php echo nl2br(htmlspecialchars($notification['message'])); ?></div>
                                <div class="notification-meta">
                                    <div>
                                        <span>Target: <?php echo ucfirst(htmlspecialchars($notification['target'])); ?></span>
                                        <?php if ($notification['expires_at']): ?>
                                        <span> • Expires: <?php echo date('M d, Y', strtotime($notification['expires_at'])); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div>Created: <?php echo date('M d, Y, g:i a', strtotime($notification['created_at'])); ?></div>
                                </div>
                                <div class="notification-actions">
                                    <?php if ($notification['is_read'] == 0): ?>
                                    <a href="admin-notifications.php?mark_read=<?php echo $notification['id']; ?>" title="Mark as Read">
                                        <span class="icon">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                                <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
                                            </svg>
                                        </span>
                                        Mark Read
                                    </a>
                                    <?php endif; ?>
                                    <a href="admin-notifications.php?delete=<?php echo $notification['id']; ?>" title="Delete" onclick="return confirm('Are you sure you want to delete this notification?');">
                                        <span class="icon">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                                <path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/>
                                            </svg>
                                        </span>
                                        Delete
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 22c1.1 0 2-.9 2-2h-4c0 1.1.9 2 2 2zm6-6v-5c0-3.07-1.63-5.64-4.5-6.32V4c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5v.68C7.64 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2zm-2 1H8v-6c0-2.48 1.51-4.5 4-4.5s4 2.02 4 4.5v6z"/>
                            </svg>
                            <p>No notifications available at this time.</p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="notification-form">
                    <h3>Create New Notification</h3>
                    <form action="" method="post">
                        <div class="form-group">
                            <label for="title">Notification Title</label>
                            <input type="text" id="title" name="title" required>
                        </div>
                        <div class="form-group">
                            <label for="message">Message</label>
                            <textarea id="message" name="message" required></textarea>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="type">Type</label>
                                <select id="type" name="type">
                                    <option value="info">Information</option>
                                    <option value="success">Success</option>
                                    <option value="warning">Warning</option>
                                    <option value="danger">Alert</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="target">Target Audience</label>
                                <select id="target" name="target">
                                    <option value="all">Everyone</option>
                                    <option value="students">Students Only</option>
                                    <option value="professors">Professors Only</option>
                                    <option value="admins">Admins Only</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="expires_days">Expires After (Days)</label>
                                <input type="number" id="expires_days" name="expires_days" min="0" value="30">
                                <small style="display: block; margin-top: 5px; color: #666;">Set to 0 for no expiration</small>
                            </div>
                        </div>
                        <button type="submit" name="create_notification" class="btn-primary">Create Notification</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
