<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/security_functions.php';

/**
 * Get the list of available security questions
 * 
 * @return array List of security questions
 */
function getSecurityQuestions() {
    return [
        'What was your first pet\'s name?',
        'In what city were you born?',
        'What is your mother\'s maiden name?',
        'What high school did you attend?',
        'What was the make of your first car?',
        'What was your childhood nickname?',
        'What is your favorite movie?',
        'What is the name of your favorite childhood teacher?',
        'What street did you grow up on?',
        'What was the first concert you attended?'
    ];
}

/**
 * Get the security question for a user
 * 
 * @param string $email User's email address
 * @return array Result with success status and security question if found
 */
function getUserSecurityQuestion($email) {
    $user = getUserByEmail($email);
    
    if (!$user || !isset($user['security_question']) || empty($user['security_question'])) {
        // Don't reveal if user exists or not
        return [
            'success' => false,
            'message' => 'No security question found for this email'
        ];
    }
    
    return [
        'success' => true,
        'question' => $user['security_question'],
        'user' => $user
    ];
}

/**
 * Verify a user's security answer
 * 
 * @param string $email User's email address
 * @param string $answer The security answer provided
 * @return array Result with success status and message
 */
function verifySecurityAnswer($email, $answer) {
    $user = getUserByEmail($email);
    
    if (!$user || !isset($user['security_answer']) || empty($user['security_answer'])) {
        // Don't reveal if user exists or not
        return [
            'success' => false,
            'message' => 'Invalid email or security answer'
        ];
    }
    
    // Verify the security answer
    if (!password_verify($answer, $user['security_answer'])) {
        return [
            'success' => false,
            'message' => 'Incorrect security answer'
        ];
    }
    
    // Generate a reset token
    $token = bin2hex(random_bytes(16));
    $expires = date('Y-m-d H:i:s', strtotime('+15 minutes')); // Short expiration for security
    
    // Update user with reset token
    foreach ($_SESSION['db']['users'] as &$dbUser) {
        if ($dbUser['id'] === $user['id']) {
            $dbUser['reset_token'] = $token;
            $dbUser['reset_token_expires'] = $expires;
            break;
        }
    }
    
    // Save changes to persistent storage
    saveSessionData();
    
    return [
        'success' => true,
        'message' => 'Security answer verified. You can now reset your password.',
        'token' => $token,
        'email' => $email
    ];
}

/**
 * Reset a user's password using security answer
 * 
 * @param string $email User's email address
 * @param string $token Reset token
 * @param string $newPassword New password
 * @return array Result with success status and message
 */
function resetPasswordWithSecurityAnswer($email, $token, $newPassword) {
    $user = getUserByEmail($email);
    
    if (!$user || !isset($user['reset_token']) || $user['reset_token'] !== $token) {
        return [
            'success' => false,
            'message' => 'Invalid or expired password reset link.'
        ];
    }
    
    // Check if token is expired
    $now = new DateTime();
    $expires = new DateTime($user['reset_token_expires']);
    
    if ($now > $expires) {
        return [
            'success' => false,
            'message' => 'Password reset link has expired. Please try again.'
        ];
    }
    
    // Validate password strength
    require_once __DIR__ . '/password_policy.php';
    $passwordValidation = validatePasswordStrength($newPassword);
    
    if (!$passwordValidation['success']) {
        return [
            'success' => false,
            'message' => 'Password does not meet security requirements',
            'password_errors' => $passwordValidation['errors']
        ];
    }
    
    // Update user's password
    foreach ($_SESSION['db']['users'] as &$user) {
        if ($user['email'] === $email) {
            $user['password'] = password_hash($newPassword, PASSWORD_DEFAULT);
            $user['reset_token'] = null;
            $user['reset_token_expires'] = null;
            break;
        }
    }
    
    // Save changes to persistent storage
    saveSessionData();
    
    return [
        'success' => true,
        'message' => 'Your password has been successfully reset. You can now log in with your new password.'
    ];
}
?>
