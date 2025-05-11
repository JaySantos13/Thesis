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
  // Process the direct request
  // This would be implemented based on your application's requirements
  $success_message = "Request submitted successfully!";
}

// Check if equipment table exists
$check_equipment = $conn->query("SHOW TABLES LIKE 'equipment'");
$equipment_exists = $check_equipment->num_rows > 0;

// Get equipment list if table exists
$equipment_list = [];
if ($equipment_exists) {
  $equipment_sql = "SELECT id, name FROM equipment ORDER BY name";
  $equipment_result = $conn->query($equipment_sql);
  
  if ($equipment_result && $equipment_result->num_rows > 0) {
    while($row = $equipment_result->fetch_assoc()) {
      $equipment_list[] = $row;
    }
  }
} else {
  // Default equipment if table doesn't exist
  $equipment_list = [
    ['id' => 1, 'name' => 'Oscilloscope'],
    ['id' => 2, 'name' => 'Multimeter'],
    ['id' => 3, 'name' => 'Power Supply'],
    ['id' => 4, 'name' => 'Function Generator'],
    ['id' => 5, 'name' => 'Logic Analyzer']
  ];
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Direct Request - Professor</title>
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
      overflow-y: auto;
      box-sizing: border-box;
    }
    
    *, *:before, *:after {
      box-sizing: inherit;
    }
    
    .dashboard-container {
      padding: 20px;
      margin: 0;
      max-width: 100%;
      width: calc(100% - 120px);
      margin-left: 120px;
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
      overflow: hidden;
      box-sizing: border-box;
    }
    
    @media screen and (max-width: 768px) {
      .dashboard-container {
        width: 100%;
        margin-left: 0;
        padding: 70px 10px 20px;
        overflow-x: hidden;
      }
    }
    
    /* Webkit scrollbar styling for body */
    body::-webkit-scrollbar {
      width: 10px;
    }
    
    body::-webkit-scrollbar-track {
      background: #f1f1f1;
      border-radius: 5px;
    }
    
    body::-webkit-scrollbar-thumb {
      background-color: rgba(229, 90, 0, 0.5);
      border-radius: 5px;
    }
    
    body::-webkit-scrollbar-thumb:hover {
      background-color: rgba(229, 90, 0, 0.7);
    }
    
    .form-layout {
      display: flex;
      flex-direction: column;
      align-items: center;
      width: 100%;
      max-width: 500px;
      margin: 0 auto;
      max-height: 90vh;
      overflow: hidden;
      border-radius: 15px;
      box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
      position: relative;
    }
    
    .logo-container {
      margin: 20px 0;
      text-align: center;
    }
    
    .logo-container svg {
      max-width: 150px;
      height: auto;
    }
    
    .form-header {
      background-color: #e55a00;
      color: white;
      text-align: center;
      padding: 15px;
      width: 100%;
      border-top-left-radius: 15px;
      border-top-right-radius: 15px;
      border: 1px solid rgba(0, 0, 0, 0.1);
      border-bottom: none;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
      position: relative;
      z-index: 1;
    }
    
    .form-header h2 {
      margin: 0;
      font-size: 18px;
      font-weight: 500;
      letter-spacing: 0.5px;
    }
    
    .form-container {
      background-color: #f8f9fa;
      padding: 20px 15px 80px 15px;
      width: 100%;
      max-height: 70vh;
      overflow-y: auto;
      overflow-x: hidden;
      position: relative;
      border-bottom-left-radius: 15px;
      border-bottom-right-radius: 15px;
      border: 1px solid rgba(0, 0, 0, 0.1);
      border-top: none;
      display: flex;
      flex-direction: column;
      align-items: center;
    }
    
    .search-container {
      margin-bottom: 15px;
      position: relative;
      padding: 0 5px;
      width: 100%;
    }
    
    .search-input {
      width: 100%;
      padding: 12px 15px 12px 45px;
      border: 1px solid rgba(0, 0, 0, 0.1);
      border-radius: 25px;
      background-color: white;
      box-sizing: border-box;
      font-size: 15px;
      height: 50px;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
      transition: all 0.3s ease;
      color: #444;
    }
    
    .search-input:focus {
      outline: none;
      box-shadow: 0 3px 8px rgba(0, 0, 0, 0.2);
    }
    
    .search-icon {
      position: absolute;
      left: 15px;
      top: 50%;
      transform: translateY(-50%);
      color: #999;
      font-size: 18px;
    }
    
    .equipment-list {
      display: flex;
      flex-direction: column;
      gap: 10px;
      width: 100%;
      max-width: 100%;
      padding: 0;
      overflow-x: hidden;
      margin-bottom: 20px;
    }
    
    .equipment-item {
      background-color: white;
      border-radius: 12px;
      padding: 16px 15px;
      margin-bottom: 0;
      display: flex;
      align-items: center;
      justify-content: space-between;
      height: auto;
      min-height: 60px;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
      transition: all 0.2s ease;
      border: 1px solid rgba(0, 0, 0, 0.05);
      width: 100%;
      box-sizing: border-box;
      overflow: hidden;
    }
    
    .equipment-item:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
      border-color: rgba(229, 90, 0, 0.2);
    }
    
    .equipment-info {
      display: flex;
      align-items: center;
      flex: 1;
      margin-right: 15px;
    }
    
    .equipment-icon {
      width: 40px;
      height: 40px;
      margin-right: 15px;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #e55a00;
      background-color: rgba(229, 90, 0, 0.1);
      border-radius: 8px;
      padding: 8px;
    }
    
    .equipment-name {
      font-size: 16px;
      flex: 1;
      font-weight: 500;
      color: #444;
    }
    
    .quantity-controls {
      display: flex;
      align-items: center;
      gap: 2px;
      background-color: #f8f9fa;
      border-radius: 12px;
      padding: 2px;
      border: 1px solid rgba(0, 0, 0, 0.05);
      min-width: 80px;
      justify-content: center;
    }
    
    .quantity-btn {
      width: 24px;
      height: 24px;
      border-radius: 50%;
      border: none;
      background-color: #f5f5f5;
      color: #444;
      font-size: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      transition: all 0.2s ease;
      box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
      padding: 0;
    }
    
    .minus-btn {
      background-color: #fff0ee;
      color: #e55a00;
    }
    
    .plus-btn {
      background-color: #f0f8e8;
      color: #4caf50;
    }
    
    .minus-btn:hover {
      background-color: #e55a00;
      color: white;
    }
    
    .plus-btn:hover {
      background-color: #4caf50;
      color: white;
    }
    
    .minus-icon, .plus-icon {
      stroke-width: 1.5;
    }
    
    .quantity-input {
      width: 22px;
      text-align: center;
      border: none;
      background: white;
      font-size: 12px;
      font-weight: 600;
      color: #444;
      border-radius: 3px;
      padding: 1px 0;
      margin: 0 1px;
      height: 20px;
    }
    
    .quantity-input::-webkit-inner-spin-button,
    .quantity-input::-webkit-outer-spin-button {
      -webkit-appearance: none;
      margin: 0;
    }
    
    .quantity-input:focus {
      outline: none;
    }
    
    .bottom-nav {
      display: flex;
      justify-content: space-around;
      background-color: white;
      padding: 10px 0;
      border-top: 1px solid #eee;
      margin-top: 20px;
      border-radius: 0 0 10px 10px;
    }
    
    .nav-item {
      display: flex;
      flex-direction: column;
      align-items: center;
      font-size: 12px;
      color: #666;
      text-decoration: none;
    }
    
    .nav-icon {
      margin-bottom: 5px;
    }
    
    .cart-btn {
      position: fixed;
      bottom: 30px;
      right: calc(50% - 250px + 30px);
      width: 56px;
      height: 56px;
      border-radius: 50%;
      background-color: #e55a00;
      color: white;
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 4px 10px rgba(229, 90, 0, 0.3);
      transition: all 0.3s ease;
      border: none;
      cursor: pointer;
      z-index: 100;
      margin: 0;
    }
    
    @media (max-width: 768px) {
      .cart-btn {
        right: 20px;
        bottom: 20px;
      }
    }
    
    .cart-btn:hover {
      transform: scale(1.05);
      background-color: #ff7f1a;
      box-shadow: 0 6px 15px rgba(229, 90, 0, 0.4);
    }
    
    .cart-badge {
      position: absolute;
      top: -8px;
      right: -8px;
      background-color: #ff3b30;
      color: white;
      font-size: 14px;
      width: 26px;
      height: 26px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: bold;
      z-index: 11;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
      border: 2px solid white;
    }
    
    .cart-modal {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.6);
      display: none;
      justify-content: center;
      align-items: center;
      z-index: 1000;
      backdrop-filter: blur(3px);
      -webkit-backdrop-filter: blur(3px);
    }
    
    .cart-content {
      background-color: white;
      width: 90%;
      max-width: 400px;
      border-radius: 15px;
      padding: 25px;
      position: relative;
      max-height: 80vh;
      overflow-y: auto;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
      animation: modalFadeIn 0.3s ease-out;
      border: 1px solid rgba(0, 0, 0, 0.1);
    }
    
    @keyframes modalFadeIn {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }
    
    .cart-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
      border-bottom: 1px solid #f0f0f0;
      padding-bottom: 15px;
    }
    
    .cart-header h3 {
      margin: 0;
      font-size: 20px;
      color: #444;
    }
    
    .close-cart-btn {
      background: #f5f5f5;
      border: none;
      font-size: 18px;
      cursor: pointer;
      position: absolute;
      right: 15px;
      top: 15px;
      width: 36px;
      height: 36px;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 0;
      line-height: 1;
      border-radius: 50%;
      color: #666;
      transition: all 0.2s ease;
    }
    
    .close-cart-btn:hover {
      background: #e55a00;
      color: white;
    }
    
    .cart-items {
      margin-bottom: 25px;
      max-height: 300px;
      overflow-y: auto;
      padding-right: 5px;
    }
    
    .cart-items::-webkit-scrollbar {
      width: 5px;
    }
    
    .cart-items::-webkit-scrollbar-track {
      background: #f1f1f1;
      border-radius: 5px;
    }
    
    .cart-items::-webkit-scrollbar-thumb {
      background-color: rgba(229, 90, 0, 0.5);
      border-radius: 5px;
    }
    
    .empty-cart-message {
      text-align: center;
      padding: 20px 0;
      color: #999;
      font-style: italic;
    }
    
    .cart-item {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 15px 0;
      border-bottom: 1px solid #f0f0f0;
      transition: background-color 0.2s ease;
    }
    
    .cart-item:hover {
      background-color: #f9f9f9;
    }
    
    .cart-item-name {
      font-size: 16px;
      font-weight: 500;
      color: #444;
    }
    
    .cart-item-quantity {
      font-size: 15px;
      color: #e55a00;
      font-weight: 600;
      background-color: rgba(229, 90, 0, 0.1);
      padding: 5px 10px;
      border-radius: 15px;
    }
    
    .cart-actions {
      display: flex;
      justify-content: space-between;
      gap: 15px;
      margin-top: 10px;
    }
    
    .cart-btn-action {
      flex: 1;
      padding: 12px 15px;
      border-radius: 8px;
      border: none;
      font-size: 16px;
      cursor: pointer;
      font-weight: 500;
      transition: all 0.2s ease;
    }
    
    .clear-btn {
      background-color: #f5f5f5;
      color: #666;
    }
    
    .clear-btn:hover {
      background-color: #e0e0e0;
    }
    
    .submit-btn {
      background-color: #e55a00;
      color: white;
      box-shadow: 0 2px 5px rgba(229, 90, 0, 0.3);
    }
    
    .submit-btn:hover {
      background-color: #ff7f1a;
      box-shadow: 0 4px 8px rgba(229, 90, 0, 0.4);
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
        <h2>Equipment</h2>
      </div>
      
      <!-- Form Container -->    
      <div class="form-container">
        <?php if (!empty($success_message)): ?>
          <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        
        <?php if (!empty($error_message)): ?>
          <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>
        
        <!-- Search Bar -->
        <div class="search-container">
          <input type="text" class="search-input" id="searchInput" placeholder="Search equipment...">
          <span class="search-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="11" cy="11" r="8"></circle>
              <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
          </span>
        </div>
        
        <!-- Equipment List -->
        <div class="equipment-list" id="equipmentList">
          <?php foreach ($equipment_list as $equipment): ?>
          <div class="equipment-item">
            <div class="equipment-info">
              <span class="equipment-icon">
                <?php if (strpos(strtolower($equipment['name']), 'printer') !== false): ?>
                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="6 9 6 2 18 2 18 9"></polyline>
                    <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                    <rect x="6" y="14" width="12" height="8"></rect>
                  </svg>
                <?php elseif (strpos(strtolower($equipment['name']), 'kit') !== false): ?>
                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 8h1a4 4 0 0 1 0 8h-1"></path>
                    <path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"></path>
                    <line x1="6" y1="1" x2="6" y2="4"></line>
                    <line x1="10" y1="1" x2="10" y2="4"></line>
                    <line x1="14" y1="1" x2="14" y2="4"></line>
                  </svg>
                <?php elseif (strpos(strtolower($equipment['name']), 'scope') !== false): ?>
                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
                    <line x1="8" y1="21" x2="16" y2="21"></line>
                    <line x1="12" y1="17" x2="12" y2="21"></line>
                  </svg>
                <?php else: ?>
                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="2" y="7" width="20" height="15" rx="2" ry="2"></rect>
                    <polyline points="17 2 12 7 7 2"></polyline>
                  </svg>
                <?php endif; ?>
              </span>
              <span class="equipment-name"><?php echo htmlspecialchars($equipment['name']); ?></span>
            </div>
            <div class="quantity-controls">
              <button class="quantity-btn minus-btn" onclick="decrementQuantity(this)" aria-label="Decrease quantity">
                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="minus-icon">
                  <circle cx="12" cy="12" r="10"></circle>
                  <line x1="8" y1="12" x2="16" y2="12"></line>
                </svg>
              </button>
              <input type="text" class="quantity-input" value="0" readonly>
              <button class="quantity-btn plus-btn" onclick="incrementQuantity(this)" aria-label="Increase quantity">
                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="plus-icon">
                  <circle cx="12" cy="12" r="10"></circle>
                  <line x1="12" y1="8" x2="12" y2="16"></line>
                  <line x1="8" y1="12" x2="16" y2="12"></line>
                </svg>
              </button>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
      
      <!-- Floating Action Button (Shopping Cart) -->
      <button class="cart-btn" id="openCartBtn" title="View Cart">
        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="white" style="filter: drop-shadow(0px 1px 1px rgba(0,0,0,0.3));">
          <path d="M7 18c-1.1 0-1.99.9-1.99 2S5.9 22 7 22s2-.9 2-2-.9-2-2-2zM1 2v2h2l3.6 7.59-1.35 2.45c-.16.28-.25.61-.25.96 0 1.1.9 2 2 2h12v-2H7.42c-.14 0-.25-.11-.25-.25l.03-.12.9-1.63h7.45c.75 0 1.41-.41 1.75-1.03l3.58-6.49c.08-.14.12-.31.12-.48 0-.55-.45-1-1-1H5.21l-.94-2H1zm16 16c-1.1 0-1.99.9-1.99 2s.89 2 1.99 2 2-.9 2-2-.9-2-2-2z"/>
        </svg>
        <span class="cart-badge" id="cartBadge">0</span>
      </button>
      
      <!-- Shopping Cart Modal -->
      <div class="cart-modal" id="cartModal">
        <div class="cart-content">
          <div class="cart-header">
            <h3>Equipment Request Cart</h3>
            <button class="close-cart-btn" id="closeCartBtn" aria-label="Close">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
              </svg>
            </button>
          </div>
          <div class="cart-items" id="cartItems">
            <!-- Cart items will be added here dynamically -->
            <div class="empty-cart-message">Your cart is empty</div>
          </div>
          <div class="cart-actions">
            <button class="cart-btn-action clear-btn" id="clearCartBtn">Clear All</button>
            <button class="cart-btn-action submit-btn" id="submitCartBtn">Submit Request</button>
          </div>
        </div>
      </div>
    </div>
  </div>
  
  <!-- JavaScript for menu toggle and equipment functionality -->    
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const menuToggle = document.getElementById('menuToggle');
      const sideNav = document.getElementById('sideNav');
      const cartBadge = document.getElementById('cartBadge');
      const cartModal = document.getElementById('cartModal');
      const cartItems = document.getElementById('cartItems');
      const openCartBtn = document.getElementById('openCartBtn');
      const closeCartBtn = document.getElementById('closeCartBtn');
      const clearCartBtn = document.getElementById('clearCartBtn');
      const submitCartBtn = document.getElementById('submitCartBtn');
      
      // Cart data
      let cart = [];
      
      // Add overlay for sidenav on mobile
      const body = document.body;
      let sidenavOverlay = document.createElement('div');
      sidenavOverlay.className = 'sidenav-overlay';
      body.appendChild(sidenavOverlay);
      
      menuToggle.addEventListener('click', function() {
        sideNav.classList.toggle('active');
        sidenavOverlay.classList.toggle('active');
      });
      
      // Close menu when clicking overlay
      sidenavOverlay.addEventListener('click', function() {
        sideNav.classList.remove('active');
        sidenavOverlay.classList.remove('active');
      });
      
      // Close menu when clicking outside on small screens
      document.addEventListener('click', function(event) {
        const isSmallScreen = window.matchMedia('(max-width: 768px)').matches;
        if (isSmallScreen && !sideNav.contains(event.target) && !menuToggle.contains(event.target)) {
          sideNav.classList.remove('active');
          sidenavOverlay.classList.remove('active');
        }
      });
      
      // Search functionality
      const searchInput = document.getElementById('searchInput');
      const equipmentList = document.getElementById('equipmentList');
      const equipmentItems = equipmentList.getElementsByClassName('equipment-item');
      
      searchInput.addEventListener('keyup', function() {
        const searchTerm = searchInput.value.toLowerCase();
        
        for (let i = 0; i < equipmentItems.length; i++) {
          const equipmentName = equipmentItems[i].querySelector('.equipment-name').textContent.toLowerCase();
          
          if (equipmentName.includes(searchTerm)) {
            equipmentItems[i].style.display = '';
          } else {
            equipmentItems[i].style.display = 'none';
          }
        }
      });
      
      // Quantity change handlers
      const quantityInputs = document.querySelectorAll('.quantity-input');
      quantityInputs.forEach(input => {
        input.addEventListener('change', updateCart);
      });
      
      // Open cart modal
      openCartBtn.addEventListener('click', function(event) {
        event.preventDefault();
        event.stopPropagation();
        updateCartDisplay();
        cartModal.style.display = 'flex';
        
        // Prevent scrolling on the body when modal is open
        document.body.style.overflow = 'hidden';
      });
      
      // Close cart modal
      closeCartBtn.addEventListener('click', function() {
        cartModal.style.display = 'none';
        
        // Re-enable scrolling on the body
        document.body.style.overflow = '';
      });
      
      // Close modal when clicking outside
      cartModal.addEventListener('click', function(event) {
        if (event.target === cartModal) {
          cartModal.style.display = 'none';
          
          // Re-enable scrolling on the body
          document.body.style.overflow = '';
        }
      });
      
      // Clear cart
      clearCartBtn.addEventListener('click', function() {
        cart = [];
        updateCartBadge();
        updateCartDisplay();
        
        // Reset all quantity inputs
        quantityInputs.forEach(input => {
          input.value = '0';
        });
      });
      
      // Submit cart
      submitCartBtn.addEventListener('click', function() {
        if (cart.length === 0) {
          alert('Your cart is empty. Please add items before submitting.');
          return;
        }
        
        // Here you would normally submit the form with cart data
        alert('Request submitted successfully!');
        
        // Clear cart after submission
        cart = [];
        updateCartBadge();
        updateCartDisplay();
        
        // Reset all quantity inputs
        quantityInputs.forEach(input => {
          input.value = '0';
        });
        
        // Close modal
        cartModal.style.display = 'none';
        
        // Re-enable scrolling on the body
        document.body.style.overflow = '';
      });
      
      // Update cart when quantities change
      function updateCart() {
        cart = [];
        
        for (let i = 0; i < equipmentItems.length; i++) {
          const quantityInput = equipmentItems[i].querySelector('.quantity-input');
          const quantity = parseInt(quantityInput.value);
          
          if (quantity > 0) {
            const name = equipmentItems[i].querySelector('.equipment-name').textContent;
            cart.push({ name, quantity });
          }
        }
        
        updateCartBadge();
      }
      
      // Update cart badge count
      function updateCartBadge() {
        const totalItems = cart.reduce((total, item) => total + item.quantity, 0);
        cartBadge.textContent = totalItems;
        
        if (totalItems > 0) {
          cartBadge.style.display = 'flex';
        } else {
          cartBadge.style.display = 'none';
        }
      }
      
      // Update cart display in modal
      function updateCartDisplay() {
        if (cart.length === 0) {
          cartItems.innerHTML = '<div class="empty-cart-message">Your cart is empty</div>';
          return;
        }
        
        let cartHTML = '';
        cart.forEach(item => {
          cartHTML += `
            <div class="cart-item">
              <div class="cart-item-name">${item.name}</div>
              <div class="cart-item-quantity">${item.quantity}</div>
            </div>
          `;
        });
        
        cartItems.innerHTML = cartHTML;
      }
      
      // Initialize cart badge
      updateCartBadge();
    });
    
    function incrementQuantity(btn) {
      const input = btn.previousElementSibling;
      input.value = parseInt(input.value) + 1;
      
      // Trigger change event to update cart
      const event = new Event('change');
      input.dispatchEvent(event);
    }
    
    function decrementQuantity(btn) {
      const input = btn.nextElementSibling;
      const currentValue = parseInt(input.value);
      if (currentValue > 0) {
        input.value = currentValue - 1;
        
        // Trigger change event to update cart
        const event = new Event('change');
        input.dispatchEvent(event);
      }
    }
  </script>
</body>
</html>
