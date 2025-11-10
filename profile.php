<?php
require_once 'config.php';
require_once 'check-session.php';
checkAuth();

$user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Cash Book</title>
    <?php include 'pwa-meta.php'; ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_PATH; ?>/style1.css?v=<?php echo defined('ASSET_VERSION') ? ASSET_VERSION : '1.0'; ?>">
    <link rel="stylesheet" href="<?php echo BASE_PATH; ?>/auth-style1.css?v=<?php echo defined('ASSET_VERSION') ? ASSET_VERSION : '1.0'; ?>">
</head>
<body>
    <div class="container" style="max-width: 1200px;">
        <!-- Header Section -->
        <header class="header">
            <div class="header-content">
                <div class="logo">
                    <i class="fas fa-book"></i>
                    <h1>Cash Book</h1>
                </div>
                <div class="header-actions">
                    <div class="user-info">
                        <?php if (!empty($user['profile_picture'])): ?>
                            <img src="<?php echo htmlspecialchars($user['profile_picture']); ?>" 
                                 alt="Profile" 
                                 class="user-avatar"
                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='inline';">
                            <i class="fas fa-user-circle" style="display:none;"></i>
                        <?php else: ?>
                            <i class="fas fa-user-circle"></i>
                        <?php endif; ?>
                        <span><?php echo htmlspecialchars($user['name']); ?></span>
                    </div>
                    <a href="dashboard" class="manage-users-link">
                        <i class="fas fa-home"></i> Dashboard
                    </a>
                    <a href="groups" class="manage-users-link">
                        <i class="fas fa-users"></i> My Groups
                    </a>
                    <a href="profile" class="manage-users-link" style="background: linear-gradient(135deg, #667eea, #764ba2); color: white;">
                        <i class="fas fa-user-edit"></i> My Profile
                    </a>
                    <button id="logoutBtn" class="manage-users-link logout-btn">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </button>
                </div>
            </div>
        </header>

        <div style="max-width: 800px; margin: 0 auto;">
        <div class="auth-card">
            <div class="auth-header">
                <h1><i class="fas fa-user-circle"></i> My Profile</h1>
                <p class="subtitle">Update your account information</p>
            </div>

            <form id="profileForm" class="auth-form" enctype="multipart/form-data">
                <!-- Profile Picture -->
                <div class="form-group">
                    <label>
                        <i class="fas fa-camera"></i> Profile Picture
                    </label>
                    <div class="profile-picture-upload">
                        <div class="profile-preview">
                            <img id="profilePreview" 
                                 src="<?php echo $user['profile_picture'] ? htmlspecialchars($user['profile_picture']) : 'uploads/default-avatar.png'; ?>" 
                                 alt="Profile Picture"
                                 onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22%3E%3Ccircle cx=%2250%22 cy=%2250%22 r=%2250%22 fill=%22%23e5e7eb%22/%3E%3Ctext x=%2250%22 y=%2250%22 text-anchor=%22middle%22 dy=%22.3em%22 font-size=%2240%22 fill=%22%23666%22%3E👤%3C/text%3E%3C/svg%3E'">
                        </div>
                        <div class="file-input-wrapper">
                            <input type="file" id="profilePicture" name="profilePicture" accept="image/*">
                            <label for="profilePicture" class="file-input-label">
                                <i class="fas fa-upload"></i> Change Photo
                            </label>
                            <span class="file-name" id="fileName">No file chosen</span>
                        </div>
                        <?php if ($user['profile_picture']): ?>
                        <button type="button" id="removePhotoBtn" class="btn" style="background: #ef4444; margin-top: 10px;">
                            <i class="fas fa-trash"></i> Remove Photo
                        </button>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Name -->
                <div class="form-group">
                    <label for="name">
                        <i class="fas fa-user"></i> Full Name
                    </label>
                    <input type="text" id="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                </div>

                <!-- Email -->
                <div class="form-group">
                    <label for="email">
                        <i class="fas fa-envelope"></i> Email Address
                    </label>
                    <input type="email" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>

                <!-- Change Password Section -->
                <div class="info-box" style="margin: 20px 0;">
                    <i class="fas fa-info-circle"></i>
                    <p>Leave password fields empty if you don't want to change your password.</p>
                </div>

                <!-- Current Password -->
                <div class="form-group">
                    <label for="currentPassword">
                        <i class="fas fa-lock"></i> Current Password
                    </label>
                    <div class="password-input">
                        <input type="password" id="currentPassword" placeholder="Enter current password">
                        <button type="button" class="toggle-password" id="toggleCurrentPassword">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <!-- New Password -->
                <div class="form-group">
                    <label for="newPassword">
                        <i class="fas fa-key"></i> New Password
                    </label>
                    <div class="password-input">
                        <input type="password" id="newPassword" placeholder="Enter new password (min 6 characters)">
                        <button type="button" class="toggle-password" id="toggleNewPassword">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <!-- Confirm New Password -->
                <div class="form-group">
                    <label for="confirmPassword">
                        <i class="fas fa-key"></i> Confirm New Password
                    </label>
                    <div class="password-input">
                        <input type="password" id="confirmPassword" placeholder="Confirm new password">
                        <button type="button" class="toggle-password" id="toggleConfirmPassword">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn btn-primary" id="updateBtn">
                    <i class="fas fa-save"></i> Update Profile
                </button>
            </form>
        </div>
        </div>
    </div>

    <div id="toast" class="toast"></div>
    
    <script>
        // Pass PHP BASE_PATH to JavaScript
        const BASE_PATH = '<?php echo BASE_PATH; ?>';
    </script>
    <script src="<?php echo BASE_PATH; ?>/pwa4.js?v=<?php echo ASSET_VERSION; ?>"></script>
    <script src="<?php echo BASE_PATH; ?>/auth2.js?v=<?php echo defined('ASSET_VERSION') ? ASSET_VERSION : '1.0'; ?>"></script>
    <script src="<?php echo BASE_PATH; ?>/dashboard1.js?v=<?php echo defined('ASSET_VERSION') ? ASSET_VERSION : '1.0'; ?>"></script>
    <script>
        // Initialize profile page
        const profilePictureInput = document.getElementById('profilePicture');
        const profilePreview = document.getElementById('profilePreview');
        const fileName = document.getElementById('fileName');
        const profileForm = document.getElementById('profileForm');
        const removePhotoBtn = document.getElementById('removePhotoBtn');

        // Password toggle functionality
        const toggleButtons = document.querySelectorAll('.toggle-password');
        toggleButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                const input = this.parentElement.querySelector('input');
                const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
                input.setAttribute('type', type);
                this.querySelector('i').classList.toggle('fa-eye');
                this.querySelector('i').classList.toggle('fa-eye-slash');
            });
        });

        // Profile picture preview
        profilePictureInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                fileName.textContent = file.name;
                const reader = new FileReader();
                reader.onload = function(e) {
                    profilePreview.src = e.target.result;
                };
                reader.readAsDataURL(file);
            } else {
                fileName.textContent = 'No file chosen';
            }
        });

        // Remove photo
        if (removePhotoBtn) {
            removePhotoBtn.addEventListener('click', async function() {
                if (!confirm('Are you sure you want to remove your profile picture?')) {
                    return;
                }

                try {
                    const response = await fetch(BASE_PATH + '/profile-api.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: new URLSearchParams({
                            action: 'remove_photo'
                        })
                    });

                    const data = await response.json();

                    if (data.success) {
                        showToast('Profile picture removed successfully!', 'success');
                        profilePreview.src = 'uploads/default-avatar.png';
                        removePhotoBtn.style.display = 'none';
                    } else {
                        showToast(data.message || 'Failed to remove picture', 'error');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    showToast('An error occurred', 'error');
                }
            });
        }

        // Handle profile form submission
        profileForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            const name = document.getElementById('name').value.trim();
            const email = document.getElementById('email').value.trim();
            const currentPassword = document.getElementById('currentPassword').value;
            const newPassword = document.getElementById('newPassword').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            const profilePicture = profilePictureInput.files[0];
            const updateBtn = document.getElementById('updateBtn');

            // Validation
            if (!name || !email) {
                showToast('Name and email are required', 'error');
                return;
            }

            // Password validation if changing password
            if (currentPassword || newPassword || confirmPassword) {
                if (!currentPassword) {
                    showToast('Please enter your current password', 'error');
                    return;
                }
                if (!newPassword || !confirmPassword) {
                    showToast('Please enter and confirm your new password', 'error');
                    return;
                }
                if (newPassword !== confirmPassword) {
                    showToast('New passwords do not match', 'error');
                    return;
                }
                if (newPassword.length < 6) {
                    showToast('New password must be at least 6 characters', 'error');
                    return;
                }
            }

            // Profile picture validation
            if (profilePicture) {
                const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
                if (!allowedTypes.includes(profilePicture.type)) {
                    showToast('Invalid file type. Only JPG, PNG, GIF, and WEBP are allowed.', 'error');
                    return;
                }

                const maxSize = 5 * 1024 * 1024; // 5MB
                if (profilePicture.size > maxSize) {
                    showToast('File size too large. Maximum 5MB allowed.', 'error');
                    return;
                }
            }

            // Show loading
            updateBtn.disabled = true;
            const originalHTML = updateBtn.innerHTML;
            updateBtn.innerHTML = '<span class="loading"></span> Updating...';

            try {
                // Create FormData
                const formData = new FormData();
                formData.append('action', 'update_profile');
                formData.append('name', name);
                formData.append('email', email);
                if (currentPassword) formData.append('current_password', currentPassword);
                if (newPassword) formData.append('new_password', newPassword);
                if (profilePicture) formData.append('profilePicture', profilePicture);

                const response = await fetch(BASE_PATH + '/profile-api.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    showToast('Profile updated successfully!', 'success');
                    // Clear password fields
                    document.getElementById('currentPassword').value = '';
                    document.getElementById('newPassword').value = '';
                    document.getElementById('confirmPassword').value = '';
                    
                    // Reload page after 1.5 seconds to reflect changes
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    showToast(data.message || 'Failed to update profile', 'error');
                    updateBtn.disabled = false;
                    updateBtn.innerHTML = originalHTML;
                }
            } catch (error) {
                console.error('Error:', error);
                showToast('An error occurred', 'error');
                updateBtn.disabled = false;
                updateBtn.innerHTML = originalHTML;
            }
        });

        function showToast(message, type = 'success') {
            const toast = document.getElementById('toast');
            const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
            
            toast.innerHTML = `<i class="fas ${icon}"></i> ${message}`;
            toast.className = `toast ${type} show`;
            
            setTimeout(() => {
                toast.classList.remove('show');
            }, 3000);
        }
    </script>
</body>
</html>

