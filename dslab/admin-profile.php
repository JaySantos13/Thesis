<?php
session_start();

// Check if user is logged in as admin
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['is_admin'])) {
    header("Location: adminlogin.php");
    exit();
}

include 'db.php';

// Get admin information
$admin_id = $_SESSION['admin_id'];
$stmt = $conn->prepare("SELECT * FROM admins WHERE id = ?");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();

// Handle form submission for profile update
$success_message = "";
$error_message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $department = isset($_POST['department']) ? trim($_POST['department']) : '';
    
    // Basic validation
    if (empty($full_name) || empty($email)) {
        $error_message = "Name and email are required fields.";
    } else {
        // Update profile information
        $update_stmt = $conn->prepare("UPDATE admins SET full_name = ?, email = ?, phone = ?, department = ? WHERE id = ?");
        $update_stmt->bind_param("ssssi", $full_name, $email, $phone, $department, $admin_id);
        
        if ($update_stmt->execute()) {
            $success_message = "Profile updated successfully!";
            
            // Update session data if needed
            $_SESSION['admin_name'] = $full_name;
            
            // Refresh admin data
            $stmt->execute();
            $result = $stmt->get_result();
            $admin = $result->fetch_assoc();
        } else {
            $error_message = "Error updating profile: " . $conn->error;
        }
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate password inputs
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error_message = "All password fields are required.";
    } elseif ($new_password !== $confirm_password) {
        $error_message = "New passwords do not match.";
    } elseif (strlen($new_password) < 8) {
        $error_message = "New password must be at least 8 characters long.";
    } else {
        // Verify current password
        if (password_verify($current_password, $admin['password'])) {
            // Hash the new password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            
            // Update the password
            $pwd_update_stmt = $conn->prepare("UPDATE admins SET password = ? WHERE id = ?");
            $pwd_update_stmt->bind_param("si", $hashed_password, $admin_id);
            
            if ($pwd_update_stmt->execute()) {
                $success_message = "Password changed successfully!";
            } else {
                $error_message = "Error changing password: " . $conn->error;
            }
        } else {
            $error_message = "Current password is incorrect.";
        }
    }
}

// Activity logging removed to fix database structure issue

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Profile - DS Lab</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="admin-sidenav.css">
    <style>
        /* Admin Profile Specific Styles */
        .profile-container {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            padding: 30px;
            margin-bottom: 30px;
        }
        
        .profile-header {
            display: flex;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .profile-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background-color: #e55a00;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 20px;
            color: white;
            font-size: 40px;
            font-weight: bold;
        }
        
        .profile-title h2 {
            color: #444;
            margin: 0 0 5px 0;
        }
        
        .profile-title p {
            color: #666;
            margin: 0;
        }
        
        .profile-section {
            margin-bottom: 40px;
        }
        
        .profile-section h3 {
            color: #e55a00;
            margin-top: 0;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #444;
            font-weight: 500;
        }
        
        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        
        .form-row {
            display: flex;
            gap: 20px;
        }
        
        .form-row .form-group {
            flex: 1;
        }
        
        .btn-primary {
            background-color: #e55a00;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
        }
        
        .btn-primary:hover {
            background-color: #ff7f1a;
        }
        
        .alert {
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .admin-info-card {
            background-color: #f9f9f9;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
        }
        
        .admin-info-card h4 {
            color: #444;
            margin-top: 0;
            margin-bottom: 15px;
        }
        
        .admin-info-item {
            display: flex;
            margin-bottom: 10px;
        }
        
        .admin-info-label {
            font-weight: 500;
            width: 150px;
            color: #666;
        }
        
        .admin-info-value {
            flex: 1;
            color: #444;
        }
        
        @media (max-width: 768px) {
            .form-row {
                flex-direction: column;
                gap: 0;
            }
            
            .profile-header {
                flex-direction: column;
                text-align: center;
            }
            
            .profile-avatar {
                margin-right: 0;
                margin-bottom: 15px;
            }
            
            .admin-info-item {
                flex-direction: column;
            }
            
            .admin-info-label {
                width: 100%;
                margin-bottom: 5px;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <?php include 'admin-sidenav-updated.php'; ?>
        
        <!-- Admin Content Area -->
        <div class="admin-content">
            <div class="admin-header">
                <h1>My Profile</h1>
                <div class="admin-user-info">
                    <span>Welcome, <?php echo htmlspecialchars($admin['full_name']); ?></span>
                </div>
            </div>
            
            <?php if (!empty($success_message)): ?>
            <div class="alert alert-success">
                <?php echo $success_message; ?>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger">
                <?php echo $error_message; ?>
            </div>
            <?php endif; ?>
            
            <div class="profile-container">
                <div class="profile-header">
                    <div class="profile-avatar">
                        <?php echo strtoupper(substr($admin['full_name'], 0, 1)); ?>
                    </div>
                    <div class="profile-title">
                        <h2><?php echo htmlspecialchars($admin['full_name']); ?></h2>
                        <p><?php echo htmlspecialchars($admin['email']); ?></p>
                        <p>Admin ID: <?php echo $admin['id']; ?></p>
                    </div>
                </div>
                
                <div class="admin-info-card">
                    <h4>Account Information</h4>
                    <div class="admin-info-item">
                        <div class="admin-info-label">Role:</div>
                        <div class="admin-info-value"><?php echo $admin['is_super_admin'] ? 'Super Administrator' : 'Administrator'; ?></div>
                    </div>
                    <div class="admin-info-item">
                        <div class="admin-info-label">Department:</div>
                        <div class="admin-info-value"><?php echo !empty($admin['department']) ? htmlspecialchars($admin['department']) : 'Not specified'; ?></div>
                    </div>
                    <div class="admin-info-item">
                        <div class="admin-info-label">Phone:</div>
                        <div class="admin-info-value"><?php echo !empty($admin['phone']) ? htmlspecialchars($admin['phone']) : 'Not specified'; ?></div>
                    </div>
                    <div class="admin-info-item">
                        <div class="admin-info-label">Last Login:</div>
                        <div class="admin-info-value"><?php echo !empty($admin['last_login']) ? date('F j, Y, g:i a', strtotime($admin['last_login'])) : 'Never'; ?></div>
                    </div>
                    <div class="admin-info-item">
                        <div class="admin-info-label">Account Created:</div>
                        <div class="admin-info-value"><?php echo !empty($admin['created_at']) ? date('F j, Y', strtotime($admin['created_at'])) : 'Unknown'; ?></div>
                    </div>
                </div>
                
                <div class="profile-section">
                    <h3>Edit Profile</h3>
                    <form action="" method="post">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="full_name">Full Name</label>
                                <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($admin['full_name']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="email">Email Address</label>
                                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($admin['email']); ?>" required>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="phone">Phone Number</label>
                                <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($admin['phone'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label for="department">Department</label>
                                <input type="text" id="department" name="department" value="<?php echo htmlspecialchars($admin['department'] ?? ''); ?>">
                            </div>
                        </div>
                        <button type="submit" name="update_profile" class="btn-primary">Update Profile</button>
                    </form>
                </div>
                
                <div class="profile-section">
                    <h3>Change Password</h3>
                    <form action="" method="post">
                        <div class="form-group">
                            <label for="current_password">Current Password</label>
                            <input type="password" id="current_password" name="current_password" required>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="new_password">New Password</label>
                                <input type="password" id="new_password" name="new_password" required>
                            </div>
                            <div class="form-group">
                                <label for="confirm_password">Confirm New Password</label>
                                <input type="password" id="confirm_password" name="confirm_password" required>
                            </div>
                        </div>
                        <button type="submit" name="change_password" class="btn-primary">Change Password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Password confirmation validation
        document.getElementById('confirm_password').addEventListener('input', function() {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = this.value;
            
            if (newPassword !== confirmPassword) {
                this.setCustomValidity('Passwords do not match');
            } else {
                this.setCustomValidity('');
            }
        });
    </script>
</body>
</html>
