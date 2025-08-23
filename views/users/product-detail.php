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
// Load product-level colors
$colors = [];
try {
    if ($db) {
        $stmt = $db->prepare("SELECT color_name FROM product_colors WHERE product_id = :pid ORDER BY color_id ASC");
        $stmt->execute([':pid' => $productId]);
        $colors = array_map(function($r){ return $r['color_name']; }, $stmt->fetchAll(PDO::FETCH_ASSOC));
    }
} catch (Exception $e) { $colors = []; }

include __DIR__ . '/../includes/global.php';
include __DIR__ . '/header.php';
?>

<div class="container my-4">
    <div class="row g-4">
        <div class="col-md-6">
            <div class="card product-card">
                <div class="card-body">
                    <?php 
                        $mainImage = isset($product['images'][0]) ? $product['images'][0] : 'assets/images/sp1.jpeg';
                        $mainImage = (strpos($mainImage, 'http') === 0) ? $mainImage : (BASE_URL . '/' . $mainImage);
                    ?>
                    <div class="product-main-image">
                        <img src="<?php echo $mainImage; ?>" class="js-main-image" alt="<?php echo htmlspecialchars($product['product_name'] ?? ''); ?>">
                    </div>
                    <?php if (!empty($product['images'])): ?>
                        <div class="d-flex gap-2 mt-3 flex-wrap product-thumbs">
                            <?php foreach ($product['images'] as $img): 
                                $src = (strpos($img, 'http') === 0) ? $img : (BASE_URL . '/' . $img);
                            ?>
                                <img src="<?php echo $src; ?>" class="img-thumbnail js-thumb" />
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card product-card">
                <div class="card-body">
                    <h3 class="card-title mb-2"><?php echo htmlspecialchars($product['product_name'] ?? ''); ?></h3>
                    <?php $defaultPrice = isset($variants[0]['price']) ? (float)$variants[0]['price'] : (float)($product['price'] ?? 0); ?>
                    <div class="product-price h4 mb-3"><span id="js-price" data-price="<?php echo htmlspecialchars((string)$defaultPrice); ?>"><?php echo number_format($defaultPrice, 0, ',', '.'); ?></span> ₫</div>
                    <div class="mb-3 product-meta">Danh mục: <?php echo htmlspecialchars($product['category_name'] ?? ''); ?> • Kho: <?php echo (int)($product['stock'] ?? 0); ?></div>

                    <?php if (!empty($variants)): ?>
                        <form method="post" action="<?php echo BASE_URL; ?>/?page=cart">
                            <input type="hidden" name="action" value="add" />
                            <?php $defaultVariantId = isset($variants[0]['variant_id']) ? (int)$variants[0]['variant_id'] : 0; ?>
                            <input type="hidden" name="variant_id" id="js-variant-id" value="<?php echo $defaultVariantId; ?>" />

                            <?php if (!empty($colors)): ?>
                                <div class="mb-3">
                                    <label class="form-label">Màu sắc</label>
                                    <div class="d-flex flex-wrap gap-2" id="js-color-wrap">
                                        <?php foreach ($colors as $idx => $c): ?>
                                            <input type="radio" class="btn-check" name="color" id="color_<?php echo $idx; ?>" value="<?php echo htmlspecialchars($c); ?>" autocomplete="off" <?php echo $idx === 0 ? 'checked' : ''; ?>>
                                            <label class="btn btn-outline-secondary py-2 px-3" for="color_<?php echo $idx; ?>" style="min-width:44px; text-transform:capitalize;"><?php echo htmlspecialchars($c); ?></label>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <div class="mb-3">
                                <label class="form-label">Kích thước</label>
                                <div class="d-flex flex-wrap gap-2" id="js-size-wrap">
                                    <?php foreach ($variants as $i => $v): ?>
                                        <?php $active = $i === 0 ? 'active' : ''; ?>
                                        <button type="button" class="btn btn-outline-dark py-2 px-3 js-size-chip <?php echo $active; ?>" data-variant-id="<?php echo (int)$v['variant_id']; ?>" data-price="<?php echo htmlspecialchars((string)$v['price']); ?>" <?php echo ((int)$v['stock'] === 0) ? 'disabled' : ''; ?>>
                                            <?php echo htmlspecialchars($v['size']); ?>
                                            <?php if ((int)$v['stock'] === 0): ?>
                                                <small class="text-muted">(Hết hàng)</small>
                                            <?php endif; ?>
                                        </button>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <div class="mb-3" style="max-width:200px;">
                                <label class="form-label">Số lượng</label>
                                <input type="number" name="quantity" min="1" value="1" class="form-control" />
                            </div>
                            <div class="product-actions">
                                <button class="btn btn-add-cart" type="submit"><i class="bi bi-bag-plus"></i> Thêm vào giỏ</button>
                                <a href="<?php echo BASE_URL; ?>/?page=checkout" class="btn btn-buy-now">Mua ngay</a>
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

<script>
document.addEventListener('click', function(e){
    var thumb = e.target.closest('.js-thumb');
    if (thumb) {
        var main = document.querySelector('.js-main-image');
        if (main) { main.src = thumb.src; }
        return;
    }
    var sizeBtn = e.target.closest('.js-size-chip');
    if (sizeBtn) {
        document.querySelectorAll('.js-size-chip').forEach(function(b){ b.classList.remove('active'); });
        sizeBtn.classList.add('active');
        var variantId = sizeBtn.getAttribute('data-variant-id');
        var price = parseFloat(sizeBtn.getAttribute('data-price') || '0');
        var priceEl = document.getElementById('js-price');
        if (priceEl) {
            try { priceEl.textContent = new Intl.NumberFormat('vi-VN').format(price); } catch(e) { priceEl.textContent = String(price); }
            priceEl.setAttribute('data-price', String(price));
        }
        var hidden = document.getElementById('js-variant-id');
        if (hidden) { hidden.value = variantId; }
    }
});
</script>
