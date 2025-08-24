<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/PostModel.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Initialize models
$postModel = $db ? new PostModel($db) : null;

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$response = ['success' => false, 'message' => ''];

// Check if database connection is available
if (!$db || !$postModel) {
    $response = ['success' => false, 'message' => 'Database connection failed'];
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

try {
    switch ($action) {
        case 'create':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $title = $_POST['title'] ?? '';
                $content = $_POST['content'] ?? '';
                $thumbnail = $_POST['thumbnail'] ?? '';

                if (empty($title) || empty($content)) {
                    throw new Exception('Title and content are required');
                }

                if ($postModel->create($title, $content, $thumbnail)) {
                    $response = ['success' => true, 'message' => 'Post created successfully'];
                } else {
                    throw new Exception('Failed to create post');
                }
            }
            break;

        case 'update':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $post_id = $_POST['post_id'] ?? '';
                $title = $_POST['title'] ?? '';
                $content = $_POST['content'] ?? '';
                $thumbnail = $_POST['thumbnail'] ?? '';

                if (empty($post_id) || empty($title) || empty($content)) {
                    throw new Exception('Post ID, title and content are required');
                }

                if ($postModel->update($post_id, $title, $content, $thumbnail)) {
                    $response = ['success' => true, 'message' => 'Post updated successfully'];
                } else {
                    throw new Exception('Failed to update post');
                }
            }
            break;

        case 'delete':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $post_id = $_POST['post_id'] ?? '';

                if (empty($post_id)) {
                    throw new Exception('Post ID is required');
                }

                if ($postModel->delete($post_id)) {
                    $response = ['success' => true, 'message' => 'Post deleted successfully'];
                } else {
                    throw new Exception('Failed to delete post');
                }
            }
            break;

        case 'get':
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                $post_id = $_GET['post_id'] ?? '';

                if (empty($post_id)) {
                    throw new Exception('Post ID is required');
                }

                $post = $postModel->getById($post_id);
                if ($post) {
                    $response = ['success' => true, 'data' => $post];
                } else {
                    throw new Exception('Post not found');
                }
            }
            break;

        case 'list':
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
                $search = $_GET['search'] ?? '';
                
                $offset = ($page - 1) * $limit;
                
                $posts = $postModel->getAll($limit, $offset, $search);
                $total = $postModel->getTotalCount($search);
                
                $response = [
                    'success' => true,
                    'data' => $posts,
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
