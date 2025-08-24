<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/UserModel.php';

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Initialize model
$userModel = $db ? new UserModel($db) : null;

// Ensure status column exists (active/inactive/pending)
function ensureStatusColumn($db) {
    try {
        $stmt = $db->query("SHOW COLUMNS FROM users LIKE 'status'");
        $exists = $stmt && $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$exists) {
            $db->exec("ALTER TABLE users ADD COLUMN status ENUM('active','inactive','pending') DEFAULT 'active'");
        }
    } catch (Exception $e) {
        // ignore if cannot alter; will fallback gracefully
    }
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$response = ['success' => false, 'message' => ''];

// Check if database connection is available
if (!$db || !$userModel) {
    $response = ['success' => false, 'message' => 'Database connection failed'];
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

try {
    switch ($action) {
        case 'create':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $username = $_POST['username'] ?? '';
                $password = $_POST['password'] ?? '';
                $email = $_POST['email'] ?? '';
                $full_name = $_POST['full_name'] ?? '';
                $phone = $_POST['phone'] ?? '';
                $address = $_POST['address'] ?? '';
                $role = $_POST['role'] ?? 'user';
                
                if (empty($username) || empty($password) || empty($email)) {
                    throw new Exception('Username, password and email are required');
                }
                
                // Check if username already exists
                if ($userModel->usernameExists($username)) {
                    throw new Exception('Username already exists');
                }
                
                // Check if email already exists
                if ($userModel->emailExists($email)) {
                    throw new Exception('Email already exists');
                }
                
                if ($userModel->create($username, $password, $email, $full_name, $phone, $address, $role)) {
                    $response = ['success' => true, 'message' => 'User created successfully'];
                } else {
                    throw new Exception('Failed to create user');
                }
            }
            break;
            
        case 'update':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $user_id = $_POST['user_id'] ?? '';
                $username = $_POST['username'] ?? '';
                $email = $_POST['email'] ?? '';
                $full_name = $_POST['full_name'] ?? '';
                $phone = $_POST['phone'] ?? '';
                $address = $_POST['address'] ?? '';
                $role = $_POST['role'] ?? 'user';
                
                if (empty($user_id) || empty($username) || empty($email)) {
                    throw new Exception('User ID, username and email are required');
                }
                
                // Check if username already exists (excluding current user)
                if ($userModel->usernameExists($username, $user_id)) {
                    throw new Exception('Username already exists');
                }
                
                // Check if email already exists (excluding current user)
                if ($userModel->emailExists($email, $user_id)) {
                    throw new Exception('Email already exists');
                }
                
                $data = [
                    'username' => $username,
                    'email' => $email,
                    'full_name' => $full_name,
                    'phone' => $phone,
                    'address' => $address,
                    'role' => $role
                ];
                
                if ($userModel->update($user_id, $data)) {
                    $response = ['success' => true, 'message' => 'User updated successfully'];
                } else {
                    throw new Exception('Failed to update user');
                }
            }
            break;
            
        case 'update_password':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $user_id = $_POST['user_id'] ?? '';
                $new_password = $_POST['new_password'] ?? '';
                
                if (empty($user_id) || empty($new_password)) {
                    throw new Exception('User ID and new password are required');
                }
                
                if ($userModel->updatePassword($user_id, $new_password)) {
                    $response = ['success' => true, 'message' => 'Password updated successfully'];
                } else {
                    throw new Exception('Failed to update password');
                }
            }
            break;
            
        case 'delete':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $user_id = $_POST['user_id'] ?? '';
                
                if (empty($user_id)) {
                    throw new Exception('User ID is required');
                }
                
                // Prevent deleting admin users
                $user = $userModel->getById($user_id);
                if ($user && $user['role'] === 'admin') {
                    throw new Exception('Cannot delete admin users');
                }
                
                // Check what data will be deleted
                $orderCount = 0;
                $cartCount = 0;
                try {
                    $stmt = $db->prepare("SELECT COUNT(*) as count FROM orders WHERE user_id = :user_id");
                    $stmt->execute([':user_id' => $user_id]);
                    $orderCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
                    
                    $stmt = $db->prepare("SELECT COUNT(*) as count FROM cart WHERE user_id = :user_id");
                    $stmt->execute([':user_id' => $user_id]);
                    $cartCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
                } catch (Exception $e) {
                    // Ignore errors in counting
                }
                
                // Start transaction for safe deletion
                $db->beginTransaction();
                try {
                    // Delete cart items first
                    $stmt = $db->prepare("DELETE ci FROM cart_items ci INNER JOIN cart c ON ci.cart_id = c.cart_id WHERE c.user_id = :user_id");
                    $stmt->execute([':user_id' => $user_id]);
                    
                    // Delete cart
                    $stmt = $db->prepare("DELETE FROM cart WHERE user_id = :user_id");
                    $stmt->execute([':user_id' => $user_id]);
                    
                    // Delete order items first
                    $stmt = $db->prepare("DELETE oi FROM order_items oi INNER JOIN orders o ON oi.order_id = o.order_id WHERE o.user_id = :user_id");
                    $stmt->execute([':user_id' => $user_id]);
                    
                    // Delete orders
                    $stmt = $db->prepare("DELETE FROM orders WHERE user_id = :user_id");
                    $stmt->execute([':user_id' => $user_id]);
                    
                    // Now delete the user
                    if ($userModel->delete($user_id)) {
                        $db->commit();
                        
                        // Get updated counts after deletion
                        ensureStatusColumn($db);
                        $counts = ['total' => (int)$userModel->getCount(), 'active' => 0, 'inactive' => 0, 'pending' => 0];
                        try {
                            $q = $db->query("SELECT COALESCE(status,'active') as status, COUNT(*) as c FROM users GROUP BY COALESCE(status,'active')");
                            while ($row = $q->fetch(PDO::FETCH_ASSOC)) {
                                $counts[strtolower($row['status'])] = (int)$row['c'];
                            }
                        } catch (Exception $e) {}
                        
                        $deletedInfo = [];
                        if ($orderCount > 0) $deletedInfo[] = "$orderCount order(s)";
                        if ($cartCount > 0) $deletedInfo[] = "$cartCount cart item(s)";
                        
                        $message = 'User deleted successfully';
                        if (!empty($deletedInfo)) {
                            $message .= '. Also deleted: ' . implode(', ', $deletedInfo);
                        }
                        
                        $response = ['success' => true, 'message' => $message, 'status_counts' => $counts];
                    } else {
                        throw new Exception('Failed to delete user');
                    }
                } catch (Exception $e) {
                    $db->rollBack();
                    throw new Exception('Failed to delete user: ' . $e->getMessage());
                }
            }
            break;
            
        case 'get':
            $user_id = $_GET['user_id'] ?? '';
            
            if (empty($user_id)) {
                throw new Exception('User ID is required');
            }
            
            $user = $userModel->getById($user_id);
            if ($user) {
                // Remove password from response
                unset($user['password']);
                $response = ['success' => true, 'data' => $user];
            } else {
                throw new Exception('User not found');
            }
            break;
            
        case 'list':
            $page = $_GET['page'] ?? 1;
            $limit = 10;
            $offset = ($page - 1) * $limit;
            $search = $_GET['search'] ?? '';
            $role = $_GET['role'] ?? '';
            
            // Always use search methods for consistency, even when no search query
            $users = $userModel->searchUsers($search, $role, $limit, $offset);
            $total = $userModel->getSearchCount($search, $role);
            
            // Remove passwords from response
            foreach ($users as &$user) {
                unset($user['password']);
            }
            
            // status counts across entire table
            ensureStatusColumn($db);
            $statusCounts = ['total' => (int)$total, 'active' => 0, 'inactive' => 0, 'pending' => 0];
            try {
                $q = $db->query("SELECT COALESCE(status,'active') as status, COUNT(*) as c FROM users GROUP BY COALESCE(status,'active')");
                while ($row = $q->fetch(PDO::FETCH_ASSOC)) {
                    $key = strtolower($row['status']);
                    $statusCounts[$key] = (int)$row['c'];
                }
            } catch (Exception $e) {
                // fallback: all active
                $statusCounts['active'] = (int)$total;
            }
            $response = [
                'success' => true, 
                'data' => $users,
                'total' => $total,
                'pages' => ceil($total / $limit),
                'current_page' => $page,
                'status_counts' => $statusCounts
            ];
            break;

        case 'update_status':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $user_id = $_POST['user_id'] ?? '';
                $status = $_POST['status'] ?? '';
                if (empty($user_id) || !in_array($status, ['active','inactive','pending'])) {
                    throw new Exception('Invalid user ID or status');
                }
                // Update status column if exists
                ensureStatusColumn($db);
                $stmt = $db->prepare("UPDATE users SET status = :status WHERE user_id = :user_id");
                if (!$stmt->execute([':status' => $status, ':user_id' => $user_id])) {
                    throw new Exception('Failed to update status');
                }
                // Return updated counts
                $counts = ['total' => (int)$userModel->getCount(), 'active' => 0, 'inactive' => 0, 'pending' => 0];
                $q = $db->query("SELECT COALESCE(status,'active') as status, COUNT(*) as c FROM users GROUP BY COALESCE(status,'active')");
                while ($row = $q->fetch(PDO::FETCH_ASSOC)) {
                    $counts[strtolower($row['status'])] = (int)$row['c'];
                }
                $response = ['success' => true, 'message' => 'Status updated', 'status_counts' => $counts];
            }
            break;

        case 'delete':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $user_id = $_POST['user_id'] ?? '';
                if (empty($user_id)) {
                    throw new Exception('User ID is required');
                }
                
                $user = $userModel->getById($user_id);
                if ($user && $user['role'] === 'admin') {
                    throw new Exception('Cannot delete admin users');
                }
                
                // Check what data will be deleted
                $orderCount = 0;
                $cartCount = 0;
                try {
                    $stmt = $db->prepare("SELECT COUNT(*) as count FROM orders WHERE user_id = :user_id");
                    $stmt->execute([':user_id' => $user_id]);
                    $orderCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
                    
                    $stmt = $db->prepare("SELECT COUNT(*) as count FROM cart WHERE user_id = :user_id");
                    $stmt->execute([':user_id' => $user_id]);
                    $cartCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
                } catch (Exception $e) {
                    // Ignore errors in counting
                }
                
                // Start transaction for safe deletion
                $db->beginTransaction();
                try {
                    // Delete cart items first
                    $stmt = $db->prepare("DELETE ci FROM cart_items ci INNER JOIN cart c ON ci.cart_id = c.cart_id WHERE c.user_id = :user_id");
                    $stmt->execute([':user_id' => $user_id]);
                    
                    // Delete cart
                    $stmt = $db->prepare("DELETE FROM cart WHERE user_id = :user_id");
                    $stmt->execute([':user_id' => $user_id]);
                    
                    // Delete order items first
                    $stmt = $db->prepare("DELETE oi FROM order_items oi INNER JOIN orders o ON oi.order_id = o.order_id WHERE o.user_id = :user_id");
                    $stmt->execute([':user_id' => $user_id]);
                    
                    // Delete orders
                    $stmt = $db->prepare("DELETE FROM orders WHERE user_id = :user_id");
                    $stmt->execute([':user_id' => $user_id]);
                    
                    // Now delete the user
                    if ($userModel->delete($user_id)) {
                        $db->commit();
                        
                        // Get updated counts after deletion
                        ensureStatusColumn($db);
                        $counts = ['total' => (int)$userModel->getCount(), 'active' => 0, 'inactive' => 0, 'pending' => 0];
                        try {
                            $q = $db->query("SELECT COALESCE(status,'active') as status, COUNT(*) as c FROM users GROUP BY COALESCE(status,'active')");
                            while ($row = $q->fetch(PDO::FETCH_ASSOC)) {
                                $counts[strtolower($row['status'])] = (int)$row['c'];
                            }
                        } catch (Exception $e) {}
                        
                        $deletedInfo = [];
                        if ($orderCount > 0) $deletedInfo[] = "$orderCount order(s)";
                        if ($cartCount > 0) $deletedInfo[] = "$cartCount cart item(s)";
                        
                        $message = 'User deleted successfully';
                        if (!empty($deletedInfo)) {
                            $message .= '. Also deleted: ' . implode(', ', $deletedInfo);
                        }
                        
                        $response = ['success' => true, 'message' => $message, 'status_counts' => $counts];
                    } else {
                        throw new Exception('Failed to delete user');
                    }
                } catch (Exception $e) {
                    $db->rollBack();
                    throw new Exception('Failed to delete user: ' . $e->getMessage());
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