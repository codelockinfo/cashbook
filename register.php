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
    <title>Register - Bookify</title>
    <?php 
    require_once 'config.php';
    include 'pwa-meta.php'; 
    ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_PATH; ?>/auth-style5.css?v=<?php echo ASSET_VERSION; ?>">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <div class="logo" style="gap:5px;">
                <img src="icons/Black and Green Simple Organic Cosmetic Product Logo (4).png" alt="bookify" height="50px">
                <h1>BOOKIFY</h1>
                </div>
                <p class="subtitle">Create your account</p>
            </div>
            
            <form id="registerForm" class="auth-form" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="profilePicture">
                        <i class="fas fa-camera"></i> Profile Picture (Optional)
                    </label>
                    <div class="profile-picture-upload">
                        <div class="profile-preview">
                            <img id="profilePreview" src="uploads/default-avatar.png" alt="Profile Preview" 
                                 onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22%3E%3Ccircle cx=%2250%22 cy=%2250%22 r=%2250%22 fill=%22%23e5e7eb%22/%3E%3Ctext x=%2250%22 y=%2250%22 text-anchor=%22middle%22 dy=%22.3em%22 font-size=%2240%22 fill=%22%23666%22%3E👤%3C/text%3E%3C/svg%3E'">
                        </div>
                        <div class="file-input-wrapper">
                            <input type="file" id="profilePicture" name="profilePicture" accept="image/*" capture="user">
                            <label for="profilePicture" class="file-input-label">
                                <i class="fas fa-upload"></i> Choose Photo
                            </label>
                            <span class="file-name" id="fileName">No file chosen</span>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="name">
                        <i class="fas fa-user"></i> Full Name
                    </label>
                    <input type="text" id="name" placeholder="Enter your full name" required autofocus>
                </div>

                <div class="form-group">
                    <label for="email">
                        <i class="fas fa-envelope"></i> Email Address
                    </label>
                    <input type="email" id="email" placeholder="Enter your email" required>
                </div>

                <div class="form-group">
                    <label for="password">
                        <i class="fas fa-lock"></i> Password
                    </label>
                    <div class="password-input">
                        <input type="password" id="password" placeholder="Enter your password (min 6 characters)" required>
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
                        <input type="password" id="confirmPassword" placeholder="Confirm your password" required>
                        <button type="button" class="toggle-password" id="toggleConfirmPassword">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>


                <button type="submit" class="btn btn-primary" id="registerBtn">
                    <i class="fas fa-user-plus"></i> Register
                </button>
            </form>

            <div class="auth-footer">
                <p>Already have an account? <a href="login">Login here</a></p>
            </div>
        </div>
    </div>

    <div id="toast" class="toast"></div>

    <script>
        // Pass PHP BASE_PATH to JavaScript
        const BASE_PATH = '<?php echo BASE_PATH; ?>';
    </script>
    <script src="<?php echo BASE_PATH; ?>/pwa10.js?v=<?php echo ASSET_VERSION; ?>"></script>
    <script src="<?php echo BASE_PATH; ?>/auth5.js?v=<?php echo ASSET_VERSION; ?>"></script>
</body>
</html>

