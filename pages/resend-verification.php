<?php
require_once '../includes/config.php';
require_once '../includes/auth_functions.php';
require_once '../includes/send_email.php';
require_once '../includes/alerts.php';

// Check if we have an email to verify
if (!isset($_SESSION['verify_email'])) {
    header("Location: login.php");
    exit;
}

$email = $_SESSION['verify_email'];
$user = getUserByEmail($email);

if (!$user) {
    // User not found
    setAlert(
        'Error',
        'User not found. Please try registering again.',
        'error',
        ['then' => 'function() { window.location.href = "register.php"; }']
    );
    exit;
}

// Generate a new verification code
$newCode = bin2hex(random_bytes(16));

// Update user with new verification code
foreach ($_SESSION['db']['users'] as &$dbUser) {
    if ($dbUser['email'] === $email) {
        $dbUser['verification_code'] = $newCode;
        break;
    }
}

// Save changes to persistent storage
saveSessionData();

// Send new verification email
$result = sendVerificationEmail($email, $user['name'], $newCode);

// Return JSON response for AJAX call
header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'message' => 'A new verification code has been sent to your email.'
]);
?>
