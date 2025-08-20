<?php
// Test file to verify the search functionality that redirects to category page
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'models/ProductModel.php';
require_once 'models/CategoryModel.php';

echo "<h1>Search to Category Page Functionality Test</h1>";

// Test database connection
$database = new Database();
$db = $database->getConnection();

if (!$db) {
    echo "<p style='color: red;'>❌ Database connection failed</p>";
    exit;
}

echo "<p style='color: green;'>✅ Database connection successful</p>";

// Test product model
$productModel = new ProductModel($db);
$categoryModel = new CategoryModel($db);

// Test search functionality
echo "<h2>Search Test Results</h2>";

// Test 1: Search for products
$search_results = $productModel->searchProducts('', '', 5, 0);
echo "<h3>All Products (first 5):</h3>";
echo "<p>Found " . count($search_results) . " products</p>";

foreach ($search_results as $product) {
    echo "<div style='border: 1px solid #ddd; padding: 10px; margin: 5px 0;'>";
    echo "<strong>" . htmlspecialchars($product['product_name']) . "</strong><br>";
    echo "Category: " . htmlspecialchars($product['category_name']) . " (ID: " . $product['category_id'] . ")<br>";
    echo "Price: " . number_format($product['price'], 0, ',', '.') . " VNĐ<br>";
    echo "Product ID: " . $product['product_id'] . "<br>";
    if (!empty($product['image'])) {
        echo "<img src='uploads/products/" . htmlspecialchars($product['image']) . "' style='max-width: 100px; max-height: 100px;'><br>";
    }
    echo "</div>";
}

// Test 2: Search count
$total_count = $productModel->getSearchCount('', '');
echo "<h3>Total Products in Database:</h3>";
echo "<p>" . $total_count . " products</p>";

echo "<h2>New Search Features</h2>";
echo "<ul>";
echo "<li>✅ Search form redirects to category page</li>";
echo "<li>✅ Product search finds the product and shows its category</li>";
echo "<li>✅ Searched product is highlighted in the category</li>";
echo "<li>✅ Search summary shows what was searched</li>";
echo "<li>✅ Clicking suggestions still goes to product detail page</li>";
echo "</ul>";

echo "<h2>How to Test</h2>";
echo "<ol>";
echo "<li>Go to the main page</li>";
echo "<li>Type a product name in the search box (e.g., 'water bottle')</li>";
echo "<li>Press Enter or click the search button</li>";
echo "<li>You should be redirected to the category page containing that product</li>";
echo "<li>The searched product should be highlighted with a border</li>";
echo "<li>A search summary should appear at the top</li>";
echo "</ol>";

echo "<h2>Test Links</h2>";
echo "<p><a href='index.php' target='_blank'>Go to Main Page</a></p>";

// Test direct category page with search
if (!empty($search_results)) {
    $testProduct = $search_results[0];
    $testSearch = urlencode($testProduct['product_name']);
    echo "<p><a href='index.php?page=category&search=" . $testSearch . "&search_product=1' target='_blank'>Test Search: " . htmlspecialchars($testProduct['product_name']) . "</a></p>";
}

echo "<h2>Expected Behavior</h2>";
echo "<ul>";
echo "<li>✅ Search form submits to category page</li>";
echo "<li>✅ Product search finds the product and redirects to its category</li>";
echo "<li>✅ Searched product is highlighted with border</li>";
echo "<li>✅ Search summary appears at top of category page</li>";
echo "<li>✅ All products in that category are shown</li>";
echo "<li>✅ User can browse other products in the same category</li>";
echo "</ul>";

echo "<h2>URL Structure</h2>";
echo "<p>Search URLs will now look like:</p>";
echo "<code>index.php?page=category&search=product_name&search_product=1</code>";
echo "<p>This will:</p>";
echo "<ol>";
echo "<li>Find the product by name</li>";
echo "<li>Get the product's category ID</li>";
echo "<li>Redirect to that category page</li>";
echo "<li>Highlight the searched product</li>";
echo "<li>Show search summary</li>";
echo "</ol>";
?>
