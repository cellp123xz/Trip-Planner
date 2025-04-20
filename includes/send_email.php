<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include PHPMailer autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Import PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

/**
 * Configure and send an email using PHPMailer
 * 
 * @param string $to Recipient email address
 * @param string $subject Email subject
 * @param string $body Email body (HTML)
 * @param string $altBody Plain text alternative body
 * @param array $attachments Optional array of attachments
 * @return bool True if email sent successfully, false otherwise
 */
function sendEmail($to, $subject, $body, $altBody = '', $attachments = []) {
    try {
        // Create a new PHPMailer instance
        $mail = new PHPMailer(true);
        
        // Server settings
        $mail->isSMTP();                                      // Use SMTP
        $mail->Host       = getenv('SMTP_HOST');              // SMTP server
        $mail->SMTPAuth   = true;                             // Enable SMTP authentication
        $mail->Username   = getenv('SMTP_USERNAME');          // SMTP username
        $mail->Password   = getenv('SMTP_PASSWORD');          // SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;   // Enable TLS encryption
        $mail->Port       = getenv('SMTP_PORT');              // TCP port to connect to
        
        // Recipients
        $mail->setFrom(getenv('SMTP_USERNAME'), 'TripPlanner');
        $mail->addAddress($to);                               // Add a recipient
        
        // Content
        $mail->isHTML(true);                                  // Set email format to HTML
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->AltBody = $altBody;
        
        // Add attachments if any
        if (!empty($attachments)) {
            foreach ($attachments as $attachment) {
                if (isset($attachment['path']) && file_exists($attachment['path'])) {
                    $mail->addAttachment(
                        $attachment['path'],
                        $attachment['name'] ?? basename($attachment['path'])
                    );
                }
            }
        }
        
        // Send the email
        $mail->send();
        return true;
    } catch (Exception $e) {
        // Log the error but don't expose details to users
        error_log("Email sending failed: {$mail->ErrorInfo}");
        return false;
    }
}

/**
 * Send a verification email to a newly registered user
 * 
 * @param string $userEmail User's email address
 * @param string $userName User's name
 * @param string $verificationCode Verification code
 * @return bool True if successful
 */
function sendVerificationEmail($userEmail, $userName, $verificationCode) {
    // In development mode, auto-verify users without sending emails
    foreach ($_SESSION['db']['users'] as &$user) {
        if ($user['email'] === $userEmail) {
            $user['is_verified'] = true;
            $user['email_verified'] = 1;
            break;
        }
    }
    
    // Build verification URL
    $verifyUrl = APP_URL . '/pages/verify.php?email=' . urlencode($userEmail) . '&code=' . $verificationCode;
    
    // Email content
    $subject = 'Verify Your TripPlanner Account';
    $body = "<div style=\"font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;\">\n        <h2 style=\"color: #0D6EFD;\">Welcome to TripPlanner!</h2>\n        <p>Hello {$userName},</p>\n        <p>Thank you for registering with TripPlanner. Please click the button below to verify your email address:</p>\n        <p style=\"text-align: center;\">\n            <a href=\"{$verifyUrl}\" style=\"display: inline-block; background-color: #0D6EFD; color: white; text-decoration: none; padding: 10px 20px; border-radius: 5px; font-weight: bold;\">Verify Email</a>\n        </p>\n        <p>If the button doesn't work, you can copy and paste this link into your browser:</p>\n        <p>{$verifyUrl}</p>\n        <p>This link will expire in 24 hours.</p>\n        <p>Best regards,<br>The TripPlanner Team</p>\n    </div>";
    
    $altBody = "Welcome to TripPlanner! Please verify your email by visiting: {$verifyUrl}";
    
    // For development, just log instead of actually sending
    error_log("[INFO] Verification email would be sent to: {$userEmail}");
    
    // Uncomment to actually send emails in production
    // return sendEmail($userEmail, $subject, $body, $altBody);
    
    return true;
}

/**
 * Send a password reset email
 * 
 * @param string $userEmail User's email address
 * @param string $userName User's name
 * @param string $resetToken Password reset token
 * @return bool True if successful
 */
function sendPasswordResetEmail($userEmail, $userName, $resetToken) {
    // Build reset URL
    $resetUrl = APP_URL . '/pages/reset-password.php?email=' . urlencode($userEmail) . '&token=' . $resetToken;
    
    // Email content
    $subject = 'Reset Your TripPlanner Password';
    $body = "<div style=\"font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;\">\n        <h2 style=\"color: #0D6EFD;\">Password Reset Request</h2>\n        <p>Hello {$userName},</p>\n        <p>We received a request to reset your TripPlanner password. Click the button below to create a new password:</p>\n        <p style=\"text-align: center;\">\n            <a href=\"{$resetUrl}\" style=\"display: inline-block; background-color: #0D6EFD; color: white; text-decoration: none; padding: 10px 20px; border-radius: 5px; font-weight: bold;\">Reset Password</a>\n        </p>\n        <p>If the button doesn't work, you can copy and paste this link into your browser:</p>\n        <p>{$resetUrl}</p>\n        <p>This link will expire in 1 hour. If you didn't request a password reset, you can safely ignore this email.</p>\n        <p>Best regards,<br>The TripPlanner Team</p>\n    </div>";
    
    $altBody = "Reset your TripPlanner password by visiting: {$resetUrl}";
    
    // For development, just log instead of actually sending
    error_log("[INFO] Password reset email would be sent to: {$userEmail} with token: {$resetToken}");
    
    // Uncomment to actually send emails in production
    // return sendEmail($userEmail, $subject, $body, $altBody);
    
    return true;
}

/**
 * Send a trip confirmation email
 * 
 * @param string $userEmail User's email address
 * @param string $userName User's name
 * @param array $tripDetails Trip details array
 * @return bool True if successful
 */
function sendTripConfirmationEmail($userEmail, $userName, $tripDetails) {
    // Format dates
    $startDate = date('F j, Y', strtotime($tripDetails['start_date']));
    $endDate = date('F j, Y', strtotime($tripDetails['end_date']));
    
    // Email content
    $subject = 'Your Trip to ' . $tripDetails['destination'] . ' is Confirmed!';
    $body = "<div style=\"font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;\">\n        <h2 style=\"color: #0D6EFD;\">Trip Confirmation</h2>\n        <p>Hello {$userName},</p>\n        <p>Your trip to <strong>{$tripDetails['destination']}</strong> has been successfully planned!</p>\n        \n        <div style=\"background-color: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;\">\n            <h3 style=\"margin-top: 0;\">Trip Details</h3>\n            <p><strong>Destination:</strong> {$tripDetails['destination']}</p>\n            <p><strong>Dates:</strong> {$startDate} to {$endDate}</p>\n            <p><strong>Activities:</strong> {$tripDetails['activities']}</p>\n            <p><strong>Notes:</strong> {$tripDetails['notes']}</p>\n        </div>\n        \n        <p>You can view and manage your trip details from your <a href=\"" . APP_URL . "/pages/dashboard.php\" style=\"color: #0D6EFD;\">dashboard</a>.</p>\n        <p>Have a wonderful trip!</p>\n        <p>Best regards,<br>The TripPlanner Team</p>\n    </div>";
    
    $altBody = "Your trip to {$tripDetails['destination']} from {$startDate} to {$endDate} has been confirmed.";
    
    // For development, just log instead of actually sending
    error_log("[INFO] Trip confirmation email would be sent to: {$userEmail}");
    
    // Uncomment to actually send emails in production
    // return sendEmail($userEmail, $subject, $body, $altBody);
    
    return true;
}
?>
