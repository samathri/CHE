<?php 
require __DIR__ . "/mailer.php";
include __DIR__ . "/php/db.php";  

if (isset($_POST["email"])) {
    $user_email = $_POST["email"];
    
    // Generate token
    $token = bin2hex(random_bytes(16));
    $token_hash = hash("sha256", $token);
    
    // Set expiry time (30 minutes from now)
    $expiry = date("Y-m-d H:i:s", time() + 60 * 30);

    // Database connection
    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    // Check if email exists
    $check_sql = "SELECT user_email FROM user_registration WHERE user_email = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("s", $user_email);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows === 0) {
        die("No account found with the provided email address.");
    }
    
    // Update token and expiry
    $sql = "UPDATE user_registration 
            SET reset_token_hash = ?, 
                reset_token_expires_at = ? 
            WHERE user_email = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Database error: " . $conn->error);
    }
    $stmt->bind_param("sss", $token_hash, $expiry, $user_email);
    
    if (!$stmt->execute()) {
        die("Error updating the reset token.");
    }
    
    // Verify the update and send email
    if ($conn->affected_rows > 0) {
        if (sendPasswordResetEmail($user_email, $token)) {
            echo "<script>
                    alert('Password reset email sent successfully. Check your mail address');
                    window.location.href = 'reset.php';
                  </script>";
        } else {
            echo "<script>
                    alert('Failed to send the password reset email.');
                    window.location.href = 'reset.php';
                  </script>";
        }
    } else {
        echo "<script>
                alert('Failed to update the reset token in the database.');
                window.location.href = 'reset.php';
              </script>";
    }
    
    
    $stmt->close();
    $conn->close();
} else {
    echo "Email is required.";
}
?>
