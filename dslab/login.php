<?php
session_start();
include 'db.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $email = $_POST['email'];
  $password = $_POST['password'];

  $sql = "SELECT * FROM users WHERE email='$email'";
  $result = $conn->query($sql);

  if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    if (password_verify($password, $row['password'])) {
      $_SESSION['user_id'] = $row['id'];
      $_SESSION['username'] = $row['username'];
      header("Location: dashboard.php");
      exit();
    } else {
      $error = "Invalid credentials.";
    }
  } else {
    $error = "No user found with that email.";
  }
}

$conn->close();
?>

<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="style.css">
  <title>Login</title>
</head>
<body>
<form action="login.php" method="POST">
  <div class="imgcontainer">
  <?php include 'icons/dsB.svg'; ?>
  </div>
  <p>Student Login</p>
  <div class="container">
    <?php if (!empty($error)): ?>
      <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>

    <input type="email" name="email" placeholder="Enter email" required>
    <input type="password" name="password" placeholder="Enter password" required>
    <button type="submit">Login</button>
    <a href="forgotpassword.php" class="forgot-link">Forgot Password?</a>
    <div class=register>
      Don't have account? <a href="register.php">Register</a>
    </div>
  </div>
</form>
</body>
</html>
