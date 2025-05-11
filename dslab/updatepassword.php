<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $token = $_POST['token'];
  $password = $_POST['password'];
  $confirm = $_POST['confirm_password'];

  if ($password !== $confirm) {
    exit("Passwords do not match.");
  }

  $stmt = $conn->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_expires > NOW()");
  $stmt->bind_param("s", $token);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?");
    $stmt->bind_param("si", $hashedPassword, $user['id']);
    $stmt->execute();

    echo "Password has been reset. <a href='login.php'>Login</a>";
  } else {
    echo "Invalid or expired token.";
  }
}
?>
