<?php
/**
 * Token-based login endpoint for Flutter WebView app
 * 
 * This file handles token verification and automatic session creation
 * Usage: https://yourwebsite.com/app-login?token=YOUR_TOKEN
 */

// Configure session for subdirectory support
if (session_status() === PHP_SESSION_NONE) {
    $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
    $cookiePath = $basePath ? strtolower($basePath) : '/';
    $isSecure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
    
    // Use SameSite=None with Secure=true for HTTPS (works for BOTH regular browsers and WebView)
    // Use SameSite=Lax for HTTP (works for regular browsers, WebView on HTTP has limitations)
    $sameSite = $isSecure ? 'None' : 'Lax';
    
    // IMPORTANT: Set session lifetime to match cookie lifetime
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

require_once 'config.php';

// Check if token is provided
// Support both ?token= and custom parameter names
$token = null;
if (isset($_GET['token']) && !empty($_GET['token'])) {
    $token = trim($_GET['token']);
} elseif (isset($_GET['app-login']) && !empty($_GET['app-login'])) {
    // Alternative parameter name support
    $token = trim($_GET['app-login']);
}

if (empty($token)) {
    // No token provided, redirect to login page
    error_log("app-login.php - No token provided in request. GET params: " . print_r($_GET, true));
    header('Location: ' . BASE_PATH . '/login?error=no_token');
    exit;
}

try {
    $conn = getDBConnection();
    
    // Verify token and get user
    $stmt = $conn->prepare("SELECT id, name, email, profile_picture FROM users WHERE api_token = ?");
    if (!$stmt) {
        error_log("app-login.php - Database error: " . $conn->error);
        header('Location: ' . BASE_PATH . '/login?error=db_error');
        exit;
    }
    
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        // Invalid token
        error_log("app-login.php - Invalid token attempted: " . substr($token, 0, 8) . "...");
        $stmt->close();
        $conn->close();
        header('Location: ' . BASE_PATH . '/login?error=invalid_token');
        exit;
    }
    
    $user = $result->fetch_assoc();
    $stmt->close();
    
    // Token is valid, create session
    // Clear any old session data first
    $_SESSION = array();
    
    // Set session variables
    $_SESSION['user_id'] = (int)$user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_profile_picture'] = $user['profile_picture'] ?? null;
    $_SESSION['logged_in'] = true;
    
    // Regenerate session ID for security
    session_regenerate_id(true);
    
    // Ensure session cookie is sent
    $sessionId = session_id();
    $secure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
    $sameSite = $secure ? 'None' : 'Lax';
    
    setcookie(
        session_name(),
        $sessionId,
        [
            'expires' => time() + 604800, // 7 days
            'path' => $cookiePath,
            'domain' => '',
            'secure' => $secure,
            'httponly' => true,
            'samesite' => $sameSite
        ]
    );
    
    error_log("app-login.php - Token login successful for user ID: " . $user['id'] . ", Email: " . $user['email']);
    error_log("app-login.php - Token used: " . substr($token, 0, 16) . "...");
    error_log("app-login.php - Redirecting to dashboard");
    
    $conn->close();
    
    // Redirect to dashboard
    header('Location: ' . BASE_PATH . '/dashboard');
    exit;
    
} catch (Exception $e) {
    error_log("app-login.php - Error: " . $e->getMessage());
    header('Location: ' . BASE_PATH . '/login?error=server_error');
    exit;
}
?>

