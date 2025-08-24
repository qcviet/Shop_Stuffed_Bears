<?php
require_once __DIR__ . '/../includes/global.php';
require_once __DIR__ . '/../../controller/AppController.php';

if (session_status() === PHP_SESSION_NONE) { session_start(); }

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/login');
    exit;
}

$app = new AppController();
$userId = $_SESSION['user_id'];
$user = $app->getUserById($userId);
$items = $app->getCartWithItems($userId) ?: [];
$total = $app->getCartTotal($userId);

// Get cart total with promotions
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/CartModel.php';
$database = new Database();
$db = $database->getConnection();
$cartModel = new CartModel($db);
$cartTotalWithPromotions = $cartModel->calculateCartTotalWithPromotions($userId);

$error = '';
$orderId = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Basic address/phone capture (update user profile info)
    $full_name = trim($_POST['full_name'] ?? ($user['full_name'] ?? ''));
    $phone = trim($_POST['phone'] ?? ($user['phone'] ?? ''));
    $address = trim($_POST['address'] ?? ($user['address'] ?? ''));
    $payment_method = $_POST['payment_method'] ?? 'COD';

    if (!$full_name || !$address) {
        $error = 'Vui lòng nhập họ tên và địa chỉ giao hàng.';
    } elseif (empty($items)) {
        $error = 'Giỏ hàng trống.';
    } else {
        // Save user info
        $app->updateUser($userId, [
            'full_name' => $full_name,
            'phone' => $phone,
            'address' => $address,
        ]);

        // Use discounted total for order creation
        $orderTotal = $cartTotalWithPromotions['total'];
        
        // Always create order as unpaid; payment gateways will update upon success
        $orderId = $app->checkoutCart($userId, $payment_method, false, $orderTotal);
        
        if ($orderId) {
            if ($payment_method === 'VNPAY') {
                // Redirect to VNPay payment
                header('Location: ' . BASE_URL . '/vnpay_php/vnpay_create_payment.php?order_id=' . $orderId);
                exit;
            } else {
                // For COD and QR, go to profile
                header('Location: ' . BASE_URL . '/?page=profile#orders');
                exit;
            }
        } else {
            $error = 'Không thể tạo đơn hàng. Có thể do hết hàng.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh toán - Shop Gấu Yêu</title>
    <link rel="stylesheet" href="<?php echo BOOTSTRAP_CSS; ?>">
    <link rel="stylesheet" href="<?php echo BOOTSTRAP_ICONS; ?>">
    <link rel="stylesheet" href="<?php echo CUSTOM_CSS; ?>">
</head>
<body>
    <?php include __DIR__ . '/header.php'; ?>
    <div class="container my-4">
        <h3 class="mb-3">Thanh toán</h3>
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="row g-4">
            <div class="col-lg-7">
                <div class="card">
                    <div class="card-header">Thông tin giao hàng</div>
                    <div class="card-body">
                        <form method="post">
                            <div class="mb-3">
                                <label class="form-label">Họ và tên</label>
                                <input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>" required />
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Số điện thoại</label>
                                <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" />
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Địa chỉ</label>
                                <textarea name="address" class="form-control" rows="3" required><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label d-block">Phương thức thanh toán</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_method" id="pm_cod" value="COD" checked>
                                    <label class="form-check-label" for="pm_cod">Thanh toán khi nhận hàng (COD)</label>
                                </div>
                                
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_method" id="pm_vnpay" value="VNPAY">
                                    <label class="form-check-label" for="pm_vnpay">Thanh toán Online (VNPay)</label>
                                </div>
                            </div>
                            <button class="btn btn-primary" type="submit">Đặt hàng</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span>Đơn hàng</span>
                    </div>
                    <div class="card-body">
                        <?php if (empty($items)): ?>
                            <div class="text-muted">Giỏ hàng trống.</div>
                        <?php else: ?>
                            <ul class="list-group list-group-flush">
                                <?php foreach ($items as $it): if (!isset($it['cart_item_id'])) continue; ?>
                                    <?php 
                                        $img = $it['image_url'] ?? 'assets/images/sp1.jpeg';
                                        $src = (strpos($img, 'http') === 0) ? $img : (BASE_URL . '/' . $img);
                                        $color = isset($it['color_name']) && $it['color_name'] !== '' ? $it['color_name'] : null;
                                        $line = ((int)$it['quantity']) * (float)$it['price'];
                                    ?>
                                    <li class="list-group-item d-flex align-items-center">
                                        <img src="<?php echo $src; ?>" alt="" style="width:60px;height:60px;object-fit:cover;border-radius:6px;margin-right:12px;" />
                                        <div class="flex-grow-1">
                                            <div class="fw-semibold"><?php echo htmlspecialchars($it['product_name'] ?? ''); ?></div>
                                            <small class="text-muted">Kích thước: <?php echo htmlspecialchars($it['size'] ?? ''); ?><?php echo $color ? ' • Màu: ' . htmlspecialchars($color) : ''; ?> • SL: <?php echo (int)$it['quantity']; ?></small>
                                        </div>
                                        <div class="ms-2 fw-semibold"><?php echo number_format($line, 0, ',', '.'); ?> ₫</div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                            
                            <!-- Order Summary with Promotions -->
                            <div class="mt-3">
                                <div class="d-flex justify-content-between">
                                    <div class="fw-semibold">Tạm tính</div>
                                    <div id="orderSubtotal" data-total="<?php echo (float)$cartTotalWithPromotions['subtotal']; ?>">
                                        <?php echo number_format((float)$cartTotalWithPromotions['subtotal'], 0, ',', '.'); ?> ₫
                                    </div>
                                </div>
                                
                                <?php if ($cartTotalWithPromotions['discount_amount'] > 0): ?>
                                    <div class="d-flex justify-content-between text-success">
                                        <div class="fw-semibold">Giảm giá</div>
                                        <div>-<?php echo number_format($cartTotalWithPromotions['discount_amount'], 0, ',', '.'); ?> ₫
                                            <span class="badge bg-success ms-1">-<?php echo number_format($cartTotalWithPromotions['discount_percent'], 1); ?>%</span>
                                        </div>
                                    </div>
                                    
                                    <!-- Applied Promotions -->
                                    <?php if (!empty($cartTotalWithPromotions['applied_promotions'])): ?>
                                        <div class="alert alert-info py-2 px-3 mt-2 mb-2">
                                            <i class="fas fa-gift me-2"></i>
                                            <strong>Khuyến mãi đã áp dụng:</strong>
                                            <?php foreach ($cartTotalWithPromotions['applied_promotions'] as $promotion): ?>
                                                <div class="small">
                                                    • <?php echo htmlspecialchars($promotion['title']); ?> 
                                                    (<?php echo htmlspecialchars($promotion['product_name']); ?>)
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                <?php endif; ?>
                                
                                <div class="d-flex justify-content-between">
                                    <div class="fw-semibold">Phí vận chuyển</div>
                                    <div>Miễn phí</div>
                                </div>
                                <hr/>
                                <div class="d-flex justify-content-between h5">
                                    <div>Tổng</div>
                                    <div id="orderTotal" data-total="<?php echo (float)$cartTotalWithPromotions['total']; ?>">
                                        <?php echo number_format((float)$cartTotalWithPromotions['total'], 0, ',', '.'); ?> ₫
                                    </div>
                                </div>
                            </div>

                            
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/footer.php'; ?>

    <script src="<?php echo BOOTSTRAP_JS; ?>"></script>
    <script>
    (function(){
        var pmCod = document.getElementById('pm_cod');
        var pmVnpay = document.getElementById('pm_vnpay');
        function toggle(){}
        document.addEventListener('change', function(e){
            if (e.target && (e.target.id === 'pm_cod' || e.target.id === 'pm_vnpay')){
                toggle();
            }
        });
    })();
    </script>
    </body>
    </html>


