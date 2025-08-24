<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/CategoryModel.php';

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Initialize model
$categoryModel = $db ? new CategoryModel($db) : null;

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$response = ['success' => false, 'message' => ''];

// Check if database connection is available
if (!$db || !$categoryModel) {
    $response = ['success' => false, 'message' => 'Database connection failed'];
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

try {
    switch ($action) {
        case 'create':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $category_name = $_POST['category_name'] ?? '';
                $description = $_POST['description'] ?? '';
                
                if (empty($category_name)) {
                    throw new Exception('Category name is required');
                }
                
                // Check if category name already exists
                if ($categoryModel->nameExists($category_name)) {
                    throw new Exception('Category name already exists');
                }
                
                if ($categoryModel->create($category_name, $description)) {
                    $response = ['success' => true, 'message' => 'Category created successfully'];
                } else {
                    throw new Exception('Failed to create category');
                }
            }
            break;
            
        case 'update':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $category_id = $_POST['category_id'] ?? '';
                $category_name = $_POST['category_name'] ?? '';
                $description = $_POST['description'] ?? '';
                
                if (empty($category_id) || empty($category_name)) {
                    throw new Exception('Category ID and name are required');
                }
                
                // Check if category name already exists (excluding current category)
                if ($categoryModel->nameExists($category_name, $category_id)) {
                    throw new Exception('Category name already exists');
                }
                
                if ($categoryModel->update($category_id, $category_name, $description)) {
                    $response = ['success' => true, 'message' => 'Category updated successfully'];
                } else {
                    throw new Exception('Failed to update category');
                }
            }
            break;
            
        case 'delete':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $category_id = $_POST['category_id'] ?? '';
                
                if (empty($category_id)) {
                    throw new Exception('Category ID is required');
                }
                
                // Check if category has products
                $category = $categoryModel->getWithProductCount();
                $hasProducts = false;
                foreach ($category as $cat) {
                    if ($cat['category_id'] == $category_id && $cat['product_count'] > 0) {
                        $hasProducts = true;
                        break;
                    }
                }
                
                if ($hasProducts) {
                    throw new Exception('Cannot delete category with existing products');
                }
                
                if ($categoryModel->delete($category_id)) {
                    $response = ['success' => true, 'message' => 'Category deleted successfully'];
                } else {
                    throw new Exception('Failed to delete category');
                }
            }
            break;
            
        case 'get':
            $category_id = $_GET['category_id'] ?? '';
            
            if (empty($category_id)) {
                throw new Exception('Category ID is required');
            }
            
            $category = $categoryModel->getById($category_id);
            if ($category) {
                $response = ['success' => true, 'data' => $category];
            } else {
                throw new Exception('Category not found');
            }
            break;
            
        case 'list':
            $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
            $limit = isset($_GET['limit']) ? max(1, min(50, (int)$_GET['limit'])) : 10;
            $offset = ($page - 1) * $limit;
            $search = $_GET['search'] ?? '';
            
            // Always use search methods for consistency, even when no search query
            $categories = $categoryModel->searchCategories($search, $limit, $offset);
            $total = $categoryModel->getSearchCount($search);
            
            $response = [
                'success' => true, 
                'data' => $categories,
                'total' => $total,
                'pages' => (int)ceil($total / $limit),
                'current_page' => $page,
                'limit' => $limit
            ];
            break;
            
        default:
            throw new Exception('Invalid action');
    }
} catch (Exception $e) {
    $response = ['success' => false, 'message' => $e->getMessage()];
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?> 