# Hostinger Git Deployment Fix

## Problem
Git pull fails because `vendor/` and `composer.lock` exist on server but aren't tracked in Git.

## Solution: Configure Hostinger Deployment Settings

### Step 1: Access Hostinger Panel
1. Log into your Hostinger control panel
2. Go to **Advanced** → **Git** (or **Deployments**)
3. Find your repository deployment settings

### Step 2: Configure Pre-Deployment Command
In Hostinger's Git deployment settings, add a **Pre-Pull Command** or **Pre-Deployment Script**:

```bash
rm -rf vendor composer.lock
```

This removes the conflicting files BEFORE Git tries to pull.

### Step 3: Configure Post-Deployment Command
After Git pull succeeds, add a **Post-Deployment Command**:

```bash
php composer.phar install --no-dev --optimize-autoloader
```

Or if `composer.phar` doesn't exist:

```bash
composer install --no-dev --optimize-autoloader
```

### Alternative: Manual SSH Fix (One-time)

If you can't configure pre/post commands in Hostinger panel, SSH into your server:

```bash
# Navigate to your project directory
cd /home/u402017191/domains/yourdomain.com/public_html

# Remove conflicting files
rm -rf vendor composer.lock

# Now try deployment again from Hostinger panel
```

Then in Hostinger panel, configure the post-deployment command to run:
```bash
php composer.phar install --no-dev --optimize-autoloader
```

### Step 4: Verify .gitignore
Make sure `.gitignore` includes:
```
/vendor/
composer.lock
```

This ensures these files are never committed to Git.

## Hostinger Panel Configuration Example

**Pre-Deployment Command:**
```
rm -rf vendor composer.lock
```

**Post-Deployment Command:**
```
php composer.phar install --no-dev --optimize-autoloader
```

**OR if composer.phar doesn't exist:**
```
composer install --no-dev --optimize-autoloader
```

## Quick Manual Fix (If Panel Doesn't Support Commands)

1. SSH into your server
2. Run:
   ```bash
   cd /path/to/your/project
   rm -rf vendor composer.lock
   ```
3. Go back to Hostinger panel and trigger deployment
4. After deployment, SSH again and run:
   ```bash
   cd /path/to/your/project
   php composer.phar install --no-dev --optimize-autoloader
   ```

## Notes

- The `.gitignore` file is already configured correctly
- `vendor/` and `composer.lock` should NOT be in Git
- They should be generated on the server after deployment
- Use `--no-dev` flag in production to skip development dependencies
- Use `--optimize-autoloader` for better performance

