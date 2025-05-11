<?php
session_start();

// Check if user is logged in as admin
if (isset($_SESSION['admin_id']) && isset($_SESSION['is_admin'])) {
    // Log the logout activity
    include 'db.php';
    
    $admin_id = $_SESSION['admin_id'];
    $ip = $_SERVER['REMOTE_ADDR'];
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    
    $log_stmt = $conn->prepare("INSERT INTO admin_activity_log (admin_id, action_type, action_details, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)");
    $action_type = "logout";
    $action_details = "Admin logout";
    $log_stmt->bind_param("issss", $admin_id, $action_type, $action_details, $ip, $user_agent);
    $log_stmt->execute();
    $log_stmt->close();
    
    $conn->close();
}

// Clear all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Redirect to admin login page
header("Location: adminlogin.php");
exit();
?>
