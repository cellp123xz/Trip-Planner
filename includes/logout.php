<?php
require_once 'config.php';
require_once 'auth_functions.php';

// Perform logout (this function already handles the session messages)
logoutUser();

// Redirect to login page with proper path
header("Location: " . APP_URL . "/pages/login.php");
exit;
?>
