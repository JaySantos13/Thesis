<?php
session_start();
// Example equipment data; replace with DB fetch as needed
$equipment = [
    ["name" => "Oscilloscope", "img" => "icons/oscilloscope.png"],
    ["name" => "Multimeter", "img" => "icons/multimeter.png"],
    ["name" => "Oscilloscope", "img" => "icons/oscilloscope.png"],
    ["name" => "Multimeter", "img" => "icons/multimeter.png"],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Equipment Info</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="equipment-info.css">
</head>
<body class="equipment-bg">
  <main class="equip-main-card" style="position:relative;">
    <header class="equip-top-row">
        <button class="equip-back" onclick="window.history.back()" aria-label="Go back">&#8592;</button>
        <div class="equip-logo-center">
            <?php include 'icons/dsB.svg'; ?>
        </div>
    </header>
    <h1 class="equip-header-bar">Equipment Info</h1>
    <form class="equip-search-row" method="get" action="" role="search">
      <input type="text" name="search" placeholder="Search" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>" aria-label="Search equipment">
      <button type="button" class="equip-filters" aria-label="Show filters">&#9881; Filters</button>
    </form>
    <section class="equip-list" aria-label="Equipment list">
      <?php foreach ($equipment as $equip): ?>
        <div class="equip-item">
          <div class="equip-name"><?php echo htmlspecialchars($equip['name']); ?></div>
          <button class="equip-action" title="Details" aria-label="View details">&#128230;</button>
        </div>
      <?php endforeach; ?>
    </section>
    <div class="equip-bottom-orange" aria-hidden="true"></div>
  </main>
  <nav aria-label="Main navigation">
    <ul>
      <li><a href="notif.php">Notifications</a></li>
      <li><a href="history.php">History</a></li>
      <li><a href="dashboard.php">Home</a></li>
      <li><a href="profile.php">Profile</a></li>
      <li><a class="active" href="more.php">More</a></li>
    </ul>
  </nav>
</body>
</html>
