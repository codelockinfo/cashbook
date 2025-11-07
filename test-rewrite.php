<?php
// Test file to check if URL rewriting is working
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>URL Rewrite Test</title>
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
        .success {
            color: #10b981;
            background: #d1fae5;
            padding: 15px;
            border-radius: 8px;
            margin: 10px 0;
        }
        .info {
            color: #3b82f6;
            background: #dbeafe;
            padding: 15px;
            border-radius: 8px;
            margin: 10px 0;
        }
        .warning {
            color: #f59e0b;
            background: #fef3c7;
            padding: 15px;
            border-radius: 8px;
            margin: 10px 0;
        }
        h1 { color: #667eea; }
        h2 { color: #333; margin-top: 30px; }
        code {
            background: #f3f4f6;
            padding: 2px 6px;
            border-radius: 4px;
            font-family: monospace;
        }
        .test-link {
            display: inline-block;
            margin: 10px 10px 10px 0;
            padding: 10px 20px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 8px;
        }
        .test-link:hover {
            background: #5568d3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>✅ URL Rewrite Test - SUCCESS!</h1>
        
        <div class="success">
            <strong>Great!</strong> This page is loading, which means PHP is working on your server.
        </div>

        <h2>📋 Current Request Info:</h2>
        <div class="info">
            <strong>Full URL:</strong> <?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?><br>
            <strong>Script Name:</strong> <?php echo htmlspecialchars($_SERVER['SCRIPT_NAME']); ?><br>
            <strong>Server Software:</strong> <?php echo htmlspecialchars($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'); ?><br>
            <strong>Document Root:</strong> <?php echo htmlspecialchars($_SERVER['DOCUMENT_ROOT']); ?>
        </div>

        <h2>🧪 Test Clean URLs:</h2>
        <p>If URL rewriting is working, these links should work WITHOUT showing .php:</p>
        
        <a href="login" class="test-link">Test: /login</a>
        <a href="register" class="test-link">Test: /register</a>
        <a href="dashboard" class="test-link">Test: /dashboard</a>

        <h2>📝 What You're Testing:</h2>
        <div class="info">
            <strong>Current page accessed as:</strong><br>
            <?php 
            $currentUrl = $_SERVER['REQUEST_URI'];
            if (strpos($currentUrl, '.php') !== false) {
                echo '<span style="color: #f59e0b;">⚠️ You accessed: <code>' . htmlspecialchars($currentUrl) . '</code></span><br>';
                echo '<span style="color: #10b981;">✅ URL rewriting will redirect to clean URL</span>';
            } else {
                echo '<span style="color: #10b981;">✅ Clean URL: <code>' . htmlspecialchars($currentUrl) . '</code></span><br>';
                echo '<span style="color: #10b981;">✅ URL rewriting is WORKING!</span>';
            }
            ?>
        </div>

        <h2>🔍 Troubleshooting:</h2>
        <div class="warning">
            <strong>If links above show 404 errors:</strong><br><br>
            
            <strong>Option 1: Check .htaccess uploaded</strong><br>
            - Verify <code>.htaccess</code> file exists in your site root<br>
            - Check file permissions (should be 644)<br><br>
            
            <strong>Option 2: Contact your host</strong><br>
            - Ask if <code>mod_rewrite</code> is enabled<br>
            - Ask if <code>AllowOverride</code> is set to <code>All</code> or <code>FileInfo</code><br><br>
            
            <strong>Option 3: Use simple version</strong><br>
            - Rename <code>.htaccess-simple</code> to <code>.htaccess</code><br>
            - This uses minimal rewrite rules<br><br>
            
            <strong>Option 4: Keep using .php extensions</strong><br>
            - Your site works fine with <code>.php</code> extensions<br>
            - Clean URLs are optional for functionality
        </div>

        <h2>✨ Next Steps:</h2>
        <ol>
            <li><strong>Click the test links above</strong> - If they work, URL rewriting is active!</li>
            <li><strong>If 404 errors occur</strong> - Try the troubleshooting steps</li>
            <li><strong>Working?</strong> - Delete this test file: <code>test-rewrite.php</code></li>
        </ol>

        <div class="info">
            <strong>Note:</strong> You can delete this test file once URL rewriting is confirmed working.<br>
            File location: <code><?php echo __FILE__; ?></code>
        </div>
    </div>
</body>
</html>

