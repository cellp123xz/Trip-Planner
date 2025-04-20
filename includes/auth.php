<?php
// Include config.php first since it contains database connection
require_once __DIR__ . '/config.php';

// Then include the auth_functions.php file
require_once __DIR__ . '/auth_functions.php';

function getLoggedInUser() {
    if (!isLoggedIn()) return null;
    
    return getUserById($_SESSION['user_id']);
}

function requireGuest() {
    if (isLoggedIn()) {
        setAlert('Already Logged In', 'You are already logged in.', 'info');
        header('Location: dashboard.php');
        exit;
    }
}

function logout() {
    logoutUser();
}

function getUserData() {
    return getCurrentUser();
}

function getCurrentUserId() {
    return isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
}
?>
