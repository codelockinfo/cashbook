<?php
// Session protection - include this file on all protected pages

// Configure session for subdirectory support
// This file is included after config.php, so BASE_PATH is available
if (session_status() === PHP_SESSION_NONE) {
    // Use BASE_PATH constant for consistency (normalized to lowercase)
    $cookiePath = defined('BASE_PATH') && BASE_PATH ? BASE_PATH : '/';
    $isSecure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
    
    // Use SameSite=None with Secure=true for HTTPS (works for BOTH regular browsers and WebView)
    // Use SameSite=Lax for HTTP (works for regular browsers, WebView on HTTP has limitations)
    $sameSite = $isSecure ? 'None' : 'Lax';
    
    // IMPORTANT: Set session lifetime to match cookie lifetime
    // This ensures session data persists on server for the same duration as the cookie
    ini_set('session.gc_maxlifetime', 604800); // 1 week (matches cookie lifetime)
    
    // IMPORTANT: Set cookie params BEFORE starting session
    // This ensures the session can read existing cookies
    session_set_cookie_params([
        'lifetime' => 604800, // 1 week
        'path' => $cookiePath,
        'domain' => '', // Empty domain works better with WebView
        'secure' => $isSecure, // Required when SameSite=None
        'httponly' => true,
        'samesite' => $sameSite
    ]);
    
    // Set session name explicitly
    session_name('PHPSESSID');
    
    session_start();
    
    // Debug: Log session info
    error_log("check-session.php - Session started - ID: " . session_id());
    error_log("check-session.php - Cookie path: " . $cookiePath);
    error_log("check-session.php - Received cookies: " . print_r($_COOKIE, true));
}

function checkAuth() {
    // Debug logging
    error_log("checkAuth() called - Session ID: " . session_id());
    error_log("checkAuth() - logged_in: " . (isset($_SESSION['logged_in']) ? ($_SESSION['logged_in'] ? 'true' : 'false') : 'not set'));
    error_log("checkAuth() - user_id: " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'not set'));
    error_log("checkAuth() - Session cookie path: " . ini_get('session.cookie_path'));
    error_log("checkAuth() - All session vars: " . print_r($_SESSION, true));
    
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        error_log("checkAuth() - Authentication failed, redirecting to login");
        header('Location: login');
        exit;
    }
    
    error_log("checkAuth() - Authentication successful");
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

