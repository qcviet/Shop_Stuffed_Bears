<?php
/**
 * User Logout Page
 */
require_once __DIR__ . '/../../config/config.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Clear all session data
session_unset();
session_destroy();

// Clear remember me cookie if exists
if (isset($_COOKIE['user_remember'])) {
    setcookie('user_remember', '', time() - 3600, '/');
}

// Redirect to home page
header('Location: ' . BASE_URL);
exit;
?> 