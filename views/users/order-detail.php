<?php
require_once __DIR__ . '/../includes/global.php';

if (session_status() === PHP_SESSION_NONE) {
	session_start();
}

if (!isset($_SESSION['user_id'])) {
	header('Location: ' . BASE_URL . '/login');
	exit();
}

require_once __DIR__ . '/../../controller/AppController.php';
$app = new AppController();

$orderId = intval($_GET['order_id'] ?? 0);
if ($orderId <= 0) {
	header('Location: ' . BASE_URL . '/?page=orders');
	exit();
}

$order = $app->getOrderById($orderId);
if (!$order || (int)$order['user_id'] !== (int)$_SESSION['user_id']) {
	header('Location: ' . BASE_URL . '/?page=orders');
	exit();
}

$items = $app->getOrderItems($orderId) ?: [];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Chi tiết đơn hàng #<?php echo $orderId; ?> - Shop Gấu Yêu</title>
	<link href="<?php echo BOOTSTRAP_CSS; ?>" rel="stylesheet">
	<link href="<?php echo BOOTSTRAP_ICONS; ?>" rel="stylesheet">
	<link href="<?php echo CUSTOM_CSS; ?>" rel="stylesheet">
</head>
<body>
	<?php /* Deprecated standalone order detail page; redirect to profile orders */ ?>
	<script>
		window.location.href = '<?php echo BASE_URL; ?>/?page=profile#orders';
	</script>
	<div class="container py-4">
		<div class="row justify-content-center">
			<div class="col-lg-10">
				<div class="card shadow-sm">
					<div class="card-header d-flex justify-content-between align-items-center">
						<h5 class="mb-0">Chi tiết đơn hàng #<?php echo $orderId; ?></h5>
						<a class="btn btn-sm btn-outline-secondary" href="<?php echo BASE_URL; ?>/?page=orders"><i class="bi bi-arrow-left"></i> Quay lại</a>
					</div>
					<div class="card-body">
						<div class="row mb-3">
							<div class="col-md-4"><strong>Ngày đặt:</strong> <?php echo date('d/m/Y H:i', strtotime($order['order_date'])); ?></div>
							<div class="col-md-4"><strong>Trạng thái:</strong> <?php echo htmlspecialchars($order['status']); ?></div>
							<div class="col-md-4"><strong>Thanh toán:</strong> <?php echo htmlspecialchars($order['payment_status']); ?></div>
						</div>
						<div class="table-responsive">
							<table class="table table-bordered align-middle">
								<thead>
									<tr>
										<th style="width:90px">Ảnh</th>
										<th>Sản phẩm</th>
										<th>Kích thước</th>
										<th>Màu sắc</th>
										<th class="text-end">Đơn giá</th>
										<th class="text-end">Số lượng</th>
										<th class="text-end">Thành tiền</th>
									</tr>
								</thead>
								<tbody>
									<?php $sum = 0; foreach ($items as $it): $line = (float)$it['price'] * (int)$it['quantity']; $sum += $line; ?>
									<tr>
										<td>
											<?php 
												$img = $it['image_url'] ?? 'assets/images/sp1.jpeg';
												$src = (strpos($img, 'http') === 0) ? $img : (BASE_URL . '/' . $img);
											?>
											<img src="<?php echo $src; ?>" alt="" style="width:70px;height:70px;object-fit:cover;border-radius:6px;" />
										</td>
										<td><?php echo htmlspecialchars($it['product_name']); ?></td>
										<td><?php echo htmlspecialchars($it['size']); ?></td>
										<td><?php echo isset($it['color_name']) && $it['color_name'] !== '' ? htmlspecialchars($it['color_name']) : '<span class="text-muted">—</span>'; ?></td>
										<td class="text-end"><?php echo number_format($it['price'], 0, ',', '.'); ?>₫</td>
										<td class="text-end"><?php echo (int)$it['quantity']; ?></td>
										<td class="text-end"><?php echo number_format($line, 0, ',', '.'); ?>₫</td>
									</tr>
									<?php endforeach; ?>
								</tbody>
								<tfoot>
									<tr>
										<th colspan="4" class="text-end">Tổng cộng</th>
										<th class="text-end"><?php echo number_format($sum, 0, ',', '.'); ?>₫</th>
									</tr>
								</tfoot>
							</table>
						</div>
						<?php if ($order['status'] === 'Chờ xác nhận'): ?>
						<form method="POST" action="<?php echo BASE_URL; ?>/?page=orders" onsubmit="return confirm('Bạn có chắc muốn hủy đơn hàng này?');">
							<input type="hidden" name="cancel_order_id" value="<?php echo (int)$orderId; ?>">
							<button type="submit" class="btn btn-outline-danger"><i class="bi bi-x-circle"></i> Hủy đơn</button>
						</form>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php include __DIR__ . '/footer.php'; ?>
	<script src="<?php echo BOOTSTRAP_JS; ?>"></script>
</body>
</html>


