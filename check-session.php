<?php
// Session protection - include this file on all protected pages

// Configure session for subdirectory support
if (session_status() === PHP_SESSION_NONE) {
    // Set session cookie parameters for better security and subdirectory support
    $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
    $cookiePath = $basePath ? $basePath : '/';
    
    session_set_cookie_params([
        'lifetime' => 86400, // 24 hours
        'path' => $cookiePath,
        'domain' => '',
        'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    
    session_start();
}

function checkAuth() {
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        header('Location: login');
        exit;
    }
}

function getCurrentUser() {
    if (isset($_SESSION['user_id'])) {
        return [
            'id' => $_SESSION['user_id'],
            'name' => $_SESSION['user_name'],
            'email' => $_SESSION['user_email'],
            'profile_picture' => $_SESSION['user_profile_picture'] ?? null
        ];
    }
    return null;
}
?>

