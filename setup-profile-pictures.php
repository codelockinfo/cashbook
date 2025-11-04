<?php
/**
 * Setup Profile Pictures - Create uploads directory
 * Run this file once: http://localhost/cashbook/setup-profile-pictures.php
 */

$uploadsDir = __DIR__ . '/uploads';
$profilePicsDir = $uploadsDir . '/profile_pictures';

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Setup Profile Pictures</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            margin: 0;
        }
        .container {
            background: white;
            border-radius: 20px;
            padding: 40px;
            max-width: 600px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }
        h1 {
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 20px;
        }
        .success {
            background: #10b981;
            color: white;
            padding: 15px 20px;
            border-radius: 12px;
            margin: 20px 0;
        }
        .error {
            background: #ef4444;
            color: white;
            padding: 15px 20px;
            border-radius: 12px;
            margin: 20px 0;
        }
        .info {
            background: #f0f9ff;
            border: 2px solid #3b82f6;
            color: #1e40af;
            padding: 15px 20px;
            border-radius: 12px;
            margin: 20px 0;
        }
        .btn {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 12px;
            text-decoration: none;
            display: inline-block;
            margin-top: 20px;
            cursor: pointer;
            font-weight: 600;
        }
        .btn:hover {
            transform: scale(1.02);
        }
        pre {
            background: #f9fafb;
            padding: 15px;
            border-radius: 8px;
            overflow-x: auto;
            border: 1px solid #e5e7eb;
        }
    </style>
</head>
<body>
    <div class='container'>
        <h1>📸 Profile Pictures Setup</h1>";

try {
    $messages = [];
    
    // Create uploads directory
    if (!file_exists($uploadsDir)) {
        if (mkdir($uploadsDir, 0755, true)) {
            $messages[] = "<div class='success'>✓ Created 'uploads' directory</div>";
        } else {
            throw new Exception("Failed to create uploads directory");
        }
    } else {
        $messages[] = "<div class='info'>ℹ️ 'uploads' directory already exists</div>";
    }
    
    // Create profile_pictures subdirectory
    if (!file_exists($profilePicsDir)) {
        if (mkdir($profilePicsDir, 0755, true)) {
            $messages[] = "<div class='success'>✓ Created 'uploads/profile_pictures' directory</div>";
        } else {
            throw new Exception("Failed to create profile_pictures directory");
        }
    } else {
        $messages[] = "<div class='info'>ℹ️ 'uploads/profile_pictures' directory already exists</div>";
    }
    
    // Create .htaccess for security
    $htaccessPath = $uploadsDir . '/.htaccess';
    $htaccessContent = "# Prevent PHP execution in uploads directory
php_flag engine off
AddType text/plain .php .php3 .php4 .php5 .phtml .phps

# Allow only images
<FilesMatch \"\\.(jpg|jpeg|png|gif|webp)$\">
    Order Allow,Deny
    Allow from all
</FilesMatch>";
    
    if (file_put_contents($htaccessPath, $htaccessContent)) {
        $messages[] = "<div class='success'>✓ Created security .htaccess file</div>";
    }
    
    // Create index.php to prevent directory listing
    $indexPath = $uploadsDir . '/index.php';
    if (file_put_contents($indexPath, '<?php header("Location: ../index.php"); exit; ?>')) {
        $messages[] = "<div class='success'>✓ Created index.php protection</div>";
    }
    
    $indexPath2 = $profilePicsDir . '/index.php';
    if (file_put_contents($indexPath2, '<?php header("Location: ../../index.php"); exit; ?>')) {
        $messages[] = "<div class='success'>✓ Created subdirectory protection</div>";
    }
    
    // Create default avatar
    $defaultAvatar = $uploadsDir . '/default-avatar.png';
    if (!file_exists($defaultAvatar)) {
        // Create a simple default avatar (you can replace this with a real image later)
        $messages[] = "<div class='info'>ℹ️ You can add a default-avatar.png to the uploads folder</div>";
    }
    
    // Display all messages
    foreach ($messages as $message) {
        echo $message;
    }
    
    echo "<div class='success'>
        <strong>✅ Setup Complete!</strong><br>
        Profile picture functionality is ready to use.
    </div>";
    
    echo "<div class='info'>
        <strong>Directory Structure:</strong>
        <pre>";
    echo "cashbook/\n";
    echo "└── uploads/\n";
    echo "    ├── .htaccess (security)\n";
    echo "    ├── index.php (protection)\n";
    echo "    ├── default-avatar.png (optional)\n";
    echo "    └── profile_pictures/\n";
    echo "        ├── index.php (protection)\n";
    echo "        └── [user uploaded photos will go here]\n";
    echo "</pre>
    </div>";
    
    echo "<div style='margin-top: 30px;'>
        <a href='register.php' class='btn'>Go to Register</a>
        <a href='login.php' class='btn' style='background: #6b7280;'>Go to Login</a>
    </div>";
    
} catch (Exception $e) {
    echo "<div class='error'>
        <strong>✗ Error:</strong><br>
        " . $e->getMessage() . "
    </div>
    
    <div class='info'>
        <strong>Manual Setup:</strong><br>
        Create these directories manually:
        <pre>cashbook/uploads/
cashbook/uploads/profile_pictures/</pre>
        
        Make sure they have write permissions (755 or 777).
    </div>";
}

echo "    </div>
</body>
</html>";
?>

