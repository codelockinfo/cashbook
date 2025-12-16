<?php
/**
 * Login redirect endpoint that preserves token in URL
 * Usage: login-redirect.php?token=YOUR_TOKEN
 * Redirects to: dashboard?token=YOUR_TOKEN
 */

require_once 'config.php';

// Get token from request
$token = $_GET['token'] ?? null;

if ($token && !empty($token)) {
    // Token provided, redirect to dashboard with token
    $dashboardUrl = BASE_PATH . '/dashboard?token=' . urlencode($token);
    error_log("login-redirect.php - Redirecting to dashboard with token");
    header('Location: ' . $dashboardUrl);
    exit;
} else {
    // No token, redirect to login
    error_log("login-redirect.php - No token provided, redirecting to login");
    header('Location: ' . BASE_PATH . '/login');
    exit;
}
?>

