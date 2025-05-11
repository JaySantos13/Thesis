<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['username'] != 'admin') {
  echo "You must be an admin to view this page.";
  exit();
}

include 'db.php';
$sql = "SELECT * FROM users";
$result = $conn->query($sql);

echo "<h1>Admin Panel</h1>";
echo "<table border='1'>
        <tr>
          <th>Username</th>
          <th>Email</th>
        </tr>";

while ($row = $result->fetch_assoc()) {
  echo "<tr>
          <td>{$row['username']}</td>
          <td>{$row['email']}</td>
        </tr>";
}

echo "</table>";

$conn->close();
?>
