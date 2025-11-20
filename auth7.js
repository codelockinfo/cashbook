// API Configuration
const API_URL = ((typeof BASE_PATH !== 'undefined' && BASE_PATH) ? BASE_PATH : '') + '/auth-api.php';

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    const currentPage = window.location.pathname.split('/').pop();
    
    if (currentPage === 'login.php' || currentPage === 'login') {
        initLogin();
    } else if (currentPage === 'register.php' || currentPage === 'register') {
        initRegister();
    } else if (currentPage === 'forgot-password.php' || currentPage === 'forgot-password') {
        initForgotPassword();
    } else if (currentPage === 'reset-password.php' || currentPage === 'reset-password') {
        initResetPassword();
    }
});

// Initialize Login Page
function initLogin() {
    const loginForm = document.getElementById('loginForm');
    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');
    
    // Toggle password visibility
    togglePassword.addEventListener('click', function() {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        this.querySelector('i').classList.toggle('fa-eye');
        this.querySelector('i').classList.toggle('fa-eye-slash');
    });
    
    // Handle login form submission
    loginForm.addEventListener('submit', handleLogin);
}

// Initialize Register Page
function initRegister() {
    const registerForm = document.getElementById('registerForm');
    const togglePassword = document.getElementById('togglePassword');
    const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirmPassword');
    const profilePictureInput = document.getElementById('profilePicture');
    const profilePreview = document.getElementById('profilePreview');
    const fileName = document.getElementById('fileName');
    
    // Toggle password visibility
    togglePassword.addEventListener('click', function() {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        this.querySelector('i').classList.toggle('fa-eye');
        this.querySelector('i').classList.toggle('fa-eye-slash');
    });
    
    toggleConfirmPassword.addEventListener('click', function() {
        const type = confirmPasswordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        confirmPasswordInput.setAttribute('type', type);
        this.querySelector('i').classList.toggle('fa-eye');
        this.querySelector('i').classList.toggle('fa-eye-slash');
    });
    
    // Profile picture preview
    if (profilePictureInput) {
        profilePictureInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                // Update file name
                fileName.textContent = file.name;
                
                // Show preview
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
        const fileLabel = document.querySelector('label[for="profilePicture"].file-input-label');
        if (fileLabel) {
            fileLabel.addEventListener('click', function(e) {
                e.preventDefault();
                profilePictureInput.click();
            });
        }
    }
    
    // Handle register form submission
    registerForm.addEventListener('submit', handleRegister);
}

// Handle Login
async function handleLogin(e) {
    e.preventDefault();
    
    const email = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value;
    const loginBtn = document.getElementById('loginBtn');
    
    // Validation
    if (!email || !password) {
        showToast('Please fill in all fields', 'error');
        return;
    }
    
    // Disable button and show loading
    loginBtn.disabled = true;
    const originalHTML = loginBtn.innerHTML;
    loginBtn.innerHTML = '<span class="loading"></span> Logging in...';
    
    try {
        const response = await fetch(API_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                action: 'login',
                email: email,
                password: password
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast('Login successful! Redirecting...', 'success');
            // Set flag for PWA install prompt on mobile
            sessionStorage.setItem('just_logged_in', 'true');
            setTimeout(() => {
                window.location.href = 'dashboard';
            }, 1000);
        } else {
            showToast(data.message || 'Login failed', 'error');
            loginBtn.disabled = false;
            loginBtn.innerHTML = originalHTML;
        }
    } catch (error) {
        console.error('Login error:', error);
        showToast('An error occurred. Please try again.', 'error');
        loginBtn.disabled = false;
        loginBtn.innerHTML = originalHTML;
    }
}

// Handle Register
async function handleRegister(e) {
    e.preventDefault();
    
    const name = document.getElementById('name').value.trim();
    const email = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirmPassword').value;
    const profilePicture = document.getElementById('profilePicture')?.files[0];
    const registerBtn = document.getElementById('registerBtn');
    
    // Validation
    if (!name || !email || !password || !confirmPassword) {
        showToast('Please fill in all fields', 'error');
        return;
    }
    
    if (password.length < 6) {
        showToast('Password must be at least 6 characters', 'error');
        return;
    }
    
    if (password !== confirmPassword) {
        showToast('Passwords do not match', 'error');
        return;
    }
    
    // Email validation
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        showToast('Please enter a valid email address', 'error');
        return;
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
    
    // Disable button and show loading
    registerBtn.disabled = true;
    const originalHTML = registerBtn.innerHTML;
    registerBtn.innerHTML = '<span class="loading"></span> Creating account...';
    
    try {
        // Create FormData for file upload
        const formData = new FormData();
        formData.append('action', 'register');
        formData.append('name', name);
        formData.append('email', email);
        formData.append('password', password);
        if (profilePicture) {
            formData.append('profilePicture', profilePicture);
        }
        
        const response = await fetch(API_URL, {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast('Registration successful! Redirecting to login...', 'success');
            setTimeout(() => {
                window.location.href = 'login';
            }, 1500);
        } else {
            showToast(data.message || 'Registration failed', 'error');
            registerBtn.disabled = false;
            registerBtn.innerHTML = originalHTML;
        }
    } catch (error) {
        console.error('Registration error:', error);
        showToast('An error occurred. Please try again.', 'error');
        registerBtn.disabled = false;
        registerBtn.innerHTML = originalHTML;
    }
}

// Initialize Forgot Password Page
function initForgotPassword() {
    const forgotPasswordForm = document.getElementById('forgotPasswordForm');
    
    // Handle forgot password form submission
    forgotPasswordForm.addEventListener('submit', handleForgotPassword);
}

// Initialize Reset Password Page
function initResetPassword() {
    const resetPasswordForm = document.getElementById('resetPasswordForm');
    const togglePassword = document.getElementById('togglePassword');
    const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirmPassword');
    const tokenInput = document.getElementById('token');
    
    // Get token from URL
    const urlParams = new URLSearchParams(window.location.search);
    const token = urlParams.get('token');
    
    if (!token) {
        showToast('Invalid reset link', 'error');
        setTimeout(() => {
            window.location.href = 'login';
        }, 2000);
        return;
    }
    
    // Store token in hidden input
    tokenInput.value = token;
    
    // Verify token
    verifyResetToken(token);
    
    // Toggle password visibility
    togglePassword.addEventListener('click', function() {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        this.querySelector('i').classList.toggle('fa-eye');
        this.querySelector('i').classList.toggle('fa-eye-slash');
    });
    
    toggleConfirmPassword.addEventListener('click', function() {
        const type = confirmPasswordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        confirmPasswordInput.setAttribute('type', type);
        this.querySelector('i').classList.toggle('fa-eye');
        this.querySelector('i').classList.toggle('fa-eye-slash');
    });
    
    // Handle reset password form submission
    resetPasswordForm.addEventListener('submit', handleResetPassword);
}

// Handle Forgot Password
async function handleForgotPassword(e) {
    e.preventDefault();
    
    const email = document.getElementById('email').value.trim();
    const resetBtn = document.getElementById('resetBtn');
    
    // Validation
    if (!email) {
        showToast('Please enter your email address', 'error');
        return;
    }
    
    // Email validation
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        showToast('Please enter a valid email address', 'error');
        return;
    }
    
    // Disable button and show loading
    resetBtn.disabled = true;
    const originalHTML = resetBtn.innerHTML;
    resetBtn.innerHTML = '<span class="loading"></span> Sending...';
    
    try {
        const response = await fetch(API_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                action: 'forgot_password',
                email: email
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast('✓ ' + data.message + '\n\nCheck your email inbox for the reset link.', 'success');
            
            // Clear form
            document.getElementById('email').value = '';
            resetBtn.disabled = false;
            resetBtn.innerHTML = originalHTML;
            
            // Optionally redirect to login after showing success
            setTimeout(() => {
                const goToLogin = confirm('Email sent successfully!\n\nClick OK to return to the login page.');
                if (goToLogin) {
                    window.location.href = 'login';
                }
            }, 3000);
        } else {
            showToast(data.message || 'Failed to send reset link', 'error');
            resetBtn.disabled = false;
            resetBtn.innerHTML = originalHTML;
        }
    } catch (error) {
        console.error('Forgot password error:', error);
        showToast('An error occurred. Please try again.', 'error');
        resetBtn.disabled = false;
        resetBtn.innerHTML = originalHTML;
    }
}

// Verify Reset Token
async function verifyResetToken(token) {
    try {
        const response = await fetch(API_URL + '?action=verify_reset_token&token=' + encodeURIComponent(token), {
            method: 'GET'
        });
        
        const data = await response.json();
        
        if (!data.success) {
            showToast(data.message || 'Invalid or expired reset link', 'error');
            setTimeout(() => {
                window.location.href = 'login';
            }, 2000);
        }
    } catch (error) {
        console.error('Token verification error:', error);
        showToast('An error occurred. Please try again.', 'error');
        setTimeout(() => {
            window.location.href = 'login';
        }, 2000);
    }
}

// Handle Reset Password
async function handleResetPassword(e) {
    e.preventDefault();
    
    const token = document.getElementById('token').value;
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirmPassword').value;
    const resetBtn = document.getElementById('resetBtn');
    
    // Validation
    if (!password || !confirmPassword) {
        showToast('Please fill in all fields', 'error');
        return;
    }
    
    if (password.length < 6) {
        showToast('Password must be at least 6 characters', 'error');
        return;
    }
    
    if (password !== confirmPassword) {
        showToast('Passwords do not match', 'error');
        return;
    }
    
    // Disable button and show loading
    resetBtn.disabled = true;
    const originalHTML = resetBtn.innerHTML;
    resetBtn.innerHTML = '<span class="loading"></span> Resetting...';
    
    try {
        const response = await fetch(API_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                action: 'reset_password',
                token: token,
                password: password,
                confirm_password: confirmPassword
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast('Password reset successful! Redirecting to login...', 'success');
            setTimeout(() => {
                window.location.href = 'login';
            }, 2000);
        } else {
            showToast(data.message || 'Failed to reset password', 'error');
            resetBtn.disabled = false;
            resetBtn.innerHTML = originalHTML;
        }
    } catch (error) {
        console.error('Reset password error:', error);
        showToast('An error occurred. Please try again.', 'error');
        resetBtn.disabled = false;
        resetBtn.innerHTML = originalHTML;
    }
}

// Show Toast Notification
function showToast(message, type = 'success') {
    const toast = document.getElementById('toast');
    const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
    
    toast.innerHTML = `<i class="fas ${icon}"></i> ${message}`;
    toast.className = `toast ${type} show`;
    
    setTimeout(() => {
        toast.classList.remove('show');
    }, 3000);
}

