<?php
session_start();
include 'db.php';

// Fetch schedules from the database (adjust table/column names as needed)
$schedules = [];
$sql = "SELECT * FROM schedules ORDER BY schedule_date, start_time";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $schedules[] = $row;
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="style.css">
  <title>My Lab Day</title>
</head>
<body style="background:#f7f7f7;">
  <div style="min-height:100vh;display:flex;align-items:flex-start;justify-content:center;background:#f7f7f7;padding-top:40px;">
    <div class="labday-card" style="box-shadow:none;padding:22px 14px 10px 14px;max-width:410px;width:100%;">
      <div class="labday-top-row">
        <button class="labday-back" onclick="window.history.back()">&#8592;</button>
      </div>
      <div class="labday-logo-center">
        <?php include 'icons/dsB.svg'; ?>
      </div>
      <h2 class="labday-section" style="text-align:center;font-size:1.15em;font-weight:500;margin-bottom:18px;margin-top:8px;">Laboratory Schedules</h2>
      <form class="labday-search-row" method="get" action="">
        <input type="text" name="search" placeholder="Search" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
        <button type="button" class="labday-filters">&#9881; Filters</button>
      </form>
      <div class="labday-list">
        <?php if (empty($schedules)): ?>
          <!-- Demo item if no data -->
          <div class="labday-item">
            <span class="labday-icon">&#128202;</span>
            <div class="labday-details">
              <span class="labday-subject">Diode & Circuits</span><br>
              <span class="labday-course">Fundametals to Electronics Circuits</span>
            </div>
            <div class="labday-date">March 11, 2025<br>4:30PM-7:30PM</div>
          </div>
        <?php else: ?>
          <?php foreach ($schedules as $sched): ?>
            <div class="labday-item">
              <span class="labday-icon">&#128202;</span>
              <div class="labday-details">
                <span class="labday-subject"><?php echo htmlspecialchars($sched['subject']); ?></span><br>
                <span class="labday-course"><?php echo htmlspecialchars($sched['course']); ?></span>
              </div>
              <div class="labday-date">
                <?php echo date('M j, Y', strtotime($sched['schedule_date'])); ?><br>
                <?php echo date('g:iA', strtotime($sched['start_time'])) . '-' . date('g:iA', strtotime($sched['end_time'])); ?>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </section>
  </main>
  <ul>
    <li><a href="/notif">Notifications</a></li>
    <li><a href="/history">History</a></li>
    <li><a class="active" href="/home">Home</a></li>
    <li><a href="/profile">Profile</a></li>
    <li><a href="/more">More</a></li>
  </ul>
</body>

</html>
