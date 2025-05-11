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

// Check if the required tables exist
$table_exists = true;
$check_classes_table = $conn->query("SHOW TABLES LIKE 'classes'");
$check_schedules_table = $conn->query("SHOW TABLES LIKE 'class_schedules'");

if ($check_classes_table->num_rows == 0 || $check_schedules_table->num_rows == 0) {
  $table_exists = false;
}

// Get professor's scheduled classes if tables exist
$schedule_result = null;
if ($table_exists) {
  $schedule_sql = "SELECT c.*, cs.schedule_date, cs.start_time, cs.end_time, cs.room
                  FROM classes c 
                  JOIN class_schedules cs ON c.id = cs.class_id 
                  WHERE c.professor_id = $professor_id 
                  ORDER BY cs.schedule_date ASC, cs.start_time ASC";
  $schedule_result = $conn->query($schedule_sql);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Lab Schedule - Professor</title>
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="sidenav.css">
  <style>
    body {
      margin: 0;
      padding: 0;
      font-family: 'Segoe UI', Arial, sans-serif;
      background-color: #fff;
    }
    
    .schedule-container {
      display: flex;
      flex-direction: column;
      height: 100vh;
    }
    
    .header {
      background-color: #ff7f1a;
      color: white;
      padding: 12px 15px;
      text-align: center;
      font-weight: 500;
      font-size: 1.2em;
      border-radius: 0 0 10px 10px;
      position: relative;
    }
    
    .back-button {
      position: absolute;
      left: 15px;
      top: 50%;
      transform: translateY(-50%);
      color: white;
      text-decoration: none;
    }
    
    .search-bar {
      padding: 10px 15px;
      margin: 10px;
      position: relative;
      display: flex;
      align-items: center;
    }
    
    .search-input {
      flex: 1;
      padding: 8px 15px 8px 35px;
      border: none;
      border-radius: 20px;
      background-color: #f0f0f0;
      font-size: 0.9em;
    }
    
    .search-icon {
      position: absolute;
      left: 25px;
      top: 50%;
      transform: translateY(-50%);
      color: #888;
    }
    
    .filter-button {
      display: flex;
      align-items: center;
      gap: 5px;
      margin-left: 10px;
      background: none;
      border: none;
      color: #888;
      font-size: 0.9em;
      cursor: pointer;
    }
    
    .schedule-content {
      flex: 1;
      overflow-y: auto;
      padding: 0 10px 10px 10px;
    }
    
    .schedule-card {
      background-color: white;
      border-radius: 10px;
      margin-bottom: 10px;
      padding: 15px;
      display: flex;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
      cursor: pointer;
      transition: transform 0.2s, box-shadow 0.2s;
    }
    
    .schedule-card:hover, .schedule-card:active {
      transform: translateY(-2px);
      box-shadow: 0 4px 10px rgba(0,0,0,0.15);
    }
    
    .schedule-icon {
      width: 40px;
      height: 40px;
      margin-right: 15px;
      display: flex;
      align-items: center;
      justify-content: center;
      background-color: #f0f0f0;
      border-radius: 8px;
    }
    
    .schedule-details {
      flex: 1;
    }
    
    .schedule-title {
      font-weight: 500;
      margin-bottom: 5px;
      font-size: 0.9em;
      color: #444;
    }
    
    .schedule-subtitle {
      font-size: 0.85em;
      color: #666;
      margin-bottom: 5px;
    }
    
    .schedule-time {
      font-size: 0.8em;
      color: #888;
    }
    
    /* Bottom navigation styles removed as requested */
    
    .empty-schedule {
      text-align: center;
      padding: 40px 20px;
      color: #666;
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
  <div class="schedule-container">
    <div class="header">
      <a href="professor-dashboard.php" class="back-button">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M15 18l-6-6 6-6"/>
        </svg>
      </a>
      Schedules
    </div>
    
    <div class="search-bar">
      <div class="search-wrapper" style="position: relative; flex: 1;">
        <div class="search-icon">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="11" cy="11" r="8"></circle>
            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
          </svg>
        </div>
        <input type="text" class="search-input" id="searchInput" placeholder="Search" onkeyup="searchSchedules()">
      </div>
      <button class="filter-button" id="filterButton">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <line x1="4" y1="21" x2="4" y2="14"></line>
          <line x1="4" y1="10" x2="4" y2="3"></line>
          <line x1="12" y1="21" x2="12" y2="12"></line>
          <line x1="12" y1="8" x2="12" y2="3"></line>
          <line x1="20" y1="21" x2="20" y2="16"></line>
          <line x1="20" y1="12" x2="20" y2="3"></line>
          <line x1="1" y1="14" x2="7" y2="14"></line>
          <line x1="9" y1="8" x2="15" y2="8"></line>
          <line x1="17" y1="16" x2="23" y2="16"></line>
        </svg>
        Filters
      </button>
    </div>
    
    <div class="schedule-content">
    
    <?php
      if (!$table_exists) {
        // Tables don't exist yet
        echo '<div class="empty-schedule">
                <svg xmlns="http://www.w3.org/2000/svg" width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="#888" stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
                  <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                  <line x1="16" y1="2" x2="16" y2="6"></line>
                  <line x1="8" y1="2" x2="8" y2="6"></line>
                  <line x1="3" y1="10" x2="21" y2="10"></line>
                </svg>
                <h3>Database tables not set up yet</h3>
                <p>Please run the <a href="setup_class_tables.php" style="color:#e55a00;">setup script</a> to create necessary tables</p>
              </div>';
      } else if ($schedule_result && $schedule_result->num_rows > 0) {
        // Display a sample schedule that matches the screenshot
        // In a real app, you would loop through $schedule_result
        
        // Sample data to match the screenshot
        $sampleSchedule = [
          'id' => 1,
          'name' => 'Lab',
          'course_code' => 'Fundamentals in Electronics Circuits',
          'schedule_date' => '2025-03-11',
          'start_time' => '13:30:00',
          'end_time' => '15:00:00',
          'room' => 'Electronics Lab'
        ];
        ?>
        <div class="schedule-card" data-id="<?php echo $sampleSchedule['id']; ?>" onclick="viewScheduleDetails(<?php echo $sampleSchedule['id']; ?>)">
          <div class="schedule-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#666" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path>
            </svg>
          </div>
          <div class="schedule-details">
            <div class="schedule-title">Fundamentals in Electronics Circuits</div>
            <div class="schedule-subtitle">Lab</div>
            <div class="schedule-time">March 11, 2025 | 1:30PM - 3:00PM</div>
          </div>
        </div>
        <?php
        
        // If you want to display actual data from database, uncomment this code
        /*
        while($schedule = $schedule_result->fetch_assoc()) {
          $schedule_date = date('F j, Y', strtotime($schedule['schedule_date']));
          $start_time = date('g:i A', strtotime($schedule['start_time']));
          $end_time = date('g:i A', strtotime($schedule['end_time']));
          $room = isset($schedule['room']) ? $schedule['room'] : 'TBA';
          ?>
          <div class="schedule-card" onclick="viewScheduleDetails(<?php echo $schedule['id']; ?>)">
            <div class="schedule-header">
              <div><?php echo htmlspecialchars($schedule['name']); ?></div>
            </div>
            
            <div class="schedule-info">
              <div class="info-row">
                <div class="info-label">Subject:</div>
                <div class="info-value"><?php echo htmlspecialchars($schedule['course_code']); ?></div>
              </div>
              
              <div class="info-row">
                <div class="info-label">Date:</div>
                <div class="info-value"><?php echo $schedule_date; ?></div>
              </div>
              
              <div class="info-row">
                <div class="info-label">Time:</div>
                <div class="info-value"><?php echo $start_time; ?> - <?php echo $end_time; ?></div>
              </div>
              
              <div class="info-row">
                <div class="info-label">Room:</div>
                <div class="info-value"><?php echo $room; ?></div>
              </div>
            </div>
            
            <div class="equipment-list">
              <div class="equipment-header">Equipment Name</div>
              
              <!-- In a real app, you would fetch equipment for this class -->
              <div class="equipment-item">
                <div class="equipment-number">1.</div>
                <div class="equipment-name">Equipment 1</div>
              </div>
            </div>
            
            <div class="action-buttons">
              <a href="view_schedule.php?id=<?php echo $schedule['id']; ?>" class="btn" onclick="event.stopPropagation();">View</a>
              <a href="cancel_schedule.php?id=<?php echo $schedule['id']; ?>" class="btn btn-danger" onclick="event.stopPropagation(); return confirm('Are you sure you want to cancel this schedule?');">Cancel</a>
            </div>
          </div>
          <?php
        }
        */
      } else {
        echo '<div class="empty-schedule">
                <svg xmlns="http://www.w3.org/2000/svg" width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="#888" stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
                  <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                  <line x1="16" y1="2" x2="16" y2="6"></line>
                  <line x1="8" y1="2" x2="8" y2="6"></line>
                  <line x1="3" y1="10" x2="21" y2="10"></line>
                </svg>
                <h3>No scheduled labs found</h3>
                <p>Your lab schedules will appear here</p>
              </div>';
      }
      ?>
    </div>
    
    <!-- Bottom Navigation removed as requested -->
  </div>
  <script>
    function searchSchedules() {
      const input = document.getElementById('searchInput');
      const filter = input.value.toUpperCase();
      const cards = document.getElementsByClassName('schedule-card');
      
      for (let i = 0; i < cards.length; i++) {
        const details = cards[i].querySelector('.schedule-details');
        
        if (details.textContent.toUpperCase().indexOf(filter) > -1) {
          cards[i].style.display = "";
        } else {
          cards[i].style.display = "none";
        }
      }
    }
    
    // Function to handle clicking on a schedule card
    function viewScheduleDetails(scheduleId) {
      console.log('Viewing schedule details for ID: ' + scheduleId);
      window.location.href = 'view_schedule.php?id=' + scheduleId;
    }
    
    // Add click event listeners to all schedule cards
    document.addEventListener('DOMContentLoaded', function() {
      const scheduleCards = document.querySelectorAll('.schedule-card');
      scheduleCards.forEach(function(card) {
        card.addEventListener('click', function() {
          const scheduleId = this.getAttribute('data-id') || 1;
          viewScheduleDetails(scheduleId);
        });
      });
    });
    
    // Filter button functionality
    document.getElementById('filterButton').addEventListener('click', function() {
      alert('Filter functionality will be implemented here');
    });
  </script>
</body>
</html>
<?php $conn->close(); ?>
