<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/ProductModel.php';
require_once __DIR__ . '/../models/CategoryModel.php';
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../models/OrderModel.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Admin CRUD Test</h1>";

try {
    // Test database connection
    echo "<h2>Database Connection Test</h2>";
    if ($db) {
        echo "✅ Database connection successful<br>";
    } else {
        echo "❌ Database connection failed<br>";
        exit;
    }

    // Test models
    echo "<h2>Model Tests</h2>";
    
    // Category Model Test
    echo "<h3>Category Model</h3>";
    $categoryModel = new CategoryModel($db);
    $categories = $categoryModel->getAll();
    echo "Categories found: " . count($categories) . "<br>";
    
    if (count($categories) > 0) {
        echo "Sample category: " . $categories[0]['category_name'] . "<br>";
    }

    // Product Model Test
    echo "<h3>Product Model</h3>";
    $productModel = new ProductModel($db);
    $products = $productModel->getAll(5); // Get first 5 products
    echo "Products found: " . count($products) . "<br>";
    
    if (count($products) > 0) {
        echo "Sample product: " . $products[0]['product_name'] . "<br>";
    }

    // User Model Test
    echo "<h3>User Model</h3>";
    $userModel = new UserModel($db);
    $users = $userModel->getAll(5); // Get first 5 users
    echo "Users found: " . count($users) . "<br>";
    
    if (count($users) > 0) {
        echo "Sample user: " . ($users[0]['full_name'] ?: $users[0]['username']) . "<br>";
    }

    // Order Model Test
    echo "<h3>Order Model</h3>";
    $orderModel = new OrderModel($db);
    $orders = $orderModel->getAll(5); // Get first 5 orders
    echo "Orders found: " . count($orders) . "<br>";
    
    if (count($orders) > 0) {
        echo "Sample order ID: " . $orders[0]['order_id'] . "<br>";
    }

    // Test API endpoints
    echo "<h2>API Endpoint Tests</h2>";
    
    // Test product list endpoint
    echo "<h3>Product List API</h3>";
    $url = 'actions/product_actions.php?action=list';
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => 'Content-Type: application/json'
        ]
    ]);
    
    $response = file_get_contents($url, false, $context);
    if ($response !== false) {
        $data = json_decode($response, true);
        if ($data && isset($data['success'])) {
            echo "✅ Product list API working<br>";
            echo "Products returned: " . count($data['data']) . "<br>";
        } else {
            echo "❌ Product list API failed<br>";
            echo "Response: " . $response . "<br>";
        }
    } else {
        echo "❌ Product list API request failed<br>";
    }

    // Test category list endpoint
    echo "<h3>Category List API</h3>";
    $url = 'actions/category_actions.php?action=list';
    $response = file_get_contents($url, false, $context);
    if ($response !== false) {
        $data = json_decode($response, true);
        if ($data && isset($data['success'])) {
            echo "✅ Category list API working<br>";
            echo "Categories returned: " . count($data['data']) . "<br>";
        } else {
            echo "❌ Category list API failed<br>";
            echo "Response: " . $response . "<br>";
        }
    } else {
        echo "❌ Category list API request failed<br>";
    }

    echo "<h2>Test Complete</h2>";
    echo "If all tests pass, the CRUD functionality should work properly.<br>";
    echo "<a href='dashboard-admin.php'>Go to Admin Dashboard</a>";

} catch (Exception $e) {
    echo "<h2>Error</h2>";
    echo "Error: " . $e->getMessage() . "<br>";
    echo "File: " . $e->getFile() . "<br>";
    echo "Line: " . $e->getLine() . "<br>";
}
?> 