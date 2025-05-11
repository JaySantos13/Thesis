<?php
session_start();
require_once 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$user_sql = "SELECT first_name, last_name, email, program, student_no FROM users WHERE id=?";
$user_stmt = $conn->prepare($user_sql);
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user = $user_stmt->get_result()->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = $_POST['firstname'] ?? '';
    $last_name = $_POST['lastname'] ?? '';
    $email = $_POST['email'] ?? '';
    $course = $_POST['course'] ?? '';
    $student_no = $_POST['id_no'] ?? '';

    // Update user information
    $update_sql = "UPDATE users SET first_name = ?, last_name = ?, email = ?, program = ?, student_no = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("sssssi", $first_name, $last_name, $email, $course, $student_no, $user_id);
    
    if ($update_stmt->execute()) {
        // Refresh user data
        $user_stmt->execute();
        $user = $user_stmt->get_result()->fetch_assoc();
        
        // Redirect to profile page with success message
        header("Location: profile.php?update=success&msg=" . urlencode("Profile updated successfully"));
        exit();
    } else {
        $error_message = "Failed to update profile. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <link rel="stylesheet" href="mobile-edit-profile.css">
</head>
<body>
    <div class="container">
        <div class="logo-container">
            <?php include 'icons/dsB.svg'; ?>
        </div>
        
        <div class="edit-header">Edit Profile</div>
        
        <form class="edit-form" method="post" action="">
            <?php if (isset($error_message)): ?>
                <div style="color: #721c24; background-color: #f8d7da; border: 1px solid #f5c6cb; padding: 10px; border-radius: 5px; margin-bottom: 10px;">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>
            
            <input type="text" class="form-input" name="firstname" placeholder="Firstname" value="<?php echo htmlspecialchars($user['first_name'] ?? ''); ?>" required>
            
            <input type="text" class="form-input" name="lastname" placeholder="Last name" value="<?php echo htmlspecialchars($user['last_name'] ?? ''); ?>" required>
            
            <input type="email" class="form-input" name="email" placeholder="Email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>
            
            <input type="text" class="form-input" name="course" placeholder="Course" value="<?php echo htmlspecialchars($user['program'] ?? ''); ?>">
            
            <input type="text" class="form-input" name="id_no" placeholder="ID No." value="<?php echo htmlspecialchars($user['student_no'] ?? ''); ?>">
            
            <a href="change-password.php" class="action-button full-width-button">Change Password</a>
            
            <a href="enable-2fa.php" class="action-button full-width-button">Enable 2FA</a>
            
            <div class="button-row">
                <a href="profile.php" class="action-button secondary-button">Cancel</a>
                <button type="submit" class="action-button primary-button">Save</button>
            </div>
        </form>
    </div>
</body>
</html>
