<?php
/**
 * Setup script to add api_token column to users table
 * Run this file once via browser or CLI to add the api_token column
 */

require_once 'config.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>API Token Setup</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
        .success { color: green; padding: 10px; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 5px; margin: 10px 0; }
        .error { color: red; padding: 10px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 5px; margin: 10px 0; }
        .info { color: #0c5460; padding: 10px; background: #d1ecf1; border: 1px solid #bee5eb; border-radius: 5px; margin: 10px 0; }
        code { background: #f4f4f4; padding: 2px 6px; border-radius: 3px; }
    </style>
</head>
<body>
    <h1>API Token Setup</h1>
    <p>This script will add the <code>api_token</code> column to your users table.</p>
    
<?php
try {
    $conn = getDBConnection();
    
    // Check if column already exists
    $result = $conn->query("SHOW COLUMNS FROM users LIKE 'api_token'");
    
    if ($result->num_rows > 0) {
        echo '<div class="info">';
        echo '<strong>✓ Column already exists!</strong><br>';
        echo 'The <code>api_token</code> column is already present in the users table.';
        echo '</div>';
        
        // Show current column info
        $columnInfo = $conn->query("SHOW COLUMNS FROM users WHERE Field = 'api_token'")->fetch_assoc();
        echo '<div class="info">';
        echo '<strong>Column Details:</strong><br>';
        echo 'Type: ' . htmlspecialchars($columnInfo['Type']) . '<br>';
        echo 'Null: ' . htmlspecialchars($columnInfo['Null']) . '<br>';
        echo 'Default: ' . htmlspecialchars($columnInfo['Default'] ?? 'NULL');
        echo '</div>';
    } else {
        // Add the column
        $sql = "ALTER TABLE `users` 
                ADD COLUMN `api_token` VARCHAR(64) NULL DEFAULT NULL AFTER `password`,
                ADD INDEX `idx_api_token` (`api_token`(64))";
        
        if ($conn->query($sql)) {
            echo '<div class="success">';
            echo '<strong>✓ Success!</strong><br>';
            echo 'The <code>api_token</code> column has been added to the users table.';
            echo '</div>';
        } else {
            throw new Exception("Failed to add column: " . $conn->error);
        }
    }
    
    // Verify the column exists
    $verifyResult = $conn->query("SHOW COLUMNS FROM users LIKE 'api_token'");
    if ($verifyResult->num_rows > 0) {
        echo '<div class="success">';
        echo '<strong>✓ Verification:</strong> Column exists and is ready to use!';
        echo '</div>';
        
        // Show table structure
        echo '<div class="info">';
        echo '<strong>Users Table Structure:</strong><br>';
        $columns = $conn->query("SHOW COLUMNS FROM users");
        echo '<table border="1" cellpadding="5" style="border-collapse: collapse; width: 100%; margin-top: 10px;">';
        echo '<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>';
        while ($col = $columns->fetch_assoc()) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($col['Field']) . '</td>';
            echo '<td>' . htmlspecialchars($col['Type']) . '</td>';
            echo '<td>' . htmlspecialchars($col['Null']) . '</td>';
            echo '<td>' . htmlspecialchars($col['Key']) . '</td>';
            echo '<td>' . htmlspecialchars($col['Default'] ?? 'NULL') . '</td>';
            echo '</tr>';
        }
        echo '</table>';
        echo '</div>';
    }
    
    $conn->close();
    
    echo '<div class="info">';
    echo '<strong>Next Steps:</strong><br>';
    echo '1. Test login via API - token should be generated and returned<br>';
    echo '2. Test token verification at: <code>app-login.php?token=YOUR_TOKEN</code><br>';
    echo '3. Integrate Flutter app using the guide in <code>FLUTTER_INTEGRATION_GUIDE.md</code>';
    echo '</div>';
    
} catch (Exception $e) {
    echo '<div class="error">';
    echo '<strong>✗ Error:</strong><br>';
    echo htmlspecialchars($e->getMessage());
    echo '</div>';
}
?>

</body>
</html>

