<?php
require_once __DIR__ . '/../../models/ProductModel.php';
require_once __DIR__ . '/../../models/CategoryModel.php';

// Initialize models only if database connection is available
$productModel = $db ? new ProductModel($db) : null;
$categoryModel = $db ? new CategoryModel($db) : null;

// Get categories for dropdown
$categories = $categoryModel ? $categoryModel->getAll() : [];
?>

<?php
// Check if database connection is available
if (!$db) {
    echo '<div class="alert alert-danger">Database connection failed. Please check your configuration.</div>';
    return;
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="mb-0">Product Management</h5>
    <button class="btn btn-primary add-product-btn">
        <i class="bi bi-plus-circle"></i> Add New Product
    </button>
</div>

<div class="table-responsive">
    <table class="table table-hover" id="productsTable">
        <thead>
            <tr>
                <th>ID</th>
                <th>Image</th>
                <th>Name</th>
                <th>Category</th>
                <th>Price</th>
                <th>Stock</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <!-- Products will be loaded dynamically -->
        </tbody>
    </table>
</div>

<div id="productsPagination"></div>

<!-- Product Modal -->
<div class="modal fade" id="productModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="productForm">
                <div class="modal-body">
                    <input type="hidden" name="product_id">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="product_name" class="form-label">Product Name *</label>
                                <input type="text" class="form-control" id="product_name" name="product_name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="category_id" class="form-label">Category *</label>
                                <select class="form-select" id="category_id" name="category_id" required>
                                    <option value="">Select Category</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo $category['category_id']; ?>">
                                            <?php echo htmlspecialchars($category['category_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="image" class="form-label">Product Images</label>
                                <input type="file" class="form-control" id="image" name="image[]" accept="image/*" multiple>
                                <small class="text-muted">You can select multiple images. JPG/PNG, up to 2MB each.</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Images</label>
                                <div id="imageList" class="d-flex gap-2 flex-wrap"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label mb-1">Colors</label>
                        <div class="d-flex gap-2 align-items-center mb-2">
                            <input type="text" class="form-control form-control-sm" id="newColorName" placeholder="Add a color (e.g. Red)" style="max-width: 220px;">
                            <button type="button" class="btn btn-primary btn-sm" id="addColorBtn">Add</button>
                        </div>
                        <div id="productColorsChips" class="d-flex flex-wrap gap-2"></div>
                        <table class="d-none"><tbody id="colorsTableInline"></tbody></table>
                    </div>

                    <div class="mb-3">
                        <label class="form-label mb-1">Variants</label>
                        <div class="text-muted small mb-2">Add size, price and stock per variant</div>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle" id="variantsTableInline">
                                <thead>
                                    <tr>
                                        <th style="width: 25%">Size</th>
                                        <th style="width: 25%">Price (VND)</th>
                                        <th style="width: 25%">Stock</th>
                                        <th style="width: 25%">Actions</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                        <button type="button" class="btn btn-primary btn-sm" id="addVariantRowInline">Add Variant</button>
                        <div class="text-muted small mt-2" id="variantsInlineHint" style="display:none;">Variants will be saved together with the product.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Product</button>
                </div>
            </form>
        </div>
    </div>
</div>