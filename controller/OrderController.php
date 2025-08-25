<?php
/**
 * Order Controller Class
 * Handles all order-related operations
 */

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/OrderModel.php';

class OrderController extends BaseController {
    private $orderModel;

    public function __construct() {
        parent::__construct();
        if ($this->isConnected()) {
            $this->orderModel = new OrderModel($this->db);
        }
    }

    public function createOrder($user_id, $total_amount, $status = 'Chờ xác nhận', $payment_status = 'Chưa thanh toán') {
        if (!$this->isConnected()) return false;
        return $this->orderModel->create($user_id, $total_amount, $status, $payment_status);
    }

    public function getOrderById($order_id) {
        if (!$this->isConnected()) return false;
        return $this->orderModel->getById($order_id);
    }

    public function getOrderItems($order_id) {
        if (!$this->isConnected()) return false;
        return $this->orderModel->getItemsByOrder($order_id);
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

    public function cancelUserOrder($user_id, $order_id) {
        if (!$this->isConnected()) return false;
        $order = $this->orderModel->getById($order_id);
        if (!$order) return false;
        if ((int)$order['user_id'] !== (int)$user_id) return false;
        if ($order['status'] !== 'Chờ xác nhận') return false;
        return $this->orderModel->updateStatus($order_id, 'Đã hủy');
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
}
?>
