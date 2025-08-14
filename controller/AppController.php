<?php
/**
 * Main Application Controller
 * Provides unified interface for all database operations
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../models/CategoryModel.php';
require_once __DIR__ . '/../models/ProductModel.php';
require_once __DIR__ . '/../models/OrderModel.php';
require_once __DIR__ . '/../models/CartModel.php';

class AppController {
    private $db;
    private $userModel;
    private $categoryModel;
    private $productModel;
    private $orderModel;
    private $cartModel;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        
        if ($this->db) {
            $this->userModel = new UserModel($this->db);
            $this->categoryModel = new CategoryModel($this->db);
            $this->productModel = new ProductModel($this->db);
            $this->orderModel = new OrderModel($this->db);
            $this->cartModel = new CartModel($this->db);
        }
    }

    // Database connection check
    public function isConnected() {
        return $this->db !== null;
    }

    // User Management Methods
    public function createUser($userData) {
        if (!$this->isConnected()) return false;
        
        // Extract data from array
        $username = $userData['username'];
        $password = $userData['password'];
        $email = $userData['email'];
        $full_name = $userData['full_name'] ?? null;
        $phone = $userData['phone'] ?? null;
        $address = $userData['address'] ?? null;
        $role = $userData['role'] ?? 'user';
        
        // Check if username or email already exists
        if ($this->userModel->usernameExists($username)) {
            throw new Exception("Username already exists");
        }
        
        if ($this->userModel->emailExists($email)) {
            throw new Exception("Email already exists");
        }
        
        return $this->userModel->create($username, $password, $email, $full_name, $phone, $address, $role);
    }

    public function loginUser($username, $password) {
        if (!$this->isConnected()) return false;
        return $this->userModel->verifyLogin($username, $password);
    }

    public function verifyUserLogin($username, $password) {
        if (!$this->isConnected()) return false;
        return $this->userModel->verifyLogin($username, $password);
    }

    public function usernameExists($username) {
        if (!$this->isConnected()) return false;
        return $this->userModel->usernameExists($username);
    }

    public function isUsernameExists($username) {
        if (!$this->isConnected()) return false;
        return $this->userModel->usernameExists($username);
    }

    public function emailExists($email) {
        if (!$this->isConnected()) return false;
        return $this->userModel->emailExists($email);
    }

    public function isEmailExists($email) {
        if (!$this->isConnected()) return false;
        return $this->userModel->emailExists($email);
    }

    public function getUserById($user_id) {
        if (!$this->isConnected()) return false;
        return $this->userModel->getById($user_id);
    }

    public function getUserOrders($user_id) {
        if (!$this->isConnected()) return false;
        return $this->orderModel->getByUser($user_id);
    }

    public function getAllUsers($limit = null, $offset = null) {
        if (!$this->isConnected()) return false;
        return $this->userModel->getAll($limit, $offset);
    }

    public function updateUser($user_id, $data) {
        if (!$this->isConnected()) return false;
        return $this->userModel->update($user_id, $data);
    }

    public function deleteUser($user_id) {
        if (!$this->isConnected()) return false;
        return $this->userModel->delete($user_id);
    }

    public function isUserActive($user_id) {
        if (!$this->isConnected()) return false;
        return $this->userModel->isActive($user_id);
    }

    public function getUsersByStatus($status, $limit = null, $offset = null) {
        if (!$this->isConnected()) return false;
        return $this->userModel->getByStatus($status, $limit, $offset);
    }

    // Category Management Methods
    public function createCategory($category_name, $description = null) {
        if (!$this->isConnected()) return false;
        
        if ($this->categoryModel->nameExists($category_name)) {
            throw new Exception("Category name already exists");
        }
        
        return $this->categoryModel->create($category_name, $description);
    }

    public function getCategoryById($category_id) {
        if (!$this->isConnected()) return false;
        return $this->categoryModel->getById($category_id);
    }

    public function getAllCategories() {
        if (!$this->isConnected()) return false;
        return $this->categoryModel->getAll();
    }

    public function getCategoriesWithProductCount() {
        if (!$this->isConnected()) return false;
        return $this->categoryModel->getWithProductCount();
    }

    public function updateCategory($category_id, $category_name, $description = null) {
        if (!$this->isConnected()) return false;
        return $this->categoryModel->update($category_id, $category_name, $description);
    }

    public function deleteCategory($category_id) {
        if (!$this->isConnected()) return false;
        return $this->categoryModel->delete($category_id);
    }

    // Product Management Methods
    public function createProduct($category_id, $product_name, $description, $price, $stock = 0) {
        if (!$this->isConnected()) return false;
        return $this->productModel->create($category_id, $product_name, $description, $price, $stock);
    }

    public function getProductById($product_id) {
        if (!$this->isConnected()) return false;
        return $this->productModel->getById($product_id);
    }

    public function getAllProducts($limit = null, $offset = null, $category_id = null) {
        if (!$this->isConnected()) return false;
        return $this->productModel->getAll($limit, $offset, $category_id);
    }

    public function getProductsByCategory($category_id, $limit = null, $offset = null) {
        if (!$this->isConnected()) return false;
        return $this->productModel->getByCategory($category_id, $limit, $offset);
    }

    public function searchProducts($search_term, $limit = null, $offset = null) {
        if (!$this->isConnected()) return false;
        return $this->productModel->search($search_term, $limit, $offset);
    }

    public function updateProduct($product_id, $data) {
        if (!$this->isConnected()) return false;
        return $this->productModel->update($product_id, $data);
    }

    public function deleteProduct($product_id) {
        if (!$this->isConnected()) return false;
        return $this->productModel->delete($product_id);
    }

    public function getNewProducts($limit = 10) {
        if (!$this->isConnected()) return false;
        return $this->productModel->getNewProducts($limit);
    }

    public function getLowStockProducts($threshold = 10) {
        if (!$this->isConnected()) return false;
        return $this->productModel->getLowStock($threshold);
    }

    // Order Management Methods
    public function createOrder($user_id, $total_amount, $status = 'Chờ xác nhận', $payment_status = 'Chưa thanh toán') {
        if (!$this->isConnected()) return false;
        return $this->orderModel->create($user_id, $total_amount, $status, $payment_status);
    }

    public function getOrderById($order_id) {
        if (!$this->isConnected()) return false;
        return $this->orderModel->getById($order_id);
    }

    public function getOrdersByUser($user_id, $limit = null, $offset = null) {
        if (!$this->isConnected()) return false;
        return $this->orderModel->getByUser($user_id, $limit, $offset);
    }

    public function getAllOrders($limit = null, $offset = null, $status = null) {
        if (!$this->isConnected()) return false;
        return $this->orderModel->getAll($limit, $offset, $status);
    }

    public function updateOrderStatus($order_id, $status) {
        if (!$this->isConnected()) return false;
        return $this->orderModel->updateStatus($order_id, $status);
    }

    public function updateOrderPaymentStatus($order_id, $payment_status) {
        if (!$this->isConnected()) return false;
        return $this->orderModel->updatePaymentStatus($order_id, $payment_status);
    }

    public function getOrderStatistics() {
        if (!$this->isConnected()) return false;
        return $this->orderModel->getStatistics();
    }

    public function getRecentOrders($limit = 10) {
        if (!$this->isConnected()) return false;
        return $this->orderModel->getRecentOrders($limit);
    }

    public function getMonthlyRevenueReport($months = 12) {
        if (!$this->isConnected()) return [];
        return $this->orderModel->getMonthlyRevenueAndCount($months);
    }

    // Cart Management Methods
    public function getOrCreateCart($user_id) {
        if (!$this->isConnected()) return false;
        return $this->cartModel->getOrCreateCart($user_id);
    }

    public function getCartWithItems($user_id) {
        if (!$this->isConnected()) return false;
        return $this->cartModel->getCartWithItems($user_id);
    }

    public function addToCart($user_id, $product_id, $quantity = 1) {
        if (!$this->isConnected()) return false;
        
        $cart = $this->cartModel->getOrCreateCart($user_id);
        if (!$cart) return false;
        
        return $this->cartModel->addItem($cart['cart_id'], $product_id, $quantity);
    }

    public function updateCartItemQuantity($cart_item_id, $quantity) {
        if (!$this->isConnected()) return false;
        return $this->cartModel->updateItemQuantity($cart_item_id, $quantity);
    }

    public function removeFromCart($cart_item_id) {
        if (!$this->isConnected()) return false;
        return $this->cartModel->removeItem($cart_item_id);
    }

    public function clearCart($user_id) {
        if (!$this->isConnected()) return false;
        
        $cart = $this->cartModel->getByUser($user_id);
        if (!$cart) return false;
        
        return $this->cartModel->clearCart($cart['cart_id']);
    }

    public function getCartTotal($user_id) {
        if (!$this->isConnected()) return false;
        
        $cart = $this->cartModel->getByUser($user_id);
        if (!$cart) return 0;
        
        return $this->cartModel->getCartTotal($cart['cart_id']);
    }

    public function getCartItemCount($user_id) {
        if (!$this->isConnected()) return false;
        
        $cart = $this->cartModel->getByUser($user_id);
        if (!$cart) return 0;
        
        return $this->cartModel->getCartItemCount($cart['cart_id']);
    }

    // Statistics Methods
    public function getDashboardStatistics() {
        if (!$this->isConnected()) return false;
        
        return [
            'users' => $this->userModel->getCount(),
            'categories' => $this->categoryModel->getCount(),
            'products' => $this->productModel->getCount(),
            'orders' => $this->orderModel->getStatistics(),
            'low_stock_products' => count($this->productModel->getLowStock()),
            'recent_orders' => $this->orderModel->getRecentOrders(5)
        ];
    }

    // Validation Methods
    public function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    public function validatePassword($password) {
        return strlen($password) >= 6;
    }

    public function validatePrice($price) {
        return is_numeric($price) && $price >= 0;
    }

    public function validateStock($stock) {
        return is_numeric($stock) && $stock >= 0;
    }

    // Error handling
    public function getLastError() {
        return $this->db ? null : "Database connection failed";
    }
}
?> 