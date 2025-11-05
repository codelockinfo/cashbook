<?php
// Enable error reporting to see all errors
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

echo "<!DOCTYPE html><html><head><title>Diagnostic Tool</title>";
echo "<style>
body{font-family:Arial,sans-serif;padding:20px;background:#f5f5f5;}
.container{max-width:900px;margin:0 auto;background:white;padding:30px;border-radius:8px;box-shadow:0 2px 10px rgba(0,0,0,0.1);}
h1{color:#333;border-bottom:2px solid #667eea;padding-bottom:10px;}
h2{color:#667eea;margin-top:30px;}
.success{color:#22c55e;font-weight:bold;}
.error{color:#ef4444;font-weight:bold;}
.warning{color:#f59e0b;font-weight:bold;}
pre{background:#f9fafb;padding:15px;border-left:4px solid #667eea;overflow-x:auto;}
.box{background:#f9fafb;padding:15px;margin:10px 0;border-radius:5px;}
</style></head><body><div class='container'>";

echo "<h1>🔍 Cash Book Diagnostic Tool</h1>";
echo "<p><strong>Time:</strong> " . date('Y-m-d H:i:s') . "</p>";

// 1. Check PHP version
echo "<h2>1. ✓ PHP Version Check</h2>";
$phpVersion = phpversion();
echo "<div class='box'>PHP Version: <strong>$phpVersion</strong> ";
if (version_compare($phpVersion, '7.4.0', '>=')) {
    echo "<span class='success'>✓ OK</span>";
} else {
    echo "<span class='error'>✗ Upgrade needed (7.4+ required)</span>";
}
echo "</div>";

// 2. Check required extensions
echo "<h2>2. 🔌 Required PHP Extensions</h2>";
$extensions = ['mysqli', 'session', 'json', 'mbstring', 'fileinfo'];
echo "<div class='box'>";
foreach ($extensions as $ext) {
    echo "$ext: ";
    if (extension_loaded($ext)) {
        echo "<span class='success'>✓ Loaded</span><br>";
    } else {
        echo "<span class='error'>✗ Not loaded</span><br>";
    }
}
echo "</div>";

// 3. Check database connection
echo "<h2>3. 🗄️ Database Connection Test</h2>";
$dbHost = 'localhost';
$dbUser = 'root';
$dbPass = '';
$dbName = 'cash_book';

echo "<div class='box'>";
echo "<strong>Config:</strong><br>";
echo "Host: $dbHost<br>User: $dbUser<br>Password: " . (empty($dbPass) ? "(empty)" : "(set)") . "<br>Database: $dbName<br><br>";

$conn = @new mysqli($dbHost, $dbUser, $dbPass);
if ($conn->connect_error) {
    echo "<span class='error'>✗ Cannot connect to MySQL Server</span><br>";
    echo "<strong>Error:</strong> " . $conn->connect_error . "<br>";
    echo "<p class='error'><strong>ACTION REQUIRED:</strong> Start MySQL service in WAMP</p>";
} else {
    echo "<span class='success'>✓ Connected to MySQL Server</span><br><br>";
    
    // Check if database exists
    $result = $conn->query("SHOW DATABASES LIKE '$dbName'");
    if ($result && $result->num_rows > 0) {
        echo "<span class='success'>✓ Database '$dbName' exists</span><br><br>";
        
        // Connect to database and check tables
        $conn->select_db($dbName);
        echo "<strong>Required Tables:</strong><br>";
        $tables = ['users', 'entries', 'groups', 'group_members', 'password_resets'];
        $allTablesExist = true;
        foreach ($tables as $table) {
            $result = $conn->query("SHOW TABLES LIKE '$table'");
            echo "&nbsp;&nbsp;$table: ";
            if ($result && $result->num_rows > 0) {
                echo "<span class='success'>✓</span><br>";
            } else {
                echo "<span class='error'>✗ Missing</span><br>";
                $allTablesExist = false;
            }
        }
        
        if (!$allTablesExist) {
            echo "<br><p class='warning'><strong>Some tables are missing!</strong> Import database.sql</p>";
        }
    } else {
        echo "<span class='error'>✗ Database '$dbName' does NOT exist</span><br>";
        echo "<br><p class='error'><strong>⚠️ ACTION REQUIRED:</strong></p>";
        echo "<ol>";
        echo "<li>Open <a href='http://localhost/phpmyadmin' target='_blank'>phpMyAdmin</a></li>";
        echo "<li>Click 'New' in the left sidebar</li>";
        echo "<li>Database name: <strong>cash_book</strong></li>";
        echo "<li>Collation: <strong>utf8mb4_unicode_ci</strong></li>";
        echo "<li>Click 'Create'</li>";
        echo "<li>Click 'Import' tab</li>";
        echo "<li>Choose file: <strong>database.sql</strong></li>";
        echo "<li>Click 'Go'</li>";
        echo "</ol>";
    }
    $conn->close();
}
echo "</div>";

// 4. Check file permissions
echo "<h2>4. 📁 File Permissions</h2>";
echo "<div class='box'>";
$dirs = [
    'uploads' => 'uploads',
    'uploads/profile_pictures' => 'uploads/profile_pictures',
    'uploads/entry_attachments' => 'uploads/entry_attachments'
];
foreach ($dirs as $label => $dir) {
    echo "$label: ";
    if (!is_dir($dir)) {
        echo "<span class='error'>✗ Does not exist</span>";
        if (@mkdir($dir, 0777, true)) {
            echo " <span class='success'>→ Created!</span>";
        } else {
            echo " <span class='error'>→ Cannot create</span>";
        }
        echo "<br>";
    } else {
        if (is_writable($dir)) {
            echo "<span class='success'>✓ Writable</span><br>";
        } else {
            echo "<span class='error'>✗ Not writable (check permissions)</span><br>";
        }
    }
}
echo "</div>";

// 5. Check session
echo "<h2>5. 🔐 Session Support</h2>";
echo "<div class='box'>";
if (session_status() === PHP_SESSION_DISABLED) {
    echo "<span class='error'>✗ Sessions are disabled</span><br>";
} else {
    @session_start();
    $_SESSION['test'] = 'working';
    if (isset($_SESSION['test']) && $_SESSION['test'] === 'working') {
        echo "<span class='success'>✓ Sessions working properly</span><br>";
        unset($_SESSION['test']);
    } else {
        echo "<span class='error'>✗ Session test failed</span><br>";
    }
}
echo "</div>";

// 6. Check critical files
echo "<h2>6. 📄 Critical Files</h2>";
echo "<div class='box'>";
$files = [
    'index.php' => 'Main entry point',
    'login.php' => 'Login page',
    'config.php' => 'Database config',
    'check-session.php' => 'Session handler',
    'dashboard.php' => 'Dashboard',
    'pwa-meta.php' => 'PWA metadata'
];
foreach ($files as $file => $desc) {
    echo "$file ($desc): ";
    if (file_exists($file) && is_readable($file)) {
        echo "<span class='success'>✓</span><br>";
    } else {
        echo "<span class='error'>✗ Missing or not readable</span><br>";
    }
}
echo "</div>";

// 7. Test config.php for errors
echo "<h2>7. 🧪 Test Config File</h2>";
echo "<div class='box'>";
try {
    ob_start();
    include_once('config.php');
    $configError = ob_get_clean();
    if (!empty($configError)) {
        echo "<span class='error'>✗ Config file has errors:</span><br>";
        echo "<pre>" . htmlspecialchars($configError) . "</pre>";
    } else {
        echo "<span class='success'>✓ Config file loads without errors</span><br>";
    }
} catch (Exception $e) {
    echo "<span class='error'>✗ Exception in config.php:</span> " . $e->getMessage() . "<br>";
}
echo "</div>";

// 8. Check .htaccess
echo "<h2>8. ⚙️ Apache Configuration</h2>";
echo "<div class='box'>";
if (file_exists('.htaccess')) {
    echo ".htaccess: <span class='success'>✓ Exists</span><br>";
} elseif (file_exists('.htaccess.backup')) {
    echo ".htaccess: <span class='warning'>⚠️ Disabled (renamed to .htaccess.backup)</span><br>";
    echo "<p>This is OK for testing. The error might be from .htaccess rules.</p>";
} else {
    echo ".htaccess: <span class='warning'>⚠️ Not found</span><br>";
}
echo "</div>";

// Final recommendations
echo "<h2>📋 Summary & Next Steps</h2>";
echo "<div class='box'>";
echo "<ol>";
echo "<li>If database doesn't exist → <strong>Create it in phpMyAdmin and import database.sql</strong></li>";
echo "<li>If all checks pass → Try accessing <a href='login.php'>login.php</a></li>";
echo "<li>If still getting 500 error → Check Apache error log at:<br><code>C:\\wamp64\\logs\\apache_error.log</code></li>";
echo "<li>Make sure WAMP icons is GREEN (all services running)</li>";
echo "</ol>";
echo "</div>";

echo "</div></body></html>";
?>

