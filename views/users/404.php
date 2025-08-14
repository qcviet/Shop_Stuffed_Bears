<?php
/**
 * 404 Error Page
 *
 * @package shopgauyeu
 * @author quocviet
 * @since 0.0.1
 */
?>

<?php include __DIR__ . '/../includes/global.php'; ?>

<?php include __DIR__ . '/header.php'; ?>
<?php include __DIR__ . '/subnav.php'; ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 text-center">
            <div class="error-404-content">
                <div class="mb-4">
                    <i class="bi bi-exclamation-triangle" style="font-size: 4rem; color: #dc3545;"></i>
                </div>
                <h1 class="mb-4">404 - Trang Không Tìm Thấy</h1>
                <p class="lead mb-4">
                    Xin lỗi, trang bạn đang tìm kiếm không tồn tại hoặc đã được di chuyển.
                </p>
                <div class="mb-4">
                    <p class="text-muted">
                        URL: <code><?php echo $_SERVER['REQUEST_URI']; ?></code>
                    </p>
                </div>
                <div class="d-flex justify-content-center gap-3">
                    <a href="<?php echo BASE_URL; ?>" class="btn btn-primary">
                        <i class="bi bi-house-heart-fill"></i> Về Trang Chủ
                    </a>
                    <button onclick="history.back()" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Quay Lại
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.error-404-content {
    padding: 3rem 0;
}

.error-404-content code {
    background-color: #f8f9fa;
    padding: 0.2rem 0.4rem;
    border-radius: 0.25rem;
    font-size: 0.875rem;
}
</style>

<?php include __DIR__ . '/footer.php'; ?> 