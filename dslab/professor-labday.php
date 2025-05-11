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

// Handle form submission
$success_message = "";
$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  // Get form data
  $subject = $_POST['subject'] ?? '';
  $section = $_POST['section'] ?? '';
  $time = $_POST['time'] ?? '';
  $date = $_POST['date'] ?? '';
  $title = $_POST['title'] ?? '';
  $group_no = $_POST['group_no'] ?? '';
  $equipment = $_POST['equipment'] ?? '';
  
  // Validate form data
  if (empty($subject) || empty($section) || empty($time) || empty($date) || empty($title)) {
    $error_message = "Please fill in all required fields";
  } else {
    // Check if lab_days table exists, create if not
    $check_lab_days = $conn->query("SHOW TABLES LIKE 'lab_days'");
    if ($check_lab_days->num_rows == 0) {
      // Create lab_days table
      $create_table_sql = "CREATE TABLE lab_days (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        professor_id INT(11) NOT NULL,
        subject VARCHAR(100) NOT NULL,
        section VARCHAR(50) NOT NULL,
        time VARCHAR(50) NOT NULL,
        date DATE NOT NULL,
        title VARCHAR(255) NOT NULL,
        group_no VARCHAR(50),
        equipment VARCHAR(100),
        created_at DATETIME NOT NULL,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
      )";
      $conn->query($create_table_sql);
    }
    
    // Insert lab day into database
    $sql = "INSERT INTO lab_days (professor_id, subject, section, time, date, title, group_no, equipment, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isssssss", $professor_id, $subject, $section, $time, $date, $title, $group_no, $equipment);
    
    if ($stmt->execute()) {
      $success_message = "Lab day assigned successfully!";
      // Clear form data after successful submission
      $subject = $section = $time = $date = $title = $group_no = $equipment = "";
    } else {
      $error_message = "Error: " . $stmt->error;
    }
    
    $stmt->close();
  }
}

// Initialize variables for dropdowns
$subjects_result = false;
$sections_result = false;
$equipment_result = false;

// Check if tables exist before querying
try {
  // Check if subjects table exists
  $check_subjects = $conn->query("SHOW TABLES LIKE 'subjects'");
  if ($check_subjects->num_rows > 0) {
    $subjects_sql = "SELECT DISTINCT name FROM subjects ORDER BY name";
    $subjects_result = $conn->query($subjects_sql);
  }
  
  // Check if sections table exists
  $check_sections = $conn->query("SHOW TABLES LIKE 'sections'");
  if ($check_sections->num_rows > 0) {
    $sections_sql = "SELECT DISTINCT name FROM sections ORDER BY name";
    $sections_result = $conn->query($sections_sql);
  }
  
  // Check if equipment table exists
  $check_equipment = $conn->query("SHOW TABLES LIKE 'equipment'");
  if ($check_equipment->num_rows > 0) {
    $equipment_sql = "SELECT id, name FROM equipment ORDER BY name";
    $equipment_result = $conn->query($equipment_sql);
  }
} catch (Exception $e) {
  // Silently handle exceptions - we'll use default values instead
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Assign Lab Day - Professor</title>
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="sidenav.css">
  <style>
    html, body {
      background-color: white;
      margin: 0;
      padding: 0;
      font-family: Arial, sans-serif;
      height: 100%;
      overflow-x: hidden;
      overflow-y: auto; /* Show scrollbar at the outermost right */
    }
    
    .dashboard-container {
      padding: 0;
      margin: 0;
      max-width: 100%;
      width: calc(100% - 120px);
      margin-left: 120px;
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
      overflow-y: visible; /* Let the body handle scrolling */
    }
    
    /* Webkit scrollbar styling for body */
    body::-webkit-scrollbar {
      width: 8px;
    }
    
    body::-webkit-scrollbar-track {
      background: transparent;
    }
    
    body::-webkit-scrollbar-thumb {
      background-color: rgba(0, 0, 0, 0.3);
      border-radius: 4px;
    }
    
    @media (max-width: 768px) {
      .dashboard-container {
        width: 100%;
        margin-left: 0;
        padding-top: 60px;
      }
    }
    
    .form-layout {
      display: flex;
      flex-direction: column;
      align-items: center;
      width: 100%;
      max-width: 350px;
      margin: 0 auto;
      max-height: 90vh;
      overflow: hidden;
      border-radius: 10px;
    }
    
    .logo-container {
      margin-bottom: 15px;
      text-align: center;
    }
    
    .logo-container svg {
      max-width: 120px;
      height: auto;
    }
    
    .form-header {
      background-color: #e55a00;
      color: white;
      text-align: center;
      padding: 8px;
      width: 100%;
      border-top-left-radius: 10px;
      border-top-right-radius: 10px;
    }
    
    .form-container {
      background-color: #e55a00;
      padding: 10px 15px;
      width: 100%;
      max-height: 70vh;
      overflow-y: auto; /* Enable scrolling for form content */
      direction: ltr;
      position: relative;
      border-bottom-left-radius: 10px;
      border-bottom-right-radius: 10px;
    }
    
    .form-container form {
      padding-right: 15px; /* Add padding to the form instead */
    }
    
    .form-title {
      text-align: center;
      color: #e55a00;
      margin-bottom: 20px;
    }
    
    .form-group {
      margin-bottom: 12px;
      width: 100%;
    }
    
    .form-group label {
      display: block;
      margin-bottom: 4px;
      font-weight: bold;
      color: white;
      font-size: 14px;
    }
    
    .form-control {
      width: 100%;
      padding: 8px 12px;
      border: none;
      border-radius: 20px;
      background-color: white;
      box-sizing: border-box;
      font-size: 14px;
    }
    
    .form-select {
      width: 100%;
      padding: 8px 12px;
      border: none;
      border-radius: 20px;
      background-color: white;
      appearance: none;
      background-image: url('data:image/svg+xml;utf8,<svg fill="black" height="24" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg"><path d="M7 10l5 5 5-5z"/></svg>');
      background-repeat: no-repeat;
      background-position: right 10px center;
      box-sizing: border-box;
      font-size: 14px;
    }
    
    .equipment-preview {
      background-color: white;
      border: none;
      border-radius: 20px;
      padding: 10px;
      margin-top: 5px;
      min-height: 100px;
      position: relative;
      box-sizing: border-box;
    }
    
    .refresh-icon {
      position: absolute;
      bottom: 10px;
      right: 10px;
      background-color: transparent;
      border: none;
      cursor: pointer;
    }
    
    .btn-submit {
      background-color: #e55a00;
      color: white;
      border: none;
      padding: 12px 20px;
      border-radius: 5px;
      cursor: pointer;
      width: 100%;
      font-size: 16px;
      margin-top: 15px;
    }
    
    .btn-submit:hover {
      background-color: #ff7f1a;
    }
    
    .alert {
      padding: 10px;
      border-radius: 5px;
      margin-bottom: 15px;
    }
    
    .alert-success {
      background-color: #d4edda;
      color: #155724;
    }
    
    .alert-danger {
      background-color: #f8d7da;
      color: #721c24;
    }
    
    .form-row {
      display: flex;
      gap: 10px;
      width: 100%;
    }
    
    .form-row .form-group {
      flex: 1;
      padding: 0;
    }
    
    /* Mobile menu toggle */
    .menu-toggle {
      display: none;
      position: fixed;
      top: 10px;
      right: 10px;
      background-color: #444;
      color: white;
      border: none;
      border-radius: 5px;
      padding: 8px;
      z-index: 1000;
      cursor: pointer;
    }
    
    @media (max-width: 768px) {
      .menu-toggle {
        display: block;
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
      <li><a href="professor-notifications.php"><span class="icon"><?php include 'icons/bell.svg'; ?></span> Notifications</a></li>
      <li><a href="professor-history.php"><span class="icon"><?php include 'icons/clock.svg'; ?></span> History</a></li>
      <li><a href="professor-dashboard.php"><span class="icon"><?php include 'icons/home.svg'; ?></span> Home</a></li>
      <li><a href="professor-profile.php"><span class="icon"><?php include 'icons/profile.svg'; ?></span> Profile</a></li>
      <li><a href="professor-more.php"><span class="icon"><?php include 'icons/more.svg'; ?></span> More</a></li>
    </ul>
  </div>
  
  <!-- Main Content -->    
  <div class="dashboard-container">
    <div class="form-layout">
      <!-- Logo -->    
      <div class="logo-container">
        <?php include 'icons/dsB.svg'; ?>
      </div>
      
      <!-- Form Header -->    
      <div class="form-header">
        <h2 style="margin: 0; font-size: 15px; font-weight: normal;">Information Lab Day</h2>
      </div>
      
      <!-- Form Container -->    
      <div class="form-container">
    
    <?php if (!empty($success_message)): ?>
      <div class="alert alert-success"><?php echo $success_message; ?></div>
    <?php endif; ?>
    
    <?php if (!empty($error_message)): ?>
      <div class="alert alert-danger"><?php echo $error_message; ?></div>
    <?php endif; ?>
    
      <form method="POST" action="">
        <div class="form-group">
          <label for="subject">Subject</label>
          <select class="form-select" id="subject" name="subject" required>
            <option value="" disabled selected>Select Subject</option>
            <?php
            if ($subjects_result && $subjects_result->num_rows > 0) {
              while($row = $subjects_result->fetch_assoc()) {
                echo "<option value='" . htmlspecialchars($row['name']) . "'>" . htmlspecialchars($row['name']) . "</option>";
              }
            } else {
              // Fallback options if no subjects in database
              $default_subjects = ["Physics", "Chemistry", "Biology", "Computer Science"];
              foreach ($default_subjects as $subject) {
                echo "<option value='" . htmlspecialchars($subject) . "'>" . htmlspecialchars($subject) . "</option>";
              }
            }
            ?>
          </select>
        </div>
        
        <div class="form-group">
          <label for="section">Section</label>
          <select class="form-select" id="section" name="section" required>
            <option value="" disabled selected>Select Section</option>
            <?php
            if ($sections_result && $sections_result->num_rows > 0) {
              while($row = $sections_result->fetch_assoc()) {
                echo "<option value='" . htmlspecialchars($row['name']) . "'>" . htmlspecialchars($row['name']) . "</option>";
              }
            } else {
              // Fallback options if no sections in database
              $default_sections = ["A", "B", "C", "D", "E"];
              foreach ($default_sections as $section) {
                echo "<option value='" . htmlspecialchars($section) . "'>" . htmlspecialchars($section) . "</option>";
              }
            }
            ?>
          </select>
        </div>
        
        <div class="form-row">
          <div class="form-group">
            <label for="time">Time</label>
            <select class="form-select" id="time" name="time" required>
              <option value="" disabled selected>Select Time</option>
              <?php
              $start_time = strtotime('7:00 AM');
              $end_time = strtotime('6:00 PM');
              $interval = 30 * 60; // 30 minutes in seconds
              
              for ($time = $start_time; $time <= $end_time; $time += $interval) {
                $time_option = date('g:i A', $time);
                echo "<option value='" . htmlspecialchars($time_option) . "'>" . htmlspecialchars($time_option) . "</option>";
              }
              ?>
            </select>
          </div>
          
          <div class="form-group">
            <label for="date">Date</label>
            <input type="date" class="form-control" id="date" name="date" required min="<?php echo date('Y-m-d'); ?>">
          </div>
        </div>
        
        <div class="form-group">
          <label for="title">Title</label>
          <input type="text" class="form-control" id="title" name="title" placeholder="Enter lab activity title" required>
        </div>
        
        <div class="form-group">
          <label for="group_no">Group No.</label>
          <select class="form-select" id="group_no" name="group_no">
            <option value="" disabled selected>Select Group</option>
            <?php
            for ($i = 1; $i <= 10; $i++) {
              echo "<option value='Group " . $i . "'>Group " . $i . "</option>";
            }
            ?>
          </select>
        </div>
        
        <div class="form-group">
          <label for="equipment">Equipment</label>
          <select class="form-select" id="equipment" name="equipment" onchange="updateEquipmentPreview()">
            <option value="" disabled selected>Select Equipment</option>
            <?php
            if ($equipment_result && $equipment_result->num_rows > 0) {
              while($row = $equipment_result->fetch_assoc()) {
                echo "<option value='" . htmlspecialchars($row['id']) . "'>" . htmlspecialchars($row['name']) . "</option>";
              }
            } else {
              // Fallback options if no equipment in database
              $default_equipment = ["Microscope", "Bunsen Burner", "Test Tubes", "Beakers", "Oscilloscope"];
              foreach ($default_equipment as $index => $equip) {
                echo "<option value='" . ($index + 1) . "'>" . htmlspecialchars($equip) . "</option>";
              }
            }
            ?>
          </select>
        </div>
        
        <div class="form-group">
          <label>Equipment Preview</label>
          <div class="equipment-preview" id="equipmentPreview">
            <!-- Equipment details will be loaded here -->
            <button class="refresh-icon">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="#666">
                <path d="M17.65 6.35C16.2 4.9 14.21 4 12 4c-4.42 0-7.99 3.58-7.99 8s3.57 8 7.99 8c3.73 0 6.84-2.55 7.73-6h-2.08c-.82 2.33-3.04 4-5.65 4-3.31 0-6-2.69-6-6s2.69-6 6-6c1.66 0 3.14.69 4.22 1.78L13 11h7V4l-2.35 2.35z"/>
              </svg>
            </button>
          </div>
        </div>
        
        <div class="form-group" style="display: none;">
          <input type="checkbox" id="confirm" name="confirm" checked required>
          <label for="confirm">I have confirmed all the details</label>
        </div>
        
        <button type="submit" class="btn-submit" style="background-color: #4CAF50; margin-top: 15px;">Assign Lab Day</button>
      </form>
    </div>
    
    </div>
  </div>
  
  <!-- JavaScript for menu toggle and equipment preview -->    
  <script>
    document.addEventListener('DOMContentLoaded', function() {
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
    
    function updateEquipmentPreview() {
      const equipmentSelect = document.getElementById('equipment');
      const previewDiv = document.getElementById('equipmentPreview');
      
      if (equipmentSelect.value) {
        // In a real application, you would fetch equipment details via AJAX
        // For now, we'll simulate it with a simple message
        previewDiv.innerHTML = '<p>Loading equipment details...</p>';
        
        // Simulate AJAX call with setTimeout
        setTimeout(() => {
          const equipmentName = equipmentSelect.options[equipmentSelect.selectedIndex].text;
          previewDiv.innerHTML = `
            <h4>${equipmentName}</h4>
            <p>Equipment ID: ${equipmentSelect.value}</p>
            <p>Status: Available</p>
            <p>Location: Lab Room ${Math.floor(Math.random() * 5) + 1}</p>
          `;
        }, 500);
      } else {
        previewDiv.innerHTML = '<p>Select an equipment to see details</p>';
      }
    }
  </script>
</body>
</html>
