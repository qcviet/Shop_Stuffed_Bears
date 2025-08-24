<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/CategoryModel.php';
require_once __DIR__ . '/../../models/ProductModel.php';

$db = (new Database())->getConnection();
$categoryModel = $db ? new CategoryModel($db) : null;
$productModel = $db ? new ProductModel($db) : null;

$categoryParam = isset($_GET['cat']) ? $_GET['cat'] : '';
$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';
$isProductSearch = isset($_GET['search_product']) && $_GET['search_product'] == '1';

// Handle "all" category or specific category
$showAllProducts = ($categoryParam === 'all');
$categoryId = $showAllProducts ? 0 : (int)$categoryParam;

// Pagination settings
$productsPerPage = 12; // Show 12 products per page
$currentPage = isset($_GET['page_num']) ? max(1, (int)$_GET['page_num']) : 1;
$offset = ($currentPage - 1) * $productsPerPage;

// If this is a product search, find similar products
if ($isProductSearch && !empty($searchQuery)) {
    $searchResults = $productModel ? $productModel->searchProductsWithPromotions($searchQuery, '', 10, 0) : [];
    
    if (!empty($searchResults)) {
        // Get the most common category from search results
        $categoryCounts = [];
        foreach ($searchResults as $product) {
            $catId = (int)$product['category_id'];
            $categoryCounts[$catId] = ($categoryCounts[$catId] ?? 0) + 1;
        }
        
        // Use the category with most matching products
        $categoryId = array_keys($categoryCounts, max($categoryCounts))[0];
        
        // Store search results for display
        $searchedProducts = $searchResults;
    } else {
        // If no similar products found, redirect to home page
        header('Location: ' . BASE_URL);
        exit;
    }
}

// If no category specified and not showing all products, redirect to home
if (!$categoryId && !$showAllProducts) {
    header('Location: ' . BASE_URL);
    exit;
}

// Get category info
if ($showAllProducts) {
    $category = ['category_id' => 0, 'category_name' => 'Tất cả sản phẩm'];
} else {
    $category = $categoryModel ? $categoryModel->getById($categoryId) : null;
}

// Read filters
$min = isset($_GET['min']) && $_GET['min'] !== '' ? (int)preg_replace('/[^0-9]/', '', $_GET['min']) : null;
$max = isset($_GET['max']) && $_GET['max'] !== '' ? (int)preg_replace('/[^0-9]/', '', $_GET['max']) : null;
$selectedSize = isset($_GET['size']) ? $_GET['size'] : '';
$selectedColor = isset($_GET['color']) ? $_GET['color'] : '';

// Get available sizes and colors
if ($showAllProducts) {
    $sizesAvailable = $productModel ? $productModel->getAllSizes() : [];
    $colorsAvailable = $productModel ? $productModel->getAllColors() : [];
} else {
    $sizesAvailable = $productModel ? $productModel->getSizesForCategory($categoryId) : [];
    $colorsAvailable = $productModel ? $productModel->getColorsForCategory($categoryId) : [];
}

// If this was a search, show search results, otherwise show category products
if ($isProductSearch && !empty($searchQuery) && !empty($searchedProducts)) {
    $products = $searchedProducts;
    $totalProducts = count($searchedProducts);
    $totalPages = 1;
} else {
    if ($showAllProducts) {
        // Get total count for pagination
        $totalProducts = $productModel ? $productModel->getAllProductsCount($min, $max, $selectedSize ? [$selectedSize] : [], $selectedColor ? [$selectedColor] : []) : 0;
        $totalPages = ceil($totalProducts / $productsPerPage);
        
        // Get all products with promotions (paginated)
        $products = $productModel ? $productModel->getAllProductsWithPromotions($min, $max, $selectedSize ? [$selectedSize] : [], $selectedColor ? [$selectedColor] : [], $productsPerPage, $offset) : [];
    } else {
        // Get total count for pagination
        $totalProducts = $productModel ? $productModel->getCategoryProductsCount($categoryId, $min, $max, $selectedSize ? [$selectedSize] : [], $selectedColor ? [$selectedColor] : []) : 0;
        $totalPages = ceil($totalProducts / $productsPerPage);
        
        // Get category products with promotions (paginated)
        $products = $productModel ? $productModel->getByCategoryWithPromotions($categoryId, $min, $max, $selectedSize ? [$selectedSize] : [], $selectedColor ? [$selectedColor] : [], $productsPerPage, $offset) : [];
    }
}

// Get searched product IDs for highlighting
$searchedProductIds = [];
if ($isProductSearch && !empty($searchQuery) && !empty($searchedProducts)) {
    $searchedProductIds = array_column($searchedProducts, 'product_id');
}
?>

<?php include __DIR__ . '/../includes/global.php'; ?>
<?php include __DIR__ . '/header.php'; ?>

<div class="category-page">
    <div class="container">
        <div class="category-container">
            <div class="row g-0">
                <!-- Beautiful Sidebar -->
                <aside class="col-lg-3">
                    <div class="category-sidebar">
                        <h3>📂 Danh mục</h3>
                        <ul class="category-list">
                            <?php foreach (($categoryModel ? $categoryModel->getAll() : []) as $cat): ?>
                                <li class="category-item">
                                    <a class="category-link <?php echo ((int)$cat['category_id'] === $categoryId) ? 'active' : ''; ?>" 
                                       href="<?php echo BASE_URL . '?page=category&cat=' . (int)$cat['category_id']; ?>">
                                        <?php echo htmlspecialchars($cat['category_name']); ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>

                        <!-- Enhanced Filter Section -->
                        <div class="filter-section">
                            <h4>Bộ lọc tìm kiếm</h4>
                            
                            <form method="get" id="filterForm">
                                <input type="hidden" name="page" value="category" />
                                <input type="hidden" name="cat" value="<?php echo $showAllProducts ? 'all' : (int)$categoryId; ?>" />
                                <input type="hidden" name="page_num" value="1" />
                                
                                <!-- Price Filter -->
                                <div class="price-filter">
                                    <label class="filter-dropdown label">💰 Khoảng giá (VND)</label>
                                    <div class="price-inputs">
                                        <div class="price-input">
                                            <label>Từ</label>
                                            <input type="text" name="min" placeholder="0" 
                                                   value="<?php echo $min ? number_format($min, 0, '.', ',') : ''; ?>" />
                                        </div>
                                        <div class="price-input">
                                            <label>Đến</label>
                                            <input type="text" name="max" placeholder="Không giới hạn" 
                                                   value="<?php echo $max ? number_format($max, 0, '.', ',') : ''; ?>" />
                                        </div>
                                    </div>
                                </div>

                                <!-- Size Filter Dropdown -->
                                <?php if (!empty($sizesAvailable)): ?>
                                <div class="filter-dropdown">
                                    <label for="sizeSelect">📏 Kích thước</label>
                                    <select name="size" id="sizeSelect">
                                        <option value="">Tất cả kích thước</option>
                                        <?php foreach ($sizesAvailable as $sz): ?>
                                            <option value="<?php echo htmlspecialchars($sz); ?>" 
                                                    <?php echo $selectedSize === $sz ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($sz); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <?php endif; ?>

                                <!-- Color Filter Dropdown -->
                                <?php if (!empty($colorsAvailable)): ?>
                                <div class="filter-dropdown">
                                    <label for="colorSelect">🎨 Màu sắc</label>
                                    <select name="color" id="colorSelect">
                                        <option value="">Tất cả màu sắc</option>
                                        <?php foreach ($colorsAvailable as $color): ?>
                                            <option value="<?php echo htmlspecialchars($color); ?>" 
                                                    <?php echo $selectedColor === $color ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($color); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <?php endif; ?>

                                <!-- Filter Buttons -->
                                <button type="submit" class="apply-filters-btn">
                                    <span class="btn-text">🔍 Áp dụng bộ lọc</span>
                                    <span class="loading-spinner" style="display: none;"></span>
                                </button>
                                
                                <?php if ($min || $max || $selectedSize || $selectedColor): ?>
                                <a href="<?php echo BASE_URL . '?page=category&cat=' . ($showAllProducts ? 'all' : (int)$categoryId); ?>" 
                                   class="clear-filters-btn">
                                    🗑️ Xóa bộ lọc
                                </a>
                                <?php endif; ?>
                            </form>
                        </div>
                    </div>
                </aside>

                <!-- Main Content Area -->
                <main class="col-lg-9">
                    <div class="category-main">
                        <!-- Category Header -->
                        <div class="category-header">
                            <h1 class="category-title">
                                <?php echo htmlspecialchars($category ? $category['category_name'] : 'Danh mục'); ?>
                            </h1>
                            <div class="product-count">
                                <?php echo $totalProducts; ?> sản phẩm
                            </div>
                        </div>

                        <!-- Product Grid -->
                        <?php if (!empty($products)): ?>
                            <div class="product-grid">
                                <?php foreach ($products as $p): ?>
                                    <div class="product-card <?php echo (in_array($p['product_id'], $searchedProductIds)) ? 'highlighted' : ''; ?>">
                                        <div class="product-image">
                                            <a href="<?php echo BASE_URL . '?page=product-detail&id=' . (int)$p['product_id']; ?>">
                                                <img src="<?php 
                                                    $img = !empty($p['image_url']) ? $p['image_url'] : 'assets/images/sp1.jpeg';
                                                    echo (strpos($img, 'http') === 0) ? $img : (BASE_URL . '/' . $img);
                                                ?>" alt="<?php echo htmlspecialchars($p['product_name']); ?>">
                                            </a>
                                        </div>
                                        
                                        <div class="product-content">
                                            <a href="<?php echo BASE_URL . '?page=product-detail&id=' . (int)$p['product_id']; ?>">
                                                <h3 class="product-title"><?php echo htmlspecialchars($p['product_name']); ?></h3>
                                            </a>
                                            
                                            <?php 
                                            $variants = [];
                                            if (!empty($p['variants_summary'])) {
                                                $parts = explode('|', $p['variants_summary']);
                                                foreach ($parts as $part) {
                                                    list($vid, $size, $price) = explode(':', $part);
                                                    $variants[] = ['variant_id' => (int)$vid, 'size' => $size, 'price' => (float)$price];
                                                }
                                            }
                                            $initialPrice = isset($variants[0]) ? (int)round($variants[0]['price']) : (isset($p['price']) ? (int)round($p['price']) : 0);
                                            
                                            // Check for promotions
                                            $hasDiscount = isset($p['discount_percent']) && $p['discount_percent'] > 0;
                                            $discountInfo = null;
                                            if ($hasDiscount) {
                                                $discountInfo = $productModel->calculateDiscountedPriceForProduct($p);
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
                                                    <span class="regular-price js-price" data-price="<?php echo $initialPrice; ?>">
                                                        <?php echo $initialPrice > 0 ? number_format($initialPrice, 0, ',', '.') . ' ₫' : '—'; ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <?php if (!empty($variants)): ?>
                                                <div class="product-sizes">
                                                    <?php foreach ($variants as $idx => $v): ?>
                                                        <button type="button" class="size-chip js-size-chip<?php echo $idx === 0 ? ' active' : ''; ?>" 
                                                                data-variant-id="<?php echo (int)$v['variant_id']; ?>" 
                                                                data-price="<?php echo (int)$v['price']; ?>">
                                                            <?php echo htmlspecialchars($v['size']); ?>
                                                        </button>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <div class="product-stock">
                                                📦 Kho: <?php echo (int)$p['stock']; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <!-- Pagination Controls -->
                            <?php if ($totalPages > 1): ?>
                                <div class="pagination-container">
                                    <div class="pagination-info">
                                        Hiển thị <?php echo ($offset + 1); ?>-<?php echo min($offset + $productsPerPage, $totalProducts); ?> 
                                        trong tổng số <?php echo $totalProducts; ?> sản phẩm
                                    </div>
                                    
                                    <nav class="pagination-nav" aria-label="Product pagination">
                                        <ul class="pagination">
                                            <!-- Previous Page -->
                                            <?php if ($currentPage > 1): ?>
                                                <li class="page-item">
                                                    <a class="page-link" href="<?php 
                                                        $params = $_GET;
                                                        $params['page_num'] = $currentPage - 1;
                                                        echo BASE_URL . '?' . http_build_query($params);
                                                    ?>">
                                                        <i class="bi bi-chevron-left"></i> Trước
                                                    </a>
                                                </li>
                                            <?php endif; ?>
                                            
                                            <!-- Page Numbers -->
                                            <?php
                                            $startPage = max(1, $currentPage - 2);
                                            $endPage = min($totalPages, $currentPage + 2);
                                            
                                            if ($startPage > 1): ?>
                                                <li class="page-item">
                                                    <a class="page-link" href="<?php 
                                                        $params = $_GET;
                                                        $params['page_num'] = 1;
                                                        echo BASE_URL . '?' . http_build_query($params);
                                                    ?>">1</a>
                                                </li>
                                                <?php if ($startPage > 2): ?>
                                                    <li class="page-item disabled">
                                                        <span class="page-link">...</span>
                                                    </li>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                            
                                            <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                                                <li class="page-item <?php echo $i === $currentPage ? 'active' : ''; ?>">
                                                    <a class="page-link" href="<?php 
                                                        $params = $_GET;
                                                        $params['page_num'] = $i;
                                                        echo BASE_URL . '?' . http_build_query($params);
                                                    ?>"><?php echo $i; ?></a>
                                                </li>
                                            <?php endfor; ?>
                                            
                                            <?php if ($endPage < $totalPages): ?>
                                                <?php if ($endPage < $totalPages - 1): ?>
                                                    <li class="page-item disabled">
                                                        <span class="page-link">...</span>
                                                    </li>
                                                <?php endif; ?>
                                                <li class="page-item">
                                                    <a class="page-link" href="<?php 
                                                        $params = $_GET;
                                                        $params['page_num'] = $totalPages;
                                                        echo BASE_URL . '?' . http_build_query($params);
                                                    ?>"><?php echo $totalPages; ?></a>
                                                </li>
                                            <?php endif; ?>
                                            
                                            <!-- Next Page -->
                                            <?php if ($currentPage < $totalPages): ?>
                                                <li class="page-item">
                                                    <a class="page-link" href="<?php 
                                                        $params = $_GET;
                                                        $params['page_num'] = $currentPage + 1;
                                                        echo BASE_URL . '?' . http_build_query($params);
                                                    ?>">
                                                        Sau <i class="bi bi-chevron-right"></i>
                                                    </a>
                                                </li>
                                            <?php endif; ?>
                                        </ul>
                                    </nav>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="empty-state">
                                <div class="empty-state-icon">🔍</div>
                                <div class="empty-state-text">Không có sản phẩm phù hợp với bộ lọc của bạn.</div>
                            </div>
                        <?php endif; ?>
                    </div>
                </main>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/footer.php'; ?>

<script src="<?php echo BASE_URL; ?>assets/js/category-page.js"></script>

