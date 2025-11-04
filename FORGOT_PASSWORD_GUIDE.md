# Forgot Password Functionality - Implementation Guide

## Overview
A complete forgot password feature has been added to the Cash Book application, allowing users to reset their passwords securely.

## Features Implemented

### 1. **Forgot Password Link**
- Added a "Forgot Password?" link on the login page
- Users can click this link to initiate the password reset process

### 2. **Email Verification**
- Users enter their registered email address
- System verifies if the email exists in the database
- Provides appropriate error messages if email is not found

### 3. **Password Reset Token**
- Generates a unique, secure token (64 characters)
- Token expires after 1 hour for security
- Tokens are stored in the `password_reset_tokens` table
- Old unused tokens are automatically deleted when a new one is generated

### 4. **Reset Password Page**
- Users access this page via the reset link
- Token is validated before allowing password reset
- Shows appropriate errors for invalid/expired/used tokens
- Users enter new password with confirmation
- Password must be at least 6 characters long

### 5. **Password Update**
- Password is securely hashed using PHP's `password_hash()`
- Token is marked as "used" after successful reset
- User is redirected to login page after successful reset

## Database Schema

### New Table: `password_reset_tokens`
```sql
CREATE TABLE password_reset_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL,
    used TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_token (token(191)),
    INDEX idx_user_id (user_id),
    INDEX idx_expires_at (expires_at)
);
```

## Installation Steps

### For New Installations
If you're setting up the database for the first time, simply run:
```bash
mysql -u root -p < database.sql
```
This will create all tables including the password reset table.

### For Existing Installations
If you already have the cashbook database set up, run the migration script:
```bash
mysql -u root -p < migrate_password_reset.sql
```

## Files Created/Modified

### New Files:
1. **forgot-password.php** - Forgot password form page
2. **reset-password.php** - Reset password form page
3. **migrate_password_reset.sql** - Database migration script
4. **FORGOT_PASSWORD_GUIDE.md** - This documentation file

### Modified Files:
1. **database.sql** - Added password_reset_tokens table
2. **login.php** - Added "Forgot Password?" link
3. **auth-style.css** - Added styles for forgot password link and info boxes
4. **auth-api.php** - Added three new API endpoints:
   - `forgot_password` - Generate reset token
   - `verify_reset_token` - Verify token validity
   - `reset_password` - Update password
5. **auth.js** - Added JavaScript handlers for new pages

## User Flow

### Step 1: Click Forgot Password
1. User goes to `login.php`
2. Clicks "Forgot Password?" link below the login button
3. Redirected to `forgot-password.php`

### Step 2: Enter Email
1. User enters their registered email address
2. Clicks "Send Reset Link" button
3. System verifies email exists in database

### Step 3: Get Reset Link
**For Development:**
- A popup shows the reset link (for testing purposes)
- Click OK to navigate to the reset page

**For Production:**
- You should integrate an email service (like PHPMailer, SendGrid, etc.)
- Email will be sent to the user's registered email address
- User clicks the link in their email

### Step 4: Reset Password
1. User lands on `reset-password.php?token=...`
2. Token is automatically verified
3. User enters new password
4. User confirms new password
5. Clicks "Reset Password" button

### Step 5: Login with New Password
1. Password is updated successfully
2. User is redirected to `login.php`
3. User can now login with the new password

## Security Features

### Token Security
- **Random Generation**: Uses `bin2hex(random_bytes(32))` for cryptographically secure tokens
- **One-Time Use**: Tokens are marked as "used" after successful password reset
- **Expiration**: Tokens expire after 1 hour
- **Unique**: Each token is unique and tied to a specific user

### Password Security
- **Validation**: Passwords must be at least 6 characters
- **Confirmation**: Users must confirm their password
- **Hashing**: Passwords are hashed using `password_hash()` with `PASSWORD_DEFAULT`

### Input Validation
- Email format validation
- SQL injection prevention using prepared statements
- XSS protection through proper output encoding
- CSRF protection through session management

## API Endpoints

### 1. Forgot Password
```
POST auth-api.php?action=forgot_password
Body: email=user@example.com
```
**Response:**
```json
{
    "success": true,
    "message": "Password reset link has been sent to your email",
    "reset_link": "http://localhost/cashbook/reset-password.php?token=abc123..."
}
```

### 2. Verify Reset Token
```
GET auth-api.php?action=verify_reset_token&token=abc123...
```
**Response:**
```json
{
    "success": true,
    "message": "Token is valid"
}
```

### 3. Reset Password
```
POST auth-api.php?action=reset_password
Body: token=abc123&password=newpass123&confirm_password=newpass123
```
**Response:**
```json
{
    "success": true,
    "message": "Password has been reset successfully"
}
```

## Testing

### Test with Demo Account
1. Go to `forgot-password.php`
2. Enter: `admin@cashbook.com`
3. Click "Send Reset Link"
4. A popup will show the reset link (for development)
5. Click OK to open the reset page
6. Enter new password: `newpassword123`
7. Confirm password: `newpassword123`
8. Click "Reset Password"
9. Login with new password

## Email Integration (Production Setup)

For production, you should implement email sending. Here's a basic example using PHPMailer:

### Step 1: Install PHPMailer
```bash
composer require phpmailer/phpmailer
```

### Step 2: Add Email Function to auth-api.php
```php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

function sendResetEmail($email, $name, $resetLink) {
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'your-email@gmail.com';
        $mail->Password   = 'your-app-password';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        
        // Recipients
        $mail->setFrom('noreply@cashbook.com', 'Cash Book');
        $mail->addAddress($email, $name);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Password Reset Request - Cash Book';
        $mail->Body    = "
            <h2>Password Reset Request</h2>
            <p>Hi $name,</p>
            <p>You requested to reset your password. Click the link below to reset it:</p>
            <p><a href='$resetLink'>Reset Password</a></p>
            <p>This link will expire in 1 hour.</p>
            <p>If you didn't request this, please ignore this email.</p>
        ";
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email could not be sent. Error: {$mail->ErrorInfo}");
        return false;
    }
}
```

### Step 3: Update forgotPassword() function
Replace this line in `auth-api.php`:
```php
// sendResetEmail($email, $user['name'], $resetLink);
```
With:
```php
sendResetEmail($email, $user['name'], $resetLink);
```

And remove this line (don't send reset_link in response):
```php
'reset_link' => $resetLink // Remove this in production
```

## Error Handling

The system handles various error scenarios:

1. **Invalid Email**: "No account found with this email address"
2. **Invalid Token**: "Invalid reset token"
3. **Expired Token**: "This reset link has expired"
4. **Used Token**: "This reset link has already been used"
5. **Password Mismatch**: "Passwords do not match"
6. **Short Password**: "Password must be at least 6 characters"
7. **Server Errors**: "An error occurred. Please try again."

## UI/UX Features

- **Modern Design**: Gradient backgrounds and smooth animations
- **Responsive**: Works on mobile, tablet, and desktop
- **Loading States**: Shows loading spinner during API calls
- **Toast Notifications**: User-friendly success/error messages
- **Password Toggle**: Eye icon to show/hide passwords
- **Info Boxes**: Helpful instructions on each page
- **Form Validation**: Real-time validation before submission

## Troubleshooting

### Issue: Token not found
- Make sure the migration script ran successfully
- Check if `password_reset_tokens` table exists in the database

### Issue: Email not found
- Verify the email exists in the `users` table
- Check for typos in the email address

### Issue: Token expired
- Tokens expire after 1 hour
- Request a new reset link

### Issue: Reset link doesn't work
- Ensure the full URL is copied correctly
- Check if there are any special characters that got encoded

## Future Enhancements

Possible improvements for production:

1. **Rate Limiting**: Prevent abuse by limiting reset requests per IP/email
2. **Email Templates**: Professional HTML email templates
3. **SMS Verification**: Add 2FA with SMS code
4. **Password Strength Meter**: Visual indicator for password strength
5. **Account Lockout**: Lock account after multiple failed attempts
6. **Email Notifications**: Notify user when password is changed
7. **Audit Log**: Track all password reset attempts

## Support

For issues or questions:
- Check the error messages in browser console
- Review PHP error logs
- Verify database connection in `config.php`
- Ensure all files have proper permissions

---

**Created:** November 4, 2025  
**Version:** 1.0  
**Status:** Production Ready (Email integration pending)

