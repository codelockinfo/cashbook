// API Configuration
const API_URL = ((typeof BASE_PATH !== 'undefined' && BASE_PATH) ? BASE_PATH : '') + '/auth-api.php';

// Debug: Log API URL
console.log('API_URL:', API_URL);
console.log('BASE_PATH:', typeof BASE_PATH !== 'undefined' ? BASE_PATH : 'undefined');

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOMContentLoaded event fired');
    const currentPage = window.location.pathname.split('/').pop();
    console.log('Current page:', currentPage);
    console.log('Full pathname:', window.location.pathname);
    
    if (currentPage === 'login.php' || currentPage === 'login') {
        console.log('Detected login page, calling initLogin()');
        initLogin();
    } else if (currentPage === 'register.php' || currentPage === 'register') {
        console.log('Detected register page, calling initRegister()');
        initRegister();
    } else if (currentPage === 'forgot-password.php' || currentPage === 'forgot-password') {
        console.log('Detected forgot-password page, calling initForgotPassword()');
        initForgotPassword();
    } else if (currentPage === 'reset-password.php' || currentPage === 'reset-password') {
        console.log('Detected reset-password page, calling initResetPassword()');
        initResetPassword();
    } else {
        console.log('No matching page handler for:', currentPage);
    }
});

// Initialize Login Page
function initLogin() {
    console.log('initLogin() called');
    const loginForm = document.getElementById('loginForm');
    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');
    
    if (!loginForm) {
        console.error('Login form not found');
        return;
    }
    
    console.log('Login form found, attaching event listeners');
    
    // Toggle password visibility
    if (togglePassword && passwordInput) {
        togglePassword.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.querySelector('i').classList.toggle('fa-eye');
            this.querySelector('i').classList.toggle('fa-eye-slash');
        });
    }
    
    // Handle login form submission
    loginForm.addEventListener('submit', function(e) {
        console.log('Form submit event triggered');
        handleLogin(e);
    });
    
    // Clear any reset-related data from session storage
    sessionStorage.removeItem('resetEmail');
    sessionStorage.removeItem('resetCode');
    
    console.log('Login initialization complete');
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
    console.log('handleLogin() called');
    e.preventDefault();
    
    const email = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value;
    const loginBtn = document.getElementById('loginBtn');
    
    console.log('Email:', email);
    console.log('Password length:', password ? password.length : 0);
    
    // Validation
    if (!email || !password) {
        console.log('Validation failed: empty fields');
        showToast('Please fill in all fields', 'error');
        return;
    }
    
    // Email validation
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        console.log('Validation failed: invalid email format');
        showToast('Please enter a valid email address', 'error');
        return;
    }
    
    // Disable button and show loading
    loginBtn.disabled = true;
    const originalHTML = loginBtn.innerHTML;
    loginBtn.innerHTML = '<span class="loading"></span> Logging in...';
    
    console.log('Making API call to:', API_URL);
    
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
        
        // Check if response is ok
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        // Get response text first to handle non-JSON responses
        const responseText = await response.text();
        
        let data;
        try {
            data = JSON.parse(responseText);
        } catch (parseError) {
            console.error('JSON parse error:', parseError);
            console.error('Response text:', responseText);
            throw new Error('Invalid response from server. Please check server logs.');
        }
        
        if (data.success) {
            showToast('Login successful! Redirecting...', 'success');
            // Clear any reset-related session storage
            sessionStorage.removeItem('resetEmail');
            sessionStorage.removeItem('resetCode');
            // Set flag for PWA install prompt on mobile
            sessionStorage.setItem('just_logged_in', 'true');
            
            // Build dashboard URL with proper base path
            const basePath = (typeof BASE_PATH !== 'undefined' && BASE_PATH) ? BASE_PATH : '';
            
            // Check if running in Flutter WebView
            // Method 1: Check URL parameter
            const urlParams = new URLSearchParams(window.location.search);
            const isFlutterFromUrl = urlParams.get('flutter') === 'true';
            
            // Method 2: Check user agent
            const isFlutterFromUA = window.navigator.userAgent.includes('Flutter') || 
                                    window.navigator.userAgent.includes('wv');
            
            // Method 3: Check for FlutterApp channel
            const isFlutterFromChannel = typeof window.FlutterApp !== 'undefined';
            
            const isFlutterApp = isFlutterFromUrl || isFlutterFromUA || isFlutterFromChannel;
            
            console.log('Flutter detection:', {
                fromUrl: isFlutterFromUrl,
                fromUA: isFlutterFromUA,
                fromChannel: isFlutterFromChannel,
                isFlutter: isFlutterApp,
                hasToken: !!data.token
            });
            
            // ALWAYS include token in URL if flutter=true parameter exists OR if token is present
            // This ensures Flutter can capture the token
            if ((isFlutterApp || isFlutterFromUrl) && data.token) {
                // Option 1: Direct redirect to dashboard with token
                const dashboardUrl = basePath ? basePath + '/dashboard?token=' + encodeURIComponent(data.token) : 'dashboard?token=' + encodeURIComponent(data.token);
                
                // Option 2: Use redirect endpoint (more reliable)
                const redirectUrl = basePath ? basePath + '/login-redirect.php?token=' + encodeURIComponent(data.token) : 'login-redirect.php?token=' + encodeURIComponent(data.token);
                
                console.log('✅ Flutter app detected - Token:', data.token.substring(0, 20) + '...');
                console.log('✅ Redirecting to dashboard with token');
                console.log('Dashboard URL:', dashboardUrl);
                console.log('Redirect URL:', redirectUrl);
                
                // Also send token to Flutter via JavaScript channel if available
                if (typeof window.FlutterApp !== 'undefined' && window.FlutterApp.postMessage) {
                    try {
                        window.FlutterApp.postMessage(JSON.stringify({
                            type: 'login_success',
                            token: data.token
                        }));
                        console.log('✅ Token sent via JavaScript channel');
                    } catch (e) {
                        console.log('JavaScript channel not available:', e);
                    }
                }
                
                // Use direct redirect (more reliable than redirect endpoint)
                setTimeout(() => {
                    console.log('🔄 Final redirect to:', dashboardUrl);
                    // Force redirect with token
                    window.location.replace(dashboardUrl);
                }, 500);
            } else if (data.token && isFlutterFromUrl) {
                // Fallback: If flutter=true but detection failed, still include token
                const dashboardUrl = basePath ? basePath + '/dashboard?token=' + encodeURIComponent(data.token) : 'dashboard?token=' + encodeURIComponent(data.token);
                console.log('✅ Fallback - Redirecting with token (flutter=true detected):', dashboardUrl);
                setTimeout(() => {
                    window.location.replace(dashboardUrl);
                }, 500);
            } else {
                // For regular browser: Normal redirect without token
                const dashboardUrl = basePath ? basePath + '/dashboard' : 'dashboard';
                console.log('🌐 Regular browser - Redirecting to dashboard:', dashboardUrl);
                
                setTimeout(() => {
                    window.location.href = dashboardUrl;
                }, 2000);
            }
        } else {
            showToast(data.message || 'Login failed', 'error');
            loginBtn.disabled = false;
            loginBtn.innerHTML = originalHTML;
        }
    } catch (error) {
        console.error('Login error:', error);
        console.error('Error stack:', error.stack);
        const errorMessage = error.message || 'An error occurred. Please try again.';
        showToast(errorMessage, 'error');
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
    const verifyCodeForm = document.getElementById('verifyCodeForm');
    const resetPasswordForm = document.getElementById('resetPasswordForm');
    
    // Handle forgot password form submission
    if (forgotPasswordForm) {
        forgotPasswordForm.addEventListener('submit', handleForgotPassword);
    }
    
    // Handle code verification form submission
    if (verifyCodeForm) {
        verifyCodeForm.addEventListener('submit', handleVerifyCode);
        
        // Handle resend code button
        const resendBtn = document.getElementById('resendBtn');
        if (resendBtn) {
            resendBtn.addEventListener('click', handleResendCode);
        }
        
        // Only allow numbers in verification code input
        const codeInput = document.getElementById('verificationCode');
        if (codeInput) {
            codeInput.addEventListener('input', function(e) {
                e.target.value = e.target.value.replace(/[^0-9]/g, '');
            });
        }
    }
    
    // Handle reset password form submission
    if (resetPasswordForm) {
        resetPasswordForm.addEventListener('submit', handleResetPasswordWithCode);
        
        // Toggle password visibility
        const togglePassword = document.getElementById('togglePassword');
        const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
        const passwordInput = document.getElementById('newPassword');
        const confirmPasswordInput = document.getElementById('confirmNewPassword');
        
        if (togglePassword && passwordInput) {
            togglePassword.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                this.querySelector('i').classList.toggle('fa-eye');
                this.querySelector('i').classList.toggle('fa-eye-slash');
            });
        }
        
        if (toggleConfirmPassword && confirmPasswordInput) {
            toggleConfirmPassword.addEventListener('click', function() {
                const type = confirmPasswordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                confirmPasswordInput.setAttribute('type', type);
                this.querySelector('i').classList.toggle('fa-eye');
                this.querySelector('i').classList.toggle('fa-eye-slash');
            });
        }
    }
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
        
        // Check if response is ok
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        // Get response text first to handle non-JSON responses
        const responseText = await response.text();
        
        let data;
        try {
            data = JSON.parse(responseText);
        } catch (parseError) {
            console.error('JSON parse error:', parseError);
            console.error('Response text:', responseText);
            throw new Error('Invalid response from server. Please check server logs.');
        }
        
        if (data.success) {
            showToast('✓ ' + data.message + '\n\nCheck your email inbox for the verification code.', 'success');
            
            // Store email for later use
            const email = document.getElementById('email').value.trim();
            window.resetEmail = email;
            
            // Hide email form and show code verification form
            const forgotPasswordForm = document.getElementById('forgotPasswordForm');
            const verifyCodeForm = document.getElementById('verifyCodeForm');
            const infoBox = document.getElementById('infoBox');
            
            if (forgotPasswordForm) forgotPasswordForm.style.display = 'none';
            if (verifyCodeForm) verifyCodeForm.style.display = 'block';
            if (infoBox) {
                infoBox.innerHTML = '<i class="fas fa-info-circle"></i><p>Enter the 6-digit verification code sent to <strong>' + email + '</strong></p>';
            }
            
            // Focus on code input
            const codeInput = document.getElementById('verificationCode');
            if (codeInput) {
                setTimeout(() => codeInput.focus(), 100);
            }
            
            resetBtn.disabled = false;
            resetBtn.innerHTML = originalHTML;
        } else {
            showToast(data.message || 'Failed to send verification code', 'error');
            resetBtn.disabled = false;
            resetBtn.innerHTML = originalHTML;
        }
    } catch (error) {
        console.error('Forgot password error:', error);
        const errorMessage = error.message || 'An error occurred. Please try again.';
        showToast(errorMessage, 'error');
        resetBtn.disabled = false;
        resetBtn.innerHTML = originalHTML;
    }
}

// Verify Reset Token (for backward compatibility with old links)
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

// Handle Verify Code
async function handleVerifyCode(e) {
    e.preventDefault();
    
    const email = window.resetEmail || '';
    const code = document.getElementById('verificationCode').value.trim();
    const verifyBtn = document.getElementById('verifyBtn');
    
    // Validation
    if (!email) {
        showToast('Email not found. Please start over.', 'error');
        return;
    }
    
    if (!code || code.length !== 6) {
        showToast('Please enter a valid 6-digit verification code', 'error');
        return;
    }
    
    // Disable button and show loading
    verifyBtn.disabled = true;
    const originalHTML = verifyBtn.innerHTML;
    verifyBtn.innerHTML = '<span class="loading"></span> Verifying...';
    
    try {
        const response = await fetch(API_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                action: 'verify_code',
                email: email,
                code: code
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast('✓ Code verified successfully!', 'success');
            
            // Store email and code for password reset
            window.resetEmail = email;
            window.resetCode = code;
            
            // Hide code form and show password reset form
            const verifyCodeForm = document.getElementById('verifyCodeForm');
            const resetPasswordForm = document.getElementById('resetPasswordForm');
            const infoBox = document.getElementById('infoBox');
            
            if (verifyCodeForm) verifyCodeForm.style.display = 'none';
            if (resetPasswordForm) resetPasswordForm.style.display = 'block';
            if (infoBox) {
                infoBox.innerHTML = '<i class="fas fa-info-circle"></i><p>Enter your new password below. Make sure it\'s at least 6 characters long.</p>';
            }
            
            // Set hidden inputs
            const resetEmailInput = document.getElementById('resetEmail');
            const resetCodeInput = document.getElementById('resetCode');
            if (resetEmailInput) resetEmailInput.value = email;
            if (resetCodeInput) resetCodeInput.value = code;
            
            // Focus on password input
            const passwordInput = document.getElementById('newPassword');
            if (passwordInput) {
                setTimeout(() => passwordInput.focus(), 100);
            }
            
            verifyBtn.disabled = false;
            verifyBtn.innerHTML = originalHTML;
        } else {
            showToast(data.message || 'Invalid verification code', 'error');
            verifyBtn.disabled = false;
            verifyBtn.innerHTML = originalHTML;
            
            // Clear code input on error
            document.getElementById('verificationCode').value = '';
        }
    } catch (error) {
        console.error('Verify code error:', error);
        showToast('An error occurred. Please try again.', 'error');
        verifyBtn.disabled = false;
        verifyBtn.innerHTML = originalHTML;
    }
}

// Handle Resend Code
async function handleResendCode() {
    const email = window.resetEmail || '';
    const resendBtn = document.getElementById('resendBtn');
    
    if (!email) {
        showToast('Email not found. Please start over.', 'error');
        return;
    }
    
    // Disable button and show loading
    resendBtn.disabled = true;
    const originalHTML = resendBtn.innerHTML;
    resendBtn.innerHTML = '<span class="loading"></span> Resending...';
    
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
            showToast('✓ Verification code has been resent to your email', 'success');
            
            // Clear code input
            document.getElementById('verificationCode').value = '';
            setTimeout(() => document.getElementById('verificationCode').focus(), 100);
        } else {
            showToast(data.message || 'Failed to resend code', 'error');
        }
        
        resendBtn.disabled = false;
        resendBtn.innerHTML = originalHTML;
    } catch (error) {
        console.error('Resend code error:', error);
        showToast('An error occurred. Please try again.', 'error');
        resendBtn.disabled = false;
        resendBtn.innerHTML = originalHTML;
    }
}

// Handle Reset Password with Code
async function handleResetPasswordWithCode(e) {
    e.preventDefault();
    
    const email = document.getElementById('resetEmail').value;
    const code = document.getElementById('resetCode').value;
    const password = document.getElementById('newPassword').value;
    const confirmPassword = document.getElementById('confirmNewPassword').value;
    const resetPasswordBtn = document.getElementById('resetPasswordBtn');
    
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
    resetPasswordBtn.disabled = true;
    const originalHTML = resetPasswordBtn.innerHTML;
    resetPasswordBtn.innerHTML = '<span class="loading"></span> Resetting...';
    
    try {
        const response = await fetch(API_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                action: 'reset_password',
                email: email,
                code: code,
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
            resetPasswordBtn.disabled = false;
            resetPasswordBtn.innerHTML = originalHTML;
        }
    } catch (error) {
        console.error('Reset password error:', error);
        showToast('An error occurred. Please try again.', 'error');
        resetPasswordBtn.disabled = false;
        resetPasswordBtn.innerHTML = originalHTML;
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

