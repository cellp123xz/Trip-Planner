<?php
function validatePasswordStrength($password) {
    $errors = [];
    
    if (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters long";
    }
    
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = "Password must contain at least one uppercase letter";
    }
    
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = "Password must contain at least one lowercase letter";
    }
    
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = "Password must contain at least one number";
    }
    
    if (!preg_match('/[^A-Za-z0-9]/', $password)) {
        $errors[] = "Password must contain at least one special character";
    }
    
    if (empty($errors)) {
        return ['success' => true];
    } else {
        return ['success' => false, 'errors' => $errors];
    }
}

function securePasswordHash($password) {
    return password_hash($password, PASSWORD_DEFAULT, ['cost' => 12]);
}

function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

function passwordNeedsRehash($hash) {
    return password_needs_rehash($hash, PASSWORD_DEFAULT, ['cost' => 12]);
}
?>
