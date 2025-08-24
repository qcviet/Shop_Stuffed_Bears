<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/PostModel.php';

$database = new Database();
$db = $database->getConnection();
$postModel = new PostModel($db);

// Get posts for display
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 6;
$offset = ($page - 1) * $limit;
$search = $_GET['search'] ?? '';

$posts = $postModel->getAll($limit, $offset, $search);
$totalPosts = $postModel->getTotalCount($search);
$totalPages = ceil($totalPosts / $limit);

// Get recent posts for sidebar
$recentPosts = $postModel->getRecent(5);
?>

<?php include __DIR__ . '/../includes/global.php'; ?>
<?php include __DIR__ . '/header.php'; ?>
<?php include __DIR__ . '/subnav.php'; ?>

<div class="container py-5">
    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h2">Bài viết & Tin tức</h1>
                <?php if (!empty($search)): ?>
                    <span class="text-muted">Kết quả tìm kiếm: "<?php echo htmlspecialchars($search); ?>"</span>
                <?php endif; ?>
            </div>

            <!-- Search Form -->
            <form class="mb-4" method="GET">
                <div class="input-group">
                    <input type="text" class="form-control" name="search" placeholder="Tìm kiếm bài viết..." 
                           value="<?php echo htmlspecialchars($search); ?>">
                    <button class="btn btn-primary" type="submit">
                        <i class="fas fa-search"></i> Tìm kiếm
                    </button>
                </div>
            </form>

            <!-- Posts Grid -->
            <?php if (empty($posts)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-newspaper fa-3x text-muted mb-3"></i>
                    <h4 class="text-muted">Không có bài viết nào</h4>
                    <?php if (!empty($search)): ?>
                        <p class="text-muted">Thử tìm kiếm với từ khóa khác</p>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($posts as $post): ?>
                        <div class="col-md-6 mb-4">
                            <article class="card h-100 shadow-sm">
                                <?php if ($post['thumbnail']): ?>
                                    <img src="<?php echo BASE_URL . '/uploads/posts/' . $post['thumbnail']; ?>" 
                                         class="card-img-top" alt="<?php echo htmlspecialchars($post['title']); ?>"
                                         style="height: 200px; object-fit: cover;">
                                <?php else: ?>
                                    <div class="card-img-top bg-light d-flex align-items-center justify-content-center" 
                                         style="height: 200px;">
                                        <i class="fas fa-newspaper fa-3x text-muted"></i>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <a href="<?php echo BASE_URL; ?>?page=article&id=<?php echo $post['post_id']; ?>" 
                                           class="text-decoration-none text-dark">
                                            <?php echo htmlspecialchars($post['title']); ?>
                                        </a>
                                    </h5>
                                    <p class="card-text text-muted">
                                        <?php 
                                        $excerpt = strip_tags($post['content']);
                                        echo strlen($excerpt) > 100 ? substr($excerpt, 0, 100) . '...' : $excerpt;
                                        ?>
                                    </p>
                                </div>
                                
                                <div class="card-footer bg-transparent">
                                    <small class="text-muted">
                                        <i class="fas fa-calendar-alt me-1"></i>
                                        <?php echo date('d/m/Y', strtotime($post['created_at'])); ?>
                                    </small>
                                    <a href="<?php echo BASE_URL; ?>?page=article&id=<?php echo $post['post_id']; ?>" 
                                       class="btn btn-outline-primary btn-sm float-end">
                                        Đọc thêm
                                    </a>
                                </div>
                            </article>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                    <nav aria-label="Articles pagination" class="mt-4">
                        <ul class="pagination justify-content-center">
                            <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>">
                                        <i class="fas fa-chevron-left"></i> Trước
                                    </a>
                                </li>
                            <?php endif; ?>
                            
                            <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                                <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                            
                            <?php if ($page < $totalPages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>">
                                        Sau <i class="fas fa-chevron-right"></i>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-fire me-2"></i>Bài viết gần đây</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($recentPosts)): ?>
                        <p class="text-muted">Chưa có bài viết nào</p>
                    <?php else: ?>
                        <?php foreach ($recentPosts as $recentPost): ?>
                            <div class="d-flex mb-3">
                                <?php if ($recentPost['thumbnail']): ?>
                                    <img src="<?php echo BASE_URL . '/uploads/posts/' . $recentPost['thumbnail']; ?>" 
                                         class="rounded me-3" alt="Thumbnail" 
                                         style="width: 60px; height: 60px; object-fit: cover;">
                                <?php else: ?>
                                    <div class="bg-light rounded me-3 d-flex align-items-center justify-content-center" 
                                         style="width: 60px; height: 60px;">
                                        <i class="fas fa-newspaper text-muted"></i>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">
                                        <a href="<?php echo BASE_URL; ?>?page=article&id=<?php echo $recentPost['post_id']; ?>" 
                                           class="text-decoration-none text-dark">
                                            <?php echo htmlspecialchars($recentPost['title']); ?>
                                        </a>
                                    </h6>
                                    <small class="text-muted">
                                        <?php echo date('d/m/Y', strtotime($recentPost['created_at'])); ?>
                                    </small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Categories or Tags could go here -->
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-tags me-2"></i>Thông tin</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-2">
                        <i class="fas fa-info-circle me-2"></i>
                        Tổng cộng: <strong><?php echo $totalPosts; ?></strong> bài viết
                    </p>
                    <p class="text-muted mb-0">
                        <i class="fas fa-clock me-2"></i>
                        Cập nhật lần cuối: <?php echo date('d/m/Y H:i'); ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/footer.php'; ?>
