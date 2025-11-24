<?php
// Test session to see what's happening
require_once 'config.php';

echo "<h2>Session Test</h2>";
echo "<pre>";
echo "Session Status: " . session_status() . " (2 = active)\n";
echo "Session ID: " . session_id() . "\n";
echo "Session Name: " . session_name() . "\n";
echo "Cookie Path: " . ini_get('session.cookie_path') . "\n";
echo "BASE_PATH: " . BASE_PATH . "\n";
echo "\n";
echo "Received Cookies:\n";
print_r($_COOKIE);
echo "\n";
echo "Session Variables:\n";
print_r($_SESSION);
echo "\n";
echo "All Headers:\n";
print_r(getallheaders());
echo "</pre>";
?>

