<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include alerts.php to use its functions
require_once __DIR__ . '/alerts.php';

// We don't need to define these functions here since they're already in alerts.php
// Removed duplicate functions: includeAlertLibrary, showSuccessAlert, showErrorAlert

function showUserIsAuth() {
    if (isset($_SESSION['user_id'])) {
        header("Location: dashboard.php");
        exit;
    }
}

function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        header("Location: " . APP_URL . "/pages/login.php");
        exit;
    }
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getCurrentUser() {
    if (!isset($_SESSION['user_id'])) {
        return null;
    }
    return [
        'id' => $_SESSION['user_id'],
        'name' => $_SESSION['user_name'],
        'email' => $_SESSION['user_email']
    ];
}

function registerUser($name, $email, $password, $verificationCode = null) {
    require_once __DIR__ . '/security_functions.php';
    $name = sanitizeString($name);
    $email = sanitizeEmail($email);
    
    if (!$email) {
        return ['success' => false, 'message' => 'Invalid email format'];
    }
    
    foreach ($_SESSION['db']['users'] as $user) {
        if (strtolower($user['email']) === strtolower($email)) {
            return ['success' => false, 'message' => 'Email already exists'];
        }
    }
    
    require_once __DIR__ . '/password_policy.php';
    $passwordValidation = validatePasswordStrength($password);
    
    if (!$passwordValidation['success']) {
        return [
            'success' => false, 
            'message' => 'Password does not meet security requirements', 
            'password_errors' => $passwordValidation['errors']
        ];
    }
    
    if (!$verificationCode) {
        $verificationCode = bin2hex(random_bytes(32));
    }
    
    $userId = createUser($name, $email, $password, $verificationCode);
    
    sendVerificationEmail($email, $name, $verificationCode);
    
    return ['success' => true, 'user_id' => $userId];
}

function loginUser($email, $password) {
    $email = filter_var(trim($email), FILTER_SANITIZE_EMAIL);
    
    $user = getUserByEmail($email);
    
    if (!$user || !password_verify($password, $user['password'])) {
        return [
            'success' => false, 
            'message' => "Invalid email or password."
        ];
    }
    
    if (!$user['is_verified']) {
        $_SESSION['verify_email'] = $email;
        return ['success' => false, 'message' => 'Please verify your email first', 'needs_verification' => true];
    }
    
    require_once __DIR__ . '/session_manager.php';
    initSessionSecurity();
    
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['last_activity'] = time();
    
    foreach ($_SESSION['db']['users'] as &$dbUser) {
        if ($dbUser['id'] === $user['id']) {
            $dbUser['last_login'] = date('Y-m-d H:i:s');
            break;
        }
    }
    
    require_once __DIR__ . '/password_policy.php';
    if (passwordNeedsRehash($user['password'])) {
        foreach ($_SESSION['db']['users'] as &$dbUser) {
            if ($dbUser['id'] === $user['id']) {
                $dbUser['password'] = securePasswordHash($password);
                break;
            }
        }
    }
    
    saveSessionData();
    
    return ['success' => true, 'user' => $user];
}

function logoutUser() {
    $dbBackup = isset($_SESSION['db']) ? $_SESSION['db'] : null;
    
    $_SESSION = [];
    
    session_destroy();
    
    session_start();
    
    if ($dbBackup) {
        $_SESSION['db'] = $dbBackup;
    } else {
        if (file_exists(STORAGE_FILE)) {
            $data = json_decode(file_get_contents(STORAGE_FILE), true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($data)) {
                $_SESSION['db'] = $data;
            }
        }
    }
    
    $_SESSION['show_logout_message'] = true;
    $_SESSION['logout_message'] = 'You have been successfully logged out.';
}

function verifyUserEmail($email, $code) {
    foreach ($_SESSION['db']['users'] as &$user) {
        if ($user['email'] === $email && $user['verification_code'] === $code) {
            $user['is_verified'] = true;
            $user['email_verified'] = 1;
            
            saveSessionData();
            
            return ['success' => true];
        }
    }
    
    return ['success' => false, 'message' => 'Invalid verification code'];
}

function generatePasswordResetToken($email) {
    $user = getUserByEmail($email);
    
    if (!$user) {
        return ['success' => true, 'message' => 'If your email is registered, you will receive a password reset link.'];
    }
    
    $token = bin2hex(random_bytes(32));
    $expiry = time() + 3600; 
    
    foreach ($_SESSION['db']['users'] as &$dbUser) {
        if ($dbUser['email'] === $email) {
            $dbUser['reset_token'] = $token;
            $dbUser['reset_expiry'] = $expiry;
            break;
        }
    }
    
    saveSessionData();
    
    $resetUrl = APP_URL . "/pages/reset-password.php?token=" . $token . "&email=" . urlencode($email);
    
    return [
        'success' => true, 
        'token' => $token, 
        'url' => $resetUrl,
        'email' => $email,
        'expiry' => $expiry
    ];
}

function verifyPasswordResetToken($email, $token) {
    $user = getUserByEmail($email);
    
    if (!$user) {
        return ['success' => false, 'message' => 'Invalid or expired password reset link'];
    }
    
    if (!isset($user['reset_token']) || $user['reset_token'] !== $token) {
        return ['success' => false, 'message' => 'Invalid password reset token'];
    }
    
    
    if (!isset($user['reset_expiry']) || $user['reset_expiry'] < time()) {
        return ['success' => false, 'message' => 'Password reset link has expired'];
    }
    
    return ['success' => true, 'user' => $user];
}


function resetPassword($email, $token, $newPassword) {
    
    $result = verifyPasswordResetToken($email, $token);
    
    if (!$result['success']) {
        return $result;
    }
    
    
    require_once __DIR__ . '/password_policy.php';
    $passwordValidation = validatePasswordStrength($newPassword);
    
    if (!$passwordValidation['success']) {
        return [
            'success' => false, 
            'message' => 'Password does not meet security requirements', 
            'password_errors' => $passwordValidation['errors']
        ];
    }
    
    
    foreach ($_SESSION['db']['users'] as &$dbUser) {
        if ($dbUser['email'] === $email) {
            
            $dbUser['password'] = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]);
            
            
            unset($dbUser['reset_token']);
            unset($dbUser['reset_expiry']);
            
            
            saveSessionData();
            
            return ['success' => true, 'message' => 'Password has been reset successfully'];
        }
    }
    
    return ['success' => false, 'message' => 'Failed to reset password'];
}

function updateUser($userId, $data, $currentPassword = null) {
    require_once __DIR__ . '/security_functions.php';
    
    $user = getUserById($userId);
    if (!$user) {
        return ['success' => false, 'message' => 'User not found'];
    }
    
    $updates = [];
    $errors = [];
    
    // Update name if provided
    if (isset($data['name']) && !empty($data['name'])) {
        $name = sanitizeString(trim($data['name']));
        if ($name !== $user['name']) {
            $updates['name'] = $name;
            $_SESSION['user_name'] = $name; // Update session data
        }
    }
    
    // Update email if provided
    if (isset($data['email']) && !empty($data['email'])) {
        $email = sanitizeEmail(trim($data['email']));
        
        if (!$email) {
            $errors[] = 'Invalid email format';
        } else if ($email !== $user['email']) {
            // Check if email is already in use by another user
            foreach ($_SESSION['db']['users'] as $otherUser) {
                if ($otherUser['id'] !== $userId && strtolower($otherUser['email']) === strtolower($email)) {
                    $errors[] = 'Email is already in use by another account';
                    break;
                }
            }
            
            if (empty($errors)) {
                $updates['email'] = $email;
                $_SESSION['user_email'] = $email; // Update session data
            }
        }
    }
    
    // Update password if provided
    if (isset($data['new_password']) && !empty($data['new_password'])) {
        // Verify current password
        if (!$currentPassword || !password_verify($currentPassword, $user['password'])) {
            $errors[] = 'Current password is incorrect';
        } else {
            require_once __DIR__ . '/password_policy.php';
            $passwordValidation = validatePasswordStrength($data['new_password']);
            
            if (!$passwordValidation['success']) {
                $errors[] = 'New password does not meet security requirements';
            } else {
                $updates['password'] = password_hash($data['new_password'], PASSWORD_BCRYPT, ['cost' => 12]);
            }
        }
    }
    
    // If there are errors, return them
    if (!empty($errors)) {
        return ['success' => false, 'message' => $errors[0], 'errors' => $errors];
    }
    
    // If there are no updates, return success
    if (empty($updates)) {
        return ['success' => true, 'message' => 'No changes were made'];
    }
    
    // Apply updates
    foreach ($_SESSION['db']['users'] as &$dbUser) {
        if ($dbUser['id'] === $userId) {
            foreach ($updates as $key => $value) {
                $dbUser[$key] = $value;
            }
            $dbUser['updated_at'] = date('Y-m-d H:i:s');
            break;
        }
    }
    
    // Save changes
    saveSessionData();
    
    return ['success' => true, 'message' => 'Profile updated successfully'];
}
?>
