<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/PostModel.php';

$database = new Database();
$db = $database->getConnection();
$postModel = new PostModel($db);

// Get article ID from URL
$articleId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$articleId) {
    header('Location: ' . BASE_URL . '?page=articles');
    exit;
}

// Get article details
$article = $postModel->getById($articleId);

if (!$article) {
    header('Location: ' . BASE_URL . '?page=articles');
    exit;
}

// Get recent posts for sidebar
$recentPosts = $postModel->getRecent(5);

// Get related posts (excluding current article)
$relatedPosts = $postModel->getAll(3, 0, '');
$relatedPosts = array_filter($relatedPosts, function($post) use ($articleId) {
    return $post['post_id'] != $articleId;
});
$relatedPosts = array_slice($relatedPosts, 0, 3);
?>

<?php include __DIR__ . '/../includes/global.php'; ?>
<?php include __DIR__ . '/header.php'; ?>
<?php include __DIR__ . '/subnav.php'; ?>

<div class="container py-5">
    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="<?php echo BASE_URL; ?>?page=home" class="text-decoration-none">Trang chủ</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="<?php echo BASE_URL; ?>?page=articles" class="text-decoration-none">Bài viết</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        <?php echo htmlspecialchars($article['title']); ?>
                    </li>
                </ol>
            </nav>

            <!-- Article Content -->
            <article class="blog-post">
                <header class="mb-4">
                    <h1 class="display-5 fw-bold mb-3"><?php echo htmlspecialchars($article['title']); ?></h1>
                    
                    <div class="d-flex align-items-center text-muted mb-3">
                        <i class="fas fa-calendar-alt me-2"></i>
                        <span><?php echo date('d/m/Y H:i', strtotime($article['created_at'])); ?></span>
                        
                        <?php if ($article['updated_at'] != $article['created_at']): ?>
                            <span class="ms-3">
                                <i class="fas fa-edit me-1"></i>
                                Cập nhật: <?php echo date('d/m/Y H:i', strtotime($article['updated_at'])); ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </header>

                <?php if ($article['thumbnail']): ?>
                    <div class="mb-4">
                        <img src="<?php echo BASE_URL . '/uploads/posts/' . $article['thumbnail']; ?>" 
                             class="img-fluid rounded shadow" 
                             alt="<?php echo htmlspecialchars($article['title']); ?>">
                    </div>
                <?php endif; ?>

                <div class="blog-post-content">
                    <?php echo $article['content']; ?>
                </div>

                <!-- Article Footer -->
                <footer class="mt-5 pt-4 border-top">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted">
                            <small>
                                <i class="fas fa-user me-1"></i>
                                Admin
                            </small>
                        </div>
                        
                        <div class="d-flex gap-2">
                            <button class="btn btn-outline-primary btn-sm" onclick="shareArticle()">
                                <i class="fas fa-share-alt me-1"></i> Chia sẻ
                            </button>
                            <button class="btn btn-outline-secondary btn-sm" onclick="printArticle()">
                                <i class="fas fa-print me-1"></i> In
                            </button>
                        </div>
                    </div>
                </footer>
            </article>

            <!-- Related Articles -->
            <?php if (!empty($relatedPosts)): ?>
                <section class="mt-5">
                    <h3 class="h4 mb-4">Bài viết liên quan</h3>
                    <div class="row">
                        <?php foreach ($relatedPosts as $relatedPost): ?>
                            <div class="col-md-6 mb-3">
                                <div class="card h-100">
                                    <?php if ($relatedPost['thumbnail']): ?>
                                        <img src="<?php echo BASE_URL . '/uploads/posts/' . $relatedPost['thumbnail']; ?>" 
                                             class="card-img-top" alt="Thumbnail" 
                                             style="height: 150px; object-fit: cover;">
                                    <?php else: ?>
                                        <div class="card-img-top bg-light d-flex align-items-center justify-content-center" 
                                             style="height: 150px;">
                                            <i class="fas fa-newspaper fa-2x text-muted"></i>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="card-body">
                                        <h6 class="card-title">
                                            <a href="<?php echo BASE_URL; ?>?page=article&id=<?php echo $relatedPost['post_id']; ?>" 
                                               class="text-decoration-none text-dark">
                                                <?php echo htmlspecialchars($relatedPost['title']); ?>
                                            </a>
                                        </h6>
                                        <small class="text-muted">
                                            <?php echo date('d/m/Y', strtotime($relatedPost['created_at'])); ?>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Recent Posts -->
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-fire me-2"></i>Bài viết gần đây</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($recentPosts)): ?>
                        <p class="text-muted">Chưa có bài viết nào</p>
                    <?php else: ?>
                        <?php foreach ($recentPosts as $recentPost): ?>
                            <?php if ($recentPost['post_id'] != $articleId): ?>
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
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Back to Articles -->
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <a href="<?php echo BASE_URL; ?>?page=articles" class="btn btn-primary">
                        <i class="fas fa-arrow-left me-2"></i>Quay lại danh sách bài viết
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function shareArticle() {
    if (navigator.share) {
        navigator.share({
            title: '<?php echo addslashes($article['title']); ?>',
            url: window.location.href
        });
    } else {
        // Fallback: copy URL to clipboard
        navigator.clipboard.writeText(window.location.href).then(function() {
            alert('Đã sao chép link bài viết vào clipboard!');
        });
    }
}

function printArticle() {
    window.print();
}
</script>

<?php include __DIR__ . '/footer.php'; ?>
