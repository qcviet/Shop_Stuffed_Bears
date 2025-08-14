<?php
/**
 * Admin Logout Script
 */
require_once '../config/config.php';

// Use a separate session for admin
if (session_status() === PHP_SESSION_NONE) {
    session_name('admin_session');
    session_start();
}

// Clear all session data
session_unset();
session_destroy();

// Redirect to admin login page
header('Location: login.php');
exit;
?> 