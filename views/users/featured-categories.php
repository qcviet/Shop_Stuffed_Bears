<?php
/**
 * Users: Featured Categories Section
 * Shows selected categories with their latest products and promotions
 *
 * @package shopgauyeu
 * @author quocviet
 * @since 0.0.1
 */
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/CategoryModel.php';
require_once __DIR__ . '/../../models/ProductModel.php';
require_once __DIR__ . '/../../models/ProductVariantModel.php';

// Get database connection
$db = (new Database())->getConnection();
$categoryModel = $db ? new CategoryModel($db) : null;
$productModel = $db ? new ProductModel($db) : null;
$variantModel = $db ? new ProductVariantModel($db) : null;

// ========================================
// CONFIGURATION - CHANGE CATEGORIES HERE
// ========================================
// Configure the 4 category IDs you want to feature (change these as you like)
// Just replace the numbers with your desired category IDs
$featuredCategoryIds = [70, 69, 72, 73]; // <-- change IDs as you like

// Example: $featuredCategoryIds = [1, 5, 10, 15]; // Different categories
// Example: $featuredCategoryIds = [70, 69, 72, 73]; // Like new-products section
// 
// To use the same categories as new-products section, copy this line:
// $featuredCategoryIds = [70, 69, 72, 73, 4, 5]; // (6 categories like new-products)

// Units per category (for size display) - optional
// This helps display sizes correctly (e.g., 500ml instead of just 500)
$categoryUnits = [
    66 => 'ml',  // Bình Nước uses milliliters
    2 => 'cm',   // Blind Box/Gấu bông uses centimeters
    67 => '',    // Makeup Tools - no specific unit
    68 => 'cm'   // Team Capybara - stuffed animals use centimeters
    // Add more: category_id => 'unit'
];

// Get the configured categories
$prominentCategories = [];
if ($categoryModel) {
    foreach ($featuredCategoryIds as $cid) {
        if ($cid) {
            $cat = $categoryModel->getById((int)$cid);
            if ($cat) {
                $prominentCategories[] = $cat;
            }
        }
    }
}

// Get latest 4 products for each category with promotions
$productsByCategory = [];
if ($productModel && $variantModel) {
    foreach ($prominentCategories as $category) {
        try {
            $categoryId = $category['category_id'];
            
            // Try to get products with promotions first, then fallback to regular products
            $products = $productModel->getNewestProductsWithPromotions(4, 0, $categoryId);
            
            // If no promotional products, get regular latest products
            if (empty($products)) {
                $products = $productModel->getAll(4, 0, $categoryId);
            }
            
            // Add variant information and promotional data to each product
            foreach ($products as &$product) {
                try {
                    $variants = $variantModel->getByProductId($product['product_id']);
                    $product['variants'] = $variants;
                    
                    // Get the first image for the product
                    if (!empty($product['image_url'])) {
                        $product['display_image'] = BASE_URL . '/' . $product['image_url'];
                    } else {
                        $product['display_image'] = BASE_URL . '/assets/images/sp1.jpeg';
                    }
                    
                    // Check for promotions
                    $hasDiscount = isset($product['discount_percent']) && $product['discount_percent'] > 0;
                    if ($hasDiscount) {
                        $discountInfo = $productModel->calculateDiscountedPriceForProduct($product);
                        $product['discount_info'] = $discountInfo;
                    }
                    
                } catch (Exception $e) {
                    error_log("Error getting variants for product {$product['product_id']}: " . $e->getMessage());
                    $product['variants'] = [];
                    $product['display_image'] = BASE_URL . '/assets/images/sp1.jpeg';
                    $product['discount_info'] = null;
                }
            }
            
            $productsByCategory[$categoryId] = $products;
        } catch (Exception $e) {
            error_log("Error getting products for category {$category['category_id']}: " . $e->getMessage());
            $productsByCategory[$category['category_id']] = [];
        }
    }
}
?>

<link rel="stylesheet" href="<?php echo FEATURED_CATEGORIES_CSS; ?>">

<div class="featured-categories py-4 py-md-5">
    <div class="container px-2 px-md-4">
        <h2 class="featured-categories-title text-center fw-bold mb-4">Danh Mục Nổi Bật</h2>
        
        <?php if (empty($prominentCategories)): ?>
            <div class="text-center py-5">
                <p class="text-muted">Không có danh mục nào để hiển thị.</p>
                <p class="text-muted small">Vui lòng kiểm tra lại category IDs trong file featured-categories.php</p>
            </div>
        <?php else: ?>
            <?php foreach ($prominentCategories as $category): ?>
                <div class="category-section mb-5">
                    <div class="category-header d-flex justify-content-between align-items-center mb-3">
                        <h3 class="category-name fw-bold">
                            <?php echo htmlspecialchars($category['category_name']); ?>
                        </h3>
                        <a href="<?php echo BASE_URL . '?page=category&cat=' . $category['category_id']; ?>" class="view-all-link">
                            Xem tất cả <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                    
                    <div class="products-grid">
                        <?php 
                        $categoryProducts = $productsByCategory[$category['category_id']] ?? [];
                        if (empty($categoryProducts)): ?>
                            <div class="text-center py-4">
                                <p class="text-muted">Không có sản phẩm nào trong danh mục này.</p>
                            </div>
                        <?php else: ?>
                            <div class="row g-3">
                                <?php foreach ($categoryProducts as $product): 
                                    $variants = $product['variants'] ?? [];
                                    $minPrice = null;
                                    $minVariantId = null;
                                    
                                    // Find the variant with minimum price
                                    if (!empty($variants)) {
                                        foreach ($variants as $variant) {
                                            $price = (float)($variant['price'] ?? 0);
                                            if ($minPrice === null || $price < $minPrice) {
                                                $minPrice = $price;
                                                $minVariantId = $variant['variant_id'];
                                            }
                                        }
                                    }
                                    
                                    $displayPrice = $minPrice ? number_format($minPrice, 0, ',', '.') . 'đ' : '';
                                    
                                    // Check for promotions
                                    $hasDiscount = isset($product['discount_info']);
                                    $discountInfo = $product['discount_info'] ?? null;
                                    
                                    // Determine unit to display for sizes based on category
                                    $unit = '';
                                    $prodCid = isset($product['category_id']) ? (int)$product['category_id'] : null;
                                    if ($prodCid && isset($categoryUnits[$prodCid])) {
                                        $unit = $categoryUnits[$prodCid];
                                    } else {
                                        $catName = strtolower($product['category_name'] ?? '');
                                        if (preg_match('/bình|binh|nước|nuoc|bottle|thermos/', $catName)) {
                                            $unit = 'ml';
                                        } elseif (preg_match('/gấu|gau|teddy|bear/', $catName)) {
                                            $unit = 'cm';
                                        }
                                    }
                                ?>
                                    <div class="col-6 col-md-3">
                                        <div class="product-card h-100">
                                            <div class="product-image-container">
                                                <a href="<?php echo BASE_URL . '?page=product-detail&id=' . $product['product_id']; ?>">
                                                    <img src="<?php echo $product['display_image']; ?>" 
                                                         alt="<?php echo htmlspecialchars($product['product_name']); ?>"
                                                         class="product-image">
                                                </a>
                                            </div>
                                            
                                            <div class="product-info p-3">
                                                <h4 class="product-name">
                                                    <a href="<?php echo BASE_URL . '?page=product-detail&id=' . $product['product_id']; ?>">
                                                        <?php echo htmlspecialchars($product['product_name']); ?>
                                                    </a>
                                                </h4>
                                                
                                                <div class="product-price">
                                                    <?php if ($hasDiscount && $discountInfo): ?>
                                                        <span class="original-price"><?php echo number_format($discountInfo['original_price'], 0, ',', '.'); ?> ₫</span>
                                                                                                                 <span class="discounted-price featured-product-price" data-price="<?php echo (int)$discountInfo['discounted_price']; ?>">
                                                             <?php echo number_format($discountInfo['discounted_price'], 0, ',', '.'); ?> ₫
                                                         </span>
                                                        <span class="promotion-badge">-<?php echo $discountInfo['discount_percent']; ?>%</span>
                                                        <?php if ($discountInfo['promotion_title']): ?>
                                                            <div class="promotion-info">
                                                                <i class="fas fa-gift me-1"></i><?php echo htmlspecialchars($discountInfo['promotion_title']); ?>
                                                            </div>
                                                        <?php endif; ?>
                                                    <?php else: ?>
                                                                                                                 <span class="featured-product-price" data-price="<?php echo (int)$minPrice; ?>"><?php echo $displayPrice; ?></span>
                                                    <?php endif; ?>
                                                </div>
                                                
                                                <?php if (!empty($variants)): ?>
                                                    <div class="product-variants mt-2">
                                                        <div class="variant-chips d-flex flex-wrap gap-1">
                                                            <?php foreach (array_slice($variants, 0, 3) as $variant): 
                                                                $sz = $variant['size'];
                                                                $label = $sz;
                                                                if ($unit && is_numeric($sz)) { 
                                                                    $label = $sz . $unit; 
                                                                }
                                                            ?>
                                                                <span class="variant-chip">
                                                                    <?php echo htmlspecialchars($label); ?>
                                                                </span>
                                                            <?php endforeach; ?>
                                                            <?php if (count($variants) > 3): ?>
                                                                <span class="variant-chip more">+<?php echo count($variants) - 3; ?></span>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
