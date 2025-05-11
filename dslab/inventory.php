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

// Get admin information and permissions if admin
$permissions = [];
if ($is_admin) {
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
} else {
    // Regular users can only view inventory, not manage it
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
}

// Get equipment list
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Get equipment from database
$query = "SELECT * FROM equipment WHERE 1=1";
if (!empty($search)) {
    $search_param = "%$search%";
    $query .= " AND (name LIKE ? OR description LIKE ? OR category LIKE ?)";
}
$query .= " ORDER BY name ASC";

$stmt = $conn->prepare($query);

if (!empty($search)) {
    $search_param = "%$search%";
    $stmt->bind_param("sss", $search_param, $search_param, $search_param);
}

$stmt->execute();
$result = $stmt->get_result();
$equipment_list = [];
while ($row = $result->fetch_assoc()) {
    $equipment_list[] = $row;
}

// Get categories for filter
$cat_query = "SELECT DISTINCT category FROM equipment ORDER BY category";
$cat_result = $conn->query($cat_query);
$categories = [];
while ($row = $cat_result->fetch_assoc()) {
    $categories[] = $row['category'];
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Management - DS Lab</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="sidenav.css">
    <style>
        /* Inventory styles */
        .inventory-container {
            background-color: #ff7f1a;
            width: 100%;
            height: calc(100vh - 60px);
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }
        
        /* Admin view specific styles */
        .admin-view .inventory-container {
            margin-left: 0;
            width: calc(100% - 250px);
            margin-left: 250px;
        }
        
        .inventory-header {
            color: white;
            padding: 15px 20px;
            font-size: 1.2em;
            font-weight: 500;
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
        }
        
        .inventory-content {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        
        .content-card {
            background: #f0f0f0;
            border-radius: 0;
            padding: 0;
            margin-top: 15px;
            width: 100%;
            max-width: 600px;
            overflow-y: auto;
            max-height: calc(100vh - 180px);
        }
        
        .search-container {
            margin-bottom: 15px;
            position: relative;
            display: flex;
            align-items: center;
            background-color: white;
            border-radius: 20px;
            padding: 5px 10px;
            width: 100%;
            max-width: 400px;
        }
        
        .search-container .icon {
            margin-left: 10px;
            color: #777;
        }
        
        .search-input {
            flex: 1;
            padding: 8px 10px;
            border: none;
            border-radius: 20px;
            font-size: 0.9em;
            background-color: transparent;
            outline: none;
        }
        
        .search-input::placeholder {
            color: #999;
        }
        
        .filter-btn {
            background-color: #f0f0f0;
            border: none;
            color: #444;
            padding: 5px 10px;
            border-radius: 15px;
            margin-left: 10px;
            font-size: 0.8em;
            cursor: pointer;
        }
        
        .equipment-list {
            display: flex;
            flex-direction: column;
            gap: 0;
            width: 100%;
        }
        
        .equipment-item {
            display: flex;
            align-items: center;
            padding: 10px 15px;
            border-radius: 0;
            background-color: #f0f0f0;
            border-bottom: 1px solid #ddd;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        
        .equipment-item:hover {
            background-color: #e5e5e5;
        }
        
        .equipment-icon {
            width: 30px;
            height: 30px;
            background-color: #ddd;
            border-radius: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
        }
        
        .equipment-details {
            flex: 1;
        }
        
        .equipment-name {
            font-weight: 500;
            margin-bottom: 3px;
            color: #444;
        }
        
        .equipment-category {
            font-size: 0.8em;
            color: #777;
        }
        
        .equipment-status {
            font-size: 0.8em;
            padding: 3px 8px;
            border-radius: 10px;
            margin-left: 10px;
        }
        
        .status-available {
            background-color: #e6f7e6;
            color: #2e7d32;
        }
        
        .status-partial {
            background-color: #fff8e1;
            color: #f57c00;
        }
        
        .status-unavailable {
            background-color: #ffebee;
            color: #c62828;
        }
        
        .add-equipment-btn {
            background-color: #444;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.9em;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .add-equipment-btn:hover {
            background-color: #333;
        }
        
        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
        }
        
        .modal-content {
            background-color: #fefefe;
            margin: 10% auto;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            width: 80%;
            max-width: 500px;
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .modal-title {
            font-size: 1.2em;
            font-weight: 500;
            color: #444;
        }
        
        .close-modal {
            color: #aaa;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        
        .close-modal:hover {
            color: #444;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: #555;
        }
        
        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 0.9em;
        }
        
        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
        }
        
        .btn-cancel {
            background-color: #f5f5f5;
            color: #555;
            border: 1px solid #ddd;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
        }
        
        .btn-submit {
            background-color: #e55a00;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
        }
        
        .btn-submit:hover {
            background-color: #ff7f1a;
        }
        
        /* Admin dashboard styles */
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
        
        @media (max-width: 768px) {
            .inventory-card {
                border-radius: 0;
                margin: 0;
                height: 100vh;
                max-width: 100%;
            }
            
            .admin-view .inventory-card {
                margin: 0;
                width: 100%;
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
            <?php if (isset($permissions['can_manage_inventory']) && $permissions['can_manage_inventory']): ?>
            <li><a href="inventory.php" class="active">
                <span class="icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M20 8h-3V4H3c-1.1 0-2 .9-2 2v11h2c0 1.66 1.34 3 3 3s3-1.34 3-3h6c0 1.66 1.34 3 3 3s3-1.34 3-3h2v-5l-3-4zM6 18.5c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zm13.5-9l1.96 2.5H17V9.5h2.5zm-1.5 9c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5z"/>
                    </svg>
                </span>
                Inventory Management
            </a></li>
            <?php endif; ?>
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

    <!-- Inventory Management Container -->
    <div class="inventory-container">
        <div class="inventory-header">
            <span>Inventory Management</span>
            <?php if ($is_admin): ?>
            <button class="add-equipment-btn" id="addEquipmentBtn">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/>
                </svg>
                Add Equipment
            </button>
            <?php endif; ?>
        </div>
        <div class="inventory-content">
            <form class="search-container" method="GET" action="inventory.php">
                <span class="icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="#777">
                        <path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/>
                    </svg>
                </span>
                <input type="text" name="search" class="search-input" placeholder="Search..." value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="filter-btn">Filter</button>
            </form>
            
            <div class="content-card">
                <div class="equipment-list">
                <?php if (empty($equipment_list)): ?>
                <div class="empty-state">
                    <p>No equipment found. <?php echo $is_admin ? 'Add some equipment to get started.' : 'Please try a different search.' ?></p>
                </div>
                <?php else: ?>
                <?php foreach ($equipment_list as $equipment): ?>
                <a href="equipment-detail.php?id=<?php echo $equipment['id']; ?>" class="equipment-item" data-id="<?php echo $equipment['id']; ?>" style="text-decoration: none; color: inherit;">
                    <div class="equipment-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="#777">
                            <path d="M22 9V7h-2V5c0-1.1-.9-2-2-2H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2v-2h2v-2h-2v-2h2v-2h-2V9h2zm-4 10H4V5h14v14zM6 13h5v4H6zm6-6h4v3h-4zm-6 0h5v5H6z"/>
                        </svg>
                    </div>
                    <div class="equipment-details">
                        <div class="equipment-name"><?php echo htmlspecialchars($equipment['name']); ?></div>
                        <div class="equipment-category"><?php echo htmlspecialchars($equipment['category']); ?></div>
                    </div>
                    <?php 
                    $status_class = '';
                    if ($equipment['status'] == 'Available') {
                        $status_class = 'status-available';
                    } elseif ($equipment['status'] == 'Partially Available') {
                        $status_class = 'status-partial';
                    } else {
                        $status_class = 'status-unavailable';
                    }
                    ?>
                    <div class="equipment-status <?php echo $status_class; ?>">
                        <?php echo htmlspecialchars($equipment['status']); ?>
                    </div>
                </a>
                <?php endforeach; ?>
                <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Equipment Modal -->
    <?php if ($is_admin): ?>
    <div id="addEquipmentModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Add New Equipment</h3>
                <span class="close-modal">&times;</span>
            </div>
            <form id="addEquipmentForm" action="process_equipment.php" method="POST">
                <div class="form-group">
                    <label for="equipment_name">Equipment Name</label>
                    <input type="text" id="equipment_name" name="name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="equipment_description">Description</label>
                    <textarea id="equipment_description" name="description" class="form-control" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label for="equipment_category">Category</label>
                    <input type="text" id="equipment_category" name="category" class="form-control" list="categories" required>
                    <datalist id="categories">
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo htmlspecialchars($cat); ?>">
                        <?php endforeach; ?>
                    </datalist>
                </div>
                <div class="form-group">
                    <label for="equipment_quantity">Quantity</label>
                    <input type="number" id="equipment_quantity" name="quantity" class="form-control" min="1" value="1" required>
                </div>
                <div class="form-group">
                    <label for="equipment_location">Location</label>
                    <input type="text" id="equipment_location" name="location" class="form-control">
                </div>
                <div class="form-actions">
                    <button type="button" class="btn-cancel" id="cancelAddEquipment">Cancel</button>
                    <button type="submit" class="btn-submit">Add Equipment</button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Modal functionality
            const addEquipmentBtn = document.getElementById('addEquipmentBtn');
            const addEquipmentModal = document.getElementById('addEquipmentModal');
            const cancelAddEquipment = document.getElementById('cancelAddEquipment');
            const closeModalButtons = document.querySelectorAll('.close-modal');
            
            if (addEquipmentBtn) {
                addEquipmentBtn.addEventListener('click', function() {
                    addEquipmentModal.style.display = 'block';
                });
            }
            
            if (cancelAddEquipment) {
                cancelAddEquipment.addEventListener('click', function() {
                    addEquipmentModal.style.display = 'none';
                });
            }
            
            closeModalButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const modal = this.closest('.modal');
                    if (modal) {
                        modal.style.display = 'none';
                    }
                });
            });
            
            // Close modal when clicking outside
            window.addEventListener('click', function(event) {
                if (event.target.classList.contains('modal')) {
                    event.target.style.display = 'none';
                }
            });
            
            // Equipment item click to show details
            const equipmentItems = document.querySelectorAll('.equipment-item');
            equipmentItems.forEach(item => {
                item.addEventListener('click', function() {
                    const equipmentId = this.getAttribute('data-id');
                    // You can implement showing equipment details here
                    // For example, fetch details via AJAX and show in a modal
                    console.log('Equipment clicked:', equipmentId);
                });
            });
        });
    </script>
</body>
</html>
