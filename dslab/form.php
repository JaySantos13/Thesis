<?php
include 'db.php';

$username = $_POST['username'];
$email = $_POST['email'];

$sql = "INSERT INTO users (username, email) VALUES ('$username', '$email')";

if ($conn->query($sql) === TRUE) {
  echo "New record created successfully";
} else {
  echo "Error: " . $conn->error;
}

$conn->close();
?>
