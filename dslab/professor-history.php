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

// Sample class history data - in a real app, this would come from the database
$classes = [
  [
    'code' => 'EE101',
    'name' => 'Fundamentals to Electronics Circuits',
    'section' => 'Section A'
  ],
  [
    'code' => 'EE102',
    'name' => 'Fundamentals to Electronics Circuits',
    'section' => 'Section B'
  ],
  [
    'code' => 'EE201',
    'name' => 'Digital Electronics',
    'section' => 'Section A'
  ],
  [
    'code' => 'EE202',
    'name' => 'Digital Electronics',
    'section' => 'Section B'
  ],
  [
    'code' => 'EE301',
    'name' => 'Microprocessors and Microcontrollers',
    'section' => 'Section A'
  ],
  [
    'code' => 'EE302',
    'name' => 'Microprocessors and Microcontrollers',
    'section' => 'Section B'
  ],
  [
    'code' => 'EE401',
    'name' => 'Communication Systems',
    'section' => 'Section A'
  ],
  [
    'code' => 'EE402',
    'name' => 'Communication Systems',
    'section' => 'Section B'
  ]
];

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Professor History</title>
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
    
    .history-container {
      padding: 0;
      width: calc(100% - 160px); /* Full width minus sidebar with extra margin */
      padding-bottom: 60px;
      padding-top: 20px; /* Reduced padding since logo is now in the flow */
      flex-grow: 1;
      margin-left: 160px; /* Increased space for sidenav */
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: flex-start;
      background-color: #f0f0f0; /* Light gray background */
      position: relative;
      overflow-y: auto; /* Allow container to scroll if needed */
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
    
    .history-card {
      background-color: #ff7f1a;
      border-radius: 10px;
      padding: 15px;
      width: 350px;
      max-width: 90%;
      position: relative;
      z-index: 5;
      margin-top: 20px;
      display: flex;
      flex-direction: column;
    }
    
    .search-input {
      display: flex;
      background-color: #fff;
      border-radius: 20px;
      padding: 8px 15px;
      margin-bottom: 10px;
      align-items: center;
    }
    
    .search-input input {
      border: none;
      outline: none;
      width: 100%;
      background: transparent;
      margin-left: 8px;
      font-size: 14px;
    }
    
    .search-input i {
      color: #888;
    }
    
    .filter-options {
      display: flex;
      justify-content: center;
      align-items: center;
      margin-bottom: 15px;
    }
    
    .filter-button {
      display: flex;
      align-items: center;
      background-color: transparent;
      border: none;
      color: #333;
      font-size: 14px;
      cursor: pointer;
    }
    
    .filter-button i {
      margin-right: 5px;
    }
    
    .class-list {
      margin-top: 10px;
      width: 100%;
      max-height: calc(100vh - 300px);
      overflow-y: auto;
      padding-right: 5px;
    }
    
    .class-item {
      display: flex;
      align-items: center;
      background-color: #fff;
      padding: 12px;
      margin-bottom: 2px;
      width: 100%;
      border-bottom: 1px solid #e0e0e0;
    }
    
    .class-icon {
      width: 24px;
      height: 24px;
      border-radius: 50%;
      background-color: #f0f0f0;
      display: flex;
      justify-content: center;
      align-items: center;
      margin-right: 12px;
      flex-shrink: 0;
    }
    
    .class-content {
      flex-grow: 1;
    }
    
    .class-name {
      font-size: 14px;
      font-weight: bold;
      margin-bottom: 4px;
      color: #333;
    }
    
    .class-section {
      font-size: 12px;
      color: #666;
    }
    
    .bottom-nav {
      display: none; /* Hide bottom nav */
      position: fixed;
      bottom: 0;
      left: 0;
      width: 100%;
      background-color: #fff;
      box-shadow: 0 -2px 5px rgba(0,0,0,0.1);
      padding: 10px 0;
      z-index: 1000;
      justify-content: space-around;
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
      color: #ff7f1a;
    }
    
    .nav-icon {
      margin-bottom: 4px;
      width: 24px;
      height: 24px;
      display: flex;
      justify-content: center;
      align-items: center;
    }
    
    @media (max-width: 768px) {
      .history-container {
        padding-top: 20px;
        margin-left: 0;
        width: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
      }
      
      .sidenav {
        background-color: #e55a00 !important;
        width: 120px !important;
      }
      
      .logo {
        width: 130px;
        height: 70px;
        margin: 15px auto 20px auto;
      }
      
      .history-card {
        width: 90%;
        max-width: 350px;
        margin-left: auto;
        margin-right: auto;
      }
      
      .class-list {
        width: 100%;
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
      <li><a class="active" href="professor-history.php"><span class="icon"><?php include 'icons/clock.svg'; ?></span> History</a></li>
      <li><a href="professor-dashboard.php"><span class="icon"><?php include 'icons/home.svg'; ?></span> Home</a></li>
      <li><a href="professor-profile.php"><span class="icon"><?php include 'icons/profile.svg'; ?></span> Profile</a></li>
      <li><a href="professor-more.php"><span class="icon"><?php include 'icons/more.svg'; ?></span> More</a></li>
    </ul>
  </div>
  
  <!-- Main Content -->
  <div class="history-container">
    <div class="logo" id="dsLabLogo">
      <?php include 'icons/dsB.svg'; ?>
    </div>
    
    <div class="history-card">
      <div class="search-input">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="#888">
          <path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/>
        </svg>
        <input type="text" name="search" placeholder="Search">
      </div>
      
      <div class="filter-options">
        <button type="button" class="filter-button">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="#333">
            <path d="M10 18h4v-2h-4v2zM3 6v2h18V6H3zm3 7h12v-2H6v2z"/>
          </svg>
          Filters
        </button>
      </div>
      
      <div class="class-list">
        <?php foreach ($classes as $class): ?>
          <div class="class-item">
            <div class="class-icon">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="#444">
                <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
              </svg>
            </div>
            <div class="class-content">
              <div class="class-name"><?php echo htmlspecialchars($class['code']); ?></div>
              <div class="class-section"><?php echo htmlspecialchars($class['name']); ?></div>
            </div>
          </div>
        <?php endforeach; ?>
        
        <?php if (empty($classes)): ?>
          <div class="class-item">
            <div class="class-content">
              <div class="class-name">No classes found.</div>
            </div>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
  
  <!-- Bottom Navigation (hidden by default) -->
  <div class="bottom-nav">
    <a href="professor-notifications.php" class="nav-item">
      <div class="nav-icon">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
          <path d="M12 22c1.1 0 2-.9 2-2h-4c0 1.1.89 2 2 2zm6-6v-5c0-3.07-1.64-5.64-4.5-6.32V4c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5v.68C7.63 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2z"/>
        </svg>
      </div>
      <span>Notifications</span>
    </a>
    <a href="professor-history.php" class="nav-item active">
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
    <a href="professor-more.php" class="nav-item">
      <div class="nav-icon">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
          <path d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"/>
        </svg>
      </div>
      <span>More</span>
    </a>
  </div>
  
  <script>
    // Toggle menu for mobile
    document.getElementById('menuToggle').addEventListener('click', function() {
      document.getElementById('sideNav').classList.toggle('active');
    });
  </script>
</body>
</html>
