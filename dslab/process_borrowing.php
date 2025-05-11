<?php
session_start();
include 'db.php';

// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $request_type = $_POST['request_type'];
    $schedule_date = $_POST['schedule_date'];
    $purpose = isset($_POST['purpose']) ? $_POST['purpose'] : '';
    
    // Set default user info (in a real app, this would come from the session)
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1;
    $user_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Test User';
    $user_email = isset($_SESSION['user_email']) ? $_SESSION['user_email'] : 'user@example.com';
    
    // Process based on request type
    if ($request_type == 'Lab Day') {
        $start_time = $_POST['start_time'];
        $end_time = $_POST['end_time'];
        
        // Insert into borrowing_requests table
        $stmt = $conn->prepare("INSERT INTO borrowing_requests (user_id, user_name, user_email, request_type, schedule_date, start_time, end_time, purpose) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssssss", $user_id, $user_name, $user_email, $request_type, $schedule_date, $start_time, $end_time, $purpose);
        
    } else if ($request_type == 'Direct Request') {
        $return_date = $_POST['return_date'];
        // For direct request, we'll use the return date to calculate an end time (e.g., 5:00 PM)
        $start_time = '09:00:00';
        $end_time = '17:00:00';
        
        // Insert into borrowing_requests table
        $stmt = $conn->prepare("INSERT INTO borrowing_requests (user_id, user_name, user_email, request_type, schedule_date, start_time, end_time, purpose) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssssss", $user_id, $user_name, $user_email, $request_type, $schedule_date, $start_time, $end_time, $purpose);
    }
    
    if ($stmt->execute()) {
        $request_id = $conn->insert_id;
        
        // Process equipment items
        if (isset($_POST['equipment']) && is_array($_POST['equipment'])) {
            $equipment_stmt = $conn->prepare("INSERT INTO borrowing_items (request_id, equipment_id, quantity) VALUES (?, ?, ?)");
            $equipment_stmt->bind_param("iii", $request_id, $equipment_id, $quantity);
            
            foreach ($_POST['equipment'] as $equipment_id => $equipment_data) {
                if (isset($equipment_data['selected']) && $equipment_data['selected'] == 'on') {
                    $quantity = intval($equipment_data['quantity']);
                    if ($quantity > 0) {
                        $equipment_stmt->execute();
                    }
                }
            }
            
            $equipment_stmt->close();
        }
        
        // Set success message
        $_SESSION['success_message'] = "Your borrowing request has been submitted successfully!";
        header("Location: borrowing_status.php");
        exit();
    } else {
        $_SESSION['error_message'] = "Error submitting request: " . $stmt->error;
        header("Location: borrowing.php");
        exit();
    }
    
    $stmt->close();
} else {
    // If not a POST request, redirect to the borrowing page
    header("Location: borrowing.php");
    exit();
}

$conn->close();
?>
