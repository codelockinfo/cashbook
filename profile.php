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
    <title>My Profile - Bookify</title>
    <?php include 'pwa-meta.php'; ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_PATH; ?>/style14.css?v=<?php echo defined('ASSET_VERSION') ? ASSET_VERSION : '1.0'; ?>">
    <link rel="stylesheet" href="<?php echo BASE_PATH; ?>/auth-style8.css?v=<?php echo defined('ASSET_VERSION') ? ASSET_VERSION : '1.0'; ?>">
</head>
<body>
    <div class="container" style="max-width: 1200px;">
        <!-- Header Section -->
        <header class="header">
            <div class="header-content">
                <div class="logo">
                <img src="icons/bookify logo.png" alt="bookify" height="50px">
                <h1>BOOKIFY</h1>
                </div>
                <div class="header-actions">
                    <div class="user-info">
                        <?php if (!empty($user['profile_picture'])): ?>
                            <img src="<?php echo htmlspecialchars($user['profile_picture']); ?>" 
                                 alt="Profile" 
                                 class="user-avatar"
                                 onerror="this.style.display='none'; if(this.nextElementSibling) { this.nextElementSibling.style.display='inline-flex'; }">
                            <i class="fas fa-user-circle"></i>
                        <?php else: ?>
                            <i class="fas fa-user-circle" style="display:inline-flex;"></i>
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
                        <i class="fas fa-circle-user"></i> My Profile
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
                <h1><i class="fas fa-circle-user"></i> My Profile</h1>
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
                        <button type="button" id="removePhotoBtn" class="btn" style="background: #ef4444;color: white; margin-top: 10px;">
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
                <div class="info-box" style="margin: 10px 0;">
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
                <div id="deleteAccount" style="margin-top: 20px; border-top: 1px solid #e5e7eb; padding-top: 20px;">
                    <button type="button" class="btn btn-danger" id="deleteAccountBtn" style="background-color: #ef4444; color: white; width: 100%;">
                        <i class="fas fa-trash-alt"></i> Delete Account
                    </button>
                </div>
            </form>
        </div>
        </div>
    </div>

    <!-- Logout Confirmation Modal -->
    <div id="logoutModal" class="confirm-modal" style="display: none;">
        <div class="confirm-modal-overlay"></div>
        <div class="confirm-modal-content">
            <div class="confirm-modal-header">
                <i class="fas fa-exclamation-circle"></i>
                <h3>Confirm Logout</h3>
            </div>
            <div class="confirm-modal-body">
                <p>Are you sure you want to logout?</p>
            </div>
            <div class="confirm-modal-footer">
                <button class="btn-cancel" id="logoutCancelBtn">
                    <i class="fas fa-times"></i> Cancel
                </button>
                <button class="btn-confirm" id="logoutConfirmBtn">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </button>
            </div>
        </div>
    </div>

    <!-- Remove Photo Confirmation Modal -->
    <div id="removePhotoModal" class="confirm-modal" style="display: none;">
        <div class="confirm-modal-overlay"></div>
        <div class="confirm-modal-content">
            <div class="confirm-modal-header confirm-modal-header-danger">
                <i class="fas fa-trash-alt"></i>
                <h3>Remove Profile Picture</h3>
            </div>
            <div class="confirm-modal-body">
                <p>Are you sure you want to remove your profile picture?</p>
            </div>
            <div class="confirm-modal-footer">
                <button class="btn-cancel" id="removePhotoCancelBtn">
                    <i class="fas fa-times"></i> Cancel
                </button>
                <button class="btn-confirm" id="removePhotoConfirmBtn">
                    <i class="fas fa-trash"></i> Remove
                </button>
            </div>
        </div>
    </div>

    <!-- Delete Account Confirmation Modal -->
    <div id="deleteAccountModal" class="confirm-modal" style="display: none;">
        <div class="confirm-modal-overlay"></div>
        <div class="confirm-modal-content">
            <div class="confirm-modal-header confirm-modal-header-danger">
                <i class="fas fa-exclamation-triangle"></i>
                <h3>Delete Account</h3>
            </div>
            <div class="confirm-modal-body">
                <p style="margin-bottom: 20px;">Are you sure you want to delete your account? This action is permanent and cannot be undone. All your data will be erased.</p>
                
                <form id="deleteAccountForm">
                    <div class="form-group">
                        <label for="deletePassword" style="color: #374151;">Password</label>
                        <div class="password-input">
                            <input type="password" id="deletePassword" placeholder="Enter your password" required>
                            <button type="button" class="toggle-password" id="toggleDeletePassword">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="form-group" style="margin-top: 15px;">
                        <label for="deleteConfirmation" style="color: #374151;">To confirm, type "DELETE"</label>
                        <input type="text" id="deleteConfirmation" placeholder="Type DELETE" required style="width: 100%; border: 1px solid #d1d5db; padding: 10px; border-radius: 6px;">
                    </div>
                </form>
            </div>
            <div class="confirm-modal-footer">
                <button class="btn-cancel" id="deleteAccountCancelBtn">
                    <i class="fas fa-times"></i> Cancel
                </button>
                <button class="btn-confirm" id="deleteAccountConfirmBtn" disabled style="background-color: #ef4444; opacity: 0.5; cursor: not-allowed;">
                    <i class="fas fa-trash-alt"></i> Delete Account
                </button>
            </div>
        </div>
    </div>

    <a href="help-support" class="help-support-floating-btn" title="Help & Support">
        <i class="fas fa-headset"></i>
    </a>

    <!-- Toast Notification -->
    <div id="toast" class="toast"></div>


    
    <script>
        // Pass PHP BASE_PATH to JavaScript
        const BASE_PATH = '<?php echo BASE_PATH; ?>';
    </script>
    <script src="<?php echo BASE_PATH; ?>/pwa10.js?v=<?php echo ASSET_VERSION; ?>"></script>
    <script src="<?php echo BASE_PATH; ?>/auth9.js?v=<?php echo defined('ASSET_VERSION') ? ASSET_VERSION : '1.0'; ?>"></script>
    <script src="<?php echo BASE_PATH; ?>/dashboard9.js?v=<?php echo defined('ASSET_VERSION') ? ASSET_VERSION : '1.0'; ?>"></script>
    <script>
        // Initialize profile page
        (function() {
            console.log('Profile page script executing...');
            
            // Check if logout button exists and add handler if not already added
            const logoutBtn = document.getElementById('logoutBtn');
            console.log('Logout button found:', logoutBtn);
            
            // Add logout handler directly to ensure it works
            if (logoutBtn && !logoutBtn.dataset.handlerAdded) {
                console.log('Adding logout handler...');
                logoutBtn.dataset.handlerAdded = 'true';
                logoutBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    console.log('Logout button clicked!');
                    showLogoutModal();
                });
            }
            
            // Show logout confirmation modal
            function showLogoutModal() {
                const modal = document.getElementById('logoutModal');
                modal.style.display = 'flex';
                document.body.style.overflow = 'hidden';
                
                // Setup button handlers
                const confirmBtn = document.getElementById('logoutConfirmBtn');
                const cancelBtn = document.getElementById('logoutCancelBtn');
                const overlay = modal.querySelector('.confirm-modal-overlay');
                
                // Remove old listeners
                const newConfirmBtn = confirmBtn.cloneNode(true);
                const newCancelBtn = cancelBtn.cloneNode(true);
                confirmBtn.parentNode.replaceChild(newConfirmBtn, confirmBtn);
                cancelBtn.parentNode.replaceChild(newCancelBtn, cancelBtn);
                
                // Add new listeners
                newConfirmBtn.addEventListener('click', performLogout);
                newCancelBtn.addEventListener('click', hideLogoutModal);
                overlay.addEventListener('click', hideLogoutModal);
            }
            
            // Hide logout modal
            function hideLogoutModal() {
                const modal = document.getElementById('logoutModal');
                modal.style.display = 'none';
                document.body.style.overflow = 'auto';
            }
            
            // Perform actual logout
            async function performLogout() {
                hideLogoutModal();
                
                try {
                    const AUTH_API_URL = BASE_PATH + '/auth-api.php';
                    const response = await fetch(AUTH_API_URL, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: new URLSearchParams({
                            action: 'logout'
                        })
                    });
                    
                    const data = await response.json();
                    console.log('Logout response:', data);
                    
                    if (data.success) {
                        showToast('Logged out successfully', 'success');
                        setTimeout(() => {
                            window.location.href = BASE_PATH + '/login';
                        }, 500);
                    } else {
                        showToast('Error logging out', 'error');
                    }
                } catch (error) {
                    console.error('Logout error:', error);
                    showToast('Error logging out', 'error');
                }
            }
            
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
        
        // Explicit click handler for mobile compatibility
        const profilePictureLabel = document.querySelector('label[for="profilePicture"].file-input-label');
        if (profilePictureLabel) {
            profilePictureLabel.addEventListener('click', function(e) {
                e.preventDefault();
                profilePictureInput.click();
            });
        }

        // Remove photo - show confirmation modal
        if (removePhotoBtn) {
            removePhotoBtn.addEventListener('click', function() {
                showRemovePhotoModal();
            });
        }
        
        // Show remove photo confirmation modal
        function showRemovePhotoModal() {
            const modal = document.getElementById('removePhotoModal');
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
            
            // Setup button handlers
            const confirmBtn = document.getElementById('removePhotoConfirmBtn');
            const cancelBtn = document.getElementById('removePhotoCancelBtn');
            const overlay = modal.querySelector('.confirm-modal-overlay');
            
            // Remove old listeners
            const newConfirmBtn = confirmBtn.cloneNode(true);
            const newCancelBtn = cancelBtn.cloneNode(true);
            confirmBtn.parentNode.replaceChild(newConfirmBtn, confirmBtn);
            cancelBtn.parentNode.replaceChild(newCancelBtn, cancelBtn);
            
            // Add new listeners
            newConfirmBtn.addEventListener('click', performRemovePhoto);
            newCancelBtn.addEventListener('click', hideRemovePhotoModal);
            overlay.addEventListener('click', hideRemovePhotoModal);
        }
        
        // Hide remove photo modal
        function hideRemovePhotoModal() {
            const modal = document.getElementById('removePhotoModal');
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }
        
        // Perform actual photo removal
        async function performRemovePhoto() {
            hideRemovePhotoModal();
            
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
        }

            // Handle profile form submission
            if (profileForm) {
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
            }

            function showToast(message, type = 'success') {
                const toast = document.getElementById('toast');
                const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
                
                toast.innerHTML = `<i class="fas ${icon}"></i> ${message}`;
                toast.className = `toast ${type} show`;
                
                setTimeout(() => {
                    toast.classList.remove('show');
                }, 3000);
            }
            // Delete Account Functionality
            const deleteAccountBtn = document.getElementById('deleteAccountBtn');
            const deleteAccountModal = document.getElementById('deleteAccountModal');
            const deleteAccountCancelBtn = document.getElementById('deleteAccountCancelBtn');
            const deleteAccountConfirmBtn = document.getElementById('deleteAccountConfirmBtn');
            const deletePasswordInput = document.getElementById('deletePassword');
            const deleteConfirmationInput = document.getElementById('deleteConfirmation');
            const deleteAccountForm = document.getElementById('deleteAccountForm');
            const toggleDeletePasswordBtn = document.getElementById('toggleDeletePassword');

            if (deleteAccountBtn) {
                deleteAccountBtn.addEventListener('click', function() {
                    deleteAccountModal.style.display = 'flex';
                    document.body.style.overflow = 'hidden';
                    deletePasswordInput.value = '';
                    deleteConfirmationInput.value = '';
                    deleteAccountConfirmBtn.disabled = true;
                    deleteAccountConfirmBtn.style.opacity = '0.5';
                    deleteAccountConfirmBtn.style.cursor = 'not-allowed';
                });
            }

            if (deleteAccountCancelBtn) {
                deleteAccountCancelBtn.addEventListener('click', function() {
                    deleteAccountModal.style.display = 'none';
                    document.body.style.overflow = 'auto';
                });
            }
            
            // Close modal when clicking overlay
            if (deleteAccountModal) {
                 const overlay = deleteAccountModal.querySelector('.confirm-modal-overlay');
                 if(overlay) {
                     overlay.addEventListener('click', function() {
                        deleteAccountModal.style.display = 'none';
                        document.body.style.overflow = 'auto';
                     });
                 }
            }

            // Monitor inputs to enable/disable delete button
            function checkDeleteInputs() {
                const password = deletePasswordInput.value;
                const confirmation = deleteConfirmationInput.value;
                
                if (password && confirmation === 'DELETE') {
                    deleteAccountConfirmBtn.disabled = false;
                    deleteAccountConfirmBtn.style.opacity = '1';
                    deleteAccountConfirmBtn.style.cursor = 'pointer';
                } else {
                    deleteAccountConfirmBtn.disabled = true;
                    deleteAccountConfirmBtn.style.opacity = '0.5';
                    deleteAccountConfirmBtn.style.cursor = 'not-allowed';
                }
            }

            if (deletePasswordInput) deletePasswordInput.addEventListener('input', checkDeleteInputs);
            if (deleteConfirmationInput) deleteConfirmationInput.addEventListener('input', checkDeleteInputs);

             // Toggle delete password visibility
            if (toggleDeletePasswordBtn) {
                // Remove any existing listener (just in case, though cloneNode is safer if we want to be sure)
                const newBtn = toggleDeletePasswordBtn.cloneNode(true);
                toggleDeletePasswordBtn.parentNode.replaceChild(newBtn, toggleDeletePasswordBtn);
                
                newBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    const type = deletePasswordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    console.log('Toggling password visibility to:', type);
                    deletePasswordInput.setAttribute('type', type);
                    this.querySelector('i').classList.toggle('fa-eye');
                    this.querySelector('i').classList.toggle('fa-eye-slash');
                });
            }



            // Handle delete account confirmation
            if (deleteAccountConfirmBtn) {
                deleteAccountConfirmBtn.addEventListener('click', async function() {
                   if (deleteAccountConfirmBtn.disabled) return;
                   
                   const password = deletePasswordInput.value;
                   
                    // Show loading state
                    const originalHTML = deleteAccountConfirmBtn.innerHTML;
                    deleteAccountConfirmBtn.disabled = true;
                    deleteAccountConfirmBtn.innerHTML = '<span class="loading"></span> Deleting...';
                    
                    try {
                        const response = await fetch(BASE_PATH + '/profile-api.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: new URLSearchParams({
                                action: 'delete_account',
                                password: password
                            })
                        });

                        const data = await response.json();

                        if (data.success) {
                            showToast('Account deleted successfully', 'success');
                            setTimeout(() => {
                                window.location.href = BASE_PATH + '/register.php';
                            }, 1000);
                        } else {
                            showToast(data.message || 'Failed to delete account', 'error');
                            deleteAccountConfirmBtn.disabled = false;
                            deleteAccountConfirmBtn.innerHTML = originalHTML;
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        showToast('An error occurred', 'error');
                        deleteAccountConfirmBtn.disabled = false;
                        deleteAccountConfirmBtn.innerHTML = originalHTML;
                    }
                });
            }
        })(); // End of IIFE
    </script>
</body>
</html>

