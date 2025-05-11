<?php
include 'db.php';
$conn = new mysqli("localhost", "root", "", "mywebsite");
$success = '';
$error = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $firstname = $_POST['firstname'];
  $lastname = $_POST['lastname'];
  $username = $_POST['username'];
  $email = $_POST['email'];
  $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

  $sql = "INSERT INTO users (first_name, last_name, username, email, password)
          VALUES ('$firstname', '$lastname', '$username', '$email', '$password')";

  if ($conn->query($sql) === TRUE) {
      $success = "New record created successfully. <a href='login.php'>Login now</a>";
  } else {
      $error = "Error: " . $conn->error;
  }
}

$conn->close();
?>

<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="style.css">
  <title>Sign-Up</title>
</head>
<body>
<form action="register.php" method="POST">
  <div class="imgcontainer">
    <?php include 'icons/dsB.svg'; ?>
  </div>
  <?php if (!empty($success)): ?>
  <div class="success-message"><?php echo $success; ?></div>
<?php endif; ?>

<?php if (!empty($error)): ?>
  <div class="error-message"><?php echo $error; ?></div>
<?php endif; ?>
  <div class="container">
    <h3>Name</h3>
    <input type="text" name="lastname" placeholder="Last Name" required>
    <input type="text" name="firstname" placeholder="First Name" required>
    <h3>Email</h3>
    <input type="text" name="username" placeholder="Enter username" required>
    <input type="email" name="email" placeholder="Enter email" required>
    <input type="password" name="password" placeholder="Enter password" required>
    <button type="submit">Register</button>
  </div>
  <div class="login-inline">
    <span>Already have an account?</span>
    <a href="login.php">Login</a>
  </div>
</form>
</body>
</html>
