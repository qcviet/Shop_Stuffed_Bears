<?php
// AJAX Handler for search suggestions and other AJAX requests
header('Content-Type: application/json');

// Include necessary files
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'controller/AppController.php';

// Initialize AppController
$app = new AppController();

// Get the action from POST data
$action = $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'search_suggestions':
            $query = $_POST['query'] ?? '';
            
            if (empty($query)) {
                echo json_encode(['success' => false, 'message' => 'Query is required']);
                exit;
            }
            
            $suggestions = $app->getSearchSuggestions($query);
            
            if ($suggestions !== false) {
                echo json_encode([
                    'success' => true,
                    'suggestions' => $suggestions
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Failed to get suggestions'
                ]);
            }
            break;
            
        case 'add_to_cart':
            if (!isset($_SESSION['user_id'])) {
                echo json_encode(['success' => false, 'message' => 'Please login first']);
                exit;
            }
            
            $product_id = (int)($_POST['product_id'] ?? 0);
            $quantity = (int)($_POST['quantity'] ?? 1);
            
            if ($product_id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid product']);
                exit;
            }
            
            // Get the first variant of the product
            $product = $app->getProductById($product_id);
            if (!$product) {
                echo json_encode(['success' => false, 'message' => 'Product not found']);
                exit;
            }
            
            // Add to cart using the first available variant
            $result = $app->addToCart($_SESSION['user_id'], $product['min_variant_id'], $quantity);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Added to cart successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to add to cart']);
            }
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
?>
