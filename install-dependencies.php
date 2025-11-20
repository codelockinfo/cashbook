<?php
/**
 * Temporary script to install Composer dependencies
 * Run this file once via browser: https://yourdomain.com/install-dependencies.php
 * DELETE THIS FILE after use for security!
 */

// Security: Only allow if accessed directly (not included)
if (basename($_SERVER['PHP_SELF']) !== 'install-dependencies.php') {
    die('Direct access only');
}

// Optional: Add a simple password protection
$password = 'your-temp-password-here'; // CHANGE THIS!
if (!isset($_GET['key']) || $_GET['key'] !== $password) {
    die('Access denied. Add ?key=your-temp-password-here to the URL');
}

echo "<h2>Installing Composer Dependencies...</h2>";
echo "<pre>";

$baseDir = __DIR__;
$composerJson = $baseDir . '/composer.json';

if (!file_exists($composerJson)) {
    die("Error: composer.json not found in $baseDir");
}

echo "Base directory: $baseDir\n";
echo "Composer.json found: YES\n\n";

// Check if composer.phar exists
$composerPhar = $baseDir . '/composer.phar';
$composerCmd = '';

if (file_exists($composerPhar)) {
    $composerCmd = "php composer.phar";
    echo "Found composer.phar\n";
} else {
    // Try system composer
    $composerCmd = "composer";
    echo "Using system composer\n";
}

echo "\nRunning: $composerCmd install --no-dev --optimize-autoloader\n";
echo str_repeat("=", 60) . "\n\n";

// Change to project directory
chdir($baseDir);

// Run composer install
$command = "$composerCmd install --no-dev --optimize-autoloader 2>&1";
$output = [];
$returnVar = 0;

exec($command, $output, $returnVar);

// Display output
foreach ($output as $line) {
    echo htmlspecialchars($line) . "\n";
}

echo "\n" . str_repeat("=", 60) . "\n";

if ($returnVar === 0) {
    echo "\n✅ <strong>SUCCESS!</strong> Dependencies installed successfully.\n";
    echo "\n⚠️ <strong>IMPORTANT:</strong> Delete this file (install-dependencies.php) now for security!\n";
    
    // Verify vendor directory exists
    $vendorDir = $baseDir . '/vendor';
    if (is_dir($vendorDir)) {
        echo "\n✅ Vendor directory created: $vendorDir\n";
        
        // Check for PHPMailer
        $phpmailerPath = $vendorDir . '/phpmailer/phpmailer/src/PHPMailer.php';
        if (file_exists($phpmailerPath)) {
            echo "✅ PHPMailer found: $phpmailerPath\n";
        } else {
            echo "⚠️ PHPMailer not found. Check installation.\n";
        }
    }
} else {
    echo "\n❌ <strong>ERROR:</strong> Installation failed (exit code: $returnVar)\n";
    echo "\nTry running manually via SSH:\n";
    echo "composer install --no-dev --optimize-autoloader\n";
}

echo "</pre>";
?>

