<?php
/**
 * Email Helper Functions
 * Handles sending emails for password reset
 */

// Load Composer autoloader first if it exists
// The autoloader registers itself automatically when required
$vendorPath = __DIR__ . '/vendor/autoload.php';
$autoloaderLoaded = false;
if (file_exists($vendorPath)) {
    try {
        require_once $vendorPath;
        $autoloaderLoaded = true;
    } catch (\Exception $e) {
        // Autoloader failed, will fall back to manual loading
        error_log("Autoloader failed to load: " . $e->getMessage());
    }
}

// If autoloader didn't work or PHPMailer class doesn't exist, manually load it
if (!class_exists('PHPMailer\PHPMailer\PHPMailer', false)) {
    $baseDir = __DIR__;
    $exceptionPath = $baseDir . '/vendor/phpmailer/phpmailer/src/Exception.php';
    $smtpPath = $baseDir . '/vendor/phpmailer/phpmailer/src/SMTP.php';
    $phpmailerPath = $baseDir . '/vendor/phpmailer/phpmailer/src/PHPMailer.php';
    
    if (file_exists($exceptionPath) && file_exists($smtpPath) && file_exists($phpmailerPath)) {
        require_once $exceptionPath;
        require_once $smtpPath;
        require_once $phpmailerPath;
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
    // Check if PHPMailer class already exists (maybe autoloader worked)
    if (!class_exists('PHPMailer\PHPMailer\PHPMailer', false)) {
        // Class doesn't exist yet, try to load via autoloader first
        if (!class_exists('PHPMailer\PHPMailer\PHPMailer', true)) {
            // Autoloader failed, manually require files
            // Get base directory - try multiple methods
            $baseDir = __DIR__;
            
            // Also try using dirname(__FILE__) as fallback
            $altBaseDir = dirname(__FILE__);
            
            // Build list of possible base directories
            $baseDirs = array_unique([$baseDir, $altBaseDir]);
            
            $filesLoaded = false;
            
            // Try each base directory
            foreach ($baseDirs as $dir) {
                // Try forward slashes first (works on Windows and Linux)
                $exceptionPath = $dir . '/vendor/phpmailer/phpmailer/src/Exception.php';
                $smtpPath = $dir . '/vendor/phpmailer/phpmailer/src/SMTP.php';
                $phpmailerPath = $dir . '/vendor/phpmailer/phpmailer/src/PHPMailer.php';
                
                // Use realpath to normalize and verify
                $exceptionReal = @realpath($exceptionPath);
                $smtpReal = @realpath($smtpPath);
                $phpmailerReal = @realpath($phpmailerPath);
                
                // If realpath worked, use those paths, otherwise try original
                if ($exceptionReal && $smtpReal && $phpmailerReal) {
                    try {
                        require_once $exceptionReal;
                        require_once $smtpReal;
                        require_once $phpmailerReal;
                        $filesLoaded = true;
                        break;
                    } catch (\Throwable $e) {
                        error_log("Error loading PHPMailer (realpath): " . $e->getMessage());
                    }
                } elseif (@file_exists($exceptionPath) && @file_exists($smtpPath) && @file_exists($phpmailerPath)) {
                    // Try direct paths if realpath failed but files exist
                    try {
                        require_once $exceptionPath;
                        require_once $smtpPath;
                        require_once $phpmailerPath;
                        $filesLoaded = true;
                        break;
                    } catch (\Throwable $e) {
                        error_log("Error loading PHPMailer (direct): " . $e->getMessage());
                    }
                }
            }
            
            if (!$filesLoaded) {
                // Log all attempted paths for debugging
                error_log("PHPMailer loading failed. Attempted base dirs: " . implode(', ', $baseDirs));
                foreach ($baseDirs as $dir) {
                    $testPath = $dir . '/vendor/phpmailer/phpmailer/src/PHPMailer.php';
                    error_log("  - Tested: $testPath (exists: " . (@file_exists($testPath) ? 'YES' : 'NO') . ")");
                }
                
                return [
                    'success' => false,
                    'message' => 'PHPMailer files not found. Please ensure composer install has been run.'
                ];
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

