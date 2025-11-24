<?php
// Suppress any output before JSON
ob_start();

// Configure session for subdirectory support
// Note: config.php will be included later, but we need session before that
if (session_status() === PHP_SESSION_NONE) {
    // Calculate path and normalize to lowercase for consistency
    $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
    $cookiePath = $basePath ? strtolower($basePath) : '/';
    $isSecure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
    
    // Use SameSite=None with Secure=true for HTTPS (works for BOTH regular browsers and WebView)
    // Use SameSite=Lax for HTTP (works for regular browsers, WebView on HTTP has limitations)
    // SameSite=None requires Secure=true, so only use it with HTTPS
    $sameSite = $isSecure ? 'None' : 'Lax';
    
    // IMPORTANT: Set cookie params BEFORE starting session
    session_set_cookie_params([
        'lifetime' => 604800, // 1 week
        'path' => $cookiePath,
        'domain' => '', // Empty domain works better with WebView
        'secure' => $isSecure,
        'httponly' => true,
        'samesite' => $sameSite
    ]);
    
    // Set session name explicitly
    session_name('PHPSESSID');
    
    session_start();
    
    // Debug: Log session info
    error_log("auth-api.php - Session started - ID: " . session_id());
    error_log("auth-api.php - Cookie path: " . $cookiePath);
    error_log("auth-api.php - SameSite: " . $sameSite . " (HTTPS: " . ($isSecure ? 'yes' : 'no') . ")");
    error_log("auth-api.php - Received cookies: " . print_r($_COOKIE, true));
}

// Clear any output before setting headers
ob_clean();

// Set headers for WebView compatibility (CORS and cookies)
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');
header('Access-Control-Allow-Credentials: true'); // Important for cookies in WebView

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Error handling to ensure valid JSON (only catch fatal errors, not warnings)
set_error_handler(function($severity, $message, $file, $line) {
    if ($severity === E_ERROR || $severity === E_PARSE || $severity === E_CORE_ERROR || $severity === E_COMPILE_ERROR) {
        throw new ErrorException($message, 0, $severity, $file, $line);
    }
    return false; // Let PHP handle other errors normally
});

try {
    // Load Composer autoloader first if it exists (required for PHPMailer)
    $vendorPath = __DIR__ . '/vendor/autoload.php';
    if (file_exists($vendorPath)) {
        require_once $vendorPath;
    }
    
    require_once 'config.php';
    require_once 'email-helper.php';
} catch (Exception $e) {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Configuration error: ' . $e->getMessage()]);
    exit;
} catch (Error $e) {
    ob_clean();
    $errorMsg = $e->getMessage();
    // Check if it's a class not found error
    if (strpos($errorMsg, 'not found') !== false || strpos($errorMsg, 'Class') !== false) {
        echo json_encode(['success' => false, 'message' => 'Required library not found. Please run: composer install']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Configuration error: ' . $errorMsg]);
    }
    exit;
}

try {
    $conn = getDBConnection();
    $action = $_POST['action'] ?? $_GET['action'] ?? '';
    
    switch ($action) {
        case 'register':
            register($conn);
            break;
        case 'login':
            login($conn);
            break;
        case 'logout':
            logout();
            break;
        case 'check':
            checkSession();
            break;
        case 'forgot_password':
            forgotPassword($conn);
            break;
        case 'verify_reset_token':
            verifyResetToken($conn);
            break;
        case 'verify_code':
            verifyResetCode($conn);
            break;
        case 'reset_password':
            resetPassword($conn);
            break;
        default:
            ob_clean();
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }
} catch (Exception $e) {
    ob_clean();
    error_log("Auth API Error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    // Show detailed error in development, generic in production
    $isLocal = strpos($_SERVER['HTTP_HOST'] ?? '', 'localhost') !== false || 
               strpos($_SERVER['HTTP_HOST'] ?? '', '127.0.0.1') !== false;
    
    $errorMsg = $isLocal ? $e->getMessage() : 'An error occurred. Please try again.';
    echo json_encode(['success' => false, 'message' => $errorMsg]);
} catch (Error $e) {
    ob_clean();
    error_log("Auth API Fatal Error: " . $e->getMessage());
    error_log("File: " . $e->getFile() . " Line: " . $e->getLine());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    // Show detailed error in development, generic in production
    $isLocal = strpos($_SERVER['HTTP_HOST'] ?? '', 'localhost') !== false || 
               strpos($_SERVER['HTTP_HOST'] ?? '', '127.0.0.1') !== false;
    
    // Check for common errors and provide helpful messages
    $errorMessage = $e->getMessage();
    $helpfulMsg = 'A fatal error occurred.';
    
    if (strpos($errorMessage, 'password_reset_tokens') !== false || 
        strpos($errorMessage, "doesn't exist") !== false ||
        strpos($errorMessage, 'Unknown table') !== false) {
        $helpfulMsg = 'Database table missing. Please run setup-forgot-password.php to create the required table.';
    } elseif (strpos($errorMessage, 'Call to undefined') !== false) {
        $helpfulMsg = 'Function not found: ' . $errorMessage;
    } elseif (strpos($errorMessage, 'Class') !== false && strpos($errorMessage, 'not found') !== false) {
        $helpfulMsg = 'Class not found. Please check if all required files are included.';
    } elseif ($isLocal) {
        $helpfulMsg = $errorMessage . ' (File: ' . basename($e->getFile()) . ':' . $e->getLine() . ')';
    }
    
    echo json_encode(['success' => false, 'message' => $helpfulMsg]);
} finally {
    // Ensure connection is closed
    if (isset($conn)) {
        $conn->close();
    }
    // Clean any remaining output
    ob_end_flush();
}

// Register new user
function register($conn) {
    try {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        
        // Validation
        if (empty($name) || empty($email) || empty($password)) {
            echo json_encode(['success' => false, 'message' => 'All fields are required']);
            return;
        }
        
        if (strlen($password) < 6) {
            echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters']);
            return;
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'Invalid email address']);
            return;
        }
        
        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            echo json_encode(['success' => false, 'message' => 'Email already registered']);
            return;
        }
        
        // Handle profile picture upload
        $profilePicture = null;
        if (isset($_FILES['profilePicture']) && $_FILES['profilePicture']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = uploadProfilePicture($_FILES['profilePicture']);
            if ($uploadResult['success']) {
                $profilePicture = $uploadResult['filename'];
            } else {
                echo json_encode(['success' => false, 'message' => $uploadResult['message']]);
                return;
            }
        }
        
        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert user
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, profile_picture) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $email, $hashedPassword, $profilePicture);
        
        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Registration successful',
                'profile_picture' => $profilePicture
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Registration failed: ' . $conn->error]);
        }
        
        $stmt->close();
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

// Upload profile picture
function uploadProfilePicture($file) {
    $uploadDir = __DIR__ . '/uploads/profile_pictures/';
    
    // Create directory if it doesn't exist
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Validate file type
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($file['type'], $allowedTypes)) {
        return ['success' => false, 'message' => 'Invalid file type. Only JPG, PNG, GIF, and WEBP are allowed.'];
    }
    
    // Validate file size (max 5MB)
    $maxSize = 5 * 1024 * 1024; // 5MB
    if ($file['size'] > $maxSize) {
        return ['success' => false, 'message' => 'File size too large. Maximum 5MB allowed.'];
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'profile_' . uniqid() . '_' . time() . '.' . $extension;
    $filepath = $uploadDir . $filename;
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return ['success' => true, 'filename' => 'uploads/profile_pictures/' . $filename];
    } else {
        return ['success' => false, 'message' => 'Failed to upload file'];
    }
}

// Login user
function login($conn) {
    try {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        
        // Validation
        if (empty($email) || empty($password)) {
            echo json_encode(['success' => false, 'message' => 'Email and password are required']);
            return;
        }
        
        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'Invalid email format']);
            return;
        }
        
        // Get user
        $stmt = $conn->prepare("SELECT id, name, email, password, profile_picture FROM users WHERE email = ?");
        if (!$stmt) {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
            return;
        }
        
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
            $stmt->close();
            return;
        }
        
        $user = $result->fetch_assoc();
        
        // Verify password
        if (empty($user['password'])) {
            error_log("Login attempt failed: Empty password hash for user ID: " . $user['id']);
            echo json_encode(['success' => false, 'message' => 'Account error. Please contact support.']);
            $stmt->close();
            return;
        }
        
        $passwordVerified = password_verify($password, $user['password']);
        if (!$passwordVerified) {
            error_log("Login attempt failed: Password verification failed for email: " . $email);
            echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
            $stmt->close();
            return;
        }
        
        error_log("Login successful for user ID: " . $user['id'] . ", Email: " . $email);
        
        // Session should already be started at the top of auth-api.php with proper cookie params
        // Just ensure it's active
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Clear any old session data first
        $_SESSION = array();
        
        // Set session variables
        $_SESSION['user_id'] = (int)$user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_profile_picture'] = $user['profile_picture'] ?? null;
        $_SESSION['logged_in'] = true;
        
        // Regenerate session ID for security
        // This creates a new session ID and saves the session
        session_regenerate_id(true);
        
        // Ensure session cookie is sent by explicitly setting it
        // Get the session ID and cookie params
        $sessionId = session_id();
        $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
        $cookiePath = $basePath ? strtolower($basePath) : '/';
        $secure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
        
        // Use SameSite=None with Secure=true for HTTPS (works for BOTH regular browsers and WebView)
        // Use SameSite=Lax for HTTP (works for regular browsers, WebView on HTTP has limitations)
        $sameSite = $secure ? 'None' : 'Lax';
        
        // Explicitly set the session cookie to ensure it's sent
        // This is especially important for WebView compatibility
        setcookie(
            session_name(),
            $sessionId,
            [
                'expires' => time() + 604800, // 1 week
                'path' => $cookiePath,
                'domain' => '', // Empty domain works better with WebView
                'secure' => $secure,
                'httponly' => true,
                'samesite' => $sameSite
            ]
        );
        
        error_log("Login - Session set - user_id: " . $_SESSION['user_id'] . ", logged_in: " . ($_SESSION['logged_in'] ? 'true' : 'false'));
        error_log("Login - Session ID: " . session_id());
        error_log("Login - Session cookie path: " . ini_get('session.cookie_path'));
        error_log("Login - Session name: " . session_name());
        error_log("Login - All session vars: " . print_r($_SESSION, true));
        
        echo json_encode([
            'success' => true,
            'message' => 'Login successful',
            'user' => [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'profile_picture' => $user['profile_picture'] ?? null
            ],
            'session_id' => session_id()
        ]);
        
        $stmt->close();
        
        // Session will be automatically saved when script ends
        // The session cookie should be sent with the response headers
    } catch (Exception $e) {
        error_log("Login error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'An error occurred during login. Please try again.']);
    }
}

// Logout user
function logout() {
    session_destroy();
    echo json_encode(['success' => true, 'message' => 'Logged out successfully']);
}

// Check if user is logged in
function checkSession() {
    if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
        echo json_encode([
            'success' => true,
            'logged_in' => true,
            'user' => [
                'id' => $_SESSION['user_id'],
                'name' => $_SESSION['user_name'],
                'email' => $_SESSION['user_email']
            ]
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'logged_in' => false
        ]);
    }
}

// Forgot Password - Generate reset token
function forgotPassword($conn) {
    try {
        $email = trim($_POST['email'] ?? '');
        
        // Validation
        if (empty($email)) {
            echo json_encode(['success' => false, 'message' => 'Email is required']);
            return;
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'Invalid email address']);
            return;
        }
        
        // Check if user exists
        $stmt = $conn->prepare("SELECT id, name FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            echo json_encode(['success' => false, 'message' => 'No account found with this email address']);
            return;
        }
        
        $user = $result->fetch_assoc();
        $userId = $user['id'];
        
        // Generate 6-digit verification code
        $code = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        
        // Code expires in 15 minutes
        $expiresAt = date('Y-m-d H:i:s', strtotime('+15 minutes'));
        
        // Delete any existing unused codes for this user
        $deleteStmt = $conn->prepare("DELETE FROM password_reset_tokens WHERE user_id = ? AND used = 0");
        if ($deleteStmt === false) {
            $errorMsg = $conn->error;
            // Check if table doesn't exist
            if (strpos($errorMsg, "doesn't exist") !== false || strpos($errorMsg, 'Unknown table') !== false) {
                echo json_encode([
                    'success' => false, 
                    'message' => 'Password reset table not found. Please run setup-forgot-password.php to create it.'
                ]);
                return;
            }
            // For other errors, log but continue (code might not exist yet)
            error_log("Failed to delete old codes: " . $errorMsg);
        } else {
            $deleteStmt->bind_param("i", $userId);
            $deleteStmt->execute();
            $deleteStmt->close();
        }
        
        // Insert new code (stored as token in database for compatibility)
        $stmt = $conn->prepare("INSERT INTO password_reset_tokens (user_id, token, expires_at) VALUES (?, ?, ?)");
        if ($stmt === false) {
            $errorMsg = $conn->error;
            // Check if table doesn't exist
            if (strpos($errorMsg, "doesn't exist") !== false || strpos($errorMsg, 'Unknown table') !== false) {
                echo json_encode([
                    'success' => false, 
                    'message' => 'Password reset table not found. Please run setup-forgot-password.php to create it.'
                ]);
                return;
            }
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $errorMsg]);
            return;
        }
        
        $stmt->bind_param("iss", $userId, $code, $expiresAt);
        
        if ($stmt->execute()) {
            $stmt->close();
            
            // Send password reset email with verification code
            $emailResult = sendPasswordResetEmail($email, $user['name'], $code);
            
            if ($emailResult['success']) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Verification code has been sent to your email',
                    'email' => $email  // Return email for frontend to use
                ]);
            } else {
                error_log("Email sending failed for $email: " . ($emailResult['message'] ?? 'Unknown error'));
                echo json_encode([
                    'success' => false,
                    'message' => $emailResult['message'] ?? 'Failed to send email. Please try again later.'
                ]);
            }
        } else {
            $errorMsg = $stmt->error ?: 'Failed to generate verification code';
            $stmt->close();
            error_log("Failed to insert verification code: " . $errorMsg);
            echo json_encode(['success' => false, 'message' => 'Failed to generate verification code: ' . $errorMsg]);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

// Verify Reset Token (for backward compatibility)
function verifyResetToken($conn) {
    try {
        $token = trim($_GET['token'] ?? $_POST['token'] ?? '');
        
        if (empty($token)) {
            echo json_encode(['success' => false, 'message' => 'Token is required']);
            return;
        }
        
        // Check if token exists and is valid
        $stmt = $conn->prepare("SELECT user_id, expires_at, used FROM password_reset_tokens WHERE token = ?");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid reset token']);
            return;
        }
        
        $resetToken = $result->fetch_assoc();
        
        // Check if token is already used
        if ($resetToken['used'] == 1) {
            echo json_encode(['success' => false, 'message' => 'This reset link has already been used']);
            return;
        }
        
        // Check if token is expired
        if (strtotime($resetToken['expires_at']) < time()) {
            echo json_encode(['success' => false, 'message' => 'This reset link has expired']);
            return;
        }
        
        echo json_encode(['success' => true, 'message' => 'Token is valid']);
        
        $stmt->close();
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

// Verify Reset Code
function verifyResetCode($conn) {
    try {
        $email = trim($_POST['email'] ?? '');
        $code = trim($_POST['code'] ?? '');
        
        if (empty($email) || empty($code)) {
            echo json_encode(['success' => false, 'message' => 'Email and verification code are required']);
            return;
        }
        
        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'Invalid email address']);
            return;
        }
        
        // Validate code format (6 digits)
        if (!preg_match('/^\d{6}$/', $code)) {
            echo json_encode(['success' => false, 'message' => 'Invalid verification code format']);
            return;
        }
        
        // Get user ID
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            echo json_encode(['success' => false, 'message' => 'No account found with this email address']);
            $stmt->close();
            return;
        }
        
        $user = $result->fetch_assoc();
        $userId = $user['id'];
        $stmt->close();
        
        // Check if code exists and is valid
        $stmt = $conn->prepare("SELECT user_id, expires_at, used FROM password_reset_tokens WHERE user_id = ? AND token = ?");
        $stmt->bind_param("is", $userId, $code);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid verification code']);
            $stmt->close();
            return;
        }
        
        $resetCode = $result->fetch_assoc();
        
        // Check if code is already used
        if ($resetCode['used'] == 1) {
            echo json_encode(['success' => false, 'message' => 'This verification code has already been used']);
            $stmt->close();
            return;
        }
        
        // Check if code is expired
        if (strtotime($resetCode['expires_at']) < time()) {
            echo json_encode(['success' => false, 'message' => 'This verification code has expired. Please request a new one.']);
            $stmt->close();
            return;
        }
        
        echo json_encode([
            'success' => true, 
            'message' => 'Verification code is valid',
            'user_id' => $userId
        ]);
        
        $stmt->close();
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

// Reset Password
function resetPassword($conn) {
    try {
        // Support both token (old method) and email+code (new method)
        $token = trim($_POST['token'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $code = trim($_POST['code'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        // Validation
        if (empty($password) || empty($confirmPassword)) {
            echo json_encode(['success' => false, 'message' => 'Password fields are required']);
            return;
        }
        
        if ($password !== $confirmPassword) {
            echo json_encode(['success' => false, 'message' => 'Passwords do not match']);
            return;
        }
        
        if (strlen($password) < 6) {
            echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters']);
            return;
        }
        
        $userId = null;
        
        // New method: email + code
        if (!empty($email) && !empty($code)) {
            // Validate email format
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                echo json_encode(['success' => false, 'message' => 'Invalid email address']);
                return;
            }
            
            // Validate code format (6 digits)
            if (!preg_match('/^\d{6}$/', $code)) {
                echo json_encode(['success' => false, 'message' => 'Invalid verification code format']);
                return;
            }
            
            // Get user ID
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                echo json_encode(['success' => false, 'message' => 'No account found with this email address']);
                $stmt->close();
                return;
            }
            
            $user = $result->fetch_assoc();
            $userId = $user['id'];
            $stmt->close();
            
            // Verify code
            $stmt = $conn->prepare("SELECT user_id, expires_at, used FROM password_reset_tokens WHERE user_id = ? AND token = ?");
            $stmt->bind_param("is", $userId, $code);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid verification code']);
                $stmt->close();
                return;
            }
            
            $resetCode = $result->fetch_assoc();
            
            // Check if code is already used
            if ($resetCode['used'] == 1) {
                echo json_encode(['success' => false, 'message' => 'This verification code has already been used']);
                $stmt->close();
                return;
            }
            
            // Check if code is expired
            if (strtotime($resetCode['expires_at']) < time()) {
                echo json_encode(['success' => false, 'message' => 'This verification code has expired']);
                $stmt->close();
                return;
            }
            
            $stmt->close();
            $verificationToken = $code;
        }
        // Old method: token (for backward compatibility)
        else if (!empty($token)) {
            // Verify token
            $stmt = $conn->prepare("SELECT user_id, expires_at, used FROM password_reset_tokens WHERE token = ?");
            $stmt->bind_param("s", $token);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid reset token']);
                return;
            }
            
            $resetToken = $result->fetch_assoc();
            
            // Check if token is already used
            if ($resetToken['used'] == 1) {
                echo json_encode(['success' => false, 'message' => 'This reset link has already been used']);
                return;
            }
            
            // Check if token is expired
            if (strtotime($resetToken['expires_at']) < time()) {
                echo json_encode(['success' => false, 'message' => 'This reset link has expired']);
                return;
            }
            
            $userId = $resetToken['user_id'];
            $stmt->close();
            $verificationToken = $token;
        } else {
            echo json_encode(['success' => false, 'message' => 'Either token or email+code is required']);
            return;
        }
        
        // Hash the new password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        if (!$hashedPassword) {
            echo json_encode(['success' => false, 'message' => 'Failed to hash password']);
            return;
        }
        
        // Update user's password
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $hashedPassword, $userId);
        
        if (!$stmt->execute()) {
            echo json_encode(['success' => false, 'message' => 'Failed to update password: ' . $stmt->error]);
            $stmt->close();
            return;
        }
        $stmt->close();
        
        // Mark token/code as used
        $stmt = $conn->prepare("UPDATE password_reset_tokens SET used = 1 WHERE token = ?");
        $stmt->bind_param("s", $verificationToken);
        $stmt->execute();
        $stmt->close();
        
        // Clear any existing session variables to force fresh login
        // Don't destroy the session completely, just clear the variables
        $_SESSION = array();
        
        echo json_encode([
            'success' => true,
            'message' => 'Password has been reset successfully. Please login with your new password.'
        ]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
?>

