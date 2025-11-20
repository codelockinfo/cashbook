<?php
/**
 * Hostinger Deployment Script (PHP version)
 * Run this file via: php deploy.php
 * Or set it up as a post-receive hook in Hostinger
 */

echo "Starting deployment...\n";

// Remove vendor directory and composer.lock if they exist
if (is_dir('vendor')) {
    echo "Removing existing vendor directory...\n";
    deleteDirectory('vendor');
}

if (file_exists('composer.lock')) {
    echo "Removing existing composer.lock...\n";
    unlink('composer.lock');
}

// Pull latest code from Git
echo "Pulling latest code from Git...\n";
$output = [];
$return_var = 0;
exec('git pull origin main 2>&1', $output, $return_var);

if ($return_var !== 0) {
    // Try master branch if main doesn't exist
    exec('git pull origin master 2>&1', $output, $return_var);
}

echo implode("\n", $output) . "\n";

// Install Composer dependencies
if (file_exists('composer.json')) {
    echo "Installing Composer dependencies...\n";
    
    // Check if composer.phar exists
    if (file_exists('composer.phar')) {
        exec('php composer.phar install --no-dev --optimize-autoloader 2>&1', $output, $return_var);
    } else {
        // Download and install Composer if not present
        echo "Downloading Composer...\n";
        copy('https://getcomposer.org/installer', 'composer-setup.php');
        exec('php composer-setup.php 2>&1', $output, $return_var);
        
        if (file_exists('composer.phar')) {
            exec('php composer.phar install --no-dev --optimize-autoloader 2>&1', $output, $return_var);
            unlink('composer-setup.php');
        }
    }
    
    echo implode("\n", $output) . "\n";
}

echo "Deployment completed!\n";

/**
 * Recursively delete a directory
 */
function deleteDirectory($dir) {
    if (!file_exists($dir)) {
        return true;
    }
    if (!is_dir($dir)) {
        return unlink($dir);
    }
    foreach (scandir($dir) as $item) {
        if ($item == '.' || $item == '..') {
            continue;
        }
        if (!deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) {
            return false;
        }
    }
    return rmdir($dir);
}

