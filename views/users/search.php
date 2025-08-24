<?php
// Include necessary models
require_once __DIR__ . '/../../models/ProductModel.php';
require_once __DIR__ . '/../../models/CategoryModel.php';

// Get search parameters
$search_query = $_GET['search'] ?? '';
$page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
$per_page = 12;
$offset = ($page - 1) * $per_page;

// Initialize models
$database = new Database();
$db = $database->getConnection();
$productModel = new ProductModel($db);
$categoryModel = new CategoryModel($db);

// Get search results
$search_results = [];
$total_results = 0;

if (!empty($search_query)) {
    $search_results = $productModel->searchProductsWithPromotions($search_query, '', $per_page, $offset);
    $total_results = $productModel->getSearchCount($search_query, '');
}

$total_pages = ceil($total_results / $per_page);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kết quả tìm kiếm - Shop Gau Yeu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/variables.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/header.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/footer.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/search.css">
    <link rel="stylesheet" href="<?php echo PROMOTIONAL_PRICES_CSS; ?>">
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="search-header">
        <div class="container">
            <h1>Kết quả tìm kiếm</h1>
            <?php if (!empty($search_query)): ?>
                <div class="search-summary">
                    Tìm kiếm "<?php echo htmlspecialchars($search_query); ?>" - Tìm thấy <?php echo $total_results; ?> sản phẩm
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="container">
        <!-- Removed duplicate search bar: rely on main header search input -->
        
        <?php if (empty($search_results)): ?>
            <div class="no-results">
                <i class="bi bi-search"></i>
                <h3>Không tìm thấy sản phẩm</h3>
                <p>Hãy thử tìm kiếm với từ khóa khác.</p>
                <a href="<?php echo BASE_URL; ?>" class="btn btn-primary">Về trang chủ</a>
            </div>
        <?php else: ?>
            <div class="product-grid">
                <?php foreach ($search_results as $product): ?>
                    <div class="product-card">
                        <img src="<?php 
                            $img = $product['image_url'] ?? '';
                            if (empty($img)) { $img = 'assets/images/sp1.jpeg'; }
                            $src = (strpos($img, 'http') === 0) ? $img : (BASE_URL . '/' . $img);
                            echo $src;
                        ?>" 
                             alt="<?php echo htmlspecialchars($product['product_name']); ?>" 
                             class="product-image">
                        <div class="product-info">
                            <a class="product-title-link" href="<?php echo BASE_URL . '?page=product-detail&id=' . (int)$product['product_id']; ?>">
                                <h3 class="product-title"><?php echo htmlspecialchars($product['product_name']); ?></h3>
                            </a>
                            <div class="product-category"><?php echo htmlspecialchars($product['category_name']); ?></div>
                            <?php 
                                $variants = [];
                                if (!empty($product['variants_summary'])) {
                                    $parts = explode('|', $product['variants_summary']);
                                    foreach ($parts as $part) {
                                        list($vid, $size, $price) = explode(':', $part);
                                        $variants[] = ['variant_id' => (int)$vid, 'size' => $size, 'price' => (float)$price];
                                    }
                                }
                                $initialPrice = isset($variants[0]) ? (int)round($variants[0]['price']) : (isset($product['price']) ? (int)round($product['price']) : 0);
                                
                                // Check for promotions
                                $hasDiscount = isset($product['discount_percent']) && $product['discount_percent'] > 0;
                                $discountInfo = null;
                                if ($hasDiscount) {
                                    $discountInfo = $productModel->calculateDiscountedPriceForProduct($product);
                                }
                            ?>
                            <div class="product-price">
                                <?php if ($hasDiscount && $discountInfo): ?>
                                    <span class="original-price"><?php echo number_format($discountInfo['original_price'], 0, ',', '.'); ?> ₫</span>
                                    <span class="discounted-price js-price" data-price="<?php echo (int)$discountInfo['discounted_price']; ?>">
                                        <?php echo number_format($discountInfo['discounted_price'], 0, ',', '.'); ?> ₫
                                    </span>
                                    <span class="promotion-badge">-<?php echo $discountInfo['discount_percent']; ?>%</span>
                                    <?php if ($discountInfo['promotion_title']): ?>
                                        <div class="promotion-info">
                                            <i class="fas fa-gift me-1"></i><?php echo htmlspecialchars($discountInfo['promotion_title']); ?>
                                        </div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="js-price" data-price="<?php echo $initialPrice; ?>">
                                        <?php echo $initialPrice > 0 ? number_format($initialPrice, 0, ',', '.') . ' ₫' : '—'; ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($variants)): ?>
                            <div class="mb-2 d-flex flex-wrap gap-2">
                                <?php foreach ($variants as $idx => $v): ?>
                                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 js-size-chip<?php echo $idx === 0 ? ' active' : ''; ?>" data-variant-id="<?php echo (int)$v['variant_id']; ?>" data-price="<?php echo (int)$v['price']; ?>"><?php echo htmlspecialchars($v['size']); ?></button>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                            <input type="hidden" class="js-variant-id" value="<?php echo !empty($variants) ? (int)$variants[0]['variant_id'] : 0; ?>">
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=search&search=<?php echo urlencode($search_query); ?>&p=<?php echo $page - 1; ?>">
                            <i class="bi bi-chevron-left"></i> Trước
                        </a>
                    <?php endif; ?>
                    
                    <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                        <?php if ($i == $page): ?>
                            <span class="current"><?php echo $i; ?></span>
                        <?php else: ?>
                            <a href="?page=search&search=<?php echo urlencode($search_query); ?>&p=<?php echo $i; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endif; ?>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                        <a href="?page=search&search=<?php echo urlencode($search_query); ?>&p=<?php echo $page + 1; ?>">
                            Sau <i class="bi bi-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    
    <?php include 'footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo BASE_URL; ?>/assets/js/search-page.js"></script>
</body>
</html>
