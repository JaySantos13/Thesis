<?php
session_start();
require_once 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$response = ['success' => false, 'message' => ''];

// Check if file was uploaded
if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
    // Create uploads directory if it doesn't exist
    $upload_dir = 'uploads/profile/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    // Get file info
    $file_tmp = $_FILES['photo']['tmp_name'];
    $file_type = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
    
    // Check if file is an image
    $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
    if (!in_array($file_type, $allowed_types)) {
        $response['message'] = 'Only JPG, JPEG, PNG & GIF files are allowed.';
    } else {
        // Convert to jpg for consistency
        $target_file = $upload_dir . $user_id . '.jpg';
        
        // Process and resize the image
        list($width, $height) = getimagesize($file_tmp);
        $size = min($width, $height);
        $src_x = ($width - $size) / 2;
        $src_y = ($height - $size) / 2;
        
        // Create a square image
        $new_img = imagecreatetruecolor(300, 300);
        
        // Create image based on file type
        switch ($file_type) {
            case 'jpg':
            case 'jpeg':
                $source = imagecreatefromjpeg($file_tmp);
                break;
            case 'png':
                $source = imagecreatefrompng($file_tmp);
                break;
            case 'gif':
                $source = imagecreatefromgif($file_tmp);
                break;
        }
        
        // Copy and resize the image
        imagecopyresampled($new_img, $source, 0, 0, $src_x, $src_y, 300, 300, $size, $size);
        
        // Save the image
        if (imagejpeg($new_img, $target_file, 90)) {
            $response['success'] = true;
            $response['message'] = 'Profile photo updated successfully.';
            
            // Update user profile in database (optional)
            // $sql = "UPDATE users SET has_photo = 1 WHERE id = ?";
            // $stmt = $conn->prepare($sql);
            // $stmt->bind_param("i", $user_id);
            // $stmt->execute();
        } else {
            $response['message'] = 'Error saving the image.';
        }
        
        // Free up memory
        imagedestroy($new_img);
        imagedestroy($source);
    }
} else {
    $response['message'] = 'No file uploaded or an error occurred.';
}

// Redirect back to profile page
header('Location: profile.php?photo=' . ($response['success'] ? 'success' : 'error') . '&msg=' . urlencode($response['message']));
exit();
?>
