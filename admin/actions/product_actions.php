<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/ProductModel.php';
require_once __DIR__ . '/../../models/CategoryModel.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Initialize models
$productModel = $db ? new ProductModel($db) : null;
$categoryModel = $db ? new CategoryModel($db) : null;

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$response = ['success' => false, 'message' => ''];

// Check if database connection is available
if (!$db || !$productModel || !$categoryModel) {
    $response = ['success' => false, 'message' => 'Database connection failed'];
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

try {
    switch ($action) {
        case 'create':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $category_id = $_POST['category_id'] ?? '';
                $product_name = $_POST['product_name'] ?? '';
                $description = $_POST['description'] ?? '';
                $price = isset($_POST['price']) && $_POST['price'] !== '' ? (int)$_POST['price'] : 0;
                $stock = $_POST['stock'] ?? 0;
                
                if (empty($product_name) || empty($category_id)) {
                    throw new Exception('Product name and category are required');
                }
                
                if ($productModel->create($category_id, $product_name, $description, $price, $stock)) {
                    $newProductId = $db->lastInsertId();
                    // Multiple images support: accept image[]
                    if (!empty($_FILES['image']) && is_array($_FILES['image']['name'])) {
                        $count = count($_FILES['image']['name']);
                        for ($i = 0; $i < $count; $i++) {
                            if (!empty($_FILES['image']['tmp_name'][$i]) && is_uploaded_file($_FILES['image']['tmp_name'][$i])) {
                                $uploadDir = __DIR__ . '/../../uploads/products/';
                                if (!is_dir($uploadDir)) { @mkdir($uploadDir, 0777, true); }
                                $ext = pathinfo($_FILES['image']['name'][$i], PATHINFO_EXTENSION);
                                $safeName = 'prod_' . $newProductId . '_' . time() . '_' . $i . '.' . strtolower($ext);
                                $destPath = $uploadDir . $safeName;
                                if (move_uploaded_file($_FILES['image']['tmp_name'][$i], $destPath)) {
                                    $relPath = 'uploads/products/' . $safeName;
                                    $stmt = $db->prepare("INSERT INTO product_images (product_id, image_url) VALUES (:pid, :url)");
                                    $stmt->execute([':pid' => $newProductId, ':url' => $relPath]);
                                }
                            }
                        }
                    } elseif (!empty($_FILES['image']['tmp_name']) && is_uploaded_file($_FILES['image']['tmp_name'])) {
                        // Single file fallback
                        $uploadDir = __DIR__ . '/../../uploads/products/';
                        if (!is_dir($uploadDir)) { @mkdir($uploadDir, 0777, true); }
                        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                        $safeName = 'prod_' . $newProductId . '_' . time() . '.' . strtolower($ext);
                        $destPath = $uploadDir . $safeName;
                        if (move_uploaded_file($_FILES['image']['tmp_name'], $destPath)) {
                            $relPath = 'uploads/products/' . $safeName;
                            $stmt = $db->prepare("INSERT INTO product_images (product_id, image_url) VALUES (:pid, :url)");
                            $stmt->execute([':pid' => $newProductId, ':url' => $relPath]);
                        }
                    }
                    $response = ['success' => true, 'message' => 'Product created successfully'];
                } else {
                    throw new Exception('Failed to create product');
                }
            }
            break;
            
        case 'update':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $product_id = $_POST['product_id'] ?? '';
                $category_id = $_POST['category_id'] ?? '';
                $product_name = $_POST['product_name'] ?? '';
                $description = $_POST['description'] ?? '';
                $price = isset($_POST['price']) && $_POST['price'] !== '' ? (int)$_POST['price'] : 0;
                $stock = $_POST['stock'] ?? 0;
                
                if (empty($product_id) || empty($product_name) || empty($category_id)) {
                    throw new Exception('Product ID, name and category are required');
                }
                
                $data = [
                    'category_id' => $category_id,
                    'product_name' => $product_name,
                    'description' => $description,
                    'price' => $price,
                    'stock' => $stock
                ];
                
                if ($productModel->update($product_id, $data)) {
                    // Append multiple new images on update
                    if (!empty($_FILES['image']) && is_array($_FILES['image']['name'])) {
                        $count = count($_FILES['image']['name']);
                        for ($i = 0; $i < $count; $i++) {
                            if (!empty($_FILES['image']['tmp_name'][$i]) && is_uploaded_file($_FILES['image']['tmp_name'][$i])) {
                                $uploadDir = __DIR__ . '/../../uploads/products/';
                                if (!is_dir($uploadDir)) { @mkdir($uploadDir, 0777, true); }
                                $ext = pathinfo($_FILES['image']['name'][$i], PATHINFO_EXTENSION);
                                $safeName = 'prod_' . $product_id . '_' . time() . '_' . $i . '.' . strtolower($ext);
                                $destPath = $uploadDir . $safeName;
                                if (move_uploaded_file($_FILES['image']['tmp_name'][$i], $destPath)) {
                                    $relPath = 'uploads/products/' . $safeName;
                                    $stmt = $db->prepare("INSERT INTO product_images (product_id, image_url) VALUES (:pid, :url)");
                                    $stmt->execute([':pid' => $product_id, ':url' => $relPath]);
                                }
                            }
                        }
                    } elseif (!empty($_FILES['image']['tmp_name']) && is_uploaded_file($_FILES['image']['tmp_name'])) {
                        // Single file fallback -> replace first image
                        $uploadDir = __DIR__ . '/../../uploads/products/';
                        if (!is_dir($uploadDir)) { @mkdir($uploadDir, 0777, true); }
                        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                        $safeName = 'prod_' . $product_id . '_' . time() . '.' . strtolower($ext);
                        $destPath = $uploadDir . $safeName;
                        if (move_uploaded_file($_FILES['image']['tmp_name'], $destPath)) {
                            $relPath = 'uploads/products/' . $safeName;
                            $stmt = $db->prepare("SELECT image_id FROM product_images WHERE product_id = :pid ORDER BY image_id ASC LIMIT 1");
                            $stmt->execute([':pid' => $product_id]);
                            $row = $stmt->fetch(PDO::FETCH_ASSOC);
                            if ($row) {
                                $stmt = $db->prepare("UPDATE product_images SET image_url = :url WHERE image_id = :iid");
                                $stmt->execute([':url' => $relPath, ':iid' => $row['image_id']]);
                            } else {
                                $stmt = $db->prepare("INSERT INTO product_images (product_id, image_url) VALUES (:pid, :url)");
                                $stmt->execute([':pid' => $product_id, ':url' => $relPath]);
                            }
                        }
                    }
                    $response = ['success' => true, 'message' => 'Product updated successfully'];
                } else {
                    throw new Exception('Failed to update product');
                }
            }
            break;
        
        case 'images':
            // Get images for a product
            $product_id = $_GET['product_id'] ?? '';
            if (empty($product_id)) { throw new Exception('Product ID is required'); }
            $stmt = $db->prepare("SELECT image_id, image_url FROM product_images WHERE product_id = :pid ORDER BY image_id ASC");
            $stmt->execute([':pid' => $product_id]);
            $images = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $response = ['success' => true, 'data' => $images];
            break;
            
        case 'delete':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $product_id = $_POST['product_id'] ?? '';
                
                if (empty($product_id)) {
                    throw new Exception('Product ID is required');
                }
                
                if ($productModel->delete($product_id)) {
                    $response = ['success' => true, 'message' => 'Product deleted successfully'];
                } else {
                    throw new Exception('Failed to delete product');
                }
            }
            break;
            
        case 'get':
            $product_id = $_GET['product_id'] ?? '';
            
            if (empty($product_id)) {
                throw new Exception('Product ID is required');
            }
            
            $product = $productModel->getById($product_id);
            if ($product) {
                $response = ['success' => true, 'data' => $product];
            } else {
                throw new Exception('Product not found');
            }
            break;
            
        case 'list':
            $page = $_GET['page'] ?? 1;
            $limit = 10;
            $offset = ($page - 1) * $limit;
            
            $products = $productModel->getAll($limit, $offset);
            $total = $productModel->getCount();
            
            $response = [
                'success' => true, 
                'data' => $products,
                'total' => $total,
                'pages' => ceil($total / $limit),
                'current_page' => $page
            ];
            break;

        case 'images':
            $product_id = $_GET['product_id'] ?? '';
            if (empty($product_id)) {
                throw new Exception('Product ID is required');
            }
            $stmt = $db->prepare("SELECT image_id, image_url FROM product_images WHERE product_id = :pid ORDER BY image_id ASC");
            $stmt->execute([':pid' => $product_id]);
            $images = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $response = ['success' => true, 'data' => $images];
            break;

        case 'delete_image':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $image_id = $_POST['image_id'] ?? '';
                if (empty($image_id)) {
                    throw new Exception('Image ID is required');
                }
                // Fetch file path to optionally delete file
                $stmt = $db->prepare("SELECT image_url FROM product_images WHERE image_id = :iid");
                $stmt->execute([':iid' => $image_id]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $stmt = $db->prepare("DELETE FROM product_images WHERE image_id = :iid");
                if ($stmt->execute([':iid' => $image_id])) {
                    // Try to remove file from disk (ignore failures)
                    if ($row && !empty($row['image_url'])) {
                        $path = __DIR__ . '/../../' . $row['image_url'];
                        if (is_file($path)) { @unlink($path); }
                    }
                    $response = ['success' => true, 'message' => 'Image deleted'];
                } else {
                    throw new Exception('Failed to delete image');
                }
            }
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