<?php
// Include configuration files
require_once 'config/config.php';
require_once 'config/database.php';

// Check if PDO MySQL extension is available
if (!extension_loaded('pdo_mysql')) {
    // PDO MySQL extension not available, show error
    echo "<!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>PHP Configuration Error - Shop Gau Yeu</title>
        <style>
            body { 
                font-family: Arial, sans-serif; 
                text-align: center; 
                padding: 50px; 
                background-color: #f8f9fa;
            }
            .error-container {
                max-width: 600px;
                margin: 0 auto;
                background: white;
                padding: 30px;
                border-radius: 10px;
                box-shadow: 0 0 20px rgba(0,0,0,0.1);
            }
            .error-icon {
                font-size: 48px;
                color: #dc3545;
                margin-bottom: 20px;
            }
            h1 { color: #dc3545; }
            .btn {
                display: inline-block;
                padding: 10px 20px;
                background-color: #007bff;
                color: white;
                text-decoration: none;
                border-radius: 5px;
                margin-top: 20px;
            }
            .btn:hover {
                background-color: #0056b3;
            }
            .code {
                background-color: #f8f9fa;
                padding: 15px;
                border-radius: 5px;
                font-family: monospace;
                margin: 10px 0;
                text-align: left;
            }
        </style>
    </head>
    <body>
        <div class='error-container'>
            <div class='error-icon'>⚠️</div>
            <h1>PHP Configuration Error</h1>
            <p>The PDO MySQL extension is not enabled in your PHP configuration.</p>
            <div class='code'>
                <strong>To fix this:</strong><br>
                1. Open your php.ini file<br>
                2. Find the line: <code>;extension=pdo_mysql</code><br>
                3. Remove the semicolon: <code>extension=pdo_mysql</code><br>
                4. Restart your web server (Apache/XAMPP)
            </div>
            <p>After enabling the extension, refresh this page.</p>
            <a href='index.php' class='btn'>Refresh Page</a>
        </div>
    </body>
    </html>";
    exit;
}

// Initialize database connection
$database = new Database();
$conn = $database->getConnection();

// Check if database connection is successful
if ($conn) {
    // Database connection successful, check which page to load
    $page = isset($_GET['page']) ? $_GET['page'] : 'home';
    
    // Route to appropriate page
    switch ($page) {
        case 'home':
        case '':
            include 'views/users/home-page.php';
            break;
        case 'about':
            include 'views/users/about-page.php';
            break;
        case 'teddy-bears':
        case 'blind-box':
        case 'gifts':
        case 'cartoons':
        case 'accessories':
        case 'search':
        case 'help':
        case 'contact':
            include 'views/users/coming-soon.php';
            break;
        case 'login':
            include 'views/users/login.php';
            break;
        case 'register':
            include 'views/users/register.php';
            break;
        case 'profile':
            include 'views/users/profile.php';
            break;
        case 'category':
            include 'views/users/category-page.php';
            break;
        case 'logout':
            include 'views/users/logout.php';
            break;
        case 'orders':
            include 'views/users/orders.php';
            break;
        case 'cart':
            include 'views/users/cart.php';
            break;
        case 'checkout':
            include 'views/users/checkout.php';
            break;
        case 'single-compression':
            include 'views/users/single-compression.php';
            break;
        case 'single-ship':
            include 'views/users/single-ship.php';
            break;
        case 'single-gift-wrapping':
            include 'views/users/single-gift-wrapping.php';
            break;
        case 'single-card':
            include 'views/users/single-card.php';
            break;
        case 'single-washing':
            include 'views/users/single-washing.php';
            break;
        case 'product-detail':
            include 'views/users/product-detail.php';
            break;
        case 'order-detail':
            include 'views/users/order-detail.php';
            break;
        default:
            include 'views/users/404.php';
            break;
    }
} else {
    echo "<!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Database Connection Error - Shop Gau Yeu</title>
        <style>
            body { 
                font-family: Arial, sans-serif; 
                text-align: center; 
                padding: 50px; 
                background-color: #f8f9fa;
            }
            .error-container {
                max-width: 500px;
                margin: 0 auto;
                background: white;
                padding: 30px;
                border-radius: 10px;
                box-shadow: 0 0 20px rgba(0,0,0,0.1);
            }
            .error-icon {
                font-size: 48px;
                color: #dc3545;
                margin-bottom: 20px;
            }
            h1 { color: #dc3545; }
            .btn {
                display: inline-block;
                padding: 10px 20px;
                background-color: #007bff;
                color: white;
                text-decoration: none;
                border-radius: 5px;
                margin-top: 20px;
            }
            .btn:hover {
                background-color: #0056b3;
            }
        </style>
    </head>
    <body>
        <div class='error-container'>
            <div class='error-icon'>⚠️</div>
            <h1>Database Connection Error</h1>
            <p>Unable to connect to the database. Please check your database configuration and try again.</p>
            <a href='index.php' class='btn'>Retry Connection</a>
        </div>
    </body>
    </html>";
}
?>
