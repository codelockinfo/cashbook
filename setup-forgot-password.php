<?php
/**
 * Quick Setup Script for Password Reset Functionality
 * Run this file once to create the password_reset_tokens table
 * Access: http://localhost/cashbook/setup-forgot-password.php
 */

require_once 'config.php';

$conn = getDBConnection();

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Setup - Forgot Password</title>
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
        <h1>🔐 Forgot Password Setup</h1>";

try {
    // Check if table already exists
    $result = $conn->query("SHOW TABLES LIKE 'password_reset_tokens'");
    
    if ($result->num_rows > 0) {
        echo "<div class='info'>
            <strong>✓ Table already exists!</strong><br>
            The password_reset_tokens table is already created.
        </div>";
    } else {
        // Create the table
        $sql = "CREATE TABLE password_reset_tokens (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            token VARCHAR(255) NOT NULL,
            expires_at DATETIME NOT NULL,
            used TINYINT(1) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_token (token(191)),
            INDEX idx_user_id (user_id),
            INDEX idx_expires_at (expires_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        if ($conn->query($sql) === TRUE) {
            echo "<div class='success'>
                <strong>✓ Success!</strong><br>
                Password reset table created successfully!
            </div>";
        } else {
            throw new Exception($conn->error);
        }
    }
    
    // Verify table structure
    $result = $conn->query("DESCRIBE password_reset_tokens");
    
    echo "<div class='info'>
        <strong>Table Structure:</strong>
        <pre>";
    
    while ($row = $result->fetch_assoc()) {
        echo $row['Field'] . " - " . $row['Type'] . "\n";
    }
    
    echo "</pre>
    </div>";
    
    echo "<div style='margin-top: 30px;'>
        <h3>✅ Setup Complete!</h3>
        <p>You can now use the forgot password functionality.</p>
        <a href='forgot-password.php' class='btn'>Go to Forgot Password</a>
        <a href='login.php' class='btn' style='background: #6b7280;'>Go to Login</a>
    </div>";
    
} catch (Exception $e) {
    echo "<div class='error'>
        <strong>✗ Error:</strong><br>
        " . $e->getMessage() . "
    </div>
    
    <div class='info'>
        <strong>Manual Setup:</strong><br>
        Run this SQL in phpMyAdmin:
        <pre>CREATE TABLE password_reset_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL,
    used TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_token (token(191)),
    INDEX idx_user_id (user_id),
    INDEX idx_expires_at (expires_at)
) ENGINE=InnoDB;</pre>
    </div>";
}

$conn->close();

echo "    </div>
</body>
</html>";
?>

