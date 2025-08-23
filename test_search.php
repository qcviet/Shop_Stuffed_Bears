<?php
// Test file to verify search functionality
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'models/ProductModel.php';
require_once 'models/CategoryModel.php';

echo "<h1>Search Functionality Test</h1>";

// Test database connection
$database = new Database();
$db = $database->getConnection();

if (!$db) {
    echo "<p style='color: red;'>❌ Database connection failed</p>";
    exit;
}

echo "<p style='color: green;'>✅ Database connection successful</p>";

// Test category model
$categoryModel = new CategoryModel($db);
$categories = $categoryModel->getAll();

echo "<h2>Categories Found: " . count($categories) . "</h2>";
foreach ($categories as $category) {
    echo "<p>- " . htmlspecialchars($category['category_name']) . " (ID: " . $category['category_id'] . ")</p>";
}

// Test product model
$productModel = new ProductModel($db);

// Test search functionality
echo "<h2>Search Test Results</h2>";

// Test 1: Search for products
$search_results = $productModel->searchProducts('', '', 5, 0);
echo "<h3>All Products (first 5):</h3>";
echo "<p>Found " . count($search_results) . " products</p>";

foreach ($search_results as $product) {
    echo "<div style='border: 1px solid #ddd; padding: 10px; margin: 5px 0;'>";
    echo "<strong>" . htmlspecialchars($product['product_name']) . "</strong><br>";
    echo "Category: " . htmlspecialchars($product['category_name']) . "<br>";
    echo "Price: " . number_format($product['price'], 0, ',', '.') . " VNĐ<br>";
    echo "Product ID: " . $product['product_id'] . "<br>";
    if (!empty($product['image_url'])) {
        $img = $product['image_url'];
        $src = (strpos($img, 'http') === 0) ? $img : $img;
        echo "<img src='" . htmlspecialchars($src) . "' style='max-width: 100px; max-height: 100px;'><br>";
    }
    echo "</div>";
}

// Test 2: Search count
$total_count = $productModel->getSearchCount('', '');
echo "<h3>Total Products in Database:</h3>";
echo "<p>" . $total_count . " products</p>";

// Test 3: Search suggestions functionality
echo "<h2>Search Suggestions Test</h2>";
echo "<p>To test search suggestions:</p>";
echo "<ol>";
echo "<li>Go to the main page</li>";
echo "<li>Type in the search box (at least 2 characters)</li>";
echo "<li>You should see suggestions appear below the search box</li>";
echo "<li>Click on a suggestion to go to the product detail page</li>";
echo "<li>Or press Enter to see all search results</li>";
echo "</ol>";

echo "<h2>Test Links</h2>";
echo "<p><a href='index.php' target='_blank'>Go to Main Page</a></p>";
echo "<p><a href='index.php?page=search&search=test' target='_blank'>Test Search Results Page</a></p>";

echo "<h2>Expected Behavior</h2>";
echo "<ul>";
echo "<li>✅ Search suggestions appear when typing</li>";
echo "<li>✅ Clicking suggestion goes to product detail page</li>";
echo "<li>✅ Pressing Enter shows search results page</li>";
echo "<li>✅ Category filter works</li>";
echo "<li>✅ Mobile responsive design</li>";
echo "</ul>";
?>

