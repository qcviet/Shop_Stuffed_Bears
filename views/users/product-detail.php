<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/ProductModel.php';
require_once __DIR__ . '/../../models/ProductVariantModel.php';

if (session_status() === PHP_SESSION_NONE) { session_start(); }

$db = (new Database())->getConnection();
$productModel = $db ? new ProductModel($db) : null;
$variantModel = $db ? new ProductVariantModel($db) : null;

$productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$productId || !$productModel) {
    header('Location: ' . BASE_URL);
    exit;
}

$product = $productModel->getById($productId);
$variants = $variantModel ? $variantModel->getByProductId($productId) : [];

include __DIR__ . '/../includes/global.php';
include __DIR__ . '/header.php';
?>

<div class="container my-4">
    <div class="row g-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <?php 
                        $mainImage = isset($product['images'][0]) ? $product['images'][0] : 'assets/images/sp1.jpeg';
                        $mainImage = (strpos($mainImage, 'http') === 0) ? $mainImage : (BASE_URL . '/' . $mainImage);
                    ?>
                    <img src="<?php echo $mainImage; ?>" class="img-fluid" alt="<?php echo htmlspecialchars($product['product_name'] ?? ''); ?>">
                    <?php if (!empty($product['images'])): ?>
                        <div class="d-flex gap-2 mt-3 flex-wrap">
                            <?php foreach ($product['images'] as $img): 
                                $src = (strpos($img, 'http') === 0) ? $img : (BASE_URL . '/' . $img);
                            ?>
                                <img src="<?php echo $src; ?>" class="img-thumbnail" style="width:80px;height:80px;object-fit:cover;" />
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h3 class="card-title mb-2"><?php echo htmlspecialchars($product['product_name'] ?? ''); ?></h3>
                    <div class="text-danger h5 mb-3"><?php echo isset($product['price']) ? number_format((float)$product['price'], 0, ',', '.') . ' ₫' : ''; ?></div>
                    <div class="mb-3 text-muted">Danh mục: <?php echo htmlspecialchars($product['category_name'] ?? ''); ?> • Kho: <?php echo (int)($product['stock'] ?? 0); ?></div>

                    <?php if (!empty($variants)): ?>
                        <form method="post" action="<?php echo BASE_URL; ?>/?page=cart">
                            <input type="hidden" name="action" value="add" />
                            <div class="mb-3">
                                <label class="form-label">Kích thước</label>
                                <select class="form-select" name="variant_id">
                                    <?php foreach ($variants as $v): ?>
                                        <option value="<?php echo (int)$v['variant_id']; ?>">
                                            <?php echo htmlspecialchars($v['size']); ?> — <?php echo number_format((float)$v['price'], 0, ',', '.'); ?> ₫ (Kho: <?php echo (int)$v['stock']; ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3" style="max-width:200px;">
                                <label class="form-label">Số lượng</label>
                                <input type="number" name="quantity" min="1" value="1" class="form-control" />
                            </div>
                            <div class="d-flex gap-2">
                                <button class="btn btn-primary" type="submit"><i class="bi bi-bag-plus"></i> Thêm vào giỏ</button>
                                <a href="<?php echo BASE_URL; ?>/?page=checkout" class="btn btn-outline-secondary">Mua ngay</a>
                            </div>
                        </form>
                    <?php else: ?>
                        <div class="alert alert-warning">Sản phẩm chưa có biến thể khả dụng.</div>
                    <?php endif; ?>

                    <?php if (!empty($product['description'])): ?>
                        <hr/>
                        <div><?php echo nl2br(htmlspecialchars($product['description'])); ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/footer.php'; ?>
