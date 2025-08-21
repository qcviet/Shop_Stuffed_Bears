<?php
/**
 * Users: New Topic
 *
 * @package shopgauyeu
 * @author quocviet
 * @since 0.0.1
 */
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/CategoryModel.php';
require_once __DIR__ . '/../../models/ProductModel.php';
require_once __DIR__ . '/../../models/ProductVariantModel.php';

// Prepare data: pick 4 categories and fetch newest product for each
$db = (new Database())->getConnection();
$categoryModel = $db ? new CategoryModel($db) : null;
$productModel = $db ? new ProductModel($db) : null;
$variantModel = $db ? new ProductVariantModel($db) : null;

// Optionally configure 4 category IDs to feature (in order of images below).
// Here we set: T1=Blind Box (2), T2=Bình Nước (66), T3=Dụng Cụ Trang Điểm (67), T4=Team Capybara (68)
$configuredCategoryIds = [2, 66, 67, 68];

// Units per category (override). Example: Bình Nước uses ml; stuffed bears use cm.
$unitsByCategoryId = [
	66 => 'ml',
	// Add stuffed bear category id here, e.g., 70 => 'cm'
];

$allCategories = $categoryModel ? $categoryModel->getAll() : [];

if (!empty($configuredCategoryIds)) {
    $featuredCategoryIds = array_slice($configuredCategoryIds, 0, 4);
    $categoryNames = [];
    foreach ($featuredCategoryIds as $cid) {
        $cat = $categoryModel->getById((int)$cid);
        if ($cat) {
            $categoryNames[(int)$cid] = $cat['category_name'];
        }
    }
} else {
    $featuredCategories = array_slice($allCategories, 0, 4);
    $featuredCategoryIds = [];
    $categoryNames = [];
    foreach ($featuredCategories as $cat) {
        $cid = (int)$cat['category_id'];
        $featuredCategoryIds[] = $cid;
        $categoryNames[$cid] = $cat['category_name'];
    }
}

// Map 4 decorative images to the 4 featured categories by index
$categoryImages = ['ct1.png', 'ct2.png', 'ct3.png', 'ct4.png'];

// Fetch newest product (max 1) for each category
$newestProductsByCat = [];
if ($productModel) {
    foreach ($featuredCategoryIds as $cid) {
        $prods = $productModel->getAll(1, 0, $cid);
        $newestProductsByCat[$cid] = $prods && isset($prods[0]) ? $prods[0] : null;
    }
}
?>

<div class="new-topic py-3 py-md-4">
    <div class="container px-2 px-md-4">
        <h1 class="new-topic-title text-center fw-bold mb-3">Chủ Đề Mới</h1>
        <div class="row g-2 g-md-3 justify-content-center mb-3">
            <?php for ($i = 0; $i < 4; $i++):
                $cid = $featuredCategoryIds[$i] ?? null;
                $img = $categoryImages[$i] ?? null;
                $href = $cid ? (BASE_URL . '?page=category&cat=' . (int)$cid) : '#';
                $title = ($cid && isset($categoryNames[$cid])) ? $categoryNames[$cid] : 'Danh mục';
            ?>
            <div class="col-6 col-md-3">
                <div class="new-topic-category h-100">
                    <div class="new-topic-category-card">
                        <a href="<?php echo $href; ?>">
                            <img src="<?php echo BASE_URL . '/assets/images/' . ($img ?: 'ct1.png'); ?>" alt="<?php echo htmlspecialchars($title); ?>"
                                class="img-fluid w-100">
                        </a>
                    </div>
                </div>
            </div>
            <?php endfor; ?>
        </div>

        <div class="new-topic-products">
            <div class="row g-2 g-md-3 justify-content-center">
                <?php for ($i = 0; $i < 4; $i++):
                    $cid = $featuredCategoryIds[$i] ?? null;
                    $product = $cid && isset($newestProductsByCat[$cid]) ? $newestProductsByCat[$cid] : null;
                    $productImage = $product && !empty($product['image_url']) ? (BASE_URL . '/' . $product['image_url']) : (BASE_URL . '/assets/images/sp1.jpeg');
                    $productName = $product ? $product['product_name'] : 'Chưa có sản phẩm';
                    $productPriceValue = ($product && isset($product['price'])) ? (float)$product['price'] : 0;
                    $productPrice = $productPriceValue > 0 ? number_format($productPriceValue, 0, ',', '.') . 'đ' : '';
                    $variants = ($variantModel && $product) ? $variantModel->getByProductId((int)$product['product_id']) : [];
                    // Determine unit to display for sizes based on category
                    $unit = '';
                    if ($product) {
                        $prodCid = isset($product['category_id']) ? (int)$product['category_id'] : null;
                        if ($prodCid && isset($unitsByCategoryId[$prodCid])) {
                            $unit = $unitsByCategoryId[$prodCid];
                        } else {
                            $catName = strtolower($product['category_name'] ?? '');
                            if (preg_match('/bình|binh|nước|nuoc|bottle|thermos/', $catName)) {
                                $unit = 'ml';
                            } elseif (preg_match('/gấu|gau|teddy|bear/', $catName)) {
                                $unit = 'cm';
                            }
                        }
                    }
                    // Choose the variant with the smallest price as default active
                    $minIdx = 0; $minPrice = null;
                    if (!empty($variants)) {
                        foreach ($variants as $idx => $v) {
                            $vp = isset($v['price']) ? (float)$v['price'] : 0;
                            if ($minPrice === null || $vp < $minPrice) { $minPrice = $vp; $minIdx = $idx; }
                        }
                    }
                    // Determine display price: prefer min variant price if variants exist, else base product price
                    $displayPriceValue = !empty($variants) ? (float)$variants[$minIdx]['price'] : $productPriceValue;
                    $displayPrice = $displayPriceValue > 0 ? number_format($displayPriceValue, 0, ',', '.') . 'đ' : '';
                ?>
                <div class="col-6 col-md-3">
                    <div class="new-topic-product h-100">
                        <div class="new-topic-product-card text-center">
                            <?php $detailLink = ($product && isset($product['product_id'])) ? (BASE_URL . '?page=product-detail&id=' . (int)$product['product_id']) : '#'; ?>
                            <a href="<?php echo $detailLink; ?>">
                                <img src="<?php echo $productImage; ?>" alt="<?php echo htmlspecialchars($productName); ?>">
                            </a>
                            <a class="product-title-link d-inline-block mt-2" href="<?php echo $detailLink; ?>">
                                <h2 class="new-topic-product-card-title" style="font-size:16px;font-weight:600;margin:0;"><?php echo htmlspecialchars($productName); ?></h2>
                            </a>
                            <p class="new-topic-product-card-price js-price" data-price="<?php echo (int)$displayPriceValue; ?>"><?php echo $displayPrice; ?></p>
                            <?php if (!empty($variants)): ?>
                                <div class="new-topic-product-sizes d-flex flex-wrap justify-content-center gap-2">
                                    <?php ?>
                                    <?php foreach ($variants as $idx => $v): 
                                        $sz = $v['size'];
                                        $vp = isset($v['price']) ? (float)$v['price'] : 0;
                                        $isActive = ($idx === $minIdx);
                                        $label = $sz;
                                        if ($unit && is_numeric($sz)) { $label = $sz . $unit; }
                                    ?>
                                        <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 js-size-chip <?php echo $isActive ? 'active' : ''; ?>" data-price="<?php echo (int)$vp; ?>" data-variant-id="<?php echo (int)$v['variant_id']; ?>"><?php echo htmlspecialchars($label); ?></button>
                                    <?php endforeach; ?>
                                </div>
                                <input type="hidden" class="js-variant-id" value="<?php echo (int)$variants[$minIdx]['variant_id']; ?>" />
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endfor; ?>
            </div>
        </div>
        <script src="<?php echo BASE_URL . '/assets/js/new-topic.js'; ?>"></script>
        <div class="new-topic-button-all mt-3">
            <a href="#">Xem thêm</a>
        </div>
    </div>
</div>