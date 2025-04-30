<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST['email'];

    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $token = bin2hex(random_bytes(50));
        $expires = date("Y-m-d H:i:s", strtotime("+1 hour"));

        $stmt = $conn->prepare("UPDATE users SET reset_token = ?, reset_expires = ? WHERE email = ?");
        $stmt->bind_param("sss", $token, $expires, $email);
        if ($stmt->execute()) {
            $msg = urlencode("Reset link has been created! Check your email.");
            header("Location: forgotpassword.php?success=$msg");
            exit;
        } else {
            $msg = urlencode("Failed to save reset token.");
            header("Location: forgotpassword.php?error=$msg");
            exit;
        }
    } else {
        $msg = urlencode("No account found with that email.");
        header("Location: forgotpassword.php?error=$msg");
        exit;
    }
}
?>
