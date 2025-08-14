<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../controller/AppController.php';

echo "<h1>Database Connection Test</h1>";

try {
    // Test database connection
    echo "<h2>1. Testing Database Connection</h2>";
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        echo "✅ Database connection successful<br>";
    } else {
        echo "❌ Database connection failed<br>";
        exit;
    }

    // Test AppController
    echo "<h2>2. Testing AppController</h2>";
    $appController = new AppController();
    
    if ($appController->isConnected()) {
        echo "✅ AppController connection successful<br>";
    } else {
        echo "❌ AppController connection failed<br>";
        exit;
    }

    // Test basic statistics
    echo "<h2>3. Testing Dashboard Statistics</h2>";
    $stats = $appController->getDashboardStatistics();
    
    if ($stats) {
        echo "✅ Dashboard statistics retrieved successfully<br>";
        echo "Products: " . ($stats['products'] ?? 0) . "<br>";
        echo "Users: " . ($stats['users'] ?? 0) . "<br>";
        echo "Categories: " . ($stats['categories'] ?? 0) . "<br>";
        echo "Orders: " . ($stats['orders']['total'] ?? 0) . "<br>";
    } else {
        echo "❌ Failed to retrieve dashboard statistics<br>";
    }

    // Test API endpoints
    echo "<h2>4. Testing API Endpoints</h2>";
    
    // Test product list endpoint
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
        } else {
            echo "❌ Product list API failed<br>";
            echo "Response: " . $response . "<br>";
        }
    } else {
        echo "❌ Product list API request failed<br>";
    }

    echo "<h2>Test Complete</h2>";
    echo "If all tests pass, the admin panel should work properly.<br>";
    echo "<a href='dashboard-admin.php'>Go to Admin Dashboard</a>";

} catch (Exception $e) {
    echo "<h2>Error</h2>";
    echo "Error: " . $e->getMessage() . "<br>";
    echo "File: " . $e->getFile() . "<br>";
    echo "Line: " . $e->getLine() . "<br>";
}
?> 