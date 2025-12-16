<?php
/**
 * Test script to verify token login functionality
 * Usage: test-token-login.php?token=YOUR_TOKEN
 */

require_once 'config.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Token Login Test</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
        .success { color: green; padding: 10px; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 5px; margin: 10px 0; }
        .error { color: red; padding: 10px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 5px; margin: 10px 0; }
        .info { color: #0c5460; padding: 10px; background: #d1ecf1; border: 1px solid #bee5eb; border-radius: 5px; margin: 10px 0; }
        code { background: #f4f4f4; padding: 2px 6px; border-radius: 3px; }
        pre { background: #f4f4f4; padding: 10px; border-radius: 5px; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>Token Login Test</h1>
    
<?php
$token = $_GET['token'] ?? null;

if (!$token) {
    echo '<div class="error">';
    echo '<strong>No token provided!</strong><br>';
    echo 'Usage: <code>test-token-login.php?token=YOUR_TOKEN</code><br><br>';
    echo 'Or test with app-login.php: <code>app-login.php?token=YOUR_TOKEN</code>';
    echo '</div>';
    
    // Show how to get a token
    echo '<div class="info">';
    echo '<strong>How to get a token:</strong><br>';
    echo '1. Login via API: <code>auth-api.php?action=login</code> (POST with email/password)<br>';
    echo '2. Response will include a <code>token</code> field<br>';
    echo '3. Use that token to test this endpoint';
    echo '</div>';
    exit;
}

echo '<div class="info">';
echo '<strong>Testing Token:</strong> <code>' . htmlspecialchars(substr($token, 0, 16)) . '...</code>';
echo '</div>';

try {
    $conn = getDBConnection();
    
    // Check if api_token column exists
    $columnCheck = $conn->query("SHOW COLUMNS FROM users LIKE 'api_token'");
    if ($columnCheck->num_rows === 0) {
        echo '<div class="error">';
        echo '<strong>⚠️ Database Column Missing!</strong><br>';
        echo 'The <code>api_token</code> column does not exist in the users table.<br>';
        echo 'Please run: <code>setup-api-token.php</code> or <code>migrate-api-token.sql</code>';
        echo '</div>';
        $conn->close();
        exit;
    }
    
    // Verify token and get user
    $stmt = $conn->prepare("SELECT id, name, email, profile_picture, api_token FROM users WHERE api_token = ?");
    if (!$stmt) {
        echo '<div class="error">';
        echo '<strong>Database Error:</strong> ' . htmlspecialchars($conn->error);
        echo '</div>';
        $conn->close();
        exit;
    }
    
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo '<div class="error">';
        echo '<strong>✗ Invalid Token</strong><br>';
        echo 'The token does not exist in the database or does not match any user.';
        echo '</div>';
        
        // Show all tokens (for debugging)
        echo '<div class="info">';
        echo '<strong>Debug Info:</strong><br>';
        $allTokens = $conn->query("SELECT id, name, email, LEFT(api_token, 16) as token_preview FROM users WHERE api_token IS NOT NULL");
        if ($allTokens->num_rows > 0) {
            echo 'Existing tokens in database:<br>';
            echo '<table border="1" cellpadding="5" style="border-collapse: collapse; margin-top: 10px;">';
            echo '<tr><th>ID</th><th>Name</th><th>Email</th><th>Token Preview</th></tr>';
            while ($row = $allTokens->fetch_assoc()) {
                echo '<tr>';
                echo '<td>' . htmlspecialchars($row['id']) . '</td>';
                echo '<td>' . htmlspecialchars($row['name']) . '</td>';
                echo '<td>' . htmlspecialchars($row['email']) . '</td>';
                echo '<td><code>' . htmlspecialchars($row['token_preview']) . '...</code></td>';
                echo '</tr>';
            }
            echo '</table>';
        } else {
            echo 'No tokens found in database. Users need to login via API first.';
        }
        echo '</div>';
        
        $stmt->close();
        $conn->close();
        exit;
    }
    
    $user = $result->fetch_assoc();
    $stmt->close();
    
    echo '<div class="success">';
    echo '<strong>✓ Token Valid!</strong><br>';
    echo 'User found: <strong>' . htmlspecialchars($user['name']) . '</strong> (' . htmlspecialchars($user['email']) . ')';
    echo '</div>';
    
    echo '<div class="info">';
    echo '<strong>User Details:</strong><br>';
    echo '<pre>';
    echo 'ID: ' . htmlspecialchars($user['id']) . "\n";
    echo 'Name: ' . htmlspecialchars($user['name']) . "\n";
    echo 'Email: ' . htmlspecialchars($user['email']) . "\n";
    echo 'Profile Picture: ' . ($user['profile_picture'] ?? 'None') . "\n";
    echo 'Token: ' . htmlspecialchars(substr($user['api_token'], 0, 32)) . '...';
    echo '</pre>';
    echo '</div>';
    
    echo '<div class="info">';
    echo '<strong>Next Steps:</strong><br>';
    echo '1. Test app-login.php: <a href="app-login.php?token=' . urlencode($token) . '" target="_blank">app-login.php?token=' . htmlspecialchars(substr($token, 0, 16)) . '...</a><br>';
    echo '2. Should redirect to dashboard and create session<br>';
    echo '3. Use this token in your Flutter WebView URL';
    echo '</div>';
    
    echo '<div class="info">';
    echo '<strong>Flutter WebView URL:</strong><br>';
    $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . BASE_PATH;
    echo '<code>' . htmlspecialchars($baseUrl) . '/app-login?token=' . htmlspecialchars($token) . '</code>';
    echo '</div>';
    
    $conn->close();
    
} catch (Exception $e) {
    echo '<div class="error">';
    echo '<strong>Error:</strong><br>';
    echo htmlspecialchars($e->getMessage());
    echo '</div>';
}
?>

</body>
</html>

