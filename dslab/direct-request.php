<?php
session_start();
include 'db.php';

$requests = [];
$sql = "SELECT * FROM requests ORDER BY schedule_date, start_time";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $requests[] = $row;
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Direct Request</title>
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="direct-request.css">
  <link rel="stylesheet" href="sidenav.css">
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
    
    /* Cart icon styling */
    .cart-icon-only {
      position: fixed;
      bottom: 20px;
      right: 20px;
      z-index: 100;
      transition: transform 0.2s;
      display: block;
      text-decoration: none;
    }
    
    .cart-icon-only:hover {
      transform: scale(1.1);
    }
    
    .cart-sidebar {
      position: fixed;
      top: 0;
      right: -320px;
      width: 320px;
      height: 100vh;
      background-color: #fff;
      box-shadow: -2px 0 10px rgba(0,0,0,0.1);
      z-index: 200;
      transition: right 0.3s ease-in-out;
      display: flex;
      flex-direction: column;
      overflow: hidden;
    }
    
    .cart-sidebar.active {
      right: 0;
    }
    
    .cart-header {
      padding: 20px;
      background-color: #444;
      color: white;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    
    .cart-header h2 {
      margin: 0;
      font-size: 1.2em;
      font-weight: 500;
    }
    
    .cart-close {
      background: none;
      border: none;
      color: white;
      font-size: 1.5em;
      cursor: pointer;
      padding: 0;
      margin: 0;
    }
    
    .cart-items {
      flex: 1;
      overflow-y: auto;
      padding: 15px;
    }
    
    .cart-item {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 12px 10px;
      border-bottom: 1px solid #eee;
    }
    
    .cart-item-title {
      font-weight: 500;
      color: #333;
      flex: 1;
    }
    
    .cart-item-qty-controls {
      display: flex;
      align-items: center;
      gap: 8px;
    }
    
    .cart-qty-btn {
      width: 24px;
      height: 24px;
      border-radius: 50%;
      background-color: #f0f0f0;
      border: none;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: bold;
      cursor: pointer;
      font-size: 14px;
      color: #444;
      transition: background-color 0.2s;
    }
    
    .cart-qty-btn:hover {
      background-color: #e0e0e0;
    }
    
    .cart-item-qty {
      background-color: #f0f0f0;
      padding: 4px 10px;
      border-radius: 12px;
      font-size: 0.9em;
      color: #444;
      min-width: 40px;
      text-align: center;
    }
    
    .cart-footer {
      padding: 20px;
      border-top: 1px solid #eee;
      display: flex;
      flex-direction: column;
      gap: 10px;
    }
    
    .cart-actions {
      display: flex;
      justify-content: space-between;
      margin-bottom: 10px;
    }
    
    .cart-clear {
      background-color: #f0f0f0;
      color: #444;
      border: none;
      padding: 8px 15px;
      border-radius: 8px;
      cursor: pointer;
      font-size: 0.9em;
      transition: background-color 0.2s;
    }
    
    .cart-clear:hover {
      background-color: #e0e0e0;
    }
    
    .cart-total {
      display: flex;
      align-items: center;
      font-weight: 500;
      gap: 10px;
    }
    
    .cart-total span:first-child {
      margin-right: 5px;
    }
    
    .cart-total .cart-clear {
      margin-left: auto;
    }
    
    .cart-checkout {
      background-color: #ff7f1a;
      color: white;
      border: none;
      padding: 12px;
      border-radius: 8px;
      font-weight: 500;
      cursor: pointer;
      transition: background-color 0.2s;
    }
    
    .cart-checkout:hover {
      background-color: #e55a00;
    }
    
    .cart-empty {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      height: 100%;
      color: #888;
      text-align: center;
      padding: 20px;
    }
    
    .cart-empty svg {
      width: 64px;
      height: 64px;
      margin-bottom: 15px;
      color: #ccc;
    }
    
    .overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0,0,0,0.5);
      z-index: 150;
      opacity: 0;
      visibility: hidden;
      transition: opacity 0.3s;
    }
    
    .overlay.active {
      opacity: 1;
      visibility: visible;
    }
    
    /* Cart button styles removed */
    
    .cart-count {
      position: absolute;
      top: -5px;
      right: -5px;
      background-color: #ff7f1a;
      color: white;
      border-radius: 50%;
      width: 22px;
      height: 22px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 12px;
      font-weight: bold;
      box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }
    
    .direct-card {
      background: #fff;
      border-radius: 18px;
      max-width: 420px;
      width: 100%;
      margin: 0 auto;
      box-shadow: 0 2px 12px rgba(0,0,0,0.1);
      padding: 15px;
      box-sizing: border-box;
      display: flex;
      flex-direction: column;
      max-height: calc(100vh - 80px);
      overflow: hidden;
      position: relative;
    }
    
    .direct-top-row {
      display: flex;
      align-items: center;
      margin-bottom: 5px;
      position: relative;
      width: 100%;
    }
    
    .direct-back {
      position: absolute;
      left: 0;
      top: 50%;
      transform: translateY(-50%);
      background: none;
      border: none;
      font-size: 24px;
      cursor: pointer;
      color: #444;
      width: 36px;
      height: 36px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: background 0.2s;
      z-index: 10;
    }
    
    .direct-back:hover {
      background: #f0f0f0;
    }
    
    .direct-logo-center {
      text-align: center;
      margin-bottom: 5px;
      position: relative;
      z-index: 5;
      width: 100%;
      padding-top: 0;
    }
    
    .direct-logo-center svg,
    .direct-logo-center img {
      max-width: 120px;
      height: auto;
    }
    
    .direct-list {
      overflow-y: auto;
      flex: 1;
      padding-bottom: 5px;
      scrollbar-width: thin;
      scrollbar-color: #ff7f1a #ffe5d0;
    }
    
    .direct-list::-webkit-scrollbar {
      width: 8px;
    }
    
    .direct-list::-webkit-scrollbar-track {
      background: #ffe5d0;
      border-radius: 10px;
    }
    
    .direct-list::-webkit-scrollbar-thumb {
      background-color: #ff7f1a;
      border-radius: 10px;
    }
    
    @media screen and (max-width: 768px) {
      .direct-card {
        border-radius: 12px;
        padding: 15px;
        max-height: calc(100vh - 100px);
      }
      
      .direct-logo-center svg,
      .direct-logo-center img {
        max-width: 100px;
      }
    }
    
    @media screen and (max-width: 480px) {
      .direct-card {
        border-radius: 0;
        max-width: 100%;
        padding: 12px;
      }
      
      .direct-search-row {
        flex-direction: column;
      }
      
      .direct-filter-btn {
        text-align: center;
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
      <li><a class="active" href="dashboard.php"><span class="icon"><?php include 'icons/home.svg'; ?></span> Home</a></li>
      <li><a href="profile.php"><span class="icon"><?php include 'icons/profile.svg'; ?></span> Profile</a></li>
      <li><a href="more.php"><span class="icon"><?php include 'icons/more.svg'; ?></span> More</a></li>
    </ul>
  </div>
  
  <div class="direct-card">
    <div class="direct-top-row">
      <button class="direct-back" onclick="window.location.href='dashboard.php'">&#8592;</button>
      <div class="direct-logo-center">
        <?php include 'icons/dsB.svg'; ?>
      </div>
    </div>
    <div class="direct-purple-header">Direct Request</div>
      <div class="direct-search-container">
        <div class="direct-search-row">
          <input type="text" placeholder="Search" class="direct-search-input">
          <button class="direct-filter-btn">Filters</button>
        </div>
      </div>
      <div class="direct-list">
        <?php
        $demo_items = [
          ["img" => "https://upload.wikimedia.org/wikipedia/commons/6/6e/Oscilloscope.jpg", "title" => "Oscilloscope"],
          ["img" => "https://upload.wikimedia.org/wikipedia/commons/3/3a/Digital_multimeter.jpg", "title" => "Multimeter"],
          ["img" => "https://upload.wikimedia.org/wikipedia/commons/6/6e/Oscilloscope.jpg", "title" => "Oscilloscope"],
          ["img" => "https://upload.wikimedia.org/wikipedia/commons/3/3a/Digital_multimeter.jpg", "title" => "Multimeter"]
        ];
        foreach ($demo_items as $item): ?>
          <div class="direct-item">
            <span class="direct-item-title"><?php echo $item['title']; ?></span>
            <div class="direct-qty">
              <button type="button" class="direct-qty-btn">-</button>
              <input type="text" class="direct-qty-input" value="0" readonly>
              <button type="button" class="direct-qty-btn">+</button>
            </div>
            <button class="direct-add-btn">Add</button>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
    
    <div class="overlay" id="overlay"></div>
    
    <div class="cart-sidebar" id="cartSidebar">
      <div class="cart-header">
        <h2>Your Cart</h2>
        <button class="cart-close" id="cartClose">&times;</button>
      </div>
      <div class="cart-items" id="cartItems">
        <div class="cart-empty" id="cartEmpty">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512" fill="currentColor">
            <path d="M0 24C0 10.7 10.7 0 24 0H69.5c22 0 41.5 12.8 50.6 32h411c26.3 0 45.5 25 38.6 50.4l-41 152.3c-8.5 31.4-37 53.3-69.5 53.3H170.7l5.4 28.5c2.2 11.3 12.1 19.5 23.6 19.5H488c13.3 0 24 10.7 24 24s-10.7 24-24 24H199.7c-34.6 0-64.3-24.6-70.7-58.5L77.4 54.5c-.7-3.8-4-6.5-7.9-6.5H24C10.7 48 0 37.3 0 24zM128 464a48 48 0 1 1 96 0 48 48 0 1 1 -96 0zm336-48a48 48 0 1 1 0 96 48 48 0 1 1 0-96z"/>
          </svg>
          <p>Your cart is empty</p>
          <p>Add some items to get started</p>
        </div>
      </div>
      <div class="cart-footer">
        <div class="cart-actions">
          <div class="cart-total">
            <span>Total Items:</span>
            <span id="cartTotalItems">0</span>
            <button class="cart-clear" id="cartClear">Clear All</button>
          </div>
        </div>
        <button class="cart-checkout" id="cartCheckout">Request</button>
      </div>
    </div>
    
    <a href="javascript:void(0)" id="cartButton" class="cart-icon-only">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512" fill="#ff7f1a" width="32" height="32">
        <path d="M0 24C0 10.7 10.7 0 24 0H69.5c22 0 41.5 12.8 50.6 32h411c26.3 0 45.5 25 38.6 50.4l-41 152.3c-8.5 31.4-37 53.3-69.5 53.3H170.7l5.4 28.5c2.2 11.3 12.1 19.5 23.6 19.5H488c13.3 0 24 10.7 24 24s-10.7 24-24 24H199.7c-34.6 0-64.3-24.6-70.7-58.5L77.4 54.5c-.7-3.8-4-6.5-7.9-6.5H24C10.7 48 0 37.3 0 24zM128 464a48 48 0 1 1 96 0 48 48 0 1 1 -96 0zm336-48a48 48 0 1 1 0 96 48 48 0 1 1 0-96z"/>
      </svg>
      <span class="cart-count" id="cartCount">0</span>
    </a>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const minusBtns = document.querySelectorAll('.direct-qty-btn:first-child');
      const plusBtns = document.querySelectorAll('.direct-qty-btn:last-of-type');
      const qtyInputs = document.querySelectorAll('.direct-qty-input');
      const addBtns = document.querySelectorAll('.direct-add-btn');
      const cartCount = document.getElementById('cartCount');
      const cartButton = document.getElementById('cartButton');
      
      let totalItems = 0;
      
      minusBtns.forEach((btn, index) => {
        btn.addEventListener('click', function() {
          let currentVal = parseInt(qtyInputs[index].value);
          if (currentVal > 0) {
            qtyInputs[index].value = currentVal - 1;
          }
        });
      });
      
      plusBtns.forEach((btn, index) => {
        btn.addEventListener('click', function() {
          let currentVal = parseInt(qtyInputs[index].value);
          qtyInputs[index].value = currentVal + 1;
        });
      });
      
      addBtns.forEach((btn, index) => {
        btn.addEventListener('click', function() {
          const quantity = parseInt(qtyInputs[index].value);
          if (quantity > 0) {
            const itemTitle = document.querySelectorAll('.direct-item-title')[index].textContent;
            
            // Check if item already exists in cart
            const existingItemIndex = cartItemsArray.findIndex(item => item.title === itemTitle);
            
            if (existingItemIndex !== -1) {
              // Update existing item
              cartItemsArray[existingItemIndex].quantity += quantity;
            } else {
              // Add new item
              cartItemsArray.push({
                title: itemTitle,
                quantity: quantity
              });
            }
            
            totalItems += quantity;
            cartCount.textContent = totalItems;
            
            // Visual feedback
            btn.textContent = 'Added';
            btn.style.backgroundColor = '#4CAF50';
            
            // Reset after a moment
            setTimeout(() => {
              btn.textContent = 'Add';
              btn.style.backgroundColor = '';
              qtyInputs[index].value = 0;
            }, 1000);
            
            // Animation for cart button
            cartButton.style.transform = 'scale(1.2)';
            setTimeout(() => {
              cartButton.style.transform = '';
            }, 200);
          }
        });
      });
      
      // Cart sidebar functionality
      const cartSidebar = document.getElementById('cartSidebar');
      const cartClose = document.getElementById('cartClose');
      const overlay = document.getElementById('overlay');
      const cartItems = document.getElementById('cartItems');
      const cartEmpty = document.getElementById('cartEmpty');
      const cartTotalItems = document.getElementById('cartTotalItems');
      const cartCheckout = document.getElementById('cartCheckout');
      const cartClear = document.getElementById('cartClear');
      
      // Store cart items
      let cartItemsArray = [];
      
      // Open cart sidebar
      cartButton.addEventListener('click', function() {
        cartSidebar.classList.add('active');
        overlay.classList.add('active');
        updateCartDisplay();
      });
      
      // Close cart sidebar
      cartClose.addEventListener('click', function() {
        cartSidebar.classList.remove('active');
        overlay.classList.remove('active');
      });
      
      // Close when clicking overlay
      overlay.addEventListener('click', function() {
        cartSidebar.classList.remove('active');
        overlay.classList.remove('active');
      });
      
      // Update cart display
      function updateCartDisplay() {
        if (cartItemsArray.length === 0) {
          cartEmpty.style.display = 'flex';
          cartTotalItems.textContent = '0';
          cartCheckout.disabled = true;
          cartCheckout.style.opacity = '0.5';
        } else {
          cartEmpty.style.display = 'none';
          
          // Clear existing items
          const existingItems = cartItems.querySelectorAll('.cart-item');
          existingItems.forEach(item => {
            if (!item.classList.contains('cart-empty')) {
              item.remove();
            }
          });
          
          // Add items to cart
          cartItemsArray.forEach((item, index) => {
            const cartItem = document.createElement('div');
            cartItem.className = 'cart-item';
            cartItem.dataset.index = index;
            cartItem.innerHTML = `
              <span class="cart-item-title">${item.title}</span>
              <div class="cart-item-qty-controls">
                <button class="cart-qty-btn cart-qty-minus" data-index="${index}">-</button>
                <span class="cart-item-qty">${item.quantity}</span>
                <button class="cart-qty-btn cart-qty-plus" data-index="${index}">+</button>
              </div>
            `;
            cartItems.appendChild(cartItem);
          });
          
          // Add event listeners to the new buttons
          document.querySelectorAll('.cart-qty-minus').forEach(btn => {
            btn.addEventListener('click', function() {
              const index = parseInt(this.dataset.index);
              if (cartItemsArray[index].quantity > 1) {
                cartItemsArray[index].quantity--;
                totalItems--;
                updateCartDisplay();
                cartCount.textContent = totalItems;
              } else {
                // Remove item if quantity becomes 0
                totalItems -= cartItemsArray[index].quantity;
                cartItemsArray.splice(index, 1);
                updateCartDisplay();
                cartCount.textContent = totalItems;
              }
            });
          });
          
          document.querySelectorAll('.cart-qty-plus').forEach(btn => {
            btn.addEventListener('click', function() {
              const index = parseInt(this.dataset.index);
              cartItemsArray[index].quantity++;
              totalItems++;
              updateCartDisplay();
              cartCount.textContent = totalItems;
            });
          });
          
          cartTotalItems.textContent = totalItems;
          cartCheckout.disabled = false;
          cartCheckout.style.opacity = '1';
        }
      }
      
      // Checkout button
      cartCheckout.addEventListener('click', function() {
        if (totalItems > 0) {
          alert('Processing checkout for ' + totalItems + ' items.');
          // Here you would redirect to checkout page
          // window.location.href = 'checkout.php';
        }
      });
      
      // Clear cart button
      cartClear.addEventListener('click', function() {
        if (totalItems > 0) {
          if (confirm('Are you sure you want to clear all items from your cart?')) {
            cartItemsArray = [];
            totalItems = 0;
            cartCount.textContent = '0';
            updateCartDisplay();
          }
        }
      });
    });
  </script>
  <!-- JavaScript for menu toggle -->    
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
  </script>
</body>
</html>
