<?php
// Configure session for subdirectory support
if (session_status() === PHP_SESSION_NONE) {
    $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
    $cookiePath = $basePath ? $basePath : '/';
    
    session_set_cookie_params([
        'lifetime' => 604800, // 1 week
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
    <title>Forgot Password - Bookify</title>
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
                <p class="subtitle">Reset your password</p>
            </div>

            <div class="info-box" id="infoBox">
                <i class="fas fa-info-circle"></i>
                <p>Enter your email address and we'll send you a verification code to reset your password.</p>
            </div>

            <!-- Step 1: Email Input -->
            <form id="forgotPasswordForm" class="auth-form" style="display: block;">
                <div class="form-group">
                    <label for="email">
                        <i class="fas fa-envelope"></i> Email Address
                    </label>
                    <input type="email" id="email" placeholder="Enter your registered email" required autofocus>
                </div>

                <button type="submit" class="btn btn-primary" id="resetBtn">
                    <i class="fas fa-paper-plane"></i> Send Verification Code
                </button>
            </form>

            <!-- Step 2: Code Verification -->
            <form id="verifyCodeForm" class="auth-form" style="display: none;">
                <div class="form-group">
                    <label for="verificationCode">
                        <i class="fas fa-key"></i> Verification Code
                    </label>
                    <input type="text" id="verificationCode" placeholder="Enter 6-digit code" maxlength="6" pattern="[0-9]{6}" required autofocus>
                    <small style="color: #666; font-size: 12px; margin-top: 5px; display: block;">Check your email for the 6-digit verification code</small>
                </div>

                <button type="submit" class="btn btn-primary" id="verifyBtn">
                    <i class="fas fa-check"></i> Verify Code
                </button>
                <button type="button" class="btn btn-secondary" id="resendBtn">
                    <i class="fas fa-redo"></i> Resend Code
                </button>
            </form>

            <!-- Step 3: Reset Password -->
            <form id="resetPasswordForm" class="auth-form" style="display: none;">
                <input type="hidden" id="resetEmail" value="">
                <input type="hidden" id="resetCode" value="">

                <div class="form-group">
                    <label for="newPassword">
                        <i class="fas fa-lock"></i> New Password
                    </label>
                    <div class="password-input">
                        <input type="password" id="newPassword" placeholder="Enter new password" required autofocus>
                        <button type="button" class="toggle-password" id="togglePassword">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <label for="confirmNewPassword">
                        <i class="fas fa-lock"></i> Confirm Password
                    </label>
                    <div class="password-input">
                        <input type="password" id="confirmNewPassword" placeholder="Confirm new password" required>
                        <button type="button" class="toggle-password" id="toggleConfirmPassword">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary" id="resetPasswordBtn">
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
    <script src="<?php echo BASE_PATH; ?>/auth9.js?v=<?php echo ASSET_VERSION; ?>"></script>
</body>
</html>

