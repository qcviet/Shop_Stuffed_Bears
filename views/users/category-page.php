<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/CategoryModel.php';
require_once __DIR__ . '/../../models/ProductModel.php';

$db = (new Database())->getConnection();
$categoryModel = $db ? new CategoryModel($db) : null;
$productModel = $db ? new ProductModel($db) : null;

$categoryId = isset($_GET['cat']) ? (int)$_GET['cat'] : 0;
if (!$categoryId) {
	header('Location: ' . BASE_URL);
	exit;
}

$category = $categoryModel ? $categoryModel->getById($categoryId) : null;

// Read filters
$min = isset($_GET['min']) && $_GET['min'] !== '' ? (int)preg_replace('/[^0-9]/', '', $_GET['min']) : null;
$max = isset($_GET['max']) && $_GET['max'] !== '' ? (int)preg_replace('/[^0-9]/', '', $_GET['max']) : null;
$sizes = isset($_GET['size']) ? (array)$_GET['size'] : [];

$sizesAvailable = $productModel ? $productModel->getSizesForCategory($categoryId) : [];
$products = $productModel ? $productModel->getByCategoryWithFilters($categoryId, $min, $max, $sizes, 16, 0) : [];
?>

<?php include __DIR__ . '/../includes/global.php'; ?>
<?php include __DIR__ . '/header.php'; ?>

<div class="container my-4">
	<div class="row">
		<!-- Sidebar -->
		<aside class="col-md-3 mb-4">
			<div class="card">
				<div class="card-header fw-semibold">Danh mục</div>
				<div class="list-group list-group-flush">
					<?php foreach (($categoryModel ? $categoryModel->getAll() : []) as $cat): ?>
						<a class="list-group-item list-group-item-action <?php echo ((int)$cat['category_id'] === $categoryId) ? 'active' : ''; ?>" href="<?php echo BASE_URL . '?page=category&cat=' . (int)$cat['category_id']; ?>">
							<?php echo htmlspecialchars($cat['category_name']); ?>
						</a>
					<?php endforeach; ?>
				</div>
			</div>

			<div class="card mt-3">
				<div class="card-header fw-semibold">Lọc theo giá</div>
				<div class="card-body">
					<form method="get">
						<input type="hidden" name="page" value="category" />
						<input type="hidden" name="cat" value="<?php echo (int)$categoryId; ?>" />
						<div class="mb-2">
							<label class="form-label">Từ (VND)</label>
							<input type="text" class="form-control form-control-sm" name="min" value="<?php echo $min ? number_format($min, 0, '.', ',') : ''; ?>" />
						</div>
						<div class="mb-2">
							<label class="form-label">Đến (VND)</label>
							<input type="text" class="form-control form-control-sm" name="max" value="<?php echo $max ? number_format($max, 0, '.', ',') : ''; ?>" />
						</div>
						<div class="mb-2">
							<label class="form-label">Kích thước</label>
							<?php foreach ($sizesAvailable as $sz): ?>
								<div class="form-check">
									<input class="form-check-input" type="checkbox" name="size[]" value="<?php echo htmlspecialchars($sz); ?>" id="sz_<?php echo md5($sz); ?>" <?php echo in_array($sz, $sizes) ? 'checked' : ''; ?> />
									<label class="form-check-label" for="sz_<?php echo md5($sz); ?>"><?php echo htmlspecialchars($sz); ?></label>
								</div>
							<?php endforeach; ?>
						</div>
						<button class="btn btn-primary btn-sm" type="submit">Áp dụng</button>
					</form>
				</div>
			</div>
		</aside>

		<!-- Main content -->
		<main class="col-md-9">
			<h5 class="mb-3"><?php echo htmlspecialchars($category ? $category['category_name'] : 'Danh mục'); ?></h5>
			<div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-3 g-md-4">
				<?php if (!empty($products)): ?>
					<?php foreach ($products as $p): ?>
						<div class="col">
							<div class="d-block text-decoration-none card h-100 p-0">
								<a href="<?php echo BASE_URL . '?page=product-detail&id=' . (int)$p['product_id']; ?>">
								<img src="<?php 
									$img = !empty($p['image_url']) ? $p['image_url'] : 'assets/images/sp1.jpeg';
									echo (strpos($img, 'http') === 0) ? $img : (BASE_URL . '/' . $img);
								?>" class="card-img-top" alt="<?php echo htmlspecialchars($p['product_name']); ?>">
								<div class="card-body">
									<div class="fw-semibold small"><?php echo htmlspecialchars($p['product_name']); ?></div>
									<div class="text-danger small mb-1"><?php 
										$minPrice = isset($p['price']) ? (int)round($p['price']) : 0;
										echo $minPrice > 0 ? number_format($minPrice, 0, '.', ',') . ' ₫' : '—';
									?>
									</div>
									<div class="badge bg-light text-muted">Kho: <?php echo (int)$p['stock']; ?></div>
									</a>
									<?php if (!empty($p['min_variant_id'])): ?>
										<form method="post" action="<?php echo BASE_URL; ?>/?page=cart" class="mt-2">
											<input type="hidden" name="action" value="add" />
											<input type="hidden" name="variant_id" value="<?php echo (int)$p['min_variant_id']; ?>" />
											<input type="hidden" name="quantity" value="1" />
											<button class="btn btn-sm btn-primary w-100" type="submit">
												<i class="bi bi-bag-plus"></i> Thêm vào giỏ
											</button>
										</form>
									<?php endif; ?>
								</div>
							</div>
						</div>
					<?php endforeach; ?>
				<?php else: ?>
					<div class="col"><div class="text-muted">Không có sản phẩm phù hợp.</div></div>
				<?php endif; ?>
			</div>
		</main>
	</div>
</div>

<?php include __DIR__ . '/footer.php'; ?>

