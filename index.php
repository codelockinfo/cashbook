<?php
// Configure session for subdirectory support
if (session_status() === PHP_SESSION_NONE) {
    $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
    // Normalize to lowercase for consistency with other files
    $cookiePath = $basePath ? strtolower($basePath) : '/';
    $isSecure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
    
    // Use SameSite=None with Secure=true for HTTPS (works for BOTH regular browsers and WebView)
    // Use SameSite=Lax for HTTP (works for regular browsers, WebView on HTTP has limitations)
    $sameSite = $isSecure ? 'None' : 'Lax';
    
    // IMPORTANT: Set session lifetime to match cookie lifetime
    // This ensures session data persists on server for the same duration as the cookie
    ini_set('session.gc_maxlifetime', 604800); // 1 week (matches cookie lifetime)
    
    session_set_cookie_params([
        'lifetime' => 604800, // 1 week
        'path' => $cookiePath,
        'domain' => '', // Empty domain works better with WebView
        'secure' => $isSecure, // Required when SameSite=None
        'httponly' => true,
        'samesite' => $sameSite
    ]);
    
    session_start();
}

// Check if user is logged in
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    // Redirect to dashboard if logged in
    header('Location: dashboard');
} else {
    // Redirect to login if not logged in
    header('Location: login');
}
exit;
?>

