<?php
session_start();
include 'db.php';

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $request_id = $_GET['id'];
    
    // Update the request status to Cancelled
    $stmt = $conn->prepare("UPDATE borrowing_requests SET status = 'Cancelled' WHERE id = ?");
    $stmt->bind_param("i", $request_id);
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Your borrowing request has been cancelled successfully.";
    } else {
        $_SESSION['error_message'] = "Error cancelling request: " . $stmt->error;
    }
    
    $stmt->close();
} else {
    $_SESSION['error_message'] = "Invalid request ID.";
}

$conn->close();
header("Location: borrowing_status.php");
exit();
?>
