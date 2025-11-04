<?php
// Session protection - include this file on all protected pages
session_start();

function checkAuth() {
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        header('Location: login.php');
        exit;
    }
}

function getCurrentUser() {
    if (isset($_SESSION['user_id'])) {
        return [
            'id' => $_SESSION['user_id'],
            'name' => $_SESSION['user_name'],
            'email' => $_SESSION['user_email'],
            'profile_picture' => $_SESSION['user_profile_picture'] ?? null
        ];
    }
    return null;
}
?>

