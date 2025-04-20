<?php

if (session_status() === PHP_SESSION_NONE) {
    
    $currentCookieParams = session_get_cookie_params();
    session_set_cookie_params(
        $currentCookieParams["lifetime"],
        $currentCookieParams["path"],
        $currentCookieParams["domain"],
        isset($_SERVER['HTTPS']), 
        true                       
    );
    
    
    session_start();
}


define('SESSION_TIMEOUT', 1800);           
define('SESSION_REGENERATE_ID_TIME', 300); 


function initSessionSecurity() {
    
    session_regenerate_id(true);
    
    
    $_SESSION['created'] = time();
    
    
    $_SESSION['last_activity'] = time();
    
    
    $_SESSION['last_regeneration'] = time();
    
    
    $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];
    $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
}


function isSessionHijacked() {
    if (!isset($_SESSION['ip_address']) || !isset($_SESSION['user_agent'])) {
        return false;
    }
    
    
    if ($_SESSION['ip_address'] !== $_SERVER['REMOTE_ADDR']) {
        return true;
    }
    
    
    if ($_SESSION['user_agent'] !== $_SERVER['HTTP_USER_AGENT']) {
        return true;
    }
    
    return false;
}


function checkSessionIdRegeneration() {
    
    if (!isset($_SESSION['user_id'])) {
        return;
    }
    
    
    if (!isset($_SESSION['last_regeneration'])) {
        $_SESSION['last_regeneration'] = time();
        return;
    }
    
    
    if (time() - $_SESSION['last_regeneration'] > SESSION_REGENERATE_ID_TIME) {
        // Generate a new session ID
        session_regenerate_id(true);
        
        
        $_SESSION['last_regeneration'] = time();
    }
}


function checkSessionTimeout() {
    
    if (!isset($_SESSION['user_id'])) {
        return;
    }
    
    
    if (!isset($_SESSION['last_activity'])) {
        $_SESSION['last_activity'] = time();
        return;
    }
    
    
    if (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT) {
        
        require_once __DIR__ . '/auth_functions.php';
        
        
        $_SESSION['logout_message'] = 'Your session has expired due to inactivity. Please log in again.';
        
        
        logoutUser();
        
        
        header("Location: " . APP_URL . "/pages/login.php");
        exit;
    }
    
    
    $_SESSION['last_activity'] = time();
}


function performSessionSecurityChecks() {
    
    if (isSessionHijacked()) {
        
        require_once __DIR__ . '/auth_functions.php';
        
        
        $_SESSION['logout_message'] = 'Your session has been terminated for security reasons. Please log in again.';
        
        
        logoutUser();
        
        
        header("Location: " . APP_URL . "/pages/login.php");
        exit;
    }
    
    
    checkSessionTimeout();
    
    
    checkSessionIdRegeneration();
}


performSessionSecurityChecks();
?>
