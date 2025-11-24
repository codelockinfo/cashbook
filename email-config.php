<?php
/**
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * 📧 EMAIL CONFIGURATION FOR PASSWORD RESET
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * 
 * IMPORTANT: Choose your mode below
 * 
 * 🔧 DEVELOPMENT MODE (Current): 
 *    - No email setup required
 *    - Shows reset link in popup
 *    - Perfect for local testing
 *    - Set DEV_MODE = true
 * 
 * 📧 PRODUCTION MODE:
 *    - Sends real emails to users
 *    - Requires email configuration below
 *    - Set DEV_MODE = false
 *    - Configure SMTP settings
 * 
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 */

// ═══════════════════════════════════════════════════════════
// 🎯 MODE SELECTION
// ═══════════════════════════════════════════════════════════

/**
 * Development Mode
 * true  = Show reset link in popup (NO EMAIL SENT) ✅ Current
 * false = Send actual emails (REQUIRES EMAIL CONFIG BELOW)
 */
define('DEV_MODE', false);
define('DEV_MODE_DISABLE_EMAIL', false);


// ═══════════════════════════════════════════════════════════
// 📧 EMAIL SETTINGS (Only needed if DEV_MODE = false)
// ═══════════════════════════════════════════════════════════

/**
 * SMTP Server Configuration
 * 
 * 📌 GMAIL EXAMPLE:
 *    SMTP_HOST: smtp.gmail.com
 *    SMTP_PORT: 587
 *    SMTP_USERNAME: youremail@gmail.com
 *    SMTP_PASSWORD: Your 16-digit App Password (NOT regular password)
 *    SMTP_ENCRYPTION: tls
 * 
 * 📌 OUTLOOK/OFFICE365:
 *    SMTP_HOST: smtp-mail.outlook.com
 *    SMTP_PORT: 587
 *    SMTP_ENCRYPTION: tls
 * 
 * 📌 YAHOO:
 *    SMTP_HOST: smtp.mail.yahoo.com
 *    SMTP_PORT: 587
 *    SMTP_ENCRYPTION: tls
 */

// Only define if not already defined in config.php
if (!defined('SMTP_HOST')) {
    define('SMTP_HOST', 'smtp.hostinger.com');           // SMTP server address
}
if (!defined('SMTP_PORT')) {
    define('SMTP_PORT', 465);                            // Port (587=TLS, 465=SSL, 25=Standard)
}
if (!defined('SMTP_USERNAME')) {
    define('SMTP_USERNAME', 'tailorpro@happyeventsurat.com'); // Your email address
}
if (!defined('SMTP_PASSWORD')) {
    define('SMTP_PASSWORD', 'Tailor@99');                // Your email password/app-password
}
if (!defined('SMTP_ENCRYPTION')) {
    define('SMTP_ENCRYPTION', 'ssl');                    // Encryption: 'tls' or 'ssl' (465 uses SSL)
}


// ═══════════════════════════════════════════════════════════
// ✉️ EMAIL SENDER INFORMATION
// ═══════════════════════════════════════════════════════════

/**
 * The "From" email and name that appears in user's inbox
 */
if (!defined('FROM_EMAIL')) {
    // Use SMTP_FROM_EMAIL from config.php if available, otherwise use default
    define('FROM_EMAIL', defined('SMTP_FROM_EMAIL') ? SMTP_FROM_EMAIL : 'tailorpro@happyeventsurat.com');
}
if (!defined('FROM_NAME')) {
    // Use SMTP_FROM_NAME from config.php if available, otherwise use default
    define('FROM_NAME', defined('SMTP_FROM_NAME') ? SMTP_FROM_NAME : 'TailorPro');
}


// ═══════════════════════════════════════════════════════════
// 🌐 SITE INFORMATION
// ═══════════════════════════════════════════════════════════

/**
 * Your website information
 * Update SITE_URL when deploying to production!
 */
define('SITE_NAME', 'Cash Book');                       // Your application name
// Auto-detect SITE_URL from current request
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptPath = dirname($_SERVER['SCRIPT_NAME'] ?? '');
$scriptPath = ($scriptPath === '/' || $scriptPath === '\\') ? '' : $scriptPath;
define('SITE_URL', $protocol . '://' . $host . $scriptPath);


// ═══════════════════════════════════════════════════════════
// 📚 QUICK REFERENCE
// ═══════════════════════════════════════════════════════════

/**
 * 🔧 FOR LOCAL TESTING:
 *    1. Keep DEV_MODE = true
 *    2. No email configuration needed
 *    3. Reset link shows in popup
 *    4. Click OK to test immediately
 * 
 * 📧 FOR PRODUCTION:
 *    1. Install PHPMailer: composer install
 *    2. Configure SMTP settings above
 *    3. Set DEV_MODE = false
 *    4. Test with real email address
 * 
 * 📖 NEED HELP?
 *    - Gmail Setup: See EMAIL_SETUP_GUIDE.md (Section: Gmail App Password)
 *    - Other Providers: See EMAIL_SETUP_GUIDE.md (Section: Other Email Services)
 *    - Troubleshooting: See EMAIL_SETUP_GUIDE.md (Section: Troubleshooting)
 * 
 * ⚠️ SECURITY NOTE:
 *    - Never commit this file with real passwords to Git
 *    - Add to .gitignore: echo "email-config.php" >> .gitignore
 *    - Use environment variables in production
 */

?>


