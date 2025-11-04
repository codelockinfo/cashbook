<?php
require_once 'config.php';

echo "<h2>Password Fix Utility</h2>";

try {
    $conn = getDBConnection();
    
    // Generate correct password hash for 'demo123'
    $correctHash = password_hash('demo123', PASSWORD_DEFAULT);
    
    echo "Updating all user passwords to 'demo123'...<br><br>";
    
    // Update all users
    $stmt = $conn->prepare("UPDATE users SET password = ?");
    $stmt->bind_param("s", $correctHash);
    
    if ($stmt->execute()) {
        echo "✅ <strong>SUCCESS!</strong> All user passwords have been updated!<br><br>";
        echo "You can now login with these credentials:<br>";
        echo "<ul>";
        echo "<li><strong>admin@cashbook.com</strong> / demo123</li>";
        echo "<li><strong>tushar@example.com</strong> / demo123</li>";
        echo "<li><strong>rajan@example.com</strong> / demo123</li>";
        echo "<li><strong>amit@example.com</strong> / demo123</li>";
        echo "<li><strong>priya@example.com</strong> / demo123</li>";
        echo "</ul>";
        echo "<br><a href='login.php' style='display: inline-block; padding: 12px 24px; background: linear-gradient(135deg, #667eea, #764ba2); color: white; text-decoration: none; border-radius: 8px; font-weight: 600;'>→ Go to Login Page</a>";
    } else {
        echo "❌ Error updating passwords: " . $conn->error;
    }
    
    $stmt->close();
    $conn->close();
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>

