<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/OrderModel.php';

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Initialize model
$orderModel = $db ? new OrderModel($db) : null;

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$response = ['success' => false, 'message' => ''];

// Check if database connection is available
if (!$db || !$orderModel) {
    $response = ['success' => false, 'message' => 'Database connection failed'];
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

try {
    switch ($action) {
        case 'update_status':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $order_id = $_POST['order_id'] ?? '';
                $status = $_POST['status'] ?? '';
                
                if (empty($order_id) || empty($status)) {
                    throw new Exception('Order ID and status are required');
                }
                
                $valid_statuses = ['Chờ xác nhận', 'Đã xác nhận', 'Đang giao', 'Đã giao', 'Đã hủy'];
                if (!in_array($status, $valid_statuses)) {
                    throw new Exception('Invalid status');
                }
                
                if ($orderModel->updateStatus($order_id, $status)) {
                    $response = ['success' => true, 'message' => 'Order status updated successfully'];
                } else {
                    throw new Exception('Failed to update order status');
                }
            }
            break;
            
        case 'update_payment_status':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $order_id = $_POST['order_id'] ?? '';
                $payment_status = $_POST['payment_status'] ?? '';
                
                if (empty($order_id) || empty($payment_status)) {
                    throw new Exception('Order ID and payment status are required');
                }
                
                $valid_payment_statuses = ['Chưa thanh toán', 'Đã thanh toán', 'Hoàn tiền'];
                if (!in_array($payment_status, $valid_payment_statuses)) {
                    throw new Exception('Invalid payment status');
                }
                
                if ($orderModel->updatePaymentStatus($order_id, $payment_status)) {
                    $response = ['success' => true, 'message' => 'Payment status updated successfully'];
                } else {
                    throw new Exception('Failed to update payment status');
                }
            }
            break;
            
        case 'delete':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $order_id = $_POST['order_id'] ?? '';
                
                if (empty($order_id)) {
                    throw new Exception('Order ID is required');
                }
                // Only allow deleting orders that are already cancelled
                $order = $orderModel->getById($order_id);
                if (!$order) {
                    throw new Exception('Order not found');
                }
                if (($order['status'] ?? '') !== 'Đã hủy') {
                    throw new Exception('Chỉ được phép xóa đơn hàng đã hủy');
                }

                if ($orderModel->delete($order_id)) {
                    $response = ['success' => true, 'message' => 'Order deleted successfully'];
                } else {
                    throw new Exception('Failed to delete order');
                }
            }
            break;
            
        case 'get':
            $order_id = $_GET['order_id'] ?? '';
            
            if (empty($order_id)) {
                throw new Exception('Order ID is required');
            }
            
            $order = $orderModel->getById($order_id);
            if ($order) {
                $items = $orderModel->getItemsByOrder($order_id);
                $response = ['success' => true, 'data' => $order, 'items' => $items];
            } else {
                throw new Exception('Order not found');
            }
            break;
            
        case 'list':
            $page = $_GET['page'] ?? 1;
            $limit = 10;
            $offset = ($page - 1) * $limit;
            $status = $_GET['status'] ?? null;
            $payment_status = $_GET['payment_status'] ?? null;

            $orders = $orderModel->getAll($limit, $offset, $status, $payment_status);
            $total = $orderModel->getCount(null, $status, $payment_status);
            
            $response = [
                'success' => true, 
                'data' => $orders,
                'total' => $total,
                'pages' => ceil($total / $limit),
                'current_page' => $page
            ];
            break;

        case 'monthly_report':
            // Optional from/to filters in format YYYY-MM
            $from = $_GET['from'] ?? null;
            $to = $_GET['to'] ?? null;
            // Build query
            $query = "SELECT DATE_FORMAT(order_date, '%Y-%m') AS ym,
                             SUM(CASE WHEN payment_status = 'Đã thanh toán' THEN total_amount ELSE 0 END) AS revenue,
                             COUNT(*) AS orders
                      FROM orders";
            $params = [];
            $conds = [];
            if ($from) { $conds[] = "DATE_FORMAT(order_date, '%Y-%m') >= :from"; $params[':from'] = $from; }
            if ($to) { $conds[] = "DATE_FORMAT(order_date, '%Y-%m') <= :to"; $params[':to'] = $to; }
            if (!empty($conds)) { $query .= ' WHERE ' . implode(' AND ', $conds); }
            $query .= " GROUP BY ym ORDER BY ym ASC";
            $stmt = $db->prepare($query);
            foreach ($params as $k => $v) { $stmt->bindValue($k, $v); }
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $response = ['success' => true, 'data' => $rows];
            break;
            
        case 'statistics':
            $stats = $orderModel->getStatistics();
            $response = ['success' => true, 'data' => $stats];
            break;
            
        case 'recent':
            $limit = $_GET['limit'] ?? 10;
            $orders = $orderModel->getRecentOrders($limit);
            $response = ['success' => true, 'data' => $orders];
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