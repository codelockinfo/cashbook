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
    <link rel="stylesheet" href="<?php echo BASE_PATH; ?>/auth-style4.css?v=<?php echo ASSET_VERSION; ?>">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <div class="logo" style="gap:5px;">
                <img src="icons/Black and Green Simple Organic Cosmetic Product Logo (4).png" alt="bookify" height="50px">
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
    </script>
    <script src="<?php echo BASE_PATH; ?>/pwa10.js?v=<?php echo ASSET_VERSION; ?>"></script>
    <script src="<?php echo BASE_PATH; ?>/auth3.js?v=<?php echo ASSET_VERSION; ?>"></script>
</body>
</html>

