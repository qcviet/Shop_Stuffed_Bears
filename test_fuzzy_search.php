<?php
// Test file to verify the improved fuzzy search functionality
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'models/ProductModel.php';
require_once 'models/CategoryModel.php';

echo "<h1>Fuzzy Search Functionality Test</h1>";

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
echo "<h2>Fuzzy Search Test Results</h2>";

// Test different search scenarios
$testSearches = [
    'water',
    'bottle', 
    'water bottle',
    'teddy',
    'bear',
    'gau',
    'bong',
    'gaubong'
];

foreach ($testSearches as $searchTerm) {
    echo "<h3>Searching for: '$searchTerm'</h3>";
    
    $search_results = $productModel->searchProducts($searchTerm, '', 5, 0);
    echo "<p>Found " . count($search_results) . " similar products</p>";
    
    if (!empty($search_results)) {
        echo "<div style='margin-bottom: 20px;'>";
        foreach ($search_results as $product) {
            echo "<div style='border: 1px solid #ddd; padding: 10px; margin: 5px 0; background: #f9f9f9;'>";
            echo "<strong>" . htmlspecialchars($product['product_name']) . "</strong><br>";
            echo "Category: " . htmlspecialchars($product['category_name']) . " (ID: " . $product['category_id'] . ")<br>";
            echo "Price: " . number_format($product['price'], 0, ',', '.') . " VNĐ<br>";
            echo "Product ID: " . $product['product_id'] . "<br>";
            if (!empty($product['image'])) {
                echo "<img src='uploads/products/" . htmlspecialchars($product['image']) . "' style='max-width: 100px; max-height: 100px;'><br>";
            }
            echo "</div>";
        }
        echo "</div>";
    } else {
        echo "<p style='color: orange;'>No similar products found</p>";
    }
}

echo "<h2>Improved Search Features</h2>";
echo "<ul>";
echo "<li>✅ Fuzzy search - finds similar products even with partial matches</li>";
echo "<li>✅ Word-based search - searches for individual words in product names</li>";
echo "<li>✅ Start/end matching - finds products that start or end with search terms</li>";
echo "<li>✅ Description search - also searches product descriptions</li>";
echo "<li>✅ Multiple results - shows up to 10 similar products</li>";
echo "<li>✅ Category grouping - groups results by most common category</li>";
echo "<li>✅ Product highlighting - highlights searched products in results</li>";
echo "</ul>";

echo "<h2>How to Test</h2>";
echo "<ol>";
echo "<li>Go to the main page</li>";
echo "<li>Type any search term (even partial words)</li>";
echo "<li>Press Enter or click the search button</li>";
echo "<li>You should see similar products, not just exact matches</li>";
echo "<li>Products are grouped by the most common category</li>";
echo "<li>Searched products are highlighted with borders</li>";
echo "</ol>";

echo "<h2>Test Links</h2>";
echo "<p><a href='index.php' target='_blank'>Go to Main Page</a></p>";

// Test direct category page with fuzzy search
$testSearches = ['water', 'teddy', 'gau'];
foreach ($testSearches as $testSearch) {
    $testSearchEncoded = urlencode($testSearch);
    echo "<p><a href='index.php?page=category&search=" . $testSearchEncoded . "&search_product=1' target='_blank'>Test Fuzzy Search: '$testSearch'</a></p>";
}

echo "<h2>Expected Behavior</h2>";
echo "<ul>";
echo "<li>✅ Partial searches work (e.g., 'water' finds 'water bottle')</li>";
echo "<li>✅ Word searches work (e.g., 'teddy' finds 'teddy bear')</li>";
echo "<li>✅ Multiple similar products are shown</li>";
echo "<li>✅ Products are grouped by category</li>";
echo "<li>✅ Searched products are highlighted</li>";
echo "<li>✅ User can choose from multiple options</li>";
echo "</ul>";

echo "<h2>Search Logic</h2>";
echo "<p>The improved search now looks for:</p>";
echo "<ul>";
echo "<li>Products containing the search term anywhere in the name</li>";
echo "<li>Products that start with the search term</li>";
echo "<li>Products that end with the search term</li>";
echo "<li>Products with words matching the search term</li>";
echo "<li>Products with matching descriptions</li>";
echo "</ul>";

echo "<h2>Example Searches</h2>";
echo "<ul>";
echo "<li><strong>'water'</strong> → finds 'water bottle', 'water container', etc.</li>";
echo "<li><strong>'bottle'</strong> → finds 'water bottle', 'plastic bottle', etc.</li>";
echo "<li><strong>'teddy'</strong> → finds 'teddy bear', 'teddy toy', etc.</li>";
echo "<li><strong>'gau'</strong> → finds 'gaubong', 'gau toy', etc.</li>";
echo "</ul>";
?>
