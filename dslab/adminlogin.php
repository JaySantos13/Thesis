<?php
session_start();
include 'db.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $email = $_POST['email'];
  $password = $_POST['password'];

  // Use prepared statements to prevent SQL injection
  $stmt = $conn->prepare("SELECT * FROM admins WHERE email = ?");
  $stmt->bind_param("s", $email);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows > 0) {
    $admin = $result->fetch_assoc();
    
    // Verify password
    if (password_verify($password, $admin['password'])) {
      // Set admin session variables
      $_SESSION['admin_id'] = $admin['id'];
      $_SESSION['admin_username'] = $admin['username'];
      $_SESSION['is_admin'] = true;
      
      // Update last login timestamp
      $update_stmt = $conn->prepare("UPDATE admins SET last_login = CURRENT_TIMESTAMP WHERE id = ?");
      $update_stmt->bind_param("i", $admin['id']);
      $update_stmt->execute();
      $update_stmt->close();
      
      // Log the admin login activity
      $ip = $_SERVER['REMOTE_ADDR'];
      $user_agent = $_SERVER['HTTP_USER_AGENT'];
      $log_stmt = $conn->prepare("INSERT INTO admin_activity_log (admin_id, action_type, action_details, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)");
      $action_type = "login";
      $action_details = "Admin login successful";
      $log_stmt->bind_param("issss", $admin['id'], $action_type, $action_details, $ip, $user_agent);
      $log_stmt->execute();
      $log_stmt->close();
      
      // Redirect to admin dashboard
      header("Location: admin-dashboard.php");
      exit();
    } else {
      $error = "Invalid password. Please try again.";
    }
  } else {
    $error = "No admin account found with that email.";
  }
  
  $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="style.css">
  <title>Admin Login</title>
  <style>
    /* Additional admin-specific styling */
    .admin-login-container {
      max-width: 400px;
      margin: 50px auto;
      padding: 20px;
      border-radius: 5px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
      background-color: #fff;
    }
    
    .admin-header {
      text-align: center;
      color: #444;
      margin-bottom: 20px;
    }
    
    .admin-header h1 {
      color: #e55a00;
    }
    
    .admin-form input {
      width: 100%;
      padding: 10px;
      margin-bottom: 15px;
      border: 1px solid #ddd;
      border-radius: 4px;
    }
    
    .admin-form button {
      width: 100%;
      padding: 10px;
      background-color: #e55a00;
      color: white;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      font-weight: bold;
    }
    
    .admin-form button:hover {
      background-color: #ff7f1a;
    }
    
    .error {
      color: #f44336;
      margin-bottom: 15px;
      text-align: center;
    }
  </style>
</head>
<body>
  <div class="admin-login-container">
    <div class="admin-header">
      <h1>DS Lab Admin</h1>
      <p>Administrator Access Only</p>
    </div>
    
    <form class="admin-form" action="adminlogin.php" method="POST">
      <?php if (!empty($error)): ?>
        <div class="error"><?php echo $error; ?></div>
      <?php endif; ?>
      
      <input type="email" name="email" placeholder="Admin Email" required>
      <input type="password" name="password" placeholder="Password" required>
      <button type="submit">Login</button>
    </form>
    
    <div style="text-align: center; margin-top: 15px;">
      <a href="login.php">Return to Student Login</a>
    </div>
  </div>
</body>
</html>