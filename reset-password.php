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

// Check if token is provided
if (!isset($_GET['token']) || empty($_GET['token'])) {
    header('Location: login');
    exit;
}

$token = htmlspecialchars($_GET['token']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Bookify</title>
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
                <div class="logo">
                <img src="icons/bookify logo.png" alt="bookify" height="50px">
                <h1>BOOKIFY</h1>
                </div>
                <p class="subtitle">Create a new password</p>
            </div>

            <div class="info-box">
                <i class="fas fa-info-circle"></i>
                <p>Enter your new password below. Make sure it's at least 6 characters long.</p>
            </div>

            <form id="resetPasswordForm" class="auth-form">
                <input type="hidden" id="token" value="<?php echo $token; ?>">

                <div class="form-group">
                    <label for="password">
                        <i class="fas fa-lock"></i> New Password
                    </label>
                    <div class="password-input">
                        <input type="password" id="password" placeholder="Enter new password" required autofocus>
                        <button type="button" class="toggle-password" id="togglePassword">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <label for="confirmPassword">
                        <i class="fas fa-lock"></i> Confirm Password
                    </label>
                    <div class="password-input">
                        <input type="password" id="confirmPassword" placeholder="Confirm new password" required>
                        <button type="button" class="toggle-password" id="toggleConfirmPassword">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary" id="resetBtn">
                    <i class="fas fa-key"></i> Reset Password
                </button>
            </form>

            <div class="auth-footer">
                <p>Remember your password? <a href="login">Login here</a></p>
            </div>
        </div>
    </div>

    <div id="toast" class="toast"></div>

    <script>
        // Pass PHP BASE_PATH to JavaScript
        const BASE_PATH = '<?php echo BASE_PATH; ?>';
    </script>
    <script src="<?php echo BASE_PATH; ?>/pwa10.js?v=<?php echo ASSET_VERSION; ?>"></script>
    <script src="<?php echo BASE_PATH; ?>/auth8.js?v=<?php echo ASSET_VERSION; ?>"></script>
</body>
</html>

