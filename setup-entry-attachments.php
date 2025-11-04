<?php
/**
 * Setup Entry Attachments - Create uploads directory for payment proofs
 * Run this file once: http://localhost/cashbook/setup-entry-attachments.php
 */

require_once 'config.php';
$conn = getDBConnection();

$uploadsDir = __DIR__ . '/uploads';
$attachmentsDir = $uploadsDir . '/entry_attachments';

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Setup Entry Attachments</title>
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
            max-width: 700px;
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
            margin: 10px 10px 0 0;
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
        <h1>📎 Payment Proof Setup</h1>";

try {
    $messages = [];
    
    // Step 1: Check/Add attachment column in database
    $result = $conn->query("SHOW COLUMNS FROM entries LIKE 'attachment'");
    
    if ($result->num_rows === 0) {
        // Add attachment column
        if ($conn->query("ALTER TABLE entries ADD COLUMN attachment VARCHAR(255) DEFAULT NULL AFTER message")) {
            $messages[] = "<div class='success'>✓ Added 'attachment' column to entries table</div>";
        } else {
            throw new Exception("Failed to add attachment column: " . $conn->error);
        }
    } else {
        $messages[] = "<div class='info'>ℹ️ 'attachment' column already exists in entries table</div>";
    }
    
    // Step 2: Create uploads directory
    if (!file_exists($uploadsDir)) {
        if (mkdir($uploadsDir, 0755, true)) {
            $messages[] = "<div class='success'>✓ Created 'uploads' directory</div>";
        } else {
            throw new Exception("Failed to create uploads directory");
        }
    } else {
        $messages[] = "<div class='info'>ℹ️ 'uploads' directory already exists</div>";
    }
    
    // Step 3: Create entry_attachments subdirectory
    if (!file_exists($attachmentsDir)) {
        if (mkdir($attachmentsDir, 0755, true)) {
            $messages[] = "<div class='success'>✓ Created 'uploads/entry_attachments' directory</div>";
        } else {
            throw new Exception("Failed to create entry_attachments directory");
        }
    } else {
        $messages[] = "<div class='info'>ℹ️ 'uploads/entry_attachments' directory already exists</div>";
    }
    
    // Step 4: Create security .htaccess
    $htaccessPath = $uploadsDir . '/.htaccess';
    if (!file_exists($htaccessPath)) {
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
    } else {
        $messages[] = "<div class='info'>ℹ️ .htaccess file already exists</div>";
    }
    
    // Step 5: Create index.php protections
    $indexPath = $uploadsDir . '/index.php';
    if (!file_exists($indexPath)) {
        if (file_put_contents($indexPath, '<?php header("Location: ../index.php"); exit; ?>')) {
            $messages[] = "<div class='success'>✓ Created index.php protection</div>";
        }
    }
    
    $indexPath2 = $attachmentsDir . '/index.php';
    if (!file_exists($indexPath2)) {
        if (file_put_contents($indexPath2, '<?php header("Location: ../../index.php"); exit; ?>')) {
            $messages[] = "<div class='success'>✓ Created subdirectory protection</div>";
        }
    }
    
    // Display all messages
    foreach ($messages as $message) {
        echo $message;
    }
    
    echo "<div class='success'>
        <strong>✅ Setup Complete!</strong><br>
        Payment proof upload functionality is ready to use.
    </div>";
    
    echo "<div class='info'>
        <strong>📂 Directory Structure:</strong>
        <pre>";
    echo "cashbook/\n";
    echo "└── uploads/\n";
    echo "    ├── .htaccess (security)\n";
    echo "    ├── index.php (protection)\n";
    echo "    ├── profile_pictures/ (user avatars)\n";
    echo "    └── entry_attachments/ (payment proofs)\n";
    echo "        ├── index.php (protection)\n";
    echo "        └── [uploaded payment proofs go here]\n";
    echo "</pre>
    </div>";
    
    echo "<div class='info'>
        <strong>🎯 Features Enabled:</strong><br>
        ✅ Upload payment proof when adding entry<br>
        ✅ View proof photo by clicking icon<br>
        ✅ Secure file storage<br>
        ✅ File validation (type & size)<br>
        ✅ Beautiful photo viewer modal
    </div>";
    
    echo "<div style='margin-top: 30px;'>
        <a href='dashboard.php' class='btn'>Go to Dashboard</a>
        <a href='login.php' class='btn' style='background: #6b7280;'>Go to Login</a>
    </div>";
    
} catch (Exception $e) {
    echo "<div class='error'>
        <strong>✗ Error:</strong><br>
        " . $e->getMessage() . "
    </div>
    
    <div class='info'>
        <strong>Manual Setup:</strong><br>
        1. Create directories manually:<br>
        <pre>cashbook/uploads/
cashbook/uploads/entry_attachments/</pre>
        
        2. Run this SQL in phpMyAdmin:<br>
        <pre>ALTER TABLE entries 
ADD COLUMN attachment VARCHAR(255) DEFAULT NULL AFTER message;</pre>
        
        Make sure directories have write permissions (755 or 777).
    </div>";
}

$conn->close();

echo "    </div>
</body>
</html>";
?>

