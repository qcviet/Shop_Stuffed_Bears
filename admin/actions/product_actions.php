<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/ProductModel.php';
require_once __DIR__ . '/../../models/ProductVariantModel.php';
require_once __DIR__ . '/../../models/CategoryModel.php';
require_once __DIR__ . '/../../models/ColorModel.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Initialize models
$productModel = $db ? new ProductModel($db) : null;
$variantModel = $db ? new ProductVariantModel($db) : null;
$categoryModel = $db ? new CategoryModel($db) : null;
$colorModel = $db ? new ColorModel($db) : null;

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
                $variants_json = $_POST['variants_json'] ?? '[]';
                $colors_json = $_POST['colors_json'] ?? '[]';

                if (empty($product_name) || empty($category_id)) {
                    throw new Exception('Product name and category are required');
                }

                // Decode variants (expecting an array of {size, price, stock})
                $variants = json_decode($variants_json, true);
                if (!is_array($variants)) { $variants = []; }

                // Require at least one variant upon creation
                if (count($variants) === 0) {
                    throw new Exception('Please add at least one variant (size, price, stock)');
                }
                // Decode colors (array of names)
                $colors = json_decode($colors_json, true);
                if (!is_array($colors)) { $colors = []; }

                try {
                    $db->beginTransaction();

                    if (!$productModel->create($category_id, $product_name, $description)) {
                        throw new Exception('Failed to create product');
                    }

                    $newProductId = $db->lastInsertId();

                    // Create variants first, so we can fail fast without writing image files
                    if (!$variantModel) { throw new Exception('Variant model not available'); }
                    foreach ($variants as $idx => $v) {
                        $size = isset($v['size']) ? trim($v['size']) : '';
                        $price = isset($v['price']) && $v['price'] !== '' ? (float)$v['price'] : 0;
                        $stock = isset($v['stock']) ? (int)$v['stock'] : 0;
                        if ($size === '') {
                            throw new Exception('Variant at position ' . ($idx + 1) . ' is missing size');
                        }
                        if (!$variantModel->create($newProductId, $size, $price, $stock)) {
                            throw new Exception('Failed to create variant at position ' . ($idx + 1));
                        }
                    }

                    // Handle images
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

                    // Insert product-level colors (independent of variants)
                    if (count($colors) > 0) {
                        $insColor = $db->prepare("INSERT INTO product_colors (product_id, color_name) VALUES (:pid, :name)");
                        foreach ($colors as $cname) {
                            $name = trim((string)$cname);
                            if ($name !== '') {
                                $insColor->execute([':pid' => $newProductId, ':name' => $name]);
                            }
                        }
                    }

                    $db->commit();
                    $response = ['success' => true, 'message' => 'Product created successfully', 'product_id' => $newProductId];
                } catch (Exception $ex) {
                    if ($db->inTransaction()) { $db->rollBack(); }
                    throw $ex;
                }
            }
            break;
            
        case 'update':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $product_id = $_POST['product_id'] ?? '';
                $category_id = $_POST['category_id'] ?? '';
                $product_name = $_POST['product_name'] ?? '';
                $description = $_POST['description'] ?? '';
                
                if (empty($product_id) || empty($product_name) || empty($category_id)) {
                    throw new Exception('Product ID, name and category are required');
                }
                
                $data = [
                    'category_id' => $category_id,
                    'product_name' => $product_name,
                    'description' => $description
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
                
                try {
                    $db->beginTransaction();

                    // 1) Delete product images rows and files
                    $stmt = $db->prepare("SELECT image_url FROM product_images WHERE product_id = :pid");
                    $stmt->execute([':pid' => $product_id]);
                    $images = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    $stmt = $db->prepare("DELETE FROM product_images WHERE product_id = :pid");
                    $stmt->execute([':pid' => $product_id]);
                    // Try to remove files from disk (ignore failures)
                    foreach ($images as $img) {
                        if (!empty($img['image_url'])) {
                            $path = __DIR__ . '/../../' . $img['image_url'];
                            if (is_file($path)) { @unlink($path); }
                        }
                    }

                    // 2) Handle variants and dependent rows
                    $variantIds = [];
                    if (isset($variantModel) && $variantModel) {
                        $stmt = $db->prepare("SELECT variant_id FROM product_variants WHERE product_id = :pid");
                        $stmt->execute([':pid' => $product_id]);
                        $variantIds = array_map(function($r){ return $r['variant_id']; }, $stmt->fetchAll(PDO::FETCH_ASSOC));

                        if (count($variantIds) > 0) {
                            // Check if any variants are referenced by order_items
                            $placeholders = implode(',', array_fill(0, count($variantIds), '?'));
                            $check = $db->prepare("SELECT COUNT(*) AS cnt FROM order_items WHERE variant_id IN ($placeholders)");
                            $check->execute($variantIds);
                            $cnt = (int)$check->fetch(PDO::FETCH_ASSOC)['cnt'];
                            if ($cnt > 0) {
                                // Abort deletion if there are historical orders
                                $db->rollBack();
                                throw new Exception('Cannot delete this product because some variants are used in existing orders.');
                            }

                            // Delete cart_items for these variants
                            $delCart = $db->prepare("DELETE FROM cart_items WHERE variant_id IN ($placeholders)");
                            $delCart->execute($variantIds);

                            // Delete variants
                            $delVar = $db->prepare("DELETE FROM product_variants WHERE product_id = :pid");
                            $delVar->execute([':pid' => $product_id]);
                        }
                    }

                    // 3) Delete product-level colors
                    $delColors = $db->prepare("DELETE FROM product_colors WHERE product_id = :pid");
                    $delColors->execute([':pid' => $product_id]);

                    // 4) Finally delete product
                    if (!$productModel->delete($product_id)) {
                        $db->rollBack();
                        throw new Exception('Failed to delete product');
                    }

                    $db->commit();
                    $response = ['success' => true, 'message' => 'Product deleted successfully'];
                } catch (Exception $ex) {
                    if ($db->inTransaction()) { $db->rollBack(); }
                    throw $ex;
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
                // include variants list
                if (isset($variantModel) && $variantModel) {
                    try {
                        $product['variants'] = $variantModel->getByProductId($product_id);
                    } catch (Exception $e) {
                        $product['variants'] = [];
                    }
                }
                $response = ['success' => true, 'data' => $product];
            } else {
                throw new Exception('Product not found');
            }
            break;
            
        case 'list':
            $page = $_GET['page'] ?? 1;
            $limit = 10;
            $offset = ($page - 1) * $limit;
            $search = $_GET['search'] ?? '';
            $category_id = $_GET['category_id'] ?? '';
            
            // Always use search methods for consistency, even when no search query
            $products = $productModel->searchProducts($search, $category_id, $limit, $offset);
            $total = $productModel->getSearchCount($search, $category_id);
            
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
        
        // Variant CRUD endpoints
        case 'variants':
            $product_id = $_GET['product_id'] ?? '';
            if (empty($product_id)) { throw new Exception('Product ID is required'); }
            if (!$variantModel) { throw new Exception('Variant model not available'); }
            $variants = $variantModel->getByProductId($product_id);
            $response = ['success' => true, 'data' => $variants];
            break;

        case 'variant_create':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                if (!$variantModel) { throw new Exception('Variant model not available'); }
                $product_id = $_POST['product_id'] ?? '';
                $size = trim($_POST['size'] ?? '');
                $price = isset($_POST['price']) && $_POST['price'] !== '' ? (float)$_POST['price'] : 0;
                $stock = isset($_POST['stock']) ? (int)$_POST['stock'] : 0;
                if (empty($product_id) || $size === '') { throw new Exception('Product ID and size are required'); }
                if ($variantModel->create($product_id, $size, $price, $stock)) {
                    $response = ['success' => true, 'message' => 'Variant created', 'variant_id' => $db->lastInsertId()];
                } else {
                    throw new Exception('Failed to create variant');
                }
            }
            break;

        case 'variant_update':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                if (!$variantModel) { throw new Exception('Variant model not available'); }
                $variant_id = $_POST['variant_id'] ?? '';
                $size = trim($_POST['size'] ?? '');
                $price = isset($_POST['price']) && $_POST['price'] !== '' ? (float)$_POST['price'] : 0;
                $stock = isset($_POST['stock']) ? (int)$_POST['stock'] : 0;
                if (empty($variant_id) || $size === '') { throw new Exception('Variant ID and size are required'); }
                if ($variantModel->update($variant_id, $size, $price, $stock)) {
                    $response = ['success' => true, 'message' => 'Variant updated'];
                } else {
                    throw new Exception('Failed to update variant');
                }
            }
            break;

        case 'variant_delete':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                if (!$variantModel) { throw new Exception('Variant model not available'); }
                $variant_id = $_POST['variant_id'] ?? '';
                if (empty($variant_id)) { throw new Exception('Variant ID is required'); }
                if ($variantModel->delete($variant_id)) {
                    $response = ['success' => true, 'message' => 'Variant deleted'];
                } else {
                    throw new Exception('Failed to delete variant');
                }
            }
            break;

        // Product-level color endpoints (per product, independent of variants)
        case 'product_colors':
            $product_id = $_GET['product_id'] ?? '';
            if (empty($product_id)) { throw new Exception('Product ID is required'); }
            $stmt = $db->prepare("SELECT color_id, color_name FROM product_colors WHERE product_id = :pid ORDER BY color_id ASC");
            $stmt->execute([':pid' => $product_id]);
            $colors = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $response = ['success' => true, 'data' => $colors];
            break;

        case 'product_color_create':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $product_id = $_POST['product_id'] ?? '';
                $name = trim($_POST['color_name'] ?? '');
                if (empty($product_id) || $name === '') { throw new Exception('Product ID and color name are required'); }
                $stmt = $db->prepare("INSERT INTO product_colors (product_id, color_name) VALUES (:pid, :name)");
                if ($stmt->execute([':pid' => $product_id, ':name' => $name])) {
                    $response = ['success' => true, 'message' => 'Color added', 'color_id' => $db->lastInsertId()];
                } else {
                    throw new Exception('Failed to add color');
                }
            }
            break;

        case 'product_color_update':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $id = $_POST['color_id'] ?? '';
                $name = trim($_POST['color_name'] ?? '');
                if (empty($id) || $name === '') { throw new Exception('Color ID and name are required'); }
                $stmt = $db->prepare("UPDATE product_colors SET color_name = :name WHERE color_id = :id");
                if ($stmt->execute([':name' => $name, ':id' => $id])) {
                    $response = ['success' => true, 'message' => 'Color updated'];
                } else {
                    throw new Exception('Failed to update color');
                }
            }
            break;

        case 'product_color_delete':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $id = $_POST['color_id'] ?? '';
                if (empty($id)) { throw new Exception('Color ID is required'); }
                $stmt = $db->prepare("DELETE FROM product_colors WHERE color_id = :id");
                if ($stmt->execute([':id' => $id])) {
                    $response = ['success' => true, 'message' => 'Color deleted'];
                } else {
                    throw new Exception('Failed to delete color');
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