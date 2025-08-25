<?php
/**
 * Main Application Controller
 * Acts as a facade that combines all separate controllers
 * Maintains the same interface for backward compatibility
 */

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/UserController.php';
require_once __DIR__ . '/CategoryController.php';
require_once __DIR__ . '/ProductController.php';
require_once __DIR__ . '/OrderController.php';
require_once __DIR__ . '/CartController.php';

class AppController extends BaseController {
    private $userController;
    private $categoryController;
    private $productController;
    private $orderController;
    private $cartController;

    public function __construct() {
        parent::__construct();
        
        if ($this->isConnected()) {
            $this->userController = new UserController();
            $this->categoryController = new CategoryController();
            $this->productController = new ProductController();
            $this->orderController = new OrderController();
            $this->cartController = new CartController();
        }
    }

    // User Management Methods - Delegated to UserController
    public function createUser($userData) {
        return $this->userController->createUser($userData);
    }

    public function loginUser($username, $password) {
        return $this->userController->loginUser($username, $password);
    }

    public function verifyUserLogin($username, $password) {
        return $this->userController->verifyUserLogin($username, $password);
    }

    public function usernameExists($username) {
        return $this->userController->usernameExists($username);
    }

    public function isUsernameExists($username) {
        return $this->userController->isUsernameExists($username);
    }

    public function emailExists($email) {
        return $this->userController->emailExists($email);
    }

    public function isEmailExists($email) {
        return $this->userController->isEmailExists($email);
    }

    public function getUserById($user_id) {
        return $this->userController->getUserById($user_id);
    }

    public function getUserOrders($user_id) {
        return $this->orderController->getOrdersByUser($user_id);
    }

    public function getAllUsers($limit = null, $offset = null) {
        return $this->userController->getAllUsers($limit, $offset);
    }

    public function updateUser($user_id, $data) {
        return $this->userController->updateUser($user_id, $data);
    }

    public function deleteUser($user_id) {
        return $this->userController->deleteUser($user_id);
    }

    public function isUserActive($user_id) {
        return $this->userController->isUserActive($user_id);
    }

    public function getUsersByStatus($status, $limit = null, $offset = null) {
        return $this->userController->getUsersByStatus($status, $limit, $offset);
    }

    // Category Management Methods - Delegated to CategoryController
    public function createCategory($category_name, $description = null) {
        return $this->categoryController->createCategory($category_name, $description);
    }

    public function getCategoryById($category_id) {
        return $this->categoryController->getCategoryById($category_id);
    }

    public function getAllCategories() {
        return $this->categoryController->getAllCategories();
    }

    public function getCategoriesWithProductCount() {
        return $this->categoryController->getCategoriesWithProductCount();
    }

    public function updateCategory($category_id, $category_name, $description = null) {
        return $this->categoryController->updateCategory($category_id, $category_name, $description);
    }

    public function deleteCategory($category_id) {
        return $this->categoryController->deleteCategory($category_id);
    }

    // Product Management Methods - Delegated to ProductController
    public function createProduct($category_id, $product_name, $description) {
        return $this->productController->createProduct($category_id, $product_name, $description);
    }

    public function getProductById($product_id) {
        return $this->productController->getProductById($product_id);
    }

    public function getAllProducts($limit = null, $offset = null, $category_id = null) {
        return $this->productController->getAllProducts($limit, $offset, $category_id);
    }

    public function getProductsByCategory($category_id, $limit = null, $offset = null) {
        return $this->productController->getProductsByCategory($category_id, $limit, $offset);
    }

    public function searchProducts($search_term, $limit = null, $offset = null) {
        return $this->productController->searchProducts($search_term, $limit, $offset);
    }

    public function updateProduct($product_id, $data) {
        return $this->productController->updateProduct($product_id, $data);
    }

    public function deleteProduct($product_id) {
        return $this->productController->deleteProduct($product_id);
    }

    public function getNewProducts($limit = 10) {
        return $this->productController->getNewProducts($limit);
    }

    public function getLowStockProducts($threshold = 10) {
        return $this->productController->getLowStockProducts($threshold);
    }

    // Order Management Methods - Delegated to OrderController
    public function createOrder($user_id, $total_amount, $status = 'Chờ xác nhận', $payment_status = 'Chưa thanh toán') {
        return $this->orderController->createOrder($user_id, $total_amount, $status, $payment_status);
    }

    public function getOrderById($order_id) {
        return $this->orderController->getOrderById($order_id);
    }

    public function getOrderItems($order_id) {
        return $this->orderController->getOrderItems($order_id);
    }

    public function getOrdersByUser($user_id, $limit = null, $offset = null) {
        return $this->orderController->getOrdersByUser($user_id, $limit, $offset);
    }

    public function getAllOrders($limit = null, $offset = null, $status = null) {
        return $this->orderController->getAllOrders($limit, $offset, $status);
    }

    public function updateOrderStatus($order_id, $status) {
        return $this->orderController->updateOrderStatus($order_id, $status);
    }

    public function updateOrderPaymentStatus($order_id, $payment_status) {
        return $this->orderController->updateOrderPaymentStatus($order_id, $payment_status);
    }

    public function cancelUserOrder($user_id, $order_id) {
        return $this->orderController->cancelUserOrder($user_id, $order_id);
    }

    public function getOrderStatistics() {
        return $this->orderController->getOrderStatistics();
    }

    public function getRecentOrders($limit = 10) {
        return $this->orderController->getRecentOrders($limit);
    }

    public function getMonthlyRevenueReport($months = 12) {
        return $this->orderController->getMonthlyRevenueReport($months);
    }

    // Cart Management Methods - Delegated to CartController
    public function getOrCreateCart($user_id) {
        return $this->cartController->getOrCreateCart($user_id);
    }

    public function getCartWithItems($user_id) {
        return $this->cartController->getCartWithItems($user_id);
    }

    public function addToCart($user_id, $variant_id, $quantity = 1, $color_name = null) {
        return $this->cartController->addToCart($user_id, $variant_id, $quantity, $color_name);
    }

    public function updateCartItemQuantity($cart_item_id, $quantity) {
        return $this->cartController->updateCartItemQuantity($cart_item_id, $quantity);
    }

    public function removeFromCart($cart_item_id) {
        return $this->cartController->removeFromCart($cart_item_id);
    }

    public function clearCart($user_id) {
        return $this->cartController->clearCart($user_id);
    }

    public function getCartTotal($user_id) {
        return $this->cartController->getCartTotal($user_id);
    }

    public function getCartItemCount($user_id) {
        return $this->cartController->getCartItemCount($user_id);
    }

    public function checkoutCart($user_id, $payment_method = 'COD', $markPaid = false, $discounted_total = null) {
        return $this->cartController->checkoutCart($user_id, $payment_method, $markPaid, $discounted_total);
    }

    // Statistics Methods
    public function getDashboardStatistics() {
        if (!$this->isConnected()) return false;
        
        return [
            'users' => $this->userController->getUsersCount(),
            'categories' => $this->categoryController->getCategoriesCount(),
            'products' => $this->productController->getProductsCount(),
            'orders' => $this->orderController->getOrderStatistics(),
            'low_stock_products' => count($this->productController->getLowStockProducts()),
            'recent_orders' => $this->orderController->getRecentOrders(5)
        ];
    }

    // Search suggestions for autocomplete
    public function getSearchSuggestions($query) {
        return $this->productController->getSearchSuggestions($query);
    }
}
?> 