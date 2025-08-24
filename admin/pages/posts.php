<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/PostModel.php';

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

$postModel = new PostModel($db);

// Get posts for display
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;
$search = $_GET['search'] ?? '';

try {
    $posts = $postModel->getAll($limit, $offset, $search);
    $totalPosts = $postModel->getTotalCount($search);
    $totalPages = ceil($totalPosts / $limit);
} catch (Exception $e) {
    echo '<div class="alert alert-warning">
        <h5>Data Loading Error</h5>
        <p>Unable to load posts data: ' . htmlspecialchars($e->getMessage()) . '</p>
        <p>This might be because the posts table does not exist. Please run the database setup script.</p>
    </div>';
    $posts = [];
    $totalPosts = 0;
    $totalPages = 0;
}
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Quản lý Bài viết</h1>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPostModal">
        <i class="fas fa-plus"></i> Thêm Bài viết
    </button>
</div>

<!-- Search and Filter -->
<div class="row mb-3">
    <div class="col-md-6">
        <form class="d-flex" method="GET">
            <input class="form-control me-2" type="search" name="search" placeholder="Tìm kiếm bài viết..." value="<?php echo htmlspecialchars($search); ?>">
            <button class="btn btn-outline-success" type="submit">Tìm</button>
        </form>
    </div>
</div>

<!-- Posts Table -->
<div class="table-responsive">
    <table class="table table-striped table-hover">
        <thead>
            <tr>
                <th>ID</th>
                <th>Ảnh</th>
                <th>Tiêu đề</th>
                <th>Ngày tạo</th>
                <th>Ngày cập nhật</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($posts)): ?>
                <tr>
                    <td colspan="6" class="text-center">Không có bài viết nào</td>
                </tr>
            <?php else: ?>
                <?php foreach ($posts as $post): ?>
                    <tr>
                        <td><?php echo $post['post_id']; ?></td>
                        <td>
                            <?php if ($post['thumbnail']): ?>
                                <img src="<?php echo BASE_URL . '/uploads/posts/' . $post['thumbnail']; ?>" 
                                     alt="Thumbnail" style="width: 50px; height: 50px; object-fit: cover;">
                            <?php else: ?>
                                <div class="bg-secondary text-white d-flex align-items-center justify-content-center" 
                                     style="width: 50px; height: 50px;">
                                    <i class="fas fa-image"></i>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($post['title']); ?></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($post['created_at'])); ?></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($post['updated_at'])); ?></td>
                        <td>
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-sm btn-info" onclick="viewPost(<?php echo $post['post_id']; ?>)">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-warning" onclick="editPost(<?php echo $post['post_id']; ?>)">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-danger" onclick="deletePost(<?php echo $post['post_id']; ?>)">
                                    <i class="fas fa-trash"></i>
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
    <nav aria-label="Posts pagination">
        <ul class="pagination justify-content-center">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                    <a class="page-link" href="?page=posts&p=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>">
                        <?php echo $i; ?>
                    </a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>
<?php endif; ?>
