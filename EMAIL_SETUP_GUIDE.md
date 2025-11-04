# Email Setup Guide for Password Reset

## 📧 Overview

This guide will help you set up email functionality for the password reset feature. The system works in two modes:

- **Development Mode** (Default): Shows reset link in a popup - **No email required**
- **Production Mode**: Sends actual emails to users

---

## 🎯 Current Status: Development Mode

**✅ Your system is currently in DEVELOPMENT MODE**

This means:
- No email configuration needed for testing
- Reset links appear in a popup/alert
- Perfect for local testing
- User clicks "OK" and goes directly to reset page

**This is ideal for testing and development!**

---

## 🔄 How It Works

### Development Mode Flow:
1. User enters email on forgot-password page
2. System generates reset token
3. **Popup appears with reset link** ← Current behavior
4. User clicks OK to open reset page
5. User enters new password

### Production Mode Flow:
1. User enters email on forgot-password page
2. System generates reset token
3. **Email sent to user's inbox** ← Production behavior
4. User clicks link in their email
5. Reset page opens in browser
6. User enters new password

---

## 🚀 Setting Up Email for Production

When you're ready to deploy your application, follow these steps:

### Step 1: Install PHPMailer

Open PowerShell/Command Prompt in your project folder and run:

```bash
composer install
```

This will install PHPMailer based on the `composer.json` file already created.

**Alternative:** If you don't have Composer installed, download it from: https://getcomposer.org/download/

---

### Step 2: Configure Email Settings

Open `email-config.php` and update these settings:

#### Option A: Using Gmail (Recommended for testing)

```php
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'youremail@gmail.com');  // Your Gmail address
define('SMTP_PASSWORD', 'your-app-password');     // Gmail App Password (NOT your regular password)
define('SMTP_ENCRYPTION', 'tls');

define('FROM_EMAIL', 'noreply@yoursite.com');
define('FROM_NAME', 'Cash Book');

define('SITE_URL', 'http://yourdomain.com/cashbook'); // Your actual domain
```

#### Option B: Using Other Email Services

**Outlook/Office365:**
```php
define('SMTP_HOST', 'smtp-mail.outlook.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'youremail@outlook.com');
define('SMTP_PASSWORD', 'your-password');
define('SMTP_ENCRYPTION', 'tls');
```

**Yahoo:**
```php
define('SMTP_HOST', 'smtp.mail.yahoo.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'youremail@yahoo.com');
define('SMTP_PASSWORD', 'your-app-password');
define('SMTP_ENCRYPTION', 'tls');
```

**Custom SMTP Server:**
```php
define('SMTP_HOST', 'mail.yourdomain.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'noreply@yourdomain.com');
define('SMTP_PASSWORD', 'your-password');
define('SMTP_ENCRYPTION', 'tls');
```

---

### Step 3: Get Gmail App Password (If using Gmail)

Gmail requires an "App Password" for security:

1. **Enable 2-Step Verification** on your Google Account:
   - Go to: https://myaccount.google.com/security
   - Click "2-Step Verification"
   - Follow the steps to enable it

2. **Generate App Password**:
   - Go to: https://myaccount.google.com/apppasswords
   - Select "Mail" and your device
   - Click "Generate"
   - Copy the 16-character password
   - Use this in `SMTP_PASSWORD` (without spaces)

📹 Video Tutorial: https://support.google.com/accounts/answer/185833

---

### Step 4: Enable Production Mode

In `email-config.php`, change this line:

```php
// FROM (Development Mode):
define('DEV_MODE', true);

// TO (Production Mode):
define('DEV_MODE', false);
```

---

### Step 5: Test Email Sending

1. Go to: `http://localhost/cashbook/forgot-password.php`
2. Enter a real email address (yours)
3. Click "Send Reset Link"
4. **Check your email inbox** (and spam folder)
5. Click the link in the email
6. Reset your password

---

## 🧪 Testing Checklist

### Development Mode Testing (Current):
- [x] Enter email on forgot-password page
- [x] Click "Send Reset Link"
- [x] Popup appears with reset link
- [x] Click OK to open reset page
- [x] Enter new password
- [x] Login with new password

### Production Mode Testing (After Email Setup):
- [ ] Install PHPMailer (composer install)
- [ ] Configure email settings
- [ ] Set DEV_MODE to false
- [ ] Enter email on forgot-password page
- [ ] Check email inbox
- [ ] Click link in email
- [ ] Reset password
- [ ] Login with new password

---

## 🎨 Email Template

The system sends beautiful HTML emails with:

✅ Professional gradient header  
✅ Clear "Reset Password" button  
✅ Expiration warning (1 hour)  
✅ Plain text fallback  
✅ Mobile responsive design  
✅ Security information  

**Preview:** The email includes:
- Your app logo and branding
- User's name (personalized)
- Big reset password button
- Backup link (if button doesn't work)
- Security warnings
- Professional footer

---

## 🔧 Troubleshooting

### Issue: Composer not found
**Solution:** 
1. Download from https://getcomposer.org/download/
2. Install it
3. Restart your terminal
4. Run `composer install` again

### Issue: Gmail - "Less secure app access"
**Solution:**
- Gmail removed this feature
- You MUST use App Passwords now (see Step 3)
- Don't use your regular Gmail password

### Issue: Emails going to spam
**Solution:**
1. Check your "From Email" configuration
2. Add SPF/DKIM records to your domain (for production)
3. Use a verified sending domain
4. Ask test users to mark as "Not Spam"

### Issue: Connection timeout
**Solution:**
1. Check if your hosting allows SMTP connections
2. Try different ports (587, 465, 25)
3. Verify SMTP_HOST is correct
4. Check firewall settings

### Issue: Authentication failed
**Solution:**
1. Double-check username and password
2. For Gmail: Use App Password, not regular password
3. Enable "Less secure app access" for other services
4. Verify 2-Step Verification is enabled (Gmail)

---

## 🔐 Security Best Practices

### For Production:

1. **Never commit credentials to Git:**
   ```bash
   # Add to .gitignore
   echo "email-config.php" >> .gitignore
   ```

2. **Use environment variables:**
   ```php
   define('SMTP_PASSWORD', getenv('EMAIL_PASSWORD'));
   ```

3. **Rate limiting:** Prevent abuse by limiting requests
   - Add to auth-api.php
   - Limit to 3 requests per hour per email

4. **Use SSL/TLS:** Always use encrypted connections

5. **Validate email domains:** Check if email domain exists

---

## 📊 Mode Comparison

| Feature | Development Mode | Production Mode |
|---------|-----------------|----------------|
| Email Required | ❌ No | ✅ Yes |
| Setup Time | Instant | 15-30 minutes |
| Testing Speed | Fast | Slower (email delay) |
| User Experience | Good for testing | Professional |
| Shows Reset Link | Popup | Email only |
| Composer Needed | ❌ No | ✅ Yes |
| SMTP Config | ❌ Not needed | ✅ Required |

---

## 🎯 Quick Switch Commands

### Test in Development Mode:
```php
// email-config.php
define('DEV_MODE', true);
```

### Deploy to Production:
```php
// email-config.php
define('DEV_MODE', false);
```

---

## 📝 Configuration Files Reference

### File Structure:
```
cashbook/
├── email-config.php       (Email settings - EDIT THIS)
├── email-helper.php       (Email sending logic - DON'T EDIT)
├── auth-api.php          (Updated with email support)
├── auth.js               (Updated for dev/prod modes)
├── composer.json         (PHPMailer dependency)
└── vendor/               (Created after composer install)
    └── phpmailer/
```

---

## 🚀 Deployment Checklist

Before going live:

- [ ] Install PHPMailer (`composer install`)
- [ ] Configure email settings in `email-config.php`
- [ ] Test email sending with real email
- [ ] Set `DEV_MODE = false`
- [ ] Update `SITE_URL` to production domain
- [ ] Add `email-config.php` to `.gitignore`
- [ ] Test forgot password flow end-to-end
- [ ] Check emails arrive within 1 minute
- [ ] Verify links work correctly
- [ ] Test token expiration (after 1 hour)
- [ ] Test with different email providers

---

## 💡 Tips

1. **Keep DEV_MODE = true for local development** - It's faster and easier
2. **Test production mode before going live** - Use your own email first
3. **Monitor email logs** - Check if emails are being sent
4. **Use a professional email service** for production (SendGrid, Mailgun, AWS SES)
5. **Set up SPF/DKIM records** for better deliverability

---

## 🆘 Support

If you need help:

1. Check the troubleshooting section above
2. Verify your email configuration
3. Check PHP error logs
4. Test with different email providers
5. Use a service like Mailtrap.io for testing

---

## 📚 Additional Resources

- **PHPMailer Documentation:** https://github.com/PHPMailer/PHPMailer
- **Gmail App Passwords:** https://support.google.com/accounts/answer/185833
- **Mailtrap (Testing):** https://mailtrap.io/
- **SendGrid (Production):** https://sendgrid.com/
- **Mailgun (Production):** https://www.mailgun.com/

---

**Created:** November 4, 2025  
**Current Mode:** Development (Popup-based)  
**Status:** ✅ Working perfectly for testing!

