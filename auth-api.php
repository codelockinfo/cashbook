<?php
// Suppress any output before JSON
ob_start();

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

// Clear any output before setting headers
ob_clean();

header('Content-Type: application/json');

// Error handling to ensure valid JSON (only catch fatal errors, not warnings)
set_error_handler(function($severity, $message, $file, $line) {
    if ($severity === E_ERROR || $severity === E_PARSE || $severity === E_CORE_ERROR || $severity === E_COMPILE_ERROR) {
        throw new ErrorException($message, 0, $severity, $file, $line);
    }
    return false; // Let PHP handle other errors normally
});

try {
    require_once 'config.php';
    require_once 'email-helper.php';
} catch (Exception $e) {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Configuration error: ' . $e->getMessage()]);
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
        
        // Get user
        $stmt = $conn->prepare("SELECT id, name, email, password, profile_picture FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
            return;
        }
        
        $user = $result->fetch_assoc();
        
        // Verify password
        if (!password_verify($password, $user['password'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
            return;
        }
        
        // Set session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_profile_picture'] = $user['profile_picture'];
        $_SESSION['logged_in'] = true;
        
        echo json_encode([
            'success' => true,
            'message' => 'Login successful',
            'user' => [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'profile_picture' => $user['profile_picture']
            ]
        ]);
        
        $stmt->close();
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
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
        
        // Generate unique reset token
        $token = bin2hex(random_bytes(32));
        
        // Token expires in 1 hour
        $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        // Delete any existing unused tokens for this user
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
            // For other errors, log but continue (token might not exist yet)
            error_log("Failed to delete old tokens: " . $errorMsg);
        } else {
            $deleteStmt->bind_param("i", $userId);
            $deleteStmt->execute();
            $deleteStmt->close();
        }
        
        // Insert new token
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
        
        $stmt->bind_param("iss", $userId, $token, $expiresAt);
        
        if ($stmt->execute()) {
            $stmt->close();
            
            // Generate reset link - use SITE_URL if defined, otherwise construct from server
            if (defined('SITE_URL') && !empty(SITE_URL)) {
                $resetLink = rtrim(SITE_URL, '/') . "/reset-password.php?token=" . $token;
            } else {
                $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
                $resetLink = $protocol . "://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/reset-password.php?token=" . $token;
            }
            
            // Send password reset email
            $emailResult = sendPasswordResetEmail($email, $user['name'], $resetLink);
            
            if ($emailResult['success']) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Password reset link has been sent to your email'
                ]);
            } else {
                error_log("Email sending failed for $email: " . ($emailResult['message'] ?? 'Unknown error'));
                echo json_encode([
                    'success' => false,
                    'message' => $emailResult['message'] ?? 'Failed to send email. Please try again later.'
                ]);
            }
        } else {
            $errorMsg = $stmt->error ?: 'Failed to generate reset token';
            $stmt->close();
            error_log("Failed to insert reset token: " . $errorMsg);
            echo json_encode(['success' => false, 'message' => 'Failed to generate reset token: ' . $errorMsg]);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

// Verify Reset Token
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

// Reset Password
function resetPassword($conn) {
    try {
        $token = trim($_POST['token'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        // Validation
        if (empty($token) || empty($password) || empty($confirmPassword)) {
            echo json_encode(['success' => false, 'message' => 'All fields are required']);
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
        
        // Hash the new password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        // Update user's password
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $hashedPassword, $resetToken['user_id']);
        
        if (!$stmt->execute()) {
            echo json_encode(['success' => false, 'message' => 'Failed to update password']);
            return;
        }
        
        // Mark token as used
        $stmt = $conn->prepare("UPDATE password_reset_tokens SET used = 1 WHERE token = ?");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        
        echo json_encode([
            'success' => true,
            'message' => 'Password has been reset successfully'
        ]);
        
        $stmt->close();
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
?>

