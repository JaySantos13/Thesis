<?php
// professor-equipment.php
session_start();
// TODO: Add authentication check for professor session

// Dummy equipment data (replace with DB query)
$equipment = [
    [
        "id" => 1,
        "name" => "Oscilloscope", 
        "icon" => "fa-wave-square",
        "image" => "img/oscilloscope.jpg",
        "description" => "Oscilloscopes (or scopes) test and display voltage signals as waveforms, visual representations of the variation of voltage over time. The voltage values are plotted on a graph, which shows how the signal changes. The vertical (Y) axis represents the voltage measurement and the horizontal (X) axis represents time."
    ],
    [
        "id" => 2,
        "name" => "Multimeter", 
        "icon" => "fa-bolt",
        "image" => "img/multimeter.jpg",
        "description" => "A multimeter is an electronic measuring instrument that combines several measurement functions in one unit. A typical multimeter can measure voltage, current, and resistance. Analog multimeters use a microammeter with a moving pointer to display readings. Digital multimeters (DMM, DVOM) have a numeric display, and may also show a graphical bar representing the measured value."
    ],
    [
        "id" => 3,
        "name" => "Function Generator", 
        "icon" => "fa-signal",
        "image" => "img/function-generator.jpg",
        "description" => "A function generator is an electronic device used to generate different types of electrical waveforms over a wide range of frequencies. Some of the most common waveforms produced by the function generator are sine, square, triangular, and sawtooth shapes. These waveforms can be either repetitive or single-shot."
    ],
    [
        "id" => 4,
        "name" => "Power Supply", 
        "icon" => "fa-plug",
        "image" => "img/power-supply.jpg",
        "description" => "A laboratory power supply is a variable power supply that can be used to test circuits under different voltage or current conditions. Lab power supplies typically offer controllable voltage and current output, as well as safety features such as current limiting to protect circuits from damage."
    ],
];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Equipment Info | DS Lab</title>
    <link rel="stylesheet" href="sidenav.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: #fff;
            margin: 0;
            font-family: 'Segoe UI', Arial, sans-serif;
            min-height: 100vh;
            display: flex;
        }
        .main-content {
            flex: 1;
            margin-left: 90px;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .logo {
            margin-bottom: 20px;
        }
        .equip-header {
            background: #e55a00;
            color: #fff;
            border-radius: 16px 16px 0 0;
            padding: 15px 0;
            width: 100%;
            max-width: 600px;
            text-align: center;
            font-weight: bold;
            font-size: 1.1rem;
            letter-spacing: 1px;
        }
        .search-bar-row {
            display: flex;
            align-items: center;
            background: #fff;
            border-radius: 16px;
            width: 100%;
            max-width: 600px;
            margin-top: -10px;
            padding: 10px 15px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.1);
        }
        .search-bar-row input[type="text"] {
            border: none;
            outline: none;
            flex: 1;
            padding: 8px 10px;
            font-size: 1rem;
            background: transparent;
        }
        .search-bar-row .fa-magnifying-glass {
            color: #e55a00;
            margin-right: 8px;
        }
        .filter-btn {
            background: #f2f2f2;
            border: none;
            border-radius: 10px;
            color: #444;
            font-size: 0.95rem;
            padding: 6px 14px;
            margin-left: 8px;
            display: flex;
            align-items: center;
            cursor: pointer;
            transition: background 0.2s;
        }
        .filter-btn i {
            margin-right: 5px;
            color: #e55a00;
        }
        .equipment-list {
            width: 100%;
            max-width: 600px;
            margin-top: 15px;
        }
        .equip-card {
            display: flex;
            align-items: center;
            background: #f7f7f7;
            border-radius: 10px;
            padding: 12px 15px;
            margin-bottom: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
            cursor: pointer;
            transition: all 0.2s;
            position: relative;
            overflow: hidden;
        }
        .equip-card:hover {
            background-color: #f0f0f0;
        }
        .equip-card:active {
            transform: scale(0.98);
        }
        .click-ripple {
            position: absolute;
            background: rgba(229, 90, 0, 0.3);
            border-radius: 50%;
            transform: scale(0);
            animation: ripple 0.6s linear;
            pointer-events: none;
        }
        @keyframes ripple {
            to {
                transform: scale(4);
                opacity: 0;
            }
        }
        .equip-card .equip-icon {
            width: 48px;
            height: 38px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            background: #fff;
            margin-right: 15px;
            color: #e55a00;
            font-size: 1.5rem;
        }
        .equip-card .equip-name {
            flex: 1;
            font-weight: 500;
            font-size: 1.05rem;
            color: #222;
        }
        .equip-card .equip-action {
            color: #444;
            font-size: 1.2rem;
            margin-left: 10px;
            cursor: pointer;
            transition: color 0.2s;
        }
        .equip-card .equip-action:hover {
            color: #e55a00;
        }
        .menu-toggle {
            position: fixed;
            top: 10px;
            left: 10px;
            background: #444;
            color: white;
            border: none;
            border-radius: 5px;
            width: 40px;
            height: 40px;
            display: none;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 100;
        }
        
        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            z-index: 1000;
            overflow-y: auto;
        }
        .modal-content {
            background-color: #fff;
            margin: 20px auto;
            width: 90%;
            max-width: 500px;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            animation: modalFadeIn 0.3s;
            position: relative;
        }
        @keyframes modalFadeIn {
            from {opacity: 0; transform: translateY(-20px);}
            to {opacity: 1; transform: translateY(0);}
        }
        .modal-header {
            background-color: #e55a00;
            color: white;
            padding: 12px 15px;
            font-weight: bold;
            font-size: 1.2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .modal-header .modal-icon {
            display: flex;
            align-items: center;
        }
        .modal-header .modal-icon i {
            font-size: 1.5rem;
            margin-right: 10px;
        }
        .modal-close {
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
            background: none;
            border: none;
            padding: 0;
            margin: 0;
        }
        .modal-body {
            padding: 0;
        }
        .equipment-image {
            width: 100%;
            height: 200px;
            object-fit: contain;
            background-color: white;
            padding: 10px;
        }
        .equipment-details {
            padding: 20px;
            background-color: #ff7f1a;
            color: white;
        }
        .equipment-title {
            font-size: 1.3rem;
            font-weight: bold;
            margin-bottom: 15px;
        }
        .equipment-description {
            line-height: 1.6;
            font-size: 0.95rem;
        }
        
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 15px;
            }
            .menu-toggle {
                display: flex;
            }
            .sidenav {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }
            .sidenav.active {
                transform: translateX(0);
            }
            .modal-content {
                width: 95%;
                margin: 10px auto;
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

    <div class="main-content">
        <div class="logo">
            <?php include 'icons/dsB.svg'; ?>
        </div>
        
        <div class="equip-header">Equipment Info</div>
        
        <div class="search-bar-row">
            <i class="fa fa-magnifying-glass"></i>
            <input type="text" placeholder="Search" id="searchInput" onkeyup="filterEquipment()">
            <button class="filter-btn"><i class="fa fa-filter"></i>Filters</button>
        </div>
        
        <div class="equipment-list" id="equipmentList">
            <?php foreach($equipment as $item): ?>
                <div class="equip-card" onclick="createRipple(event); showEquipmentDetails(<?= $item['id'] ?>)">
                    <div class="equip-icon"><i class="fa <?= htmlspecialchars($item['icon']) ?>"></i></div>
                    <div class="equip-name"><?= htmlspecialchars($item['name']) ?></div>
                    <span class="equip-action"><i class="fa fa-info-circle"></i></span>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Equipment Details Modal -->
        <div id="equipmentModal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="modal-icon">
                        <i id="modalIcon" class="fa"></i>
                        <span id="modalTitle">Equipment Details</span>
                    </div>
                    <button class="modal-close" onclick="closeModal()">×</button>
                </div>
                <div class="modal-body">
                    <img id="equipmentImage" class="equipment-image" src="" alt="Equipment">
                    <div class="equipment-details">
                        <div id="equipmentTitle" class="equipment-title"></div>
                        <div id="equipmentDescription" class="equipment-description"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

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

        function filterEquipment() {
            var input = document.getElementById('searchInput');
            var filter = input.value.toLowerCase();
            var list = document.getElementById('equipmentList');
            var cards = list.getElementsByClassName('equip-card');
            for (var i = 0; i < cards.length; i++) {
                var name = cards[i].getElementsByClassName('equip-name')[0].textContent;
                if (name.toLowerCase().indexOf(filter) > -1) {
                    cards[i].style.display = '';
                } else {
                    cards[i].style.display = 'none';
                }
            }
        }
        
        // Create ripple effect when clicking equipment cards
        function createRipple(event) {
            const button = event.currentTarget;
            const circle = document.createElement('span');
            const diameter = Math.max(button.clientWidth, button.clientHeight);
            const radius = diameter / 2;
            
            // Position the ripple at click coordinates
            circle.style.width = circle.style.height = `${diameter}px`;
            circle.style.left = `${event.clientX - button.getBoundingClientRect().left - radius}px`;
            circle.style.top = `${event.clientY - button.getBoundingClientRect().top - radius}px`;
            circle.classList.add('click-ripple');
            
            // Remove existing ripples
            const ripple = button.getElementsByClassName('click-ripple');
            if (ripple.length) {
                ripple[0].remove();
            }
            
            // Add the ripple to the button
            button.appendChild(circle);
            
            // Show a temporary check icon
            const action = button.querySelector('.equip-action i');
            const originalClass = action.className;
            action.className = 'fa fa-check';
            action.style.color = '#e55a00';
            
            // Restore original icon after animation
            setTimeout(() => {
                action.className = originalClass;
                action.style.color = '';
            }, 600);
        }
        
        // Equipment details data
        const equipmentData = <?= json_encode($equipment) ?>;
        
        // Show equipment details modal
        function showEquipmentDetails(id) {
            const equipment = equipmentData.find(item => item.id === id);
            if (!equipment) return;
            
            document.getElementById('modalTitle').textContent = 'Equipment Details';
            document.getElementById('modalIcon').className = 'fa ' + equipment.icon;
            document.getElementById('equipmentTitle').textContent = equipment.name;
            document.getElementById('equipmentDescription').textContent = equipment.description;
            document.getElementById('equipmentImage').src = equipment.image;
            document.getElementById('equipmentImage').alt = equipment.name;
            
            document.getElementById('equipmentModal').style.display = 'block';
            document.body.style.overflow = 'hidden'; // Prevent scrolling behind modal
        }
        
        // Close modal
        function closeModal() {
            document.getElementById('equipmentModal').style.display = 'none';
            document.body.style.overflow = 'auto'; // Restore scrolling
        }
        
        // Close modal when clicking outside of it
        window.onclick = function(event) {
            const modal = document.getElementById('equipmentModal');
            if (event.target === modal) {
                closeModal();
            }
        }
    </script>
</body>
</html>
