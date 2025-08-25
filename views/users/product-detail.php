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

// Get product with promotions
$productWithPromotions = $productModel->getProductWithPromotionsById($productId);
$discountInfo = $productModel->calculateDiscountedPrice($productId);

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
                    
                    <?php 
                    $defaultPrice = isset($variants[0]['price']) ? (float)$variants[0]['price'] : (float)($product['price'] ?? 0);
                    $hasDiscount = $discountInfo && $discountInfo['discount_percent'] > 0;
                    ?>
                    
                    <div class="product-price mb-3">
                        <?php if ($hasDiscount): ?>
                            <div class="d-flex align-items-center gap-2">
                                <span class="h4 text-danger" id="js-price" data-price="<?php echo htmlspecialchars((string)$discountInfo['discounted_min_price']); ?>">
                                    <?php echo number_format($discountInfo['discounted_min_price'], 0, ',', '.'); ?>
                                </span>
                                <span class="h6 text-muted text-decoration-line-through">
                                    <?php echo number_format($discountInfo['original_min_price'], 0, ',', '.'); ?>
                                </span>
                                <span class="badge bg-danger fs-6">
                                    -<?php echo $discountInfo['discount_percent']; ?>%
                                </span>
                            </div>
                            <?php if ($discountInfo['promotion_title']): ?>
                                <div class="alert alert-info py-2 px-3 mt-2 mb-0">
                                    <i class="fas fa-gift me-2"></i>
                                    <strong><?php echo htmlspecialchars($discountInfo['promotion_title']); ?></strong>
                                    <?php if ($discountInfo['promotion_description']): ?>
                                        <br><small><?php echo htmlspecialchars($discountInfo['promotion_description']); ?></small>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="h4" id="js-price" data-price="<?php echo htmlspecialchars((string)$defaultPrice); ?>">
                                <?php echo number_format($defaultPrice, 0, ',', '.'); ?> ₫
                            </div>
                        <?php endif; ?>
                    </div>
                    
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
                                        <button type="button" class="btn btn-outline-dark py-2 px-3 js-size-chip <?php echo $active; ?>" data-variant-id="<?php echo (int)$v['variant_id']; ?>" data-price="<?php echo htmlspecialchars((string)$v['price']); ?>" data-original-price="<?php echo htmlspecialchars((string)$v['price']); ?>" <?php echo ((int)$v['stock'] === 0) ? 'disabled' : ''; ?>>
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

<!-- Related Products Section -->
<div class="container my-5">
    <div class="row">
        <div class="col-12">
            <h3 class="mb-4">Sản phẩm liên quan</h3>
            <div class="row g-4" id="related-products">
                <?php
                // Get related products from the same category
                $relatedProducts = [];
                if ($productModel && isset($product['category_id'])) {
                    try {
                        // Get up to 4 products from the same category, excluding current product
                        $relatedProducts = $productModel->getAll(4, 0, $product['category_id']);
                        // Filter out current product
                        $relatedProducts = array_filter($relatedProducts, function($p) use ($productId) {
                            return $p['product_id'] != $productId;
                        });
                        // Limit to 4 products
                        $relatedProducts = array_slice($relatedProducts, 0, 4);
                    } catch (Exception $e) {
                        error_log("Error getting related products: " . $e->getMessage());
                    }
                }
                
                if (!empty($relatedProducts)): ?>
                    <?php foreach ($relatedProducts as $relatedProduct): 
                        // Get variants for price calculation
                        $relatedVariants = $variantModel ? $variantModel->getByProductId($relatedProduct['product_id']) : [];
                        $minPrice = null;
                        if (!empty($relatedVariants)) {
                            $minPrice = min(array_column($relatedVariants, 'price'));
                        }
                        
                        // Get first image
                        $relatedImage = !empty($relatedProduct['image_url']) ? $relatedProduct['image_url'] : 'assets/images/sp1.jpeg';
                        $relatedImage = (strpos($relatedImage, 'http') === 0) ? $relatedImage : (BASE_URL . '/' . $relatedImage);
                    ?>
                        <div class="col-6 col-md-3">
                            <div class="card h-100 product-card">
                                <div class="product-image-container">
                                    <a href="<?php echo BASE_URL . '?page=product-detail&id=' . $relatedProduct['product_id']; ?>">
                                        <img src="<?php echo $relatedImage; ?>" class="card-img-top product-image" alt="<?php echo htmlspecialchars($relatedProduct['product_name']); ?>">
                                    </a>
                                </div>
                                <div class="card-body d-flex flex-column">
                                    <h6 class="card-title">
                                        <a href="<?php echo BASE_URL . '?page=product-detail&id=' . $relatedProduct['product_id']; ?>" class="text-decoration-none">
                                            <?php echo htmlspecialchars($relatedProduct['product_name']); ?>
                                        </a>
                                    </h6>
                                                                         <div class="product-price mt-auto">
                                         <?php if ($minPrice): ?>
                                             <span class="related-product-price" data-price="<?php echo (int)$minPrice; ?>">
                                                 <?php echo number_format($minPrice, 0, ',', '.'); ?> ₫
                                             </span>
                                         <?php else: ?>
                                             <span class="text-muted">Liên hệ</span>
                                         <?php endif; ?>
                                     </div>
                                    <?php if (!empty($relatedVariants)): ?>
                                        <div class="product-variants mt-2">
                                            <div class="variant-chips d-flex flex-wrap gap-1">
                                                <?php foreach (array_slice($relatedVariants, 0, 3) as $variant): ?>
                                                    <span class="variant-chip">
                                                        <?php echo htmlspecialchars($variant['size']); ?>
                                                    </span>
                                                <?php endforeach; ?>
                                                <?php if (count($relatedVariants) > 3): ?>
                                                    <span class="variant-chip more">+<?php echo count($relatedVariants) - 3; ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12">
                        <div class="text-center py-4">
                            <p class="text-muted">Không có sản phẩm liên quan.</p>
                        </div>
                    </div>
                <?php endif; ?>
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
        var originalPrice = parseFloat(sizeBtn.getAttribute('data-original-price') || '0');
        
        // Update price display - only for the main product price, not related products
        // Use a more specific selector to avoid affecting other price elements on the page
        var mainProductCard = sizeBtn.closest('.col-md-6'); // Get the main product card container
        var priceEl = mainProductCard ? mainProductCard.querySelector('#js-price') : null;
        
        // Debug logging to help troubleshoot
        console.log('Size button clicked:', {
            sizeBtn: sizeBtn,
            mainProductCard: mainProductCard,
            priceEl: priceEl,
            price: price,
            originalPrice: originalPrice
        });
        
        if (priceEl) {
            // Check if this product has promotional pricing
            var hasDiscount = priceEl.closest('.product-price').querySelector('.text-decoration-line-through');
            
            if (hasDiscount) {
                // This is a promotional product - update both original and discounted prices
                var originalPriceEl = priceEl.closest('.product-price').querySelector('.text-decoration-line-through');
                var discountPercent = parseFloat(priceEl.closest('.product-price').querySelector('.badge').textContent.replace('-', '').replace('%', ''));
                
                if (originalPriceEl) {
                    originalPriceEl.textContent = new Intl.NumberFormat('vi-VN').format(originalPrice) + ' ₫';
                }
                
                // Calculate and update discounted price
                var discountedPrice = originalPrice * (1 - discountPercent / 100);
                priceEl.textContent = new Intl.NumberFormat('vi-VN').format(discountedPrice) + ' ₫';
                priceEl.setAttribute('data-price', String(discountedPrice));
                
                // Debug logging
                console.log('Updated promotional price:', {
                    original: originalPrice,
                    discounted: discountedPrice,
                    discountPercent: discountPercent
                });
            } else {
                // Regular product - just update the price
                priceEl.textContent = new Intl.NumberFormat('vi-VN').format(price) + ' ₫';
                priceEl.setAttribute('data-price', String(price));
                
                // Debug logging
                console.log('Updated regular price:', price);
            }
        }
        
        // Update hidden variant ID
        var hidden = document.getElementById('js-variant-id');
        if (hidden) { hidden.value = variantId; }
    }
});
</script>
