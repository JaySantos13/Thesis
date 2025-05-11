<?php
session_start();
include 'db.php';

// Check if request ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error_message'] = "Invalid request ID.";
    header("Location: borrowing_status.php");
    exit();
}

$request_id = $_GET['id'];

// Get request details
$request = null;
$sql = "SELECT * FROM borrowing_requests WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $request_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $request = $result->fetch_assoc();
} else {
    $_SESSION['error_message'] = "Request not found.";
    header("Location: borrowing_status.php");
    exit();
}

// Get the items for this request
$items = [];
$items_sql = "SELECT bi.*, e.name as equipment_name 
             FROM borrowing_items bi 
             JOIN equipment e ON bi.equipment_id = e.id 
             WHERE bi.request_id = ?";
$stmt = $conn->prepare($items_sql);
$stmt->bind_param("i", $request_id);
$stmt->execute();
$items_result = $stmt->get_result();

while ($item = $items_result->fetch_assoc()) {
    $items[] = $item;
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Update request status to Returned
    $update_sql = "UPDATE borrowing_requests SET status = 'Returned' WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("i", $request_id);
    
    if ($update_stmt->execute()) {
        // Update all items to Returned status
        $items_update_sql = "UPDATE borrowing_items SET status = 'Returned' WHERE request_id = ?";
        $items_update_stmt = $conn->prepare($items_update_sql);
        $items_update_stmt->bind_param("i", $request_id);
        $items_update_stmt->execute();
        
        // Check if any items were reported as damaged
        if (isset($_POST['damaged_items']) && is_array($_POST['damaged_items'])) {
            foreach ($_POST['damaged_items'] as $item_id) {
                $damage_sql = "UPDATE borrowing_items SET status = 'Damaged' WHERE id = ?";
                $damage_stmt = $conn->prepare($damage_sql);
                $damage_stmt->bind_param("i", $item_id);
                $damage_stmt->execute();
            }
        }
        
        $_SESSION['success_message'] = "Equipment returned successfully.";
        header("Location: borrowing_status.php");
        exit();
    } else {
        $_SESSION['error_message'] = "Error updating request: " . $update_stmt->error;
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Return Equipment</title>
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="sidenav.css">
  <link rel="stylesheet" href="borrowing.css">
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
      <li><a href="notif.php"><span class="icon"><?php include 'icons/bell.svg'; ?></span> Notifications</a></li>
      <li><a href="history.php"><span class="icon"><?php include 'icons/clock.svg'; ?></span> History</a></li>
      <li><a href="dashboard.php"><span class="icon"><?php include 'icons/home.svg'; ?></span> Home</a></li>
      <li><a href="profile.php"><span class="icon"><?php include 'icons/profile.svg'; ?></span> Profile</a></li>
      <li><a href="more.php"><span class="icon"><?php include 'icons/more.svg'; ?></span> More</a></li>
    </ul>
  </div>
  
  <div class="borrowing-card">
    <div class="borrowing-header">
      Return Equipment
    </div>
    
    <div class="borrowing-content">
      <div class="request-info" style="margin-bottom: 20px;">
        <div class="request-info-row">
          <div class="request-info-label">Request Type:</div>
          <div class="request-info-value"><?php echo $request['request_type']; ?></div>
        </div>
        
        <div class="request-info-row">
          <div class="request-info-label">Date:</div>
          <div class="request-info-value"><?php echo date('M j, Y', strtotime($request['schedule_date'])); ?></div>
        </div>
      </div>
      
      <form method="post" action="">
        <div class="form-group">
          <label class="form-label">Equipment to Return</label>
          
          <?php if (empty($items)): ?>
            <p>No equipment items in this request.</p>
          <?php else: ?>
            <div class="equipment-list" style="max-height: 300px;">
              <?php foreach ($items as $item): ?>
                <div class="equipment-item">
                  <input type="checkbox" id="item_<?php echo $item['id']; ?>" name="returned_items[]" value="<?php echo $item['id']; ?>" checked disabled>
                  <label for="item_<?php echo $item['id']; ?>" class="equipment-name"><?php echo $item['equipment_name']; ?> (Qty: <?php echo $item['quantity']; ?>)</label>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
        
        <div class="form-group">
          <label class="form-label">Report Damaged Items (if any)</label>
          
          <?php if (empty($items)): ?>
            <p>No equipment items to report.</p>
          <?php else: ?>
            <div class="equipment-list" style="max-height: 300px;">
              <?php foreach ($items as $item): ?>
                <div class="equipment-item">
                  <input type="checkbox" id="damaged_<?php echo $item['id']; ?>" name="damaged_items[]" value="<?php echo $item['id']; ?>">
                  <label for="damaged_<?php echo $item['id']; ?>" class="equipment-name"><?php echo $item['equipment_name']; ?> (Qty: <?php echo $item['quantity']; ?>)</label>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
        
        <div class="form-group">
          <label class="form-label">Comments (Optional)</label>
          <textarea name="return_comments" class="form-control" rows="3" placeholder="Any comments about the returned equipment"></textarea>
        </div>
        
        <div style="display: flex; gap: 10px; justify-content: space-between;">
          <a href="borrowing_status.php" class="btn btn-secondary">Cancel</a>
          <button type="submit" class="btn">Confirm Return</button>
        </div>
      </form>
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
