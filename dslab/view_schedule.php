<?php
session_start();

// Check if professor is logged in
if (!isset($_SESSION['professor_id'])) {
  header("Location: professorlogin.php");
  exit();
}

include 'db.php';

// Get professor information
$professor_id = $_SESSION['professor_id'];
$sql = "SELECT * FROM professors WHERE id = $professor_id";
$result = $conn->query($sql);
$professor = $result->fetch_assoc();

// Check if schedule ID is provided
if (!isset($_GET['id'])) {
  $_SESSION['error_message'] = "No schedule ID provided";
  header("Location: professor-lab-schedule.php");
  exit();
}

$schedule_id = $_GET['id'];

// In a real app, you would fetch the schedule from the database
// For now, we'll use sample data to match the screenshot
$schedule = [
  'id' => 1,
  'name' => 'Lab',
  'course_code' => 'Fundamentals in Electronics Circuits',
  'schedule_date' => '2025-03-11',
  'start_time' => '13:30:00',
  'end_time' => '15:00:00',
  'room' => 'Electronics Lab',
  'status' => 'Approved',
  'created_at' => '2025-03-01 10:00:00',
  'equipment' => [
    ['name' => 'Oscilloscope', 'quantity' => 1],
    ['name' => 'Multimeter', 'quantity' => 1],
    ['name' => 'Soldering Iron', 'quantity' => 1]
  ]
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Schedule Details</title>
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="sidenav.css">
  <style>
    .schedule-details-container {
      padding: 20px;
      max-width: 800px;
      margin: 0 auto;
    }
    
    .back-button {
      display: flex;
      align-items: center;
      color: #444;
      text-decoration: none;
      margin-bottom: 15px;
      font-weight: 500;
    }
    
    .back-button svg {
      margin-right: 8px;
    }
    
    .schedule-details-card {
      background: white;
      border-radius: 10px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.1);
      overflow: hidden;
    }
    
    .schedule-details-header {
      background-color: #ff7f1a;
      color: white;
      padding: 15px 20px;
      font-size: 1.2em;
      font-weight: 500;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    
    .schedule-details-content {
      padding: 20px;
    }
    
    .details-section {
      margin-bottom: 25px;
    }
    
    .details-section-title {
      font-weight: 600;
      color: #444;
      margin-bottom: 15px;
      padding-bottom: 8px;
      border-bottom: 1px solid #eee;
    }
    
    .details-grid {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 15px;
    }
    
    .details-item {
      margin-bottom: 10px;
    }
    
    .details-label {
      font-weight: 500;
      color: #666;
      margin-bottom: 5px;
    }
    
    .details-value {
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
    
    .btn {
      background-color: #ff7f1a;
      color: white;
      border: none;
      padding: 10px 20px;
      border-radius: 5px;
      cursor: pointer;
      font-weight: 500;
      text-decoration: none;
      display: inline-block;
    }
    
    .btn-outline {
      background-color: transparent;
      color: #ff7f1a;
      border: 1px solid #ff7f1a;
    }
    
    .btn-danger {
      background-color: #dc3545;
      color: white;
    }
    
    .status-badge {
      padding: 6px 12px;
      border-radius: 20px;
      font-size: 0.9em;
      font-weight: 500;
      background-color: #d4edda;
      color: #155724;
    }
    
    @media (max-width: 768px) {
      .schedule-details-container {
        padding: 10px;
      }
      
      .details-grid {
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
        text-align: center;
      }
    }
  </style>
</head>
<body>
  <?php 
  // Define current page for navigation highlighting
  $current_page = 'professor-lab-schedule.php';
  
  // Include the navigation template
  include 'nav_template_professor.php'; 
  ?>
  
  <div class="schedule-details-container">
    <a href="professor-lab-schedule.php" class="back-button">
      <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M19 12H5M12 19l-7-7 7-7"/>
      </svg>
      Back to Schedules
    </a>
    
    <div class="schedule-details-card">
      <div class="schedule-details-header">
        <div>Schedule Details</div>
        <div class="status-badge"><?php echo $schedule['status']; ?></div>
      </div>
      
      <div class="schedule-details-content">
        <div class="details-section">
          <h3 class="details-section-title">Lab Information</h3>
          <div class="details-grid">
            <div class="details-item">
              <div class="details-label">Lab Type</div>
              <div class="details-value"><?php echo $schedule['name']; ?></div>
            </div>
            
            <div class="details-item">
              <div class="details-label">Subject</div>
              <div class="details-value"><?php echo $schedule['course_code']; ?></div>
            </div>
            
            <div class="details-item">
              <div class="details-label">Date</div>
              <div class="details-value"><?php echo date('F j, Y', strtotime($schedule['schedule_date'])); ?></div>
            </div>
            
            <div class="details-item">
              <div class="details-label">Time</div>
              <div class="details-value">
                <?php echo date('g:i A', strtotime($schedule['start_time'])); ?> - 
                <?php echo date('g:i A', strtotime($schedule['end_time'])); ?>
              </div>
            </div>
            
            <div class="details-item">
              <div class="details-label">Room</div>
              <div class="details-value"><?php echo $schedule['room']; ?></div>
            </div>
            
            <div class="details-item">
              <div class="details-label">Created On</div>
              <div class="details-value"><?php echo date('F j, Y g:i A', strtotime($schedule['created_at'])); ?></div>
            </div>
          </div>
        </div>
        
        <div class="details-section">
          <h3 class="details-section-title">Equipment</h3>
          <div class="equipment-list">
            <div class="equipment-list-header">
              <div>#</div>
              <div>Name</div>
              <div>Qty</div>
            </div>
            
            <?php foreach ($schedule['equipment'] as $index => $item): ?>
              <div class="equipment-item">
                <div class="equipment-number"><?php echo $index + 1; ?></div>
                <div class="equipment-name"><?php echo $item['name']; ?></div>
                <div class="equipment-quantity"><?php echo $item['quantity']; ?></div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
        
        <div class="action-buttons">
          <a href="edit_schedule.php?id=<?php echo $schedule['id']; ?>" class="btn btn-outline">Edit Schedule</a>
          <a href="cancel_schedule.php?id=<?php echo $schedule['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to cancel this schedule?');">Cancel Schedule</a>
        </div>
      </div>
    </div>
  </div>
  
  <!-- No script needed here as it's included in the nav template -->
</body>
</html>
<?php $conn->close(); ?>
