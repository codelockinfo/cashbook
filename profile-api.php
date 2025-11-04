<?php
session_start();
header('Content-Type: application/json');

require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$conn = getDBConnection();
$action = $_POST['action'] ?? '';

switch ($action) {
    case 'update_profile':
        updateProfile($conn);
        break;
    case 'remove_photo':
        removePhoto($conn);
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}

// Update user profile
function updateProfile($conn) {
    try {
        $userId = $_SESSION['user_id'];
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        
        // Validation
        if (empty($name) || empty($email)) {
            echo json_encode(['success' => false, 'message' => 'Name and email are required']);
            return;
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'Invalid email address']);
            return;
        }
        
        // Check if email is already taken by another user
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->bind_param("si", $email, $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            echo json_encode(['success' => false, 'message' => 'Email is already taken']);
            return;
        }
        
        // Get current user data
        $stmt = $conn->prepare("SELECT password, profile_picture FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $currentUser = $result->fetch_assoc();
        
        // Handle password change
        if (!empty($currentPassword)) {
            // Verify current password
            if (!password_verify($currentPassword, $currentUser['password'])) {
                echo json_encode(['success' => false, 'message' => 'Current password is incorrect']);
                return;
            }
            
            // Validate new password
            if (empty($newPassword)) {
                echo json_encode(['success' => false, 'message' => 'New password is required']);
                return;
            }
            
            if (strlen($newPassword) < 6) {
                echo json_encode(['success' => false, 'message' => 'New password must be at least 6 characters']);
                return;
            }
            
            // Hash new password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        } else {
            // Keep current password
            $hashedPassword = $currentUser['password'];
        }
        
        // Handle profile picture upload
        $profilePicture = $currentUser['profile_picture'];
        if (isset($_FILES['profilePicture']) && $_FILES['profilePicture']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = uploadProfilePicture($_FILES['profilePicture']);
            if ($uploadResult['success']) {
                // Delete old profile picture if exists
                if ($profilePicture && file_exists(__DIR__ . '/' . $profilePicture)) {
                    unlink(__DIR__ . '/' . $profilePicture);
                }
                $profilePicture = $uploadResult['filename'];
            } else {
                echo json_encode(['success' => false, 'message' => $uploadResult['message']]);
                return;
            }
        }
        
        // Update user
        $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, password = ?, profile_picture = ? WHERE id = ?");
        $stmt->bind_param("ssssi", $name, $email, $hashedPassword, $profilePicture, $userId);
        
        if ($stmt->execute()) {
            // Update session
            $_SESSION['user_name'] = $name;
            $_SESSION['user_email'] = $email;
            $_SESSION['user_profile_picture'] = $profilePicture;
            
            echo json_encode([
                'success' => true,
                'message' => 'Profile updated successfully',
                'user' => [
                    'name' => $name,
                    'email' => $email,
                    'profile_picture' => $profilePicture
                ]
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update profile']);
        }
        
        $stmt->close();
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

// Remove profile photo
function removePhoto($conn) {
    try {
        $userId = $_SESSION['user_id'];
        
        // Get current profile picture
        $stmt = $conn->prepare("SELECT profile_picture FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        
        if ($user && $user['profile_picture']) {
            // Delete file
            $filePath = __DIR__ . '/' . $user['profile_picture'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            
            // Update database
            $stmt = $conn->prepare("UPDATE users SET profile_picture = NULL WHERE id = ?");
            $stmt->bind_param("i", $userId);
            
            if ($stmt->execute()) {
                $_SESSION['user_profile_picture'] = null;
                echo json_encode(['success' => true, 'message' => 'Profile picture removed successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to remove profile picture']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'No profile picture to remove']);
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

$conn->close();
?>

