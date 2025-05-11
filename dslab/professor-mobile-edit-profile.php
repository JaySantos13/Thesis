<?php
session_start();

// Check if professor is logged in
if (!isset($_SESSION['professor_id'])) {
  header("Location: professorlogin.php");
  exit();
}

include 'db.php';

// Get professor information
$professor_id = $_SESSION['professor_id'];
$sql = "SELECT * FROM professors WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $professor_id);
$stmt->execute();
$professor = $stmt->get_result()->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $department = $_POST['department'] ?? '';
    $phone = $_POST['phone'] ?? '';

    // Update professor information
    $update_sql = "UPDATE professors SET name = ?, email = ?, department = ?, phone = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("ssssi", $name, $email, $department, $phone, $professor_id);
    
    if ($update_stmt->execute()) {
        // Refresh professor data
        $stmt->execute();
        $professor = $stmt->get_result()->fetch_assoc();
        
        // Redirect to profile page with success message
        header("Location: professor-profile.php?update=success&msg=" . urlencode("Profile updated successfully"));
        exit();
    } else {
        $error_message = "Failed to update profile. Please try again.";
    }
}

$conn->close();
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
            
            <input type="text" class="form-input" name="name" placeholder="Full Name" value="<?php echo htmlspecialchars($professor['name'] ?? ''); ?>" required>
            
            <input type="email" class="form-input" name="email" placeholder="Email" value="<?php echo htmlspecialchars($professor['email'] ?? ''); ?>" required>
            
            <input type="text" class="form-input" name="department" placeholder="Department" value="<?php echo htmlspecialchars($professor['department'] ?? ''); ?>" required>
            
            <input type="text" class="form-input" name="phone" placeholder="Phone" value="<?php echo htmlspecialchars($professor['phone'] ?? ''); ?>">
            
            <a href="professor-change-password.php" class="action-button full-width-button">Change Password</a>
            
            <a href="professor-enable-2fa.php" class="action-button full-width-button">Enable 2FA</a>
            
            <div class="button-row">
                <a href="professor-profile.php" class="action-button secondary-button">Cancel</a>
                <button type="submit" class="action-button primary-button">Save</button>
            </div>
        </form>
    </div>
</body>
</html>
