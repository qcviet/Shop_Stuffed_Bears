<?php

/**
 * Users: Subnav
 *
 * @package shopgauyeu
 * @author quocviet
 * @since 0.0.1
 */

// Get categories for dynamic menu
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/CategoryModel.php';

$db = (new Database())->getConnection();
$categoryModel = $db ? new CategoryModel($db) : null;
$categories = $categoryModel ? $categoryModel->getAll() : [];
?>

<div class="subnav py-3">
    <div class="container">
        <div class="row">
            <div class="subnav-menu d-flex flex-wrap justify-content-center fw-bold gap-4">
                <!-- Home -->
                <div class="subnav-menu__item">
                    <a href="<?php echo BASE_URL; ?>">
                        <i class="bi bi-house-heart-fill"></i> Trang Chủ
                    </a>
                </div>
                <!-- Show All Products -->
                <div class="subnav-menu__item subnav-menu__item--all">
                    <a href="<?php echo BASE_URL; ?>?page=category&cat=all" class="subnav-menu__link--all">
                        <i class="bi bi-grid-3x3-gap-fill"></i> Tất cả sản phẩm
                    </a>
                </div>
                <!-- Articles -->
                <div class="subnav-menu__item">
                    <a href="<?php echo BASE_URL; ?>?page=articles">
                        <i class="bi bi-newspaper"></i> Bài viết
                    </a>
                </div>

                <!-- Promotions -->
                <div class="subnav-menu__item">
                    <a href="<?php echo BASE_URL; ?>?page=promotions">
                        <i class="bi bi-gift"></i> Khuyến mãi
                    </a>
                </div>

                <!-- About -->
                <div class="subnav-menu__item">
                    <a href="<?php echo BASE_URL; ?>?page=about">
                        <i class="bi bi-info-circle-fill"></i> Về Gấu Yêu
                    </a>
                </div>

                <!-- Contact -->
                <div class="subnav-menu__item">
                    <a href="<?php echo BASE_URL; ?>?page=contact">
                        <i class="bi bi-telephone-fill"></i> Liên Hệ
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>