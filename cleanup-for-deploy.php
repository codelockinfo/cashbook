<?php
/**
 * Cleanup Script for Hostinger Deployment
 * 
 * Run this file in your browser BEFORE deploying:
 * https://yourdomain.com/cleanup-for-deploy.php
 * 
 * This will remove vendor/ and composer.lock so Git pull can succeed
 */

// Security: Only allow this to run in specific conditions
// Remove or comment out the exit line after first use for security
$allowed = true; // Set to false after use for security

if (!$allowed) {
    die('This script has been disabled for security. Re-enable it in the file if needed.');
}

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deployment Cleanup</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            border-bottom: 3px solid #667eea;
            padding-bottom: 10px;
        }
        .success {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
            border-left: 4px solid #28a745;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
            border-left: 4px solid #dc3545;
        }
        .info {
            background: #d1ecf1;
            color: #0c5460;
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
            border-left: 4px solid #17a2b8;
        }
        .warning {
            background: #fff3cd;
            color: #856404;
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
            border-left: 4px solid #ffc107;
        }
        pre {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
            border: 1px solid #dee2e6;
        }
        .btn {
            display: inline-block;
            background: #667eea;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
            border: none;
            cursor: pointer;
            font-size: 16px;
        }
        .btn:hover {
            background: #5568d3;
        }
        .btn-danger {
            background: #dc3545;
        }
        .btn-danger:hover {
            background: #c82333;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧹 Deployment Cleanup Script</h1>
        
        <?php
        $messages = [];
        $errors = [];
        $warnings = [];
        
        // Function to recursively delete directory
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
        
        // Check if cleanup was requested
        if (isset($_GET['cleanup']) && $_GET['cleanup'] === 'yes') {
            echo '<div class="info">🔄 Starting cleanup process...</div>';
            
            // Remove vendor directory
            if (is_dir('vendor')) {
                if (deleteDirectory('vendor')) {
                    $messages[] = '✓ Successfully removed vendor/ directory';
                } else {
                    $errors[] = '✗ Failed to remove vendor/ directory';
                }
            } else {
                $warnings[] = 'ℹ️ vendor/ directory does not exist';
            }
            
            // Remove composer.lock
            if (file_exists('composer.lock')) {
                if (unlink('composer.lock')) {
                    $messages[] = '✓ Successfully removed composer.lock';
                } else {
                    $errors[] = '✗ Failed to remove composer.lock';
                }
            } else {
                $warnings[] = 'ℹ️ composer.lock does not exist';
            }
            
            // Display results
            foreach ($messages as $msg) {
                echo '<div class="success">' . htmlspecialchars($msg) . '</div>';
            }
            
            foreach ($warnings as $msg) {
                echo '<div class="warning">' . htmlspecialchars($msg) . '</div>';
            }
            
            foreach ($errors as $msg) {
                echo '<div class="error">' . htmlspecialchars($msg) . '</div>';
            }
            
            if (empty($errors)) {
                echo '<div class="success">';
                echo '<h2>✅ Cleanup Completed Successfully!</h2>';
                echo '<p>You can now go to Hostinger panel and click <strong>Deploy</strong> button.</p>';
                echo '<p>The Git pull should now succeed without conflicts.</p>';
                echo '</div>';
            } else {
                echo '<div class="error">';
                echo '<h2>❌ Cleanup Had Errors</h2>';
                echo '<p>You may need to manually remove these files via SSH or File Manager.</p>';
                echo '</div>';
            }
            
        } else {
            // Show current status
            echo '<div class="info">';
            echo '<h2>📋 Current Status</h2>';
            
            if (is_dir('vendor')) {
                echo '<p>⚠️ <strong>vendor/</strong> directory exists (will conflict with Git pull)</p>';
            } else {
                echo '<p>✓ <strong>vendor/</strong> directory does not exist</p>';
            }
            
            if (file_exists('composer.lock')) {
                echo '<p>⚠️ <strong>composer.lock</strong> file exists (will conflict with Git pull)</p>';
            } else {
                echo '<p>✓ <strong>composer.lock</strong> file does not exist</p>';
            }
            
            echo '</div>';
            
            if (is_dir('vendor') || file_exists('composer.lock')) {
                echo '<div class="warning">';
                echo '<h2>⚠️ Action Required</h2>';
                echo '<p>These files need to be removed before Git deployment can succeed.</p>';
                echo '<p><strong>Click the button below to remove them:</strong></p>';
                echo '<a href="?cleanup=yes" class="btn btn-danger" onclick="return confirm(\'Are you sure you want to remove vendor/ and composer.lock? This is safe - they will be regenerated after deployment.\')">🗑️ Remove vendor/ and composer.lock</a>';
                echo '</div>';
            } else {
                echo '<div class="success">';
                echo '<h2>✅ Ready for Deployment</h2>';
                echo '<p>No conflicting files found. You can proceed with deployment in Hostinger panel.</p>';
                echo '</div>';
            }
        }
        ?>
        
        <div class="info" style="margin-top: 30px;">
            <h3>📝 Instructions:</h3>
            <ol>
                <li>Run this cleanup script (click the button above if needed)</li>
                <li>Go to Hostinger panel → Git → Manage Repositories</li>
                <li>Click the <strong>Deploy</strong> button</li>
                <li>After deployment, Composer dependencies will be installed automatically (if post-deploy script is configured)</li>
            </ol>
            
            <h3>🔒 Security Note:</h3>
            <p>After successful deployment, you should delete or disable this file for security. Set <code>$allowed = false;</code> in the file.</p>
        </div>
    </div>
</body>
</html>

