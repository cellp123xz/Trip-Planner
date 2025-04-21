<?php
require_once '../includes/config.php';
require_once '../includes/auth_functions.php';
require_once '../includes/send_email.php';
require_once '../includes/alerts.php';

if (!isset($_SESSION['verify_email'])) {
    header("Location: login.php");
    exit;
}

$email = $_SESSION['verify_email'];
$user = getUserByEmail($email);

if (!$user) {
    setAlert(
        'Error',
        'User not found. Please try registering again.',
        'error',
        ['then' => 'function() { window.location.href = "register.php"; }']
    );
    exit;
}

$newCode = bin2hex(random_bytes(16));

foreach ($_SESSION['db']['users'] as &$dbUser) {
    if ($dbUser['email'] === $email) {
        $dbUser['verification_code'] = $newCode;
        break;
    }
}

saveSessionData();

$result = sendVerificationEmail($email, $user['name'], $newCode);

header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'message' => 'A new verification code has been sent to your email.'
]);
?>
