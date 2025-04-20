<?php
// This file is now deprecated. All password functions have been moved to auth_functions.php
// to avoid function name conflicts.

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth_functions.php';

// No functions defined here to avoid conflicts
?>
