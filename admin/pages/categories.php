<?php
// Check if database connection is available
if (!$db) {
    echo '<div class="alert alert-danger">Database connection failed. Please check your configuration.</div>';
    return;
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="mb-0">Category Management</h5>
    <button class="btn btn-primary add-category-btn">
        <i class="bi bi-plus-circle"></i> Add New Category
    </button>
</div>

<!-- Search Form -->
<div class="card mb-4">
    <div class="card-body">
        <form id="categorySearchForm" class="row g-3">
            <div class="col-md-6">
                <label for="categorySearch" class="form-label">Search Categories</label>
                <input type="text" class="form-control" id="categorySearch" name="search" placeholder="Search by name, description...">
            </div>
            <div class="col-md-3">
                <label class="form-label">&nbsp;</label>
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search"></i> Search
                    </button>
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label">&nbsp;</label>
                <div class="d-grid">
                    <button type="button" class="btn btn-outline-secondary" id="clearCategorySearch">
                        <i class="bi bi-x-circle"></i> Clear
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="table-responsive">
            <table class="table table-hover" id="categoriesTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Products</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Categories will be loaded dynamically -->
                </tbody>
            </table>
            <div id="categoriesPagination"></div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Category Statistics</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Total Categories</label>
                    <h4 class="text-primary" id="totalCategories">0</h4>
                </div>
                <div class="mb-3">
                    <label class="form-label">Active Categories</label>
                    <h4 class="text-success" id="activeCategories">0</h4>
                </div>
                <div class="mb-3">
                    <label class="form-label">Total Products</label>
                    <h4 class="text-info" id="totalProducts">0</h4>
                </div>
                <div class="mb-3">
                    <label class="form-label">Average Products per Category</label>
                    <h4 class="text-warning" id="avgProducts">0</h4>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Category Modal -->
<div class="modal fade" id="categoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="categoryForm">
                <div class="modal-body">
                    <input type="hidden" name="category_id">
                    
                    <div class="mb-3">
                        <label for="category_name" class="form-label">Category Name *</label>
                        <input type="text" class="form-control" id="category_name" name="category_name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Category</button>
                </div>
            </form>
        </div>
    </div>
</div> 