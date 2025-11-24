<?php
// Detect environment (local vs live)
function isLocalEnvironment() {
    $local_hosts = ['localhost', '127.0.0.1', '::1', 'localhost:8080'];
    $server_name = $_SERVER['SERVER_NAME'] ?? $_SERVER['HTTP_HOST'] ?? 'localhost';
    
    // Check if it's a local environment
    foreach ($local_hosts as $local) {
        if (strpos($server_name, $local) !== false) {
            return true;
        }
    }
    
    return false;
}

// Get base path for URLs (auto-detect subdirectory)
function getBasePath() {
    $script_name = dirname($_SERVER['SCRIPT_NAME']);
    $path = rtrim($script_name, '/');
    // Normalize to lowercase to avoid case sensitivity issues with cookies
    return strtolower($path);
}

// Define base path constant
define('BASE_PATH', getBasePath());

// Cache busting version for CSS/JS files
// Update this version number when you deploy new changes
define('ASSET_VERSION', '1.5.0');

// Set PHP timezone to Asia/Kolkata (IST)
date_default_timezone_set('Asia/Kolkata');

// Set database configuration based on environment
if (isLocalEnvironment()) {
    // Local development credentials
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'cash_book');
} else {
    // Live/Production credentials
    define('DB_HOST', 'localhost');
    define('DB_USER', 'u402017191_cashbook');
    define('DB_PASS', '99@Cashbook');
    define('DB_NAME', 'u402017191_cashbook');
}

// Create database connection
function getDBConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        die(json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]));
    }
    
    $conn->set_charset('utf8mb4');
    
    // Set MySQL session timezone to IST
    $conn->query("SET time_zone = '+05:30'");
    
    return $conn;
}

// ═══════════════════════════════════════════════════════════
// 📧 SMTP CONFIGURATION FOR EMAIL SENDING
// ═══════════════════════════════════════════════════════════

// Optional mail configuration (define SMTP_* constants here)
$mailConfigFile = __DIR__ . '/mail.php';
if (file_exists($mailConfigFile)) {
    require_once $mailConfigFile;
}

if (!defined('SMTP_HOST')) {
    define('SMTP_HOST', getenv('SMTP_HOST') ?: 'smtp.hostinger.com');
}

if (!defined('SMTP_PORT')) {
    define('SMTP_PORT', (int)(getenv('SMTP_PORT') ?: 465));
}

if (!defined('SMTP_ENCRYPTION')) {
    // Port 465 typically uses 'ssl', port 587 uses 'tls'
    define('SMTP_ENCRYPTION', strtolower(getenv('SMTP_ENCRYPTION') ?: 'ssl'));
}

if (!defined('SMTP_USERNAME')) {
    define('SMTP_USERNAME', getenv('SMTP_USERNAME') ?: 'tailorpro@happyeventsurat.com');
}

if (!defined('SMTP_PASSWORD')) {
    define('SMTP_PASSWORD', getenv('SMTP_PASSWORD') ?: 'Tailor@99');
}

if (!defined('SMTP_FROM_EMAIL')) {
    define('SMTP_FROM_EMAIL', getenv('SMTP_FROM_EMAIL') ?: 'tailorpro@happyeventsurat.com');
}

if (!defined('SMTP_FROM_NAME')) {
    define('SMTP_FROM_NAME', getenv('SMTP_FROM_NAME') ?: 'Bookify');
}

// ═══════════════════════════════════════════════════════════
// 🌐 SITE INFORMATION
// ═══════════════════════════════════════════════════════════

if (!defined('SITE_NAME')) {
    define('SITE_NAME', 'Cash Book');  // Your application name
}

if (!defined('SITE_URL')) {
    // Auto-detect SITE_URL from current request
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $scriptPath = dirname($_SERVER['SCRIPT_NAME'] ?? '');
    $scriptPath = ($scriptPath === '/' || $scriptPath === '\\') ? '' : $scriptPath;
    define('SITE_URL', $protocol . '://' . $host . $scriptPath);
}

// ═══════════════════════════════════════════════════════════
// 🔐 SESSION MANAGEMENT
// ═══════════════════════════════════════════════════════════

// Get cookie parameters optimized for both regular browsers and WebView compatibility
function getSessionCookieParams() {
    $cookiePath = defined('BASE_PATH') && BASE_PATH ? BASE_PATH : '/';
    $isSecure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
    
    // Use SameSite=None with Secure=true for HTTPS (works for BOTH regular browsers and WebView)
    // Use SameSite=Lax for HTTP (works for regular browsers, WebView on HTTP has limitations)
    // SameSite=None requires Secure=true, so only use it with HTTPS
    $sameSite = $isSecure ? 'None' : 'Lax';
    
    return [
        'lifetime' => 604800, // 1 week
        'path' => $cookiePath,
        'domain' => '', // Empty domain allows subdomains and works better with WebView
        'secure' => $isSecure, // Required when SameSite=None
        'httponly' => true, // HttpOnly works fine for both browsers and WebView
        'samesite' => $sameSite
    ];
}

// Start session if not already started (with proper cookie params)
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(getSessionCookieParams());
    session_start();
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Get current user ID
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

// Redirect if not logged in
function requireLogin() {
    if (!isLoggedIn()) {
        $basePath = BASE_PATH ?: '';
        header('Location: ' . $basePath . '/login.php');
        exit();
    }
}
?>

