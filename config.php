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
    return rtrim($script_name, '/');
}

// Define base path constant
define('BASE_PATH', getBasePath());

// Cache busting version for CSS/JS files
// Update this version number when you deploy new changes
define('ASSET_VERSION', '1.2.3');

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
    return $conn;
}
?>

