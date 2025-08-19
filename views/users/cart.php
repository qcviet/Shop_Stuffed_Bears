<?php
require_once __DIR__ . '/../includes/global.php';
require_once __DIR__ . '/../../controller/AppController.php';

if (session_status() === PHP_SESSION_NONE) { session_start(); }

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/login');
    exit;
}

$app = new AppController();
$message = '';

// Handle actions: update, remove, clear, add (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $isAjax = isset($_POST['ajax']) && $_POST['ajax'] === '1';
    $action = $_POST['action'] ?? '';
    if ($action === 'update') {
        $cart_item_id = (int)($_POST['cart_item_id'] ?? 0);
        $quantity = max(0, (int)($_POST['quantity'] ?? 1));
        if ($cart_item_id > 0) { $app->updateCartItemQuantity($cart_item_id, $quantity); }
    } elseif ($action === 'remove') {
        $cart_item_id = (int)($_POST['cart_item_id'] ?? 0);
        if ($cart_item_id > 0) { $app->removeFromCart($cart_item_id); }
    } elseif ($action === 'clear') {
        $app->clearCart($_SESSION['user_id']);
    } elseif ($action === 'add') {
        // Add by variant
        $variant_id = (int)($_POST['variant_id'] ?? 0);
        $qty = max(1, (int)($_POST['quantity'] ?? 1));
        if ($variant_id > 0) { $app->addToCart($_SESSION['user_id'], $variant_id, $qty); }
    }
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit;
    }
    header('Location: ' . BASE_URL . '/?page=cart');
    exit;
}

$items = $app->getCartWithItems($_SESSION['user_id']) ?: [];
$total = $app->getCartTotal($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giỏ hàng - Shop Gấu Yêu</title>
    <link rel="stylesheet" href="<?php echo BOOTSTRAP_CSS; ?>">
    <link rel="stylesheet" href="<?php echo BOOTSTRAP_ICONS; ?>">
    <link rel="stylesheet" href="<?php echo CUSTOM_CSS; ?>">
</head>
<body>
    <?php include __DIR__ . '/header.php'; ?>
    <div class="container my-4">
        <h3 class="mb-3">Giỏ hàng</h3>
        <?php if (empty($items)): ?>
            <div class="alert alert-info">Giỏ hàng trống. <a href="<?php echo BASE_URL; ?>">Tiếp tục mua sắm</a></div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table align-middle" id="cartTable">
                    <thead>
                        <tr>
                            <th style="width:90px">Ảnh</th>
                            <th>Sản phẩm</th>
                            <th>Kích thước</th>
                            <th>Đơn giá</th>
                            <th>Số lượng</th>
                            <th>Tạm tính</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $row): ?>
                            <?php if (!isset($row['cart_item_id'])) continue; ?>
                            <tr>
                                <td>
                                    <?php 
                                        $img = $row['image_url'] ?? 'assets/images/sp1.jpeg';
                                        $src = (strpos($img, 'http') === 0) ? $img : (BASE_URL . '/' . $img);
                                    ?>
                                    <img src="<?php echo $src; ?>" alt="" style="width:70px;height:70px;object-fit:cover;border-radius:6px;" />
                                </td>
                                <td><?php echo htmlspecialchars($row['product_name'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($row['size'] ?? ''); ?></td>
                                <td class="js-unit-price" data-price="<?php echo (float)$row['price']; ?>"><?php echo number_format((float)($row['price'] ?? 0), 0, ',', '.'); ?> ₫</td>
                                <td style="max-width:180px;">
                                    <div class="d-inline-flex align-items-center gap-1 js-qty-form" data-item-id="<?php echo (int)$row['cart_item_id']; ?>">
                                        <button class="btn btn-sm btn-outline-secondary js-qty-minus" type="button">−</button>
                                        <input type="number" min="0" class="form-control form-control-sm js-qty-input" value="<?php echo (int)$row['quantity']; ?>" style="width:70px; text-align:center;" />
                                        <button class="btn btn-sm btn-outline-secondary js-qty-plus" type="button">+</button>
                                    </div>
                                </td>
                                <td class="js-line-total"><?php echo number_format(((int)$row['quantity']) * (float)$row['price'], 0, ',', '.'); ?> ₫</td>
                                <td>
                                    <form method="post">
                                        <input type="hidden" name="action" value="remove" />
                                        <input type="hidden" name="cart_item_id" value="<?php echo (int)$row['cart_item_id']; ?>" />
                                        <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center">
                <form method="post">
                    <input type="hidden" name="action" value="clear" />
                    <button class="btn btn-outline-secondary">Xóa giỏ hàng</button>
                </form>
                <div class="h5 mb-0">Tổng: <span id="grandTotal" data-total="<?php echo (float)$total; ?>"><?php echo number_format((float)$total, 0, ',', '.'); ?></span> ₫</div>
            </div>
            <div class="mt-3 d-flex justify-content-end">
                <a href="<?php echo BASE_URL; ?>/?page=checkout" class="btn btn-primary">Tiến hành thanh toán</a>
            </div>
        <?php endif; ?>
    </div>

    <?php include __DIR__ . '/footer.php'; ?>

    <script src="<?php echo BOOTSTRAP_JS; ?>"></script>
    <script>
    (function(){
        function formatVnd(n){
            try { return new Intl.NumberFormat('vi-VN').format(n); }
            catch(e){ return (n||0).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ','); }
        }
        function recalcRow(row){
            var qtyEl = row.querySelector('.js-qty-input');
            var priceEl = row.querySelector('.js-unit-price');
            var lineEl = row.querySelector('.js-line-total');
            if (!qtyEl || !priceEl || !lineEl) return 0;
            var qty = parseInt(qtyEl.value || '0', 10);
            var price = parseFloat(priceEl.getAttribute('data-price') || '0');
            var line = Math.max(0, qty) * price;
            lineEl.textContent = formatVnd(line) + ' ₫';
            return line;
        }
        function recalcAll(){
            var total = 0;
            document.querySelectorAll('#cartTable tbody tr').forEach(function(tr){
                total += recalcRow(tr);
            });
            var grand = document.getElementById('grandTotal');
            if (grand){
                grand.textContent = formatVnd(total);
                grand.setAttribute('data-total', String(total));
            }
        }
        function postQty(cartItemId, qty){
            var formData = new FormData();
            formData.append('ajax','1');
            formData.append('action','update');
            formData.append('cart_item_id', String(cartItemId));
            formData.append('quantity', String(qty));
            return fetch('<?php echo BASE_URL; ?>/?page=cart', { method: 'POST', body: formData });
        }
        document.addEventListener('click', function(e){
            var minus = e.target.closest('.js-qty-minus');
            var plus = e.target.closest('.js-qty-plus');
            if (!minus && !plus) return;
            var wrap = (minus || plus).closest('.js-qty-form');
            if (!wrap) return;
            var input = wrap.querySelector('.js-qty-input');
            if (!input) return;
            var current = parseInt(input.value || '0', 10);
            if (minus) { current = Math.max(0, current - 1); }
            if (plus) { current = current + 1; }
            input.value = String(current);
            var row = wrap.closest('tr');
            recalcRow(row);
            recalcAll();
            var id = wrap.getAttribute('data-item-id');
            if (id) { postQty(id, current); }
        });
        document.addEventListener('input', function(e){
            if (e.target && e.target.classList.contains('js-qty-input')){
                var wrap = e.target.closest('.js-qty-form');
                var row = e.target.closest('tr');
                recalcRow(row);
                recalcAll();
                var id = wrap ? wrap.getAttribute('data-item-id') : null;
                if (id) { postQty(id, parseInt(e.target.value || '0', 10)); }
            }
        });
    })();
    </script>
</body>
</html>


