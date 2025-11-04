<?php
session_start();

// Check if user is logged in
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    // Redirect to dashboard if logged in
    header('Location: dashboard.php');
} else {
    // Redirect to login if not logged in
    header('Location: login.php');
}
exit;
?>

