<?php
require_once 'config.php';

echo "<h2>Database Connection Test</h2>";

try {
    $conn = getDBConnection();
    echo "✅ Database connection successful!<br><br>";
    
    // Check if users table exists
    $result = $conn->query("SHOW TABLES LIKE 'users'");
    if ($result->num_rows > 0) {
        echo "✅ Users table exists<br><br>";
        
        // Check users in table
        $result = $conn->query("SELECT id, name, email FROM users");
        echo "<h3>Users in database:</h3>";
        if ($result->num_rows > 0) {
            echo "<ul>";
            while ($row = $result->fetch_assoc()) {
                echo "<li>ID: {$row['id']}, Name: {$row['name']}, Email: {$row['email']}</li>";
            }
            echo "</ul>";
        } else {
            echo "❌ No users found in database!<br>";
            echo "<strong>SOLUTION:</strong> Run <a href='setup.php'>setup.php</a> to create users.<br>";
        }
        
        // Test password verification
        echo "<br><h3>Password Test:</h3>";
        $stmt = $conn->prepare("SELECT password FROM users WHERE email = 'admin@cashbook.com'");
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $testPassword = 'demo123';
            
            if (password_verify($testPassword, $user['password'])) {
                echo "✅ Password 'demo123' matches the hash in database!<br>";
            } else {
                echo "❌ Password 'demo123' DOES NOT match the hash in database!<br>";
                echo "<strong>SOLUTION:</strong> Run <a href='setup.php'>setup.php</a> to fix password hashes.<br>";
            }
        } else {
            echo "❌ User admin@cashbook.com not found!<br>";
        }
        
    } else {
        echo "❌ Users table does NOT exist!<br>";
        echo "<strong>SOLUTION:</strong> Run <a href='setup.php'>setup.php</a> to create database tables.<br>";
    }
    
    $conn->close();
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>

