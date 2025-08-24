<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/PromotionModel.php';
require_once __DIR__ . '/../../models/CategoryModel.php'; // Added for category display
require_once __DIR__ . '/../../models/ProductModel.php'; // Added for product display

$database = new Database();
$db = $database->getConnection();

// Check if database connection is available
if (!$db) {
    echo '<div class="alert alert-danger">
        <h5>Database Connection Error</h5>
        <p>Unable to connect to database. Please check:</p>
        <ul>
            <li>PDO MySQL extension is enabled in php.ini</li>
            <li>Database server is running</li>
            <li>Database credentials are correct</li>
        </ul>
        <p><strong>To enable PDO MySQL:</strong></p>
        <ol>
            <li>Open php.ini file</li>
            <li>Find: <code>;extension=pdo_mysql</code></li>
            <li>Change to: <code>extension=pdo_mysql</code></li>
            <li>Restart Apache/XAMPP</li>
        </ol>
    </div>';
    return;
}

$promotionModel = new PromotionModel($db);

// Get promotions for display
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;
$search = $_GET['search'] ?? '';

try {
    $promotions = $promotionModel->getAll($limit, $offset, $search);
    $totalPromotions = $promotionModel->getTotalCount($search);
    $totalPages = ceil($totalPromotions / $limit);
} catch (Exception $e) {
    echo '<div class="alert alert-warning">
        <h5>Data Loading Error</h5>
        <p>Unable to load promotions data: ' . htmlspecialchars($e->getMessage()) . '</p>
        <p>This might be because the promotions table does not exist. Please run the database setup script.</p>
    </div>';
    $promotions = [];
    $totalPromotions = 0;
    $totalPages = 0;
}
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Quản lý Khuyến mãi</h1>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPromotionModal">
        <i class="fas fa-plus"></i> Thêm Khuyến mãi
    </button>
</div>

<!-- Search and Filter -->
<div class="row mb-3">
    <div class="col-md-6">
        <form class="d-flex" method="GET">
            <input class="form-control me-2" type="search" name="search" placeholder="Tìm kiếm khuyến mãi..." value="<?php echo htmlspecialchars($search); ?>">
            <button class="btn btn-outline-success" type="submit">Tìm</button>
        </form>
    </div>
</div>

<!-- Promotions Table -->
<div class="table-responsive">
    <table class="table table-striped table-hover">
        <thead>
            <tr>
                <th>ID</th>
                <th>Tiêu đề</th>
                <th>Loại</th>
                <th>Mục tiêu</th>
                <th>Giảm giá</th>
                <th>Thời gian</th>
                <th>Trạng thái</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($promotions)): ?>
                <tr>
                    <td colspan="8" class="text-center">Không có khuyến mãi nào</td>
                </tr>
            <?php else: ?>
                <?php foreach ($promotions as $promotion): ?>
                    <tr>
                        <td><?php echo $promotion['promotion_id']; ?></td>
                        <td><?php echo htmlspecialchars($promotion['title']); ?></td>
                        <td>
                            <?php 
                            $typeLabels = [
                                'general' => '<span class="badge bg-primary">Chung</span>',
                                'category' => '<span class="badge bg-info">Danh mục</span>',
                                'product' => '<span class="badge bg-warning">Sản phẩm</span>'
                            ];
                            echo $typeLabels[$promotion['promotion_type']] ?? '<span class="badge bg-secondary">Không xác định</span>';
                            ?>
                        </td>
                        <td>
                            <?php 
                            if ($promotion['promotion_type'] === 'category' && $promotion['target_id']) {
                                // Get category name
                                $categoryModel = new CategoryModel($db);
                                $category = $categoryModel->getById($promotion['target_id']);
                                echo $category ? htmlspecialchars($category['category_name']) : 'Danh mục không tồn tại';
                            } elseif ($promotion['promotion_type'] === 'product' && $promotion['target_id']) {
                                // Get product name
                                $productModel = new ProductModel($db);
                                $product = $productModel->getById($promotion['target_id']);
                                echo $product ? htmlspecialchars($product['product_name']) : 'Sản phẩm không tồn tại';
                            } else {
                                echo '-';
                            }
                            ?>
                        </td>
                        <td>
                            <span class="badge bg-success">-<?php echo $promotion['discount_percent']; ?>%</span>
                        </td>
                        <td>
                            <small>
                                Từ: <?php echo date('d/m/Y', strtotime($promotion['start_date'])); ?><br>
                                Đến: <?php echo date('d/m/Y', strtotime($promotion['end_date'])); ?>
                            </small>
                        </td>
                        <td>
                            <span class="badge bg-<?php echo $promotion['is_active'] ? 'success' : 'secondary'; ?>">
                                <?php echo $promotion['is_active'] ? 'Hoạt động' : 'Không hoạt động'; ?>
                            </span>
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-sm btn-info" onclick="viewPromotion(<?php echo $promotion['promotion_id']; ?>)">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-warning" onclick="editPromotion(<?php echo $promotion['promotion_id']; ?>)">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-danger" onclick="deletePromotion(<?php echo $promotion['promotion_id']; ?>)">
                                    <i class="fas fa-trash"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-<?php echo $promotion['is_active'] ? 'warning' : 'success'; ?>" 
                                        onclick="togglePromotionStatus(<?php echo $promotion['promotion_id']; ?>)">
                                    <i class="fas fa-<?php echo $promotion['is_active'] ? 'pause' : 'play'; ?>"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Pagination -->
<?php if ($totalPages > 1): ?>
    <nav aria-label="Promotions pagination">
        <ul class="pagination justify-content-center">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                    <a class="page-link" href="?page=promotions&p=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>">
                        <?php echo $i; ?>
                    </a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>
<?php endif; ?>
