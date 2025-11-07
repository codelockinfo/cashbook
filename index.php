<?php
// Configure session for subdirectory support
if (session_status() === PHP_SESSION_NONE) {
    $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
    $cookiePath = $basePath ? $basePath : '/';
    
    session_set_cookie_params([
        'lifetime' => 86400,
        'path' => $cookiePath,
        'domain' => '',
        'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
        'httponly' => true,
        'samesite' => 'Lax'
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

