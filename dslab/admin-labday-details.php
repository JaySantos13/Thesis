<?php
session_start();
include 'db.php';

// Allow both admin and regular users to access this page
// We'll just check if a session exists
if (!isset($_SESSION['admin_id']) && !isset($_SESSION['user_id'])) {
    // If neither admin nor user session exists, redirect to login
    header("Location: login.php");
    exit();
}

// Get request ID from URL
$request_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// If no ID provided, redirect to borrowing page
if ($request_id === 0) {
    header("Location: borrowing.php");
    exit();
}

// Fetch lab day request details
$request = null;
$sql = "SELECT * FROM borrowing_requests WHERE id = ? AND request_type = 'Lab Day'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $request_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $request = $result->fetch_assoc();
} else {
    // Request not found or not a lab day request
    header("Location: borrowing.php");
    exit();
}

// Fetch group members (in a real system, this would be from a group_members table)
// For this example, we'll use dummy data
$members = [
    'Karl Paolo Cabarlitasan',
    'Jay Michael Santos',
    'Ray Christian Reynaldo',
    'Joaquin Alejandro Ortiz'
];

// Fetch equipment items for this request
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

// For demo purposes, if no items are found, add some sample items
if (empty($items)) {
    $items = [
        ['equipment_name' => 'Oscilloscope', 'quantity' => 1, 'due_return' => '03/18/25'],
        ['equipment_name' => 'Multimeter', 'quantity' => 2, 'due_return' => '03/18/25'],
        ['equipment_name' => 'Soldering Iron', 'quantity' => 1, 'due_return' => '03/18/25']
    ];
}

// Process form submission for approval/rejection
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['approve'])) {
        // Update request status to Approved
        $update_sql = "UPDATE borrowing_requests SET status = 'Approved' WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("i", $request_id);
        
        if ($update_stmt->execute()) {
            $_SESSION['success_message'] = "Lab day request has been approved.";
            header("Location: borrowing.php");
            exit();
        } else {
            $_SESSION['error_message'] = "Error approving request: " . $update_stmt->error;
        }
    } elseif (isset($_POST['reject'])) {
        // Update request status to Rejected
        $update_sql = "UPDATE borrowing_requests SET status = 'Rejected' WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("i", $request_id);
        
        if ($update_stmt->execute()) {
            $_SESSION['success_message'] = "Lab day request has been rejected.";
            header("Location: borrowing.php");
            exit();
        } else {
            $_SESSION['error_message'] = "Error rejecting request: " . $update_stmt->error;
        }
    }
}

// For demo purposes, set some values if they're not in the database
$subject = isset($request['purpose']) ? $request['purpose'] : 'Fundamentals to Electronics Circuits';
$borrower = isset($request['user_name']) ? $request['user_name'] : 'Dave Daryl Basatingo';
$group_no = '5';

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Lab Day Details</title>
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="sidenav.css">
  <link rel="stylesheet" href="borrowing.css">
  <style>
    body {
      height: 100vh;
      margin: 0;
      overflow: hidden;
      background: #f7f7f7;
      font-family: 'Segoe UI', Arial, sans-serif;
      display: flex;
      flex-direction: column;
      align-items: center;
    }
    
    .labday-card {
      background: white;
      border-radius: 15px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.1);
      width: 100%;
      max-width: 800px;
      margin: 20px auto;
      overflow: hidden;
      display: flex;
      flex-direction: column;
      height: calc(100vh - 40px);
    }
    
    .labday-header {
      background-color: #ff7f1a;
      color: white;
      padding: 15px 20px;
      font-size: 1.2em;
      font-weight: 500;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    
    .back-button {
      background: none;
      border: none;
      color: white;
      font-size: 1.5em;
      cursor: pointer;
      padding: 0;
      margin-right: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    
    .labday-content {
      flex: 1;
      padding: 0;
      overflow-y: auto;
      background-color: #ff9f4a;
    }
    
    .info-table {
      width: 100%;
      border-collapse: collapse;
    }
    
    .info-table td {
      padding: 8px 15px;
      border: 1px solid #e55a00;
    }
    
    .info-table td:first-child {
      width: 120px;
      font-weight: 500;
      background-color: rgba(255, 255, 255, 0.1);
    }
    
    .equipment-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
    }
    
    .equipment-table th {
      background-color: #ff7f1a;
      color: white;
      text-align: left;
      padding: 10px 15px;
      font-weight: 500;
    }
    
    .equipment-table td {
      padding: 10px 15px;
      border-bottom: 1px solid #e55a00;
      background-color: white;
    }
    
    .equipment-table tr:last-child td {
      border-bottom: none;
    }
    
    .qty-column {
      width: 60px;
      text-align: center;
    }
    
    .due-column {
      width: 120px;
      text-align: center;
    }
    
    .edit-column {
      width: 50px;
      text-align: center;
    }
    
    .edit-icon {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 24px;
      height: 24px;
      border-radius: 50%;
      background-color: #f0f0f0;
      cursor: pointer;
    }
    
    .edit-icon:hover {
      background-color: #e0e0e0;
    }
    
    .action-buttons {
      display: flex;
      justify-content: flex-end;
      gap: 10px;
      padding: 15px;
      background-color: white;
    }
    
    .btn-reject {
      background-color: #dc3545;
    }
    
    .btn-reject:hover {
      background-color: #c82333;
    }
    
    .btn-approve {
      background-color: #28a745;
    }
    
    .btn-approve:hover {
      background-color: #218838;
    }
    
    @media (max-width: 768px) {
      .labday-card {
        border-radius: 0;
        margin: 0;
        height: 100vh;
        max-width: 100%;
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
      <li><a href="notif.php"><span class="icon"><?php include 'icons/bell.svg'; ?></span> Notifications</a></li>
      <li><a href="history.php"><span class="icon"><?php include 'icons/clock.svg'; ?></span> History</a></li>
      <li><a href="dashboard.php"><span class="icon"><?php include 'icons/home.svg'; ?></span> Home</a></li>
      <li><a href="profile.php"><span class="icon"><?php include 'icons/profile.svg'; ?></span> Profile</a></li>
      <li><a href="more.php"><span class="icon"><?php include 'icons/more.svg'; ?></span> More</a></li>
    </ul>
  </div>
  
  <div class="labday-card">
    <div class="labday-header">
      <button class="back-button" onclick="window.location.href='borrowing.php'">&larr;</button>
      <span>Lab Day Details</span>
    </div>
    
    <div class="labday-content">
      <table class="info-table">
        <tr>
          <td>Title</td>
          <td>Lab day</td>
        </tr>
        <tr>
          <td>Subject</td>
          <td><?php echo htmlspecialchars($subject); ?></td>
        </tr>
        <tr>
          <td>Date</td>
          <td><?php echo date('F j, Y', strtotime($request['schedule_date'])); ?></td>
        </tr>
        <tr>
          <td>Time</td>
          <td>
            <?php 
              echo date('g:iA', strtotime($request['start_time'])) . '-' . 
                   date('g:iA', strtotime($request['end_time'])); 
            ?>
          </td>
        </tr>
        <tr>
          <td>Borrower</td>
          <td><?php echo htmlspecialchars($borrower); ?></td>
        </tr>
        <tr>
          <td>Group No.</td>
          <td><?php echo htmlspecialchars($group_no); ?></td>
        </tr>
        <tr>
          <td>Members</td>
          <td>
            <?php foreach ($members as $member): ?>
              <?php echo htmlspecialchars($member); ?><br>
            <?php endforeach; ?>
          </td>
        </tr>
      </table>
      
      <table class="equipment-table">
        <thead>
          <tr>
            <th class="qty-column">Qty.</th>
            <th>Equipment Name</th>
            <th class="due-column">Due Return</th>
            <th class="edit-column"></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($items as $item): ?>
            <tr>
              <td class="qty-column"><?php echo $item['quantity']; ?></td>
              <td><?php echo htmlspecialchars($item['equipment_name']); ?></td>
              <td class="due-column"><?php echo isset($item['due_return']) ? $item['due_return'] : '03/18/25'; ?></td>
              <td class="edit-column">
                <div class="edit-icon">
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="#666">
                    <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/>
                  </svg>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      
      <form method="post" class="action-buttons">
        <button type="submit" name="reject" class="btn btn-reject" onclick="return confirm('Are you sure you want to reject this lab day request?')">Reject</button>
        <button type="submit" name="approve" class="btn btn-approve">Approve</button>
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
