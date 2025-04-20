<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Define constants
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 15 * 60); // 15 minutes in seconds

/**
 * Record a failed login attempt for an email address
 * 
 * @param string $email The email address that failed to log in
 */
function recordFailedLoginAttempt($email) {
    $email = strtolower(trim($email));
    
    // Initialize the login attempts array if it doesn't exist
    if (!isset($_SESSION['login_attempts'])) {
        $_SESSION['login_attempts'] = [];
    }
    
    // Initialize the attempts for this email if it doesn't exist
    if (!isset($_SESSION['login_attempts'][$email])) {
        $_SESSION['login_attempts'][$email] = [
            'count' => 0,
            'last_attempt' => 0,
            'lockout_until' => 0
        ];
    }
    
    // Increment the attempt count
    $_SESSION['login_attempts'][$email]['count']++;
    $_SESSION['login_attempts'][$email]['last_attempt'] = time();
    
    // If the user has exceeded the maximum number of attempts, lock the account
    if ($_SESSION['login_attempts'][$email]['count'] >= MAX_LOGIN_ATTEMPTS) {
        $_SESSION['login_attempts'][$email]['lockout_until'] = time() + LOGIN_LOCKOUT_TIME;
    }
    
    // Save the login attempts to the persistent storage
    saveSessionData();
}

/**
 * Reset the login attempts for an email address after successful login
 * 
 * @param string $email The email address to reset
 */
function resetLoginAttempts($email) {
    $email = strtolower(trim($email));
    
    if (isset($_SESSION['login_attempts']) && isset($_SESSION['login_attempts'][$email])) {
        unset($_SESSION['login_attempts'][$email]);
        saveSessionData();
    }
}

/**
 * Check if an email address is locked out due to too many failed login attempts
 * 
 * @param string $email The email address to check
 * @return array Result with locked status and time remaining if locked
 */
function checkLoginLockout($email) {
    $email = strtolower(trim($email));
    
    // If login attempts tracking is not initialized, the user is not locked out
    if (!isset($_SESSION['login_attempts']) || !isset($_SESSION['login_attempts'][$email])) {
        return ['locked' => false];
    }
    
    $attempts = $_SESSION['login_attempts'][$email];
    
    // If the user is not locked out or the lockout period has expired
    if ($attempts['lockout_until'] === 0 || time() > $attempts['lockout_until']) {
        // If the lockout has expired, reset the attempts
        if ($attempts['lockout_until'] !== 0 && time() > $attempts['lockout_until']) {
            $_SESSION['login_attempts'][$email]['count'] = 0;
            $_SESSION['login_attempts'][$email]['lockout_until'] = 0;
            saveSessionData();
        }
        
        return ['locked' => false];
    }
    
    // Calculate the time remaining in the lockout
    $timeRemaining = $attempts['lockout_until'] - time();
    $minutesRemaining = ceil($timeRemaining / 60);
    
    return [
        'locked' => true,
        'time_remaining' => $timeRemaining,
        'minutes_remaining' => $minutesRemaining
    ];
}

/**
 * Get the number of remaining login attempts for an email address
 * 
 * @param string $email The email address to check
 * @return int The number of remaining attempts
 */
function getRemainingLoginAttempts($email) {
    $email = strtolower(trim($email));
    
    // If login attempts tracking is not initialized, the user has all attempts available
    if (!isset($_SESSION['login_attempts']) || !isset($_SESSION['login_attempts'][$email])) {
        return MAX_LOGIN_ATTEMPTS;
    }
    
    $attempts = $_SESSION['login_attempts'][$email];
    
    // If the lockout period has expired, reset the attempts
    if ($attempts['lockout_until'] !== 0 && time() > $attempts['lockout_until']) {
        $_SESSION['login_attempts'][$email]['count'] = 0;
        $_SESSION['login_attempts'][$email]['lockout_until'] = 0;
        saveSessionData();
        return MAX_LOGIN_ATTEMPTS;
    }
    
    return max(0, MAX_LOGIN_ATTEMPTS - $attempts['count']);
}
?>
