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
</head>
<body class="direct-bg">
  <div class="direct-container">
    <div class="direct-card">
      <div class="direct-logo-center">
        <button class="direct-back" onclick="window.history.back()">&#8592;</button>
        <?php include 'icons/dsB.svg'; ?>
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
      <div class="direct-basket"></div>
    </div>
  </div>
  <ul>
    <li><a href="notif.php">Notifications</a></li>
    <li><a href="history.php">History</a></li>
    <li><a class="active" href="dashboard.php">Home</a></li>
    <li><a href="profile.php">Profile</a></li>
    <li><a href="more.php">More</a></li>
  </ul>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const minusBtns = document.querySelectorAll('.direct-qty-btn:first-child');
      const plusBtns = document.querySelectorAll('.direct-qty-btn:last-of-type');
      const qtyInputs = document.querySelectorAll('.direct-qty-input');
      
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
    });
  </script>
</body>
</html>
