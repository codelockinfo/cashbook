# 🚀 Quick Deployment Fix for Hostinger

## Problem
Git deployment fails with error: "The following untracked working tree files would be overwritten by merge: vendor/..."

## ✅ Solution: Use the Cleanup Script

### Step 1: Run Cleanup Script
1. Upload `cleanup-for-deploy.php` to your server (if not already there)
2. Open in browser: `https://yourdomain.com/cleanup-for-deploy.php`
3. Click the "Remove vendor/ and composer.lock" button
4. Wait for confirmation message

### Step 2: Deploy in Hostinger
1. Go to Hostinger Panel → **Advanced** → **Git** → **Manage Repositories**
2. Click the **Deploy** button for your repository
3. Deployment should now succeed! ✅

### Step 3: Install Dependencies (if needed)
After deployment, if dependencies aren't installed automatically, SSH in and run:
```bash
cd /path/to/your/project
php composer.phar install --no-dev --optimize-autoloader
```

## 🔄 For Future Deployments

### Option A: Use Cleanup Script (Easiest)
Before each deployment:
1. Run `cleanup-for-deploy.php` in browser
2. Then click Deploy in Hostinger

### Option B: Configure Hostinger Pre-Deploy Command
In Hostinger Git settings, add:
- **Pre-Deployment Command:** `rm -rf vendor composer.lock`
- **Post-Deployment Command:** `php composer.phar install --no-dev --optimize-autoloader`

### Option C: SSH Manual Cleanup
```bash
cd /path/to/your/project
rm -rf vendor composer.lock
# Then deploy from Hostinger panel
```

## 🔒 Security
After deployment, disable `cleanup-for-deploy.php` by setting:
```php
$allowed = false;
```
Or delete the file entirely.

## 📁 Files Included
- `cleanup-for-deploy.php` - Browser-based cleanup script
- `pre-deploy.sh` - Pre-deployment script (if Hostinger supports it)
- `post-deploy.sh` - Post-deployment script (if Hostinger supports it)
- `.gitignore` - Ensures vendor/ is never committed to Git

