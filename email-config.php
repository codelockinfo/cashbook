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

define('SMTP_HOST', 'smtp.hostinger.com');           // SMTP server address
define('SMTP_PORT', 465);                            // Port (587=TLS, 465=SSL, 25=Standard)
define('SMTP_USERNAME', 'tailorpro@happyeventsurat.com'); // Your email address
define('SMTP_PASSWORD', 'Tailor@99');                // Your email password/app-password
define('SMTP_ENCRYPTION', 'tls');                    // Encryption: 'tls' or 'ssl'


// ═══════════════════════════════════════════════════════════
// ✉️ EMAIL SENDER INFORMATION
// ═══════════════════════════════════════════════════════════

/**
 * The "From" email and name that appears in user's inbox
 */
define('FROM_EMAIL', 'tailorpro@happyeventsurat.com');    // Sender email address
define('FROM_NAME', 'TailorPro');                       // Sender name


// ═══════════════════════════════════════════════════════════
// 🌐 SITE INFORMATION
// ═══════════════════════════════════════════════════════════

/**
 * Your website information
 * Update SITE_URL when deploying to production!
 */
define('SITE_NAME', 'Cash Book');                       // Your application name
define('SITE_URL', 'http://localhost/cashbook');        // Your site URL (CHANGE IN PRODUCTION!)


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


