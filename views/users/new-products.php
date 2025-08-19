<?php
/**
 * Users: New Products (by Category)
 */
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/CategoryModel.php';
require_once __DIR__ . '/../../models/ProductModel.php';

$db = (new Database())->getConnection();
$categoryModel = $db ? new CategoryModel($db) : null;
$productModel = $db ? new ProductModel($db) : null;

$categories = $categoryModel ? $categoryModel->getAll() : [];
$activeCategoryId = isset($_GET['cat']) ? (int)$_GET['cat'] : null;
if (!$activeCategoryId && !empty($categories)) {
	$activeCategoryId = (int)$categories[0]['category_id'];
}
$products = $productModel && $activeCategoryId ? $productModel->getAll(12, 0, $activeCategoryId) : [];
?>

<div class="new-products py-5" id="new-products">
	<div class="container">
		<!-- Featured category images (clickable) -->
		<?php 
			$featuredImages = [
				'assets/images/1.png',
				'assets/images/3.png',
				'assets/images/gaubong1.png',
				'assets/images/gaubong5.png',
				'assets/images/gaubong1.png',
				'assets/images/gaubong5.png',
			];
			// Configure the 6 category IDs you want to link to (order matches images above)
			$featuredCategoryIds = [74, 72, 73, 70, 69, 67]; // <-- change IDs as you like
			// Load names for alt/title
			$categoryNames = [];
			if ($categoryModel) {
				foreach ($featuredCategoryIds as $cid) {
					if ($cid) {
						$cat = $categoryModel->getById($cid);
						$categoryNames[$cid] = $cat ? $cat['category_name'] : '';
					}
				}
			}
		?>
		<div class="new-products-bottom mb-4">
			<div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-3 g-md-4">
				<?php for ($i = 0; $i < 6; $i++): 
					$img = $featuredImages[$i];
					$cid = $featuredCategoryIds[$i] ?? null;
					$href = $cid ? (BASE_URL . '?page=category&cat=' . (int)$cid . '#new-products') : '#';
					$title = $cid && isset($categoryNames[$cid]) ? $categoryNames[$cid] : '';
				?>
				<div class="col">
					<a href="<?php echo $href; ?>" class="new-products-bottom-card d-block" title="<?php echo htmlspecialchars($title); ?>">
						<img src="<?php echo BASE_URL . '/' . $img; ?>" class="img-fluid" alt="<?php echo htmlspecialchars($title ?: 'Category'); ?>">
					</a>
				</div>
				<?php endfor; ?>
			</div>
		</div>
	</div>
</div>