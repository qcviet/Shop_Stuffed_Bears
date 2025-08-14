<?php
/**
 * Coming Soon Page
 *
 * @package shopgauyeu
 * @author quocviet
 * @since 0.0.1
 */

$page = isset($_GET['page']) ? $_GET['page'] : 'unknown';
$pageNames = [
    'teddy-bears' => 'Gấu Bông',
    'blind-box' => 'Blind Box',
    'gifts' => 'Quà Tặng',
    'cartoons' => 'Hoạt Hình',
    'accessories' => 'Phụ Kiện & Gấu Bông',
    'login' => 'Đăng Nhập',
    'register' => 'Đăng Ký',
    'cart' => 'Giỏ Hàng',
    'search' => 'Tìm Kiếm',
    'help' => 'Trợ Giúp',
    'contact' => 'Liên Hệ'
];

$pageTitle = isset($pageNames[$page]) ? $pageNames[$page] : 'Trang Này';
?>

<?php include __DIR__ . '/../includes/global.php'; ?>

<?php include __DIR__ . '/header.php'; ?>
<?php include __DIR__ . '/subnav.php'; ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 text-center">
            <div class="coming-soon-content">
                <div class="mb-4">
                    <i class="bi bi-tools" style="font-size: 4rem; color: #007bff;"></i>
                </div>
                <h1 class="mb-4"><?php echo $pageTitle; ?> - Sắp Ra Mắt!</h1>
                <p class="lead mb-4">
                    Chúng tôi đang nỗ lực để mang đến cho bạn trải nghiệm tốt nhất. 
                    Trang <?php echo $pageTitle; ?> sẽ sớm được hoàn thiện và ra mắt.
                </p>
                <div class="mb-4">
                    <div class="progress" style="height: 10px;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" 
                             role="progressbar" 
                             style="width: 75%" 
                             aria-valuenow="75" 
                             aria-valuemin="0" 
                             aria-valuemax="100">
                        </div>
                    </div>
                    <small class="text-muted mt-2 d-block">75% hoàn thành</small>
                </div>
                <div class="d-flex justify-content-center gap-3">
                    <a href="<?php echo BASE_URL; ?>" class="btn btn-primary">
                        <i class="bi bi-house-heart-fill"></i> Về Trang Chủ
                    </a>
                    <a href="<?php echo BASE_URL; ?>/about" class="btn btn-outline-secondary">
                        <i class="bi bi-info-circle"></i> Về Chúng Tôi
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.coming-soon-content {
    padding: 3rem 0;
}

.progress {
    background-color: #e9ecef;
    border-radius: 10px;
}

.progress-bar {
    background-color: #007bff;
    border-radius: 10px;
}
</style>

<?php include __DIR__ . '/footer.php'; ?> 