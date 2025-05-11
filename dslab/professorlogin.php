<?php
session_start();
include 'db.php';

$error = "";

// Create professors table if it doesn't exist
$check_table = "SHOW TABLES LIKE 'professors'";
$table_exists = $conn->query($check_table);

if ($table_exists->num_rows == 0) {
  // Create the professors table
  $create_table = "CREATE TABLE IF NOT EXISTS `professors` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(100) NOT NULL,
    `email` varchar(100) NOT NULL UNIQUE,
    `password` varchar(255) NOT NULL,
    `department` varchar(100) DEFAULT NULL,
    `phone` varchar(20) DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
  
  $conn->query($create_table);
  
  // Add a default professor account
  $default_password = password_hash('professor123', PASSWORD_DEFAULT);
  $insert_professor = "INSERT INTO `professors` (`name`, `email`, `password`, `department`, `phone`) VALUES
    ('Dr. John Smith', 'john.smith@dslab.edu', '$default_password', 'Computer Science', '123-456-7890')";
  
  $conn->query($insert_professor);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $email = $_POST['email'];
  $password = $_POST['password'];
  
  // For testing purposes
  if ($email == 'test@test.com' && $password == 'test123') {
    $_SESSION['professor_id'] = 1;
    $_SESSION['professor_name'] = 'Test Professor';
    header("Location: professor-dashboard.php");
    exit();
  }

  $sql = "SELECT * FROM professors WHERE email='$email'";
  $result = $conn->query($sql);

  if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    if (password_verify($password, $row['password'])) {
      $_SESSION['professor_id'] = $row['id'];
      $_SESSION['professor_name'] = $row['name'];
      header("Location: professor-dashboard.php");
      exit();
    } else {
      $error = "Invalid credentials.";
    }
  } else {
    $error = "No professor found with that email.";
  }
}

$conn->close();
?>

<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="style.css">
  <title>Professor Login</title>
</head>
<body>
<form action="professorlogin.php" method="POST">
  <div class="imgcontainer">
  <?php include 'icons/dsB.svg'; ?>
  </div>
  <p>Professor Login</p>
  <div class="container">
    <?php if (!empty($error)): ?>
      <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>

    <input type="email" name="email" placeholder="Enter email" required>
    <input type="password" name="password" placeholder="Enter password" required>
    <button type="submit">Login</button>
    <a href="forgotpassword.php" class="forgot-link">Forgot Password?</a>
  </div>
</form>
</body>
</html>