<?php
session_start();

$error = '';
$success = '';

if (isset($_GET['error'])) {
  $error = htmlspecialchars($_GET['error']);
}
if (isset($_GET['success'])) {
  $success = htmlspecialchars($_GET['success']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Forgot Password</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>

<form action="sendreset.php" method="POST">
  <div class="imgcontainer">
    <?php include 'icons/dsB.svg'; ?>
  </div>
  <p>Forgot Your Password?</p>

  <div class="container">
    <?php if (!empty($error)): ?>
      <div class="error-message"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if (!empty($success)): ?>
      <div class="success-message"><?php echo $success; ?></div>
    <?php endif; ?>

    <input type="email" name="email" placeholder="Enter your email" required>
    <button type="submit">Send Reset Link</button>
  </div>

  <a href="login.php" class="back-link">Back to Login</a>
</form>

</body>
</html>
