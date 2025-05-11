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

// Get subjects (in a real app, these would come from a database)
$subjects = [
  [
    'id' => 1,
    'name' => 'Fundamentals to Electronics Circuits',
    'code' => 'EE101',
    'sections' => ['A', 'B', 'C', 'D'],
    'groups' => ['Group 1', 'Group 2', 'Group 3', 'Group 4'],
    'equipment' => [
      ['id' => 1, 'name' => 'Oscilloscope', 'quantity' => 10],
      ['id' => 2, 'name' => 'Digital Multimeter', 'quantity' => 15],
      ['id' => 3, 'name' => 'Function Generator', 'quantity' => 8],
      ['id' => 4, 'name' => 'Power Supply', 'quantity' => 12]
    ],
    'students' => [
      ['id' => 1, 'name' => 'Beatingo, Dave Daryll', 'student_id' => '2023-0001'],
      ['id' => 2, 'name' => 'Cabarrubias, Karl Paolo', 'student_id' => '2023-0002'],
      ['id' => 3, 'name' => 'Reynaldo, Ray Christian', 'student_id' => '2023-0003'],
      ['id' => 4, 'name' => 'Santos, Jay Michael', 'student_id' => '2023-0004'],
      ['id' => 5, 'name' => 'Ortiz, Joaquin Alejandro', 'student_id' => '2023-0005']
    ]
  ],
  [
    'id' => 2,
    'name' => 'Chemistry For Engineers',
    'code' => 'CHEM201',
    'sections' => ['A', 'B', 'C'],
    'groups' => ['Group 1', 'Group 2', 'Group 3'],
    'equipment' => [
      ['id' => 5, 'name' => 'Bunsen Burner', 'quantity' => 20],
      ['id' => 6, 'name' => 'Test Tubes', 'quantity' => 50],
      ['id' => 7, 'name' => 'Beakers', 'quantity' => 30],
      ['id' => 8, 'name' => 'pH Meter', 'quantity' => 10]
    ],
    'students' => [
      ['id' => 6, 'name' => 'Beatingo, Dave Daryll', 'student_id' => '2023-0001'],
      ['id' => 7, 'name' => 'Cabarrubias, Karl Paolo', 'student_id' => '2023-0002'],
      ['id' => 8, 'name' => 'Reynaldo, Ray Christian', 'student_id' => '2023-0003'],
      ['id' => 9, 'name' => 'Santos, Jay Michael', 'student_id' => '2023-0004'],
      ['id' => 10, 'name' => 'Ortiz, Joaquin Alejandro', 'student_id' => '2023-0005']
    ]
  ]
];

// Process notification submission
$notification_sent = false;
$error_message = '';
$selected_subject_id = isset($_GET['subject_id']) ? (int)$_GET['subject_id'] : null;
$selected_subject = null;

// Find the selected subject
if ($selected_subject_id) {
  foreach ($subjects as $subject) {
    if ($subject['id'] == $selected_subject_id) {
      $selected_subject = $subject;
      break;
    }
  }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_notification'])) {
  $subject_id = isset($_POST['subject_id']) ? $_POST['subject_id'] : '';
  $section = isset($_POST['section']) ? $_POST['section'] : '';
  $title = isset($_POST['title']) ? trim($_POST['title']) : '';
  $group_no = isset($_POST['group_no']) ? $_POST['group_no'] : '';
  $equipment_ids = isset($_POST['equipment_ids']) ? $_POST['equipment_ids'] : [];
  $student_ids = isset($_POST['student_ids']) ? $_POST['student_ids'] : [];
  $schedule_date = isset($_POST['schedule_date']) ? $_POST['schedule_date'] : '';
  $schedule_time = isset($_POST['schedule_time']) ? $_POST['schedule_time'] : '';
  
  // Validate inputs
  if (empty($subject_id)) {
    $error_message = 'Please select a subject';
  } elseif (empty($section)) {
    $error_message = 'Please select a section';
  } elseif (empty($title)) {
    $error_message = 'Please enter a title';
  } elseif (empty($group_no)) {
    $error_message = 'Please select a group number';
  } elseif (empty($equipment_ids)) {
    $error_message = 'Please select at least one equipment';
  } elseif (empty($student_ids)) {
    $error_message = 'Please select at least one student';
  } elseif (empty($schedule_date)) {
    $error_message = 'Please select a date';
  } elseif (empty($schedule_time)) {
    $error_message = 'Please select a time';
  } else {
    // Get subject details
    $subject_name = '';
    foreach ($subjects as $subject) {
      if ($subject['id'] == $subject_id) {
        $subject_name = $subject['name'];
        break;
      }
    }
    
    // In a real app, you would insert into a notifications table
    // For example:
    // $sql = "INSERT INTO notifications (sender_id, sender_type, subject_id, section, title, group_no, schedule_date, schedule_time, created_at) 
    //         VALUES ($professor_id, 'professor', $subject_id, '$section', '$title', '$group_no', '$schedule_date', '$schedule_time', NOW())";
    // if ($conn->query($sql) === TRUE) {
    //   $notification_id = $conn->insert_id;
    //   
    //   // Insert equipment selections
    //   foreach ($equipment_ids as $equipment_id) {
    //     $sql = "INSERT INTO notification_equipment (notification_id, equipment_id) VALUES ($notification_id, $equipment_id)";
    //     $conn->query($sql);
    //   }
    //   
    //   // Insert student selections
    //   foreach ($student_ids as $student_id) {
    //     $sql = "INSERT INTO notification_students (notification_id, student_id) VALUES ($notification_id, $student_id)";
    //     $conn->query($sql);
    //   }
    //   
    //   $notification_sent = true;
    // } else {
    //   $error_message = "Error: " . $sql . "<br>" . $conn->error;
    // }
    
    // For this demo, we'll just set notification_sent to true
    $notification_sent = true;
    
    // Reset the selected subject after successful submission
    $selected_subject_id = null;
    $selected_subject = null;
  }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Send Schedule Notification</title>
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="sidenav.css">
  <style>
    body {
      background-color: #f3f3f3;
      margin: 0;
      padding: 0;
      font-family: Arial, sans-serif;
      display: flex;
      height: 100vh;
      overflow: hidden;
    }
    
    .notification-container {
      padding: 0;
      width: calc(100% - 160px);
      padding-bottom: 60px;
      padding-top: 20px;
      flex-grow: 1;
      margin-left: 160px;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: flex-start;
      background-color: #f0f0f0;
      position: relative;
      overflow-y: auto;
    }
    
    .logo {
      position: relative;
      margin: 20px auto 30px auto;
      width: 150px;
      height: 80px;
      z-index: 100;
      background-color: #f0f0f0;
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 5px;
      border-radius: 8px;
    }
    
    .logo svg {
      width: 100%;
      height: 100%;
      object-fit: contain;
    }
    
    .notification-form {
      background-color: #ff7f1a;
      border-radius: 10px;
      padding: 20px;
      margin: 0;
      width: 450px;
      max-width: 90%;
      position: relative;
      z-index: 5;
      margin-top: 20px;
    }
    
    .notification-form h2 {
      color: #444;
      margin-top: 0;
      margin-bottom: 20px;
      text-align: center;
    }
    
    .form-group {
      margin-bottom: 15px;
    }
    
    .form-group label {
      display: block;
      margin-bottom: 5px;
      font-weight: bold;
      color: #444;
    }
    
    .form-control {
      width: 100%;
      padding: 10px;
      border: 1px solid #ddd;
      border-radius: 5px;
      font-size: 16px;
      box-sizing: border-box;
    }
    
    .form-control:focus {
      outline: none;
      border-color: #e55a00;
    }
    
    textarea.form-control {
      min-height: 100px;
      resize: vertical;
    }
    
    .btn-send {
      background-color: #4CAF50;
      color: white;
      border: none;
      border-radius: 20px;
      padding: 12px 20px;
      font-size: 16px;
      cursor: pointer;
      width: 100%;
      font-weight: bold;
      transition: background-color 0.3s;
      margin-top: 10px;
    }
    
    .btn-send:hover {
      background-color: #45a049;
    }
    
    .alert {
      padding: 10px 15px;
      border-radius: 5px;
      margin-bottom: 15px;
      font-weight: bold;
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
    
    .subject-card {
      background-color: #fff;
      border-radius: 8px;
      padding: 15px;
      margin-bottom: 10px;
      display: flex;
      align-items: center;
      cursor: pointer;
      transition: background-color 0.2s;
      width: 100%;
      box-sizing: border-box;
      border-left: 4px solid #e55a00;
    }
    
    .subject-card:hover {
      background-color: #f5f5f5;
    }
    
    .subject-icon {
      margin-right: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    
    .subject-details {
      flex-grow: 1;
    }
    
    .subject-name {
      font-weight: bold;
      color: #444;
      margin-bottom: 5px;
    }
    
    .subject-code {
      font-size: 12px;
      color: #666;
    }
    
    .header-bar {
      background-color: #ffa64d;
      color: white;
      padding: 15px;
      text-align: center;
      font-weight: bold;
      border-radius: 10px 10px 0 0;
      margin-top: -20px;
      margin-left: -20px;
      margin-right: -20px;
      margin-bottom: 20px;
    }
    
    .back-button {
      position: absolute;
      top: 15px;
      left: -30px;
      background: none;
      border: none;
      color: #444;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 5px;
      z-index: 20;
    }
    
    .preview-section {
      background-color: #f0f0f0;
      border-radius: 8px;
      padding: 15px;
      margin-bottom: 15px;
    }
    
    .preview-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 10px;
    }
    
    .preview-title {
      font-weight: bold;
      color: #444;
    }
    
    .preview-expand {
      background: none;
      border: none;
      color: #444;
      cursor: pointer;
    }
    
    .preview-content {
      min-height: 100px;
    }
    
    .add-student-btn {
      background-color: #f0f0f0;
      border: 1px dashed #aaa;
      border-radius: 20px;
      padding: 8px 15px;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      font-size: 14px;
      color: #666;
      width: fit-content;
      margin: 10px 0;
    }
    
    .add-student-btn svg {
      margin-right: 5px;
    }
    
    /* Student Selection Styles */
    .student-selection-container {
      background-color: #ffcbb8;
      border-radius: 8px;
      margin-bottom: 15px;
      overflow: hidden;
      display: flex;
      flex-direction: column;
    }
    
    .student-header {
      margin-top: 0;
      margin-left: 0;
      margin-right: 0;
      border-radius: 0;
      background-color: #ffa64d;
    }
    
    .search-container {
      padding: 10px;
      background-color: #ffcbb8;
    }
    
    .search-input-container {
      display: flex;
      align-items: center;
      background-color: #fff;
      border-radius: 20px;
      padding: 5px 10px;
      margin-bottom: 5px;
    }
    
    .search-input {
      border: none;
      outline: none;
      width: 100%;
      padding: 5px;
      font-size: 14px;
    }
    
    .student-list {
      background-color: #ffcbb8;
      max-height: 250px;
      overflow-y: auto;
      padding: 0 10px;
    }
    
    .student-item {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 10px;
      border-bottom: 1px solid rgba(255, 255, 255, 0.3);
      background-color: #ffcbb8;
    }
    
    .student-name {
      font-size: 14px;
      color: #333;
      font-weight: normal;
    }
    
    .student-checkbox {
      width: 18px;
      height: 18px;
      cursor: pointer;
    }
    
    .select-all-container {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 10px;
      background-color: #f0f0f0;
      border-top: 1px solid #ddd;
    }
    
    .select-all-label {
      display: flex;
      align-items: center;
      font-size: 10px;
      color: #333;
      cursor: pointer;
    }
    
    .select-all-checkbox {
      margin-right: 8px;
      width: 16px;
      height: 16px;
    }
    
    .btn-send-small {
      background-color: #4CAF50;
      color: white;
      border: none;
      border-radius: 4px;
      padding: 5px 15px;
      font-size: 12px;
      cursor: pointer;
      font-weight: bold;
    }
    
    .dropdown-select {
      position: relative;
      width: 100%;
    }
    
    .dropdown-select select {
      width: 100%;
      padding: 10px;
      border: 1px solid #ddd;
      border-radius: 5px;
      appearance: none;
      background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="%23444"><path d="M7 10l5 5 5-5z"/></svg>');
      background-repeat: no-repeat;
      background-position: right 10px center;
    }
    
    @media (max-width: 768px) {
      .notification-container {
        width: 100%;
        margin-left: 0;
      }
      
      .notification-form {
        width: 90%;
        max-width: 350px;
      }
    }
    
    .bottom-nav {
      position: fixed;
      bottom: 0;
      left: 0;
      right: 0;
      background-color: #fff;
      display: flex;
      justify-content: space-around;
      padding: 10px 0;
      box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1);
      z-index: 1000;
    }
    
    .nav-item {
      display: flex;
      flex-direction: column;
      align-items: center;
      text-decoration: none;
      color: #777;
      font-size: 12px;
    }
    
    .nav-item.active {
      color: #e55a00;
    }
    
    .nav-icon {
      margin-bottom: 4px;
      width: 24px;
      height: 24px;
    }
    
    @media (min-width: 769px) {
      .bottom-nav {
        display: none;
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
  <div class="notification-container">
    <div class="logo" id="dsLabLogo">
      <?php include 'icons/dsB.svg'; ?>
    </div>
    
    <div class="notification-form">
      <?php if ($notification_sent): ?>
        <div class="alert alert-success">
          Schedule notification sent successfully!
        </div>
      <?php endif; ?>
      
      <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger">
          <?php echo htmlspecialchars($error_message); ?>
        </div>
      <?php endif; ?>
      
      <?php if ($selected_subject): ?>
        <!-- Detailed Subject Form -->
        <button class="back-button" onclick="window.location.href='professor-send-notification.php'">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="#444">
            <path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"/>
          </svg>
        </button>
        
        <div class="header-bar">
          Send Schedule
        </div>
        
        <form action="professor-send-notification.php" method="POST">
          <input type="hidden" name="subject_id" value="<?php echo $selected_subject['id']; ?>">
          
          <div class="subject-card">
            <div class="subject-icon">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="#444">
                <path d="M18 2H6c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zM6 4h5v8l-2.5-1.5L6 12V4z"/>
              </svg>
            </div>
            <div class="subject-details">
              <div class="subject-name"><?php echo htmlspecialchars($selected_subject['name']); ?></div>
              <div class="subject-code"><?php echo htmlspecialchars($selected_subject['code']); ?></div>
            </div>
          </div>
          
          <div class="form-group">
            <label for="section">Section</label>
            <div class="dropdown-select">
              <select class="form-control" id="section" name="section" required>
                <option value="" disabled selected>Select section</option>
                <?php foreach ($selected_subject['sections'] as $section): ?>
                  <option value="<?php echo htmlspecialchars($section); ?>"><?php echo htmlspecialchars($section); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
          
          <div class="form-group">
            <label for="title">Title</label>
            <input type="text" class="form-control" id="title" name="title" placeholder="Enter title" required>
          </div>
          
          <div class="form-group">
            <label for="group_no">Group No.</label>
            <div class="dropdown-select">
              <select class="form-control" id="group_no" name="group_no" required>
                <option value="" disabled selected>Select group</option>
                <?php foreach ($selected_subject['groups'] as $group): ?>
                  <option value="<?php echo htmlspecialchars($group); ?>"><?php echo htmlspecialchars($group); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
          
          <div class="form-group">
            <div class="preview-section">
              <div class="preview-header">
                <div class="preview-title">Equipment Preview</div>
                <button type="button" class="preview-expand">
                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="#444">
                    <path d="M16.59 8.59L12 13.17 7.41 8.59 6 10l6 6 6-6z"/>
                  </svg>
                </button>
              </div>
              <div class="preview-content">
                <?php foreach ($selected_subject['equipment'] as $equipment): ?>
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="equipment_ids[]" value="<?php echo $equipment['id']; ?>" id="equipment_<?php echo $equipment['id']; ?>">
                    <label class="form-check-label" for="equipment_<?php echo $equipment['id']; ?>">
                      <?php echo htmlspecialchars($equipment['name']); ?> (<?php echo htmlspecialchars($equipment['quantity']); ?> available)
                    </label>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
          </div>
          
          <div class="form-group">
            <div class="student-selection-container">
              <div class="header-bar student-header">
                Send Schedule
              </div>
              
              <div class="search-container">
                <div class="search-input-container">
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="#888">
                    <path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/>
                  </svg>
                  <input type="text" id="student-search" placeholder="Search" class="search-input" oninput="filterStudents()">
                </div>
              </div>
              
              <div class="student-list">
                <?php foreach ($selected_subject['students'] as $student): ?>
                  <div class="student-item">
                    <div class="student-name"><?php echo htmlspecialchars($student['name']); ?></div>
                    <input type="checkbox" name="student_ids[]" value="<?php echo $student['id']; ?>" id="student_<?php echo $student['id']; ?>" class="student-checkbox">
                  </div>
                <?php endforeach; ?>
              </div>
              
              <div class="select-all-container">
                <label class="select-all-label">
                  <input type="checkbox" id="select-all-students" class="select-all-checkbox" onclick="toggleAllStudents()">
                  <span>Select all of the students to be notified</span>
                </label>
                <button type="submit" name="send_notification" class="btn-send-small">Send</button>
              </div>
            </div>
          </div>
          
          <div class="form-group">
            <label for="schedule_date">Date</label>
            <input type="date" class="form-control" id="schedule_date" name="schedule_date" required>
          </div>
          
          <div class="form-group">
            <label for="schedule_time">Time</label>
            <input type="time" class="form-control" id="schedule_time" name="schedule_time" required>
          </div>
          
          <button type="submit" name="send_notification" class="btn-send">Send Schedule!</button>
        </form>
        
      <?php else: ?>
        <!-- Subject Selection Screen -->
        <div class="header-bar">
          Send Schedule
        </div>
        
        <?php foreach ($subjects as $subject): ?>
          <a href="professor-send-notification.php?subject_id=<?php echo $subject['id']; ?>" class="subject-card">
            <div class="subject-icon">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="#444">
                <path d="M18 2H6c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zM6 4h5v8l-2.5-1.5L6 12V4z"/>
              </svg>
            </div>
            <div class="subject-details">
              <div class="subject-name"><?php echo htmlspecialchars($subject['name']); ?></div>
              <div class="subject-code"><?php echo htmlspecialchars($subject['code']); ?></div>
            </div>
          </a>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
  
  <!-- Bottom Navigation -->
  <div class="bottom-nav">
    <a href="professor-notifications.php" class="nav-item">
      <div class="nav-icon">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
          <path d="M12 22c1.1 0 2-.9 2-2h-4c0 1.1.89 2 2 2zm6-6v-5c0-3.07-1.64-5.64-4.5-6.32V4c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5v.68C7.63 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2z"/>
        </svg>
      </div>
      <span>Notifications</span>
    </a>
    <a href="professor-history.php" class="nav-item">
      <div class="nav-icon">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
          <path d="M13 3c-4.97 0-9 4.03-9 9H1l3.89 3.89.07.14L9 12H6c0-3.87 3.13-7 7-7s7 3.13 7 7-3.13 7-7 7c-1.93 0-3.68-.79-4.94-2.06l-1.42 1.42C8.27 19.99 10.51 21 13 21c4.97 0 9-4.03 9-9s-4.03-9-9-9zm-1 5v5l4.28 2.54.72-1.21-3.5-2.08V8H12z"/>
        </svg>
      </div>
      <span>History</span>
    </a>
    <a href="professor-dashboard.php" class="nav-item">
      <div class="nav-icon">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
          <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/>
        </svg>
      </div>
      <span>Home</span>
    </a>
    <a href="professor-profile.php" class="nav-item">
      <div class="nav-icon">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
          <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
        </svg>
      </div>
      <span>Profile</span>
    </a>
    <a href="professor-more.php" class="nav-item active">
      <div class="nav-icon">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
          <path d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"/>
        </svg>
      </div>
      <span>More</span>
    </a>
  </div>
  
  <!-- JavaScript for menu toggle and form functionality -->    
  <script>
    // Function to filter students based on search input
    function filterStudents() {
      const searchInput = document.getElementById('student-search');
      if (!searchInput) return;
      
      const filter = searchInput.value.toLowerCase();
      const studentItems = document.querySelectorAll('.student-item');
      
      studentItems.forEach(item => {
        const studentName = item.querySelector('.student-name').textContent.toLowerCase();
        if (studentName.includes(filter)) {
          item.style.display = '';
        } else {
          item.style.display = 'none';
        }
      });
    }
    
    // Function to toggle all student checkboxes
    function toggleAllStudents() {
      const selectAllCheckbox = document.getElementById('select-all-students');
      if (!selectAllCheckbox) return;
      
      const studentCheckboxes = document.querySelectorAll('.student-checkbox');
      studentCheckboxes.forEach(checkbox => {
        checkbox.checked = selectAllCheckbox.checked;
      });
    }
    
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
      
      // Set default date to today
      const today = new Date();
      const dateInput = document.getElementById('schedule_date');
      if (dateInput) {
        dateInput.valueAsDate = today;
      }
      
      // Set default time to current time (rounded to nearest hour)
      const now = new Date();
      now.setMinutes(0);
      now.setSeconds(0);
      const timeInput = document.getElementById('schedule_time');
      if (timeInput) {
        timeInput.value = now.getHours().toString().padStart(2, '0') + ':00';
      }
      
      // Toggle preview sections
      const previewExpandButtons = document.querySelectorAll('.preview-expand');
      previewExpandButtons.forEach(button => {
        button.addEventListener('click', function() {
          const previewContent = this.closest('.preview-section').querySelector('.preview-content');
          if (previewContent.style.display === 'none') {
            previewContent.style.display = 'block';
            this.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="#444"><path d="M16.59 8.59L12 13.17 7.41 8.59 6 10l6 6 6-6z"/></svg>';
          } else {
            previewContent.style.display = 'none';
            this.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="#444"><path d="M12 8l-6 6 1.41 1.41L12 10.83l4.59 4.58L18 14z"/></svg>';
          }
        });
      });
      
      // Select all equipment checkboxes
      const addSelectAllCheckboxes = function(containerSelector, checkboxSelector) {
        const container = document.querySelector(containerSelector);
        if (!container) return;
        
        const checkboxes = container.querySelectorAll(checkboxSelector);
        if (checkboxes.length === 0) return;
        
        const selectAllDiv = document.createElement('div');
        selectAllDiv.className = 'form-check';
        selectAllDiv.innerHTML = `
          <input class="form-check-input" type="checkbox" id="select-all-${containerSelector}">
          <label class="form-check-label" for="select-all-${containerSelector}">
            <strong>Select All</strong>
          </label>
        `;
        
        container.insertBefore(selectAllDiv, container.firstChild);
        
        const selectAllCheckbox = selectAllDiv.querySelector('input[type="checkbox"]');
        selectAllCheckbox.addEventListener('change', function() {
          checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
          });
        });
      };
      
      // Add select all checkbox to equipment section only
      addSelectAllCheckboxes('.preview-content', 'input[name="equipment_ids[]"]');
      
      // Update student checkboxes when "Select all" is clicked
      const selectAllStudents = document.getElementById('select-all-students');
      if (selectAllStudents) {
        selectAllStudents.addEventListener('change', toggleAllStudents);
      }
      
      // Initialize the search functionality
      const searchInput = document.getElementById('student-search');
      if (searchInput) {
        searchInput.addEventListener('input', filterStudents);
      }
    });
  </script>
</body>
</html>
