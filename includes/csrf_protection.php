<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function generateCsrfToken() {
    $token = bin2hex(random_bytes(32));
    $_SESSION['csrf_token'] = $token;
    $_SESSION['csrf_token_time'] = time();
    return $token;
}

function verifyCsrfToken($token) {
    if (!isset($_SESSION['csrf_token']) || !isset($_SESSION['csrf_token_time'])) {
        return false;
    }
    if (time() - $_SESSION['csrf_token_time'] > 7200) {
        unset($_SESSION['csrf_token']);
        unset($_SESSION['csrf_token_time']);
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

function csrfTokenField() {
    $token = isset($_SESSION['csrf_token']) ? $_SESSION['csrf_token'] : generateCsrfToken();
    echo '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
}

function csrfCheck() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return true;
    }
    if (!isset($_POST['csrf_token'])) {
        return false;
    }
    return verifyCsrfToken($_POST['csrf_token']);
}
?>
