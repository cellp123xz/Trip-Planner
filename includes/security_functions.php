<?php

function escapeHtml($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

function sanitizeEmail($email) {
    $email = filter_var(trim($email), FILTER_SANITIZE_EMAIL);
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return $email;
    }
    return false;
}

function sanitizeUrl($url) {
    return filter_var($url, FILTER_SANITIZE_URL);
}

function sanitizeString($string) {
    return htmlspecialchars(trim($string), ENT_QUOTES, 'UTF-8');
}

function setSecurityHeaders() {
    header('X-Frame-Options: DENY');
    header('X-XSS-Protection: 1; mode=block');
    header('X-Content-Type-Options: nosniff');
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header("Content-Security-Policy: default-src 'self'; script-src 'self' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com 'unsafe-inline'; style-src 'self' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com 'unsafe-inline'; img-src 'self' data:; font-src 'self' https://cdnjs.cloudflare.com;");
}


function isDevelopmentEnvironment() {
    $devHosts = ['localhost', '127.0.0.1', '::1'];
    $currentHost = $_SERVER['HTTP_HOST'] ?? '';
    
    
    foreach ($devHosts as $devHost) {
        if (strpos($currentHost, $devHost) !== false) {
            return true;
        }
    }
    
    return false;
}


function protectDevFeature($isDev = true) {
    
    if (!$isDev) {
        return true;
    }
    
    
    return isDevelopmentEnvironment();
}
?>
