<?php
// Session will be configured in config.php
// Just ensure it's started if config.php hasn't been loaded yet
if (session_status() === PHP_SESSION_NONE) {
    $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
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

// Redirect to dashboard if already logged in
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header('Location: dashboard');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Bookify</title>
    <?php 
    require_once 'config.php';
    include 'pwa-meta.php'; 
    ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_PATH; ?>/auth-style8.css?v=<?php echo ASSET_VERSION; ?>">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <div class="logo" style="gap:5px;">
                <img src="icons/bookify logo.png" alt="bookify" height="50px">
                <h1>BOOKIFY</h1>
                </div>
                <p class="subtitle">Login to your account</p>
            </div>

            <form id="loginForm" class="auth-form">
                <div class="form-group">
                    <label for="email">
                        <i class="fas fa-envelope"></i> Email Address
                    </label>
                    <input type="email" id="email" placeholder="Enter your email" required autofocus>
                </div>

                <div class="form-group">
                    <label for="password">
                        <i class="fas fa-lock"></i> Password
                    </label>
                    <div class="password-input">
                        <input type="password" id="password" placeholder="Enter your password" required>
                        <button type="button" class="toggle-password" id="togglePassword">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary" id="loginBtn">
                    <i class="fas fa-sign-in-alt"></i> Login
                </button>

                <div class="forgot-password-link">
                    <a href="forgot-password"><i class="fas fa-question-circle"></i> Forgot Password?</a>
                </div>
            </form>

            <div class="auth-footer">
                <p>Don't have an account? <a href="register">Register here</a></p>
            </div>
        </div>
    </div>

    <div id="toast" class="toast"></div>

    <script>
        // Pass PHP BASE_PATH to JavaScript
        const BASE_PATH = '<?php echo BASE_PATH; ?>';
        
        // Clear any old session cookies with wrong path (case mismatch)
        // This fixes the issue where cookies might have /Cashbook instead of /cashbook
        (function() {
            // Get all cookies
            const cookies = document.cookie.split(';');
            cookies.forEach(function(cookie) {
                const eqPos = cookie.indexOf('=');
                const name = eqPos > -1 ? cookie.substr(0, eqPos).trim() : cookie.trim();
                // Clear PHPSESSID cookies with wrong path
                if (name === 'PHPSESSID') {
                    // Clear with various possible paths
                    document.cookie = name + '=; path=/; expires=Thu, 01 Jan 1970 00:00:00 GMT';
                    document.cookie = name + '=; path=/Cashbook; expires=Thu, 01 Jan 1970 00:00:00 GMT';
                    document.cookie = name + '=; path=/cashbook; expires=Thu, 01 Jan 1970 00:00:00 GMT';
                    document.cookie = name + '=; path=<?php echo BASE_PATH; ?>; expires=Thu, 01 Jan 1970 00:00:00 GMT';
                }
            });
        })();
    </script>
    <script src="<?php echo BASE_PATH; ?>/pwa10.js?v=<?php echo ASSET_VERSION; ?>"></script>
    <script src="<?php echo BASE_PATH; ?>/auth9.js?v=<?php echo ASSET_VERSION; ?>"></script>
</body>
</html>

