<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/PromotionModel.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Initialize models
$promotionModel = $db ? new PromotionModel($db) : null;

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$response = ['success' => false, 'message' => ''];

// Check if database connection is available
if (!$db || !$promotionModel) {
    $response = ['success' => false, 'message' => 'Database connection failed'];
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

try {
    switch ($action) {
        case 'create':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $data = [
                    'title' => $_POST['title'] ?? '',
                    'description' => $_POST['description'] ?? '',
                    'content' => $_POST['content'] ?? '',
                    'discount_percent' => $_POST['discount_percent'] ?? 0,
                    'promotion_type' => $_POST['promotion_type'] ?? 'general',
                    'target_id' => $_POST['target_id'] ?? null,
                    'start_date' => $_POST['start_date'] ?? '',
                    'end_date' => $_POST['end_date'] ?? '',
                    'is_active' => isset($_POST['is_active']) ? 1 : 0
                ];

                // Validate target_id based on promotion_type
                if ($data['promotion_type'] === 'category' || $data['promotion_type'] === 'product') {
                    if (empty($data['target_id'])) {
                        throw new Exception('Target ID is required for category or product promotions');
                    }
                }

                if (empty($data['title']) || empty($data['start_date']) || empty($data['end_date'])) {
                    throw new Exception('Title, start date and end date are required');
                }

                if ($promotionModel->create($data)) {
                    $response = ['success' => true, 'message' => 'Promotion created successfully'];
                } else {
                    throw new Exception('Failed to create promotion');
                }
            }
            break;

        case 'update':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $promotion_id = $_POST['promotion_id'] ?? '';
                $data = [
                    'title' => $_POST['title'] ?? '',
                    'description' => $_POST['description'] ?? '',
                    'content' => $_POST['content'] ?? '',
                    'discount_percent' => $_POST['discount_percent'] ?? 0,
                    'promotion_type' => $_POST['promotion_type'] ?? 'general',
                    'target_id' => $_POST['target_id'] ?? null,
                    'start_date' => $_POST['start_date'] ?? '',
                    'end_date' => $_POST['end_date'] ?? '',
                    'is_active' => isset($_POST['is_active']) ? 1 : 0
                ];

                // Validate target_id based on promotion_type
                if ($data['promotion_type'] === 'category' || $data['promotion_type'] === 'product') {
                    if (empty($data['target_id'])) {
                        throw new Exception('Target ID is required for category or product promotions');
                    }
                }

                if (empty($promotion_id) || empty($data['title']) || empty($data['start_date']) || empty($data['end_date'])) {
                    throw new Exception('Promotion ID, title, start date and end date are required');
                }

                if ($promotionModel->update($promotion_id, $data)) {
                    $response = ['success' => true, 'message' => 'Promotion updated successfully'];
                } else {
                    throw new Exception('Failed to update promotion');
                }
            }
            break;

        case 'delete':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $promotion_id = $_POST['promotion_id'] ?? '';

                if (empty($promotion_id)) {
                    throw new Exception('Promotion ID is required');
                }

                if ($promotionModel->delete($promotion_id)) {
                    $response = ['success' => true, 'message' => 'Promotion deleted successfully'];
                } else {
                    throw new Exception('Failed to delete promotion');
                }
            }
            break;

        case 'toggle_status':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $promotion_id = $_POST['promotion_id'] ?? '';

                if (empty($promotion_id)) {
                    throw new Exception('Promotion ID is required');
                }

                if ($promotionModel->toggleStatus($promotion_id)) {
                    $response = ['success' => true, 'message' => 'Promotion status toggled successfully'];
                } else {
                    throw new Exception('Failed to toggle promotion status');
                }
            }
            break;

        case 'get':
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                $promotion_id = $_GET['promotion_id'] ?? '';

                if (empty($promotion_id)) {
                    throw new Exception('Promotion ID is required');
                }

                $promotion = $promotionModel->getById($promotion_id);
                if ($promotion) {
                    $response = ['success' => true, 'data' => $promotion];
                } else {
                    throw new Exception('Promotion not found');
                }
            }
            break;

        case 'list':
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
                $search = $_GET['search'] ?? '';
                
                $offset = ($page - 1) * $limit;
                
                $promotions = $promotionModel->getAll($limit, $offset, $search);
                $total = $promotionModel->getTotalCount($search);
                
                $response = [
                    'success' => true,
                    'data' => $promotions,
                    'pagination' => [
                        'current_page' => $page,
                        'per_page' => $limit,
                        'total' => $total,
                        'total_pages' => ceil($total / $limit)
                    ]
                ];
            }
            break;

        default:
            throw new Exception('Invalid action');
    }

} catch (Exception $e) {
    $response = ['success' => false, 'message' => $e->getMessage()];
}

header('Content-Type: application/json');
echo json_encode($response);
