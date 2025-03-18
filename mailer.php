<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

function sendPasswordResetEmail($toEmail, $token) {
    $mail = new PHPMailer(true);

    try {
        // SMTP Configuration
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'sandanimasusadi@gmail.com'; 
        $mail->Password = 'jifg gird zxia txnt'; 
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;

        // Email Headers
        $mail->setFrom('sandanimasusadi@gmail.com', 'Marcharie'); 
        $mail->addAddress($toEmail); 

        // Email Content
        $mail->isHTML(true);
        $mail->Subject = 'Reset Your Password';
        $mail->Body = "
            <html>
            <body>
                <p>We received a request to reset your password. Click the link below to reset it:</p>
                <p><a href='http://localhost/Macherie/reset-password.php?token=$token'>Reset Password</a></p>
                <p>If you did not request this, please ignore this email.</p>
            </body>
            </html>";

        $mail->send();
        return true;
    } catch (Exception $e) {
        echo "Failed to send email. Error: {$mail->ErrorInfo}";
        return false;
    }
}
?>
