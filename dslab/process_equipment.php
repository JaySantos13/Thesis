<?php
session_start();
include 'db.php';

// Check if user is logged in as admin
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['is_admin'])) {
    header("Location: login.php");
    exit();
}

// Get admin permissions
$admin_id = $_SESSION['admin_id'];
$perm_stmt = $conn->prepare("SELECT * FROM admin_permissions WHERE admin_id = ?");
$perm_stmt->bind_param("i", $admin_id);
$perm_stmt->execute();
$perm_result = $perm_stmt->get_result();
$permissions = $perm_result->fetch_assoc();

// Check if admin has inventory management permission
if (!isset($permissions['can_manage_inventory']) || !$permissions['can_manage_inventory']) {
    header("Location: admin-dashboard.php");
    exit();
}

// Log activity
function logAdminActivity($conn, $admin_id, $action, $details) {
    $stmt = $conn->prepare("INSERT INTO admin_activity_log (admin_id, action, details) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $admin_id, $action, $details);
    $stmt->execute();
    $stmt->close();
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Add new equipment
    if (isset($_POST['name']) && !isset($_POST['edit_id'])) {
        $name = $_POST['name'];
        $description = isset($_POST['description']) ? $_POST['description'] : '';
        $category = isset($_POST['category']) ? $_POST['category'] : '';
        $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
        $location = isset($_POST['location']) ? $_POST['location'] : '';
        
        // Validate input
        if (empty($name)) {
            $_SESSION['error'] = "Equipment name is required";
            header("Location: inventory.php");
            exit();
        }
        
        if ($quantity < 1) {
            $quantity = 1;
        }
        
        // Set status based on quantity
        $status = 'Available';
        
        // Insert new equipment
        $stmt = $conn->prepare("INSERT INTO equipment (name, description, category, quantity, available_quantity, status, location) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssiiss", $name, $description, $category, $quantity, $quantity, $status, $location);
        
        if ($stmt->execute()) {
            $equipment_id = $conn->insert_id;
            logAdminActivity($conn, $admin_id, "Added Equipment", "Added equipment: $name (ID: $equipment_id)");
            $_SESSION['success'] = "Equipment added successfully";
        } else {
            $_SESSION['error'] = "Error adding equipment: " . $conn->error;
        }
        
        $stmt->close();
        header("Location: inventory.php");
        exit();
    }
    
    // Edit existing equipment
    if (isset($_POST['edit_id'])) {
        $equipment_id = (int)$_POST['edit_id'];
        $name = $_POST['name'];
        $description = isset($_POST['description']) ? $_POST['description'] : '';
        $category = isset($_POST['category']) ? $_POST['category'] : '';
        $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
        $available = isset($_POST['available_quantity']) ? (int)$_POST['available_quantity'] : 1;
        $location = isset($_POST['location']) ? $_POST['location'] : '';
        
        // Validate input
        if (empty($name)) {
            $_SESSION['error'] = "Equipment name is required";
            header("Location: inventory.php");
            exit();
        }
        
        if ($quantity < 1) {
            $quantity = 1;
        }
        
        if ($available > $quantity) {
            $available = $quantity;
        }
        
        if ($available < 0) {
            $available = 0;
        }
        
        // Set status based on available quantity
        $status = 'Available';
        if ($available == 0) {
            $status = 'Not Available';
        } else if ($available < $quantity) {
            $status = 'Partially Available';
        }
        
        // Update equipment
        $stmt = $conn->prepare("UPDATE equipment SET name = ?, description = ?, category = ?, quantity = ?, available_quantity = ?, status = ?, location = ? WHERE id = ?");
        $stmt->bind_param("sssiissi", $name, $description, $category, $quantity, $available, $status, $location, $equipment_id);
        
        if ($stmt->execute()) {
            logAdminActivity($conn, $admin_id, "Updated Equipment", "Updated equipment: $name (ID: $equipment_id)");
            $_SESSION['success'] = "Equipment updated successfully";
        } else {
            $_SESSION['error'] = "Error updating equipment: " . $conn->error;
        }
        
        $stmt->close();
        header("Location: inventory.php");
        exit();
    }
}

// Process GET requests (delete, get details)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Delete equipment
    if (isset($_GET['delete_id'])) {
        $equipment_id = (int)$_GET['delete_id'];
        
        // Check if equipment is currently borrowed
        $check_stmt = $conn->prepare("SELECT COUNT(*) as count FROM equipment_borrowing WHERE equipment_id = ? AND status IN ('Pending', 'Approved', 'Borrowed')");
        $check_stmt->bind_param("i", $equipment_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        $check_row = $check_result->fetch_assoc();
        
        if ($check_row['count'] > 0) {
            $_SESSION['error'] = "Cannot delete equipment that is currently borrowed or has pending requests";
            header("Location: inventory.php");
            exit();
        }
        
        // Get equipment name for activity log
        $name_stmt = $conn->prepare("SELECT name FROM equipment WHERE id = ?");
        $name_stmt->bind_param("i", $equipment_id);
        $name_stmt->execute();
        $name_result = $name_stmt->get_result();
        $name_row = $name_result->fetch_assoc();
        $equipment_name = $name_row['name'];
        
        // Delete equipment
        $stmt = $conn->prepare("DELETE FROM equipment WHERE id = ?");
        $stmt->bind_param("i", $equipment_id);
        
        if ($stmt->execute()) {
            logAdminActivity($conn, $admin_id, "Deleted Equipment", "Deleted equipment: $equipment_name (ID: $equipment_id)");
            $_SESSION['success'] = "Equipment deleted successfully";
        } else {
            $_SESSION['error'] = "Error deleting equipment: " . $conn->error;
        }
        
        $stmt->close();
        header("Location: inventory.php");
        exit();
    }
    
    // Get equipment details (for AJAX requests)
    if (isset($_GET['get_details']) && isset($_GET['id'])) {
        $equipment_id = (int)$_GET['id'];
        
        $stmt = $conn->prepare("SELECT * FROM equipment WHERE id = ?");
        $stmt->bind_param("i", $equipment_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $equipment = $result->fetch_assoc();
            echo json_encode($equipment);
        } else {
            echo json_encode(['error' => 'Equipment not found']);
        }
        
        $stmt->close();
        exit();
    }
}

// If we get here, redirect back to inventory page
header("Location: inventory.php");
exit();
?>
