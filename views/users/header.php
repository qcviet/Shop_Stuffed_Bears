<?php
// Include configuration
require_once __DIR__ . '/../../config/config.php';
?>
<div class="header">
    <div class="container">
        <div class="row">
            <nav class="header-navbar d-flex flex-column flex-md-row justify-content-between align-items-center">
                <button class="hamburger d-md-none" aria-label="Open menu" aria-expanded="false">
                    <span></span><span></span><span></span>
                </button>
                <div class="header-logo">
                    <a href="<?php echo BASE_URL; ?>" >
                        <img src="<?php echo BASE_URL . '/assets/images/logo.png'; ?>" alt="Shop Gau Yeu Logo" class="img-fluid">
                    </a>
                </div>
                <div class="header-search">
                    <form class="search-form" action="<?php echo BASE_URL; ?>" method="get">
                        <div class="search-container">
                            <div class="search-input-container">
                                <input type="text" name="search" id="search" placeholder="Tìm kiếm sản phẩm..." 
                                       value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                                <button type="submit" class="search-btn">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                        </div>
                        <input type="hidden" name="page" value="search">
                    </form>
                </div>
                <ul class="header-menu__items d-flex flex-wrap justify-content-center align-items-center fw-bold mb-0">
                    <li class="header-menu__items-link"><a href="<?php echo BASE_URL; ?>?page=cart"><i class="bi bi-bag-heart"></i></a></li>
                    <?php if (isset($_SESSION['user_id']) && ($_SESSION['role'] ?? '') === 'user'): ?>
                        <li class="header-menu__items-link user-menu">
                            <a href="#" class="user-menu__toggle">
                                <i class="bi bi-person-circle"></i>
                                <span><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                            </a>
                            <ul class="user-menu__dropdown">
                                <li><a href="<?php echo BASE_URL; ?>?page=profile"><i class="bi bi-person"></i> Hồ sơ cá nhân</a></li>
                                <li><a href="<?php echo BASE_URL; ?>?page=profile#orders"><i class="bi bi-bag"></i> Đơn hàng của tôi</a></li>
                                <li><a href="<?php echo BASE_URL; ?>?page=logout"><i class="bi bi-box-arrow-right"></i> Đăng xuất</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="header-menu__items-link-button"><a href="<?php echo BASE_URL; ?>?page=login">Đăng Nhập</a></li>
                        <li class="header-menu__items-link-button"><a href="<?php echo BASE_URL; ?>?page=register">Đăng Ký</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
            <div class="mobile-drawer d-md-none">
                <div class="mobile-drawer__content">
                    <div class="mobile-drawer__header d-flex align-items-center justify-content-between">
                        <strong>Menu</strong>
                        <button class="drawer-close" aria-label="Close menu">&times;</button>
                    </div>
                    <div class="mobile-drawer__body">
                        <?php include __DIR__ . '/subnav.php'; ?>
                    </div>
                </div>
                <div class="mobile-drawer__backdrop"></div>
            </div>
        </div>
    </div>
</div>

<!-- Include header/search/product JS -->
<script>
    const BASE_URL = '<?php echo BASE_URL; ?>';
</script>
<script src="<?php echo BASE_URL; ?>/assets/js/header-dropdown.js"></script>
<script src="<?php echo BASE_URL; ?>/assets/js/user-product.js"></script>
<script src="<?php echo BASE_URL; ?>/assets/js/search.js"></script>