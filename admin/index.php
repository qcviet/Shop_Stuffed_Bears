<?php
/**
 * Admin Panel Entry Point
 */
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../controller/AppController.php';

// Use a separate session for admin
if (session_status() === PHP_SESSION_NONE) {
    session_name('admin_session');
    session_start();
}

// Optional: ensure DB extension
if (!extension_loaded('pdo_mysql')) {
    die("PDO MySQL extension is not enabled. Please enable it in your php.ini file.");
}

$database = new Database();
$conn = $database->getConnection();
if (!$conn) {
    die("Database connection failed. Please check your database configuration.");
}

// Check if user is logged in and has admin role
$isLoggedIn = isset($_SESSION['user_id']) && (($_SESSION['role'] ?? '') === 'admin');

if (!$isLoggedIn) {
    include 'login.php';
    exit;
}

// Get current user data for the dashboard
$appController = new AppController();
$currentUser = $appController->getUserById($_SESSION['user_id']);

if (!$currentUser) {
    // User not found, clear session and redirect to login
    session_destroy();
    header('Location: login.php');
    exit;
}

// Update session with fresh user data
$_SESSION['username'] = $currentUser['username'];
$_SESSION['role'] = $currentUser['role'];
$_SESSION['full_name'] = $currentUser['full_name'];
$_SESSION['email'] = $currentUser['email'];

$adminPage = $_GET['page'] ?? 'dashboard';

include 'dashboard-admin.php';
?> 