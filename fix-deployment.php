<?php
/**
 * Quick Fix for Hostinger Deployment Conflicts
 * 
 * Run this ONCE to remove conflicting files:
 * https://yourdomain.com/fix-deployment.php
 * 
 * Then delete this file and deploy from Hostinger panel
 */

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Fix Deployment</title>
    <style>
        body { font-family: Arial; max-width: 600px; margin: 50px auto; padding: 20px; }
        .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .btn { background: #667eea; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block; margin-top: 10px; }
    </style>
</head>
<body>
    <h1>🔧 Fix Deployment Conflicts</h1>
    
    <?php
    if (isset($_GET['fix']) && $_GET['fix'] === 'yes') {
        $removed = [];
        $errors = [];
        
        // Remove vendor directory
        if (is_dir('vendor')) {
            function deleteDir($dir) {
                if (!file_exists($dir)) return true;
                if (!is_dir($dir)) return unlink($dir);
                foreach (scandir($dir) as $item) {
                    if ($item == '.' || $item == '..') continue;
                    if (!deleteDir($dir . DIRECTORY_SEPARATOR . $item)) return false;
                }
                return rmdir($dir);
            }
            if (deleteDir('vendor')) {
                $removed[] = 'vendor/';
            } else {
                $errors[] = 'vendor/';
            }
        }
        
        // Remove files
        $files = ['composer.lock', 'cleanup-for-deploy.php'];
        foreach ($files as $file) {
            if (file_exists($file)) {
                if (unlink($file)) {
                    $removed[] = $file;
                } else {
                    $errors[] = $file;
                }
            }
        }
        
        if (!empty($removed)) {
            echo '<div class="success"><strong>✓ Removed:</strong><br>' . implode('<br>', $removed) . '</div>';
        }
        
        if (!empty($errors)) {
            echo '<div class="error"><strong>✗ Failed to remove:</strong><br>' . implode('<br>', $errors) . '</div>';
        }
        
        if (empty($errors)) {
            echo '<div class="success">';
            echo '<h2>✅ Ready to Deploy!</h2>';
            echo '<p>1. Go to Hostinger Panel → Git → Manage Repositories</p>';
            echo '<p>2. Click the <strong>Deploy</strong> button</p>';
            echo '<p>3. Delete this file (fix-deployment.php) after successful deployment</p>';
            echo '</div>';
        }
    } else {
        echo '<p>This will remove conflicting files so Git deployment can succeed.</p>';
        echo '<a href="?fix=yes" class="btn">Remove Conflicting Files</a>';
    }
    ?>
</body>
</html>

