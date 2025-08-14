<?php
require_once __DIR__ . '/../includes/global.php';

// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/login');
    exit();
}

// Get user orders
require_once __DIR__ . '/../../controller/AppController.php';
$app = new AppController();
$orders = $app->getOrdersByUser($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đơn hàng của tôi - Shop Gấu Yêu</title>
    <link href="<?php echo BOOTSTRAP_CSS; ?>" rel="stylesheet">
    <link href="<?php echo BOOTSTRAP_ICONS; ?>" rel="stylesheet">
    <link href="<?php echo CUSTOM_CSS; ?>" rel="stylesheet">
    <style>
        .orders-container {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px 0;
        }
        .orders-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }
        .orders-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 20px 20px 0 0;
            text-align: center;
        }
        .order-item {
            border: 1px solid #e9ecef;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
            transition: all 0.3s ease;
        }
        .order-item:hover {
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
        }
        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        .status-pending { background-color: #fff3cd; color: #856404; }
        .status-shipping { background-color: #cce5ff; color: #004085; }
        .status-delivered { background-color: #d4edda; color: #155724; }
        .status-cancelled { background-color: #f8d7da; color: #721c24; }
        .payment-pending { background-color: #fff3cd; color: #856404; }
        .payment-paid { background-color: #d4edda; color: #155724; }
    </style>
</head>
<body>
    <div class="orders-container">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="orders-card">
                        <div class="orders-header">
                            <h3 class="mb-2">
                                <i class="bi bi-box"></i> Đơn hàng của tôi
                            </h3>
                            <p class="mb-0">Xem lịch sử đơn hàng và trạng thái giao hàng</p>
                        </div>

                        <div class="p-4">
                            <?php if (empty($orders)): ?>
                                <div class="text-center py-5">
                                    <div class="mb-3">
                                        <i class="bi bi-box" style="font-size: 4rem; color: #ccc;"></i>
                                    </div>
                                    <h4 class="text-muted">Chưa có đơn hàng nào</h4>
                                    <p class="text-muted">Bạn chưa có đơn hàng nào. Hãy bắt đầu mua sắm!</p>
                                    <a href="<?php echo BASE_URL; ?>" class="btn btn-primary">
                                        <i class="bi bi-shop"></i> Mua sắm ngay
                                    </a>
                                </div>
                            <?php else: ?>
                                <div class="row">
                                    <?php foreach ($orders as $order): ?>
                                        <div class="col-12">
                                            <div class="order-item">
                                                <div class="row align-items-center">
                                                    <div class="col-md-3">
                                                        <h6 class="mb-1">Đơn hàng #<?php echo $order['order_id']; ?></h6>
                                                        <small class="text-muted">
                                                            <i class="bi bi-calendar"></i> 
                                                            <?php echo date('d/m/Y H:i', strtotime($order['order_date'])); ?>
                                                        </small>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <strong class="text-primary">
                                                            <?php echo number_format($order['total_amount'], 0, ',', '.'); ?> VNĐ
                                                        </strong>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <?php
                                                        $statusClass = '';
                                                        switch ($order['status']) {
                                                            case 'Chờ xác nhận':
                                                                $statusClass = 'status-pending';
                                                                break;
                                                            case 'Đang giao':
                                                                $statusClass = 'status-shipping';
                                                                break;
                                                            case 'Đã giao':
                                                                $statusClass = 'status-delivered';
                                                                break;
                                                            case 'Đã hủy':
                                                                $statusClass = 'status-cancelled';
                                                                break;
                                                        }
                                                        ?>
                                                        <span class="status-badge <?php echo $statusClass; ?>">
                                                            <?php echo $order['status']; ?>
                                                        </span>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <?php
                                                        $paymentClass = $order['payment_status'] === 'Đã thanh toán' ? 'payment-paid' : 'payment-pending';
                                                        ?>
                                                        <span class="status-badge <?php echo $paymentClass; ?>">
                                                            <?php echo $order['payment_status']; ?>
                                                        </span>
                                                    </div>
                                                </div>
                                                
                                                <div class="mt-3">
                                                    <button class="btn btn-outline-primary btn-sm" 
                                                            onclick="viewOrderDetails(<?php echo $order['order_id']; ?>)">
                                                        <i class="bi bi-eye"></i> Xem chi tiết
                                                    </button>
                                                    <?php if ($order['status'] === 'Chờ xác nhận'): ?>
                                                        <button class="btn btn-outline-danger btn-sm ms-2" 
                                                                onclick="cancelOrder(<?php echo $order['order_id']; ?>)">
                                                            <i class="bi bi-x-circle"></i> Hủy đơn
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <div class="text-center mt-4">
                                <a href="<?php echo BASE_URL; ?>" class="text-decoration-none me-3">
                                    <i class="bi bi-house"></i> Trang chủ
                                </a>
                                <a href="<?php echo BASE_URL; ?>/profile" class="text-decoration-none me-3">
                                    <i class="bi bi-person"></i> Hồ sơ
                                </a>
                                <a href="<?php echo BASE_URL; ?>/logout" class="text-decoration-none text-danger">
                                    <i class="bi bi-box-arrow-right"></i> Đăng xuất
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="<?php echo BOOTSTRAP_JS; ?>"></script>
    <script>
        function viewOrderDetails(orderId) {
            alert('Chức năng xem chi tiết đơn hàng sẽ được phát triển sau.');
        }
        
        function cancelOrder(orderId) {
            if (confirm('Bạn có chắc chắn muốn hủy đơn hàng này?')) {
                alert('Chức năng hủy đơn hàng sẽ được phát triển sau.');
            }
        }
    </script>
</body>
</html> 