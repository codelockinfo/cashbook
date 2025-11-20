<?php
/**
 * Email Helper Functions
 * Handles sending emails for password reset
 */

// Load Composer autoloader first if it exists
// The autoloader registers itself automatically when required
$vendorPath = __DIR__ . '/vendor/autoload.php';
if (file_exists($vendorPath)) {
    try {
        require_once $vendorPath;
    } catch (\Exception $e) {
        // Autoloader failed, will fall back to manual loading
        error_log("Autoloader failed to load: " . $e->getMessage());
    }
}

// Use PHPMailer classes only after autoloader is loaded
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/email-config.php';

/**
 * Send password reset email
 * @param string $email User's email address
 * @param string $name User's name
 * @param string $resetLink Password reset link
 * @return array Result with success status and message
 */
function sendPasswordResetEmail($email, $name, $resetLink) {
    // Define paths - use __DIR__ and normalize path separators
    $baseDir = __DIR__;
    $phpmailerPath = $baseDir . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'phpmailer' . DIRECTORY_SEPARATOR . 'phpmailer' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'PHPMailer.php';
    $exceptionPath = $baseDir . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'phpmailer' . DIRECTORY_SEPARATOR . 'phpmailer' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Exception.php';
    $smtpPath = $baseDir . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'phpmailer' . DIRECTORY_SEPARATOR . 'phpmailer' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'SMTP.php';
    
    // Check if PHPMailer class already exists (maybe autoloader worked)
    if (!class_exists('PHPMailer\PHPMailer\PHPMailer', false)) {
        // Class doesn't exist yet, try to load via autoloader first
        if (!class_exists('PHPMailer\PHPMailer\PHPMailer', true)) {
            // Autoloader failed or not available, manually require files
            // Check if files exist using normalized paths
            if (file_exists($exceptionPath) && file_exists($smtpPath) && file_exists($phpmailerPath)) {
                // Load files in correct order (dependencies first)
                require_once $exceptionPath;
                require_once $smtpPath;
                require_once $phpmailerPath;
            } else {
                // Try with forward slashes as well (some systems may need this)
                $phpmailerPathAlt = str_replace(DIRECTORY_SEPARATOR, '/', $phpmailerPath);
                $exceptionPathAlt = str_replace(DIRECTORY_SEPARATOR, '/', $exceptionPath);
                $smtpPathAlt = str_replace(DIRECTORY_SEPARATOR, '/', $smtpPath);
                
                if (file_exists($exceptionPathAlt) && file_exists($smtpPathAlt) && file_exists($phpmailerPathAlt)) {
                    require_once $exceptionPathAlt;
                    require_once $smtpPathAlt;
                    require_once $phpmailerPathAlt;
                } else {
                    // Files don't exist - log for debugging
                    $missingFiles = [];
                    if (!file_exists($exceptionPath) && !file_exists($exceptionPathAlt)) $missingFiles[] = 'Exception.php';
                    if (!file_exists($smtpPath) && !file_exists($smtpPathAlt)) $missingFiles[] = 'SMTP.php';
                    if (!file_exists($phpmailerPath) && !file_exists($phpmailerPathAlt)) $missingFiles[] = 'PHPMailer.php';
                    
                    error_log("PHPMailer files missing. Base dir: $baseDir. Exception: " . ($exceptionPath) . " (exists: " . (file_exists($exceptionPath) ? 'yes' : 'no') . ")");
                    return [
                        'success' => false,
                        'message' => 'PHPMailer files not found. Please ensure composer install has been run.'
                    ];
                }
            }
        }
    }
    
    // Final check - class should exist now
    if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        error_log("PHPMailer class still not found after all loading attempts. Base dir: $baseDir");
        return [
            'success' => false,
            'message' => 'PHPMailer class could not be loaded. Please check file permissions or run: composer install'
        ];
    }
    
    // Try to instantiate PHPMailer to ensure it works
    try {
        $mail = new PHPMailer(true);
    } catch (\Exception $e) {
        error_log("PHPMailer instantiation error: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Failed to initialize PHPMailer: ' . $e->getMessage()
        ];
    } catch (\Error $e) {
        error_log("PHPMailer instantiation fatal error: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Failed to initialize PHPMailer. Please check server logs for details.'
        ];
    }
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USERNAME;
        $mail->Password   = SMTP_PASSWORD;
        $mail->SMTPSecure = SMTP_ENCRYPTION;
        $mail->Port       = SMTP_PORT;
        
        // Recipients
        $mail->setFrom(FROM_EMAIL, FROM_NAME);
        $mail->addAddress($email, $name);
        $mail->addReplyTo(FROM_EMAIL, FROM_NAME);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Password Reset Request - ' . SITE_NAME;
        $mail->Body    = getPasswordResetEmailTemplate($name, $resetLink);
        $mail->AltBody = getPasswordResetEmailPlainText($name, $resetLink);
        
        $mail->send();
        
        return [
            'success' => true,
            'message' => 'Password reset email sent successfully'
        ];
        
    } catch (Exception $e) {
        error_log("Email sending failed: {$mail->ErrorInfo}");
        error_log("Exception: " . $e->getMessage());
        
        return [
            'success' => false,
            'message' => 'Failed to send email: ' . $mail->ErrorInfo,
            'error' => $mail->ErrorInfo,
            'exception' => $e->getMessage()
        ];
    }
}

/**
 * Get HTML email template for password reset
 */
function getPasswordResetEmailTemplate($name, $resetLink) {
    return '
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Password Reset</title>
    </head>
    <body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f4f4f4;">
        <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f4f4f4; padding: 20px;">
            <tr>
                <td align="center">
                    <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                        
                        <!-- Header -->
                        <tr>
                            <td style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 40px; text-align: center;">
                                <h1 style="margin: 0; color: #ffffff; font-size: 28px;">🔐 Password Reset</h1>
                            </td>
                        </tr>
                        
                        <!-- Content -->
                        <tr>
                            <td style="padding: 40px;">
                                <h2 style="color: #333; margin-top: 0;">Hello ' . htmlspecialchars($name) . ',</h2>
                                
                                <p style="color: #666; line-height: 1.6; font-size: 16px;">
                                    We received a request to reset your password for your <strong>' . SITE_NAME . '</strong> account.
                                </p>
                                
                                <p style="color: #666; line-height: 1.6; font-size: 16px;">
                                    Click the button below to reset your password:
                                </p>
                                
                                <!-- Reset Button -->
                                <div style="text-align: center; margin: 30px 0;">
                                    <a href="' . htmlspecialchars($resetLink) . '" 
                                       style="display: inline-block; padding: 15px 30px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #ffffff; text-decoration: none; border-radius: 8px; font-weight: bold; font-size: 16px;">
                                        Reset Password
                                    </a>
                                </div>
                                
                                <p style="color: #666; line-height: 1.6; font-size: 14px; background-color: #f9f9f9; padding: 15px; border-left: 4px solid #667eea; border-radius: 4px;">
                                    <strong>⚠️ Important:</strong> This link will expire in <strong>1 hour</strong> for security reasons.
                                </p>
                                
                                <p style="color: #666; line-height: 1.6; font-size: 14px;">
                                    If the button doesn\'t work, copy and paste this link into your browser:
                                </p>
                                
                                <p style="color: #667eea; line-height: 1.6; font-size: 13px; word-break: break-all; background-color: #f9f9f9; padding: 10px; border-radius: 4px;">
                                    ' . htmlspecialchars($resetLink) . '
                                </p>
                                
                                <hr style="border: none; border-top: 1px solid #e0e0e0; margin: 30px 0;">
                                
                                <p style="color: #999; line-height: 1.6; font-size: 13px;">
                                    If you didn\'t request a password reset, please ignore this email or contact support if you have concerns.
                                </p>
                            </td>
                        </tr>
                        
                        <!-- Footer -->
                        <tr>
                            <td style="background-color: #f9f9f9; padding: 20px; text-align: center; border-top: 1px solid #e0e0e0;">
                                <p style="margin: 0; color: #999; font-size: 12px;">
                                    © ' . date('Y') . ' ' . SITE_NAME . '. All rights reserved.
                                </p>
                            </td>
                        </tr>
                        
                    </table>
                </td>
            </tr>
        </table>
    </body>
    </html>
    ';
}

/**
 * Get plain text version for email clients that don't support HTML
 */
function getPasswordResetEmailPlainText($name, $resetLink) {
    return "Hello $name,

We received a request to reset your password for your " . SITE_NAME . " account.

Click the link below to reset your password:
$resetLink

Important: This link will expire in 1 hour for security reasons.

If you didn't request a password reset, please ignore this email or contact support if you have concerns.

© " . date('Y') . " " . SITE_NAME . ". All rights reserved.";
}
?>

