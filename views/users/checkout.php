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

        // Checkout (mark paid = false for COD)
        $orderId = $app->checkoutCart($userId, $payment_method, false);
        if ($orderId) {
            header('Location: ' . BASE_URL . '/?page=orders');
            exit;
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
                                    <input class="form-check-input" type="radio" name="payment_method" id="pm_qr" value="QR">
                                    <label class="form-check-label" for="pm_qr">Thanh toán Online (Quét QR)</label>
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
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <div class="fw-semibold"><?php echo htmlspecialchars($it['product_name'] ?? ''); ?></div>
                                            <small class="text-muted">Size: <?php echo htmlspecialchars($it['size'] ?? ''); ?> x <?php echo (int)$it['quantity']; ?></small>
                                        </div>
                                        <div><?php echo number_format(((int)$it['quantity'])* (float)$it['price'], 0, ',', '.'); ?> ₫</div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                            <div class="d-flex justify-content-between mt-3">
                                <div class="fw-semibold">Tạm tính</div>
                                <div id="orderSubtotal" data-total="<?php echo (float)$total; ?>"><?php echo number_format((float)$total, 0, ',', '.'); ?> ₫</div>
                            </div>
                            <div class="d-flex justify-content-between">
                                <div class="fw-semibold">Phí vận chuyển</div>
                                <div>Miễn phí</div>
                            </div>
                            <hr/>
                            <div class="d-flex justify-content-between h5">
                                <div>Tổng</div>
                                <div id="orderTotal" data-total="<?php echo (float)$total; ?>"><?php echo number_format((float)$total, 0, ',', '.'); ?> ₫</div>
                            </div>

                            <div id="qrSection" class="mt-3" style="display:none;">
                                <div class="border rounded p-3">
                                    <div class="fw-semibold mb-2">Quét QR để thanh toán</div>
                                    <?php 
                                        $qrText = rawurlencode('PAY|Shop Gau Yeu|User=' . $userId . '|Amount=' . (int)$total . '|Note=Thanh toan don hang');
                                        $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=' . $qrText;
                                    ?>
                                    <div class="text-center">
                                        <img id="qrImage" src="<?php echo $qrUrl; ?>" alt="QR thanh toán" width="220" height="220" />
                                    </div>
                                    <div class="small text-muted mt-2">
                                        - Nội dung chuyển khoản: <strong>Order-<?php echo (int)$userId; ?></strong><br/>
                                        - Số tiền: <strong id="qrAmountText"><?php echo number_format((float)$total, 0, ',', '.'); ?> ₫</strong>
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
        var pmQr = document.getElementById('pm_qr');
        var qrSection = document.getElementById('qrSection');
        var qrImg = document.getElementById('qrImage');
        var totalEl = document.getElementById('orderTotal');
        var amountText = document.getElementById('qrAmountText');
        function toggle(){
            if (!pmCod || !pmQr) return;
            var useQr = pmQr.checked;
            qrSection.style.display = useQr ? 'block' : 'none';
        }
        function updateQr(){
            if (!qrImg || !totalEl) return;
            var total = parseInt(totalEl.getAttribute('data-total') || '0', 10);
            var txt = encodeURIComponent('PAY|Shop Gau Yeu|User=<?php echo (int)$userId; ?>|Amount=' + total + '|Note=Thanh toan don hang');
            var url = 'https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=' + txt;
            qrImg.src = url;
            if (amountText) { amountText.textContent = new Intl.NumberFormat('vi-VN').format(total) + ' ₫'; }
        }
        document.addEventListener('change', function(e){
            if (e.target && (e.target.id === 'pm_cod' || e.target.id === 'pm_qr')){
                toggle();
                if (pmQr && pmQr.checked) updateQr();
            }
        });
        // Initialize
        toggle();
    })();
    </script>
    </body>
    </html>


