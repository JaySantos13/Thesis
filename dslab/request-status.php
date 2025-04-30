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
  <link rel="stylesheet" href="style.css">
<link rel="stylesheet" href="requeststat.css">
  <title>Request Status</title>
</head>
<body class="requeststat-body" style="background:#f7f7f7;">
  <div style="min-height:100vh;display:flex;align-items:flex-start;justify-content:center;background:#f7f7f7;padding-top:40px;">
    <div class="labday-card" style="box-shadow:none;padding:22px 14px 10px 14px;max-width:410px;width:100%;">
      <div class="labday-top-row">
        <button class="labday-back" onclick="window.history.back()">&#8592;</button>
      </div>
      <div class="labday-logo-center" style="margin-bottom:8px;">
        <?php include 'icons/dsB.svg'; ?>
      </div>
      <header class="labday-header" style="background:none;color:#222;font-size:1.25em;font-weight:400;letter-spacing:0.5px;text-align:center;margin-bottom:18px;">Request Status</header>
        <form class="labday-search-row" method="get" action="">
          <input type="text" name="search" placeholder="Search" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
          <button type="button" class="labday-filters">&#9881; Filters</button>
        </form>
        <div class="labday-list">
          <?php if (empty($requests)): ?>
            <!-- Demo item if no data -->
            <div class="labday-item">
              <span class="labday-icon">&#128209;</span>
              <div class="labday-details">
                <span class="labday-subject">Diode & Circuits</span><br>
                <span class="labday-course">Fundametals to Electronics Circuits</span>
              </div>
              <div class="labday-date">March 11, 2025<br>4:30PM-7:30PM</div>
              <span class="labday-role" style="background:#ff7f1a;color:#fff;border-radius:8px;padding:4px 10px;margin-left:10px;align-self:center;">Borrower</span>
            </div>
            <div class="labday-item">
              <span class="labday-icon">&#128209;</span>
              <div class="labday-details">
                <span class="labday-subject">Diode & Circuits</span><br>
                <span class="labday-course">Fundametals to Electronics Circuits</span>
              </div>
              <div class="labday-date">March 20, 2025<br>5:30PM-7:30PM</div>
              <span class="labday-role" style="background:#ff7f1a;color:#fff;border-radius:8px;padding:4px 10px;margin-left:10px;align-self:center;">Member</span>
            </div>
          <?php else: ?>
            <?php foreach ($requests as $req): ?>
              <div class="labday-item">
                <span class="labday-icon">&#128209;</span>
                <div class="labday-details">
                  <span class="labday-subject"><?php echo htmlspecialchars($req['subject']); ?></span><br>
                  <span class="labday-course"><?php echo htmlspecialchars($req['course']); ?></span>
                </div>
                <div class="labday-date">
                  <?php echo date('M j, Y', strtotime($req['schedule_date'])); ?><br>
                  <?php echo date('g:iA', strtotime($req['start_time'])) . '-' . date('g:iA', strtotime($req['end_time'])); ?>
                </div>
                <span class="labday-role" style="background:#ff7f1a;color:#fff;border-radius:8px;padding:4px 10px;margin-left:10px;align-self:center;">
                  <?php echo htmlspecialchars($req['role']); ?>
                </span>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
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
