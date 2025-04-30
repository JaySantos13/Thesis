<?php
$token = $_GET['token'] ?? '';
?>

<!DOCTYPE html>
<html>
<head>
  <title>Reset Password</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <form action="update-password.php" method="POST">
    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
    <p>Enter your new password</p>
    <div class="container">
      <input type="password" name="password" placeholder="New password" required>
      <input type="password" name="confirm_password" placeholder="Confirm password" required>
      <button type="submit">Reset Password</button>
    </div>
  </form>
</body>
</html>
