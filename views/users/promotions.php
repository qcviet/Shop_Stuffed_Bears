<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/PromotionModel.php';

$database = new Database();
$db = $database->getConnection();
$promotionModel = new PromotionModel($db);

// Get active promotions
$activePromotions = $promotionModel->getActive();

// Get recent promotions (including inactive ones for display)
$recentPromotions = $promotionModel->getRecent(10);
?>

<?php include __DIR__ . '/../includes/global.php'; ?>
<?php include __DIR__ . '/header.php'; ?>
<?php include __DIR__ . '/subnav.php'; ?>

<div class="container py-5">
    <!-- Header -->
    <div class="text-center mb-5">
        <h1 class="display-4 fw-bold text-primary mb-3">
            <i class="fas fa-gift me-3"></i>Khuyến mãi & Ưu đãi
        </h1>
        <p class="lead text-muted">Khám phá những ưu đãi hấp dẫn nhất từ Shop Gấu Yêu</p>
    </div>

    <!-- Active Promotions -->
    <?php if (!empty($activePromotions)): ?>
        <section class="mb-5">
            <h2 class="h3 mb-4 text-success">
                <i class="fas fa-fire me-2"></i>Khuyến mãi đang diễn ra
            </h2>
            <div class="row">
                <?php foreach ($activePromotions as $promotion): ?>
                    <div class="col-lg-6 mb-4">
                        <div class="card h-100 border-success border-2 shadow-lg">
                            <div class="card-header bg-success text-white">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">
                                        <i class="fas fa-star me-2"></i><?php echo htmlspecialchars($promotion['title']); ?>
                                    </h5>
                                    <span class="badge bg-warning text-dark">
                                        <i class="fas fa-clock me-1"></i>Đang diễn ra
                                    </span>
                                </div>
                            </div>
                            
                            <div class="card-body">
                                <?php if ($promotion['description']): ?>
                                    <p class="card-text"><?php echo htmlspecialchars($promotion['description']); ?></p>
                                <?php endif; ?>
                                
                                <div class="mb-3">
                                    <span class="badge bg-danger fs-6 p-2">
                                        <i class="fas fa-percentage me-1"></i>
                                        Giảm <?php echo $promotion['discount_percent']; ?>%
                                    </span>
                                    
                                    <?php 
                                    $typeLabels = [
                                        'general' => '<span class="badge bg-primary ms-2">Khuyến mãi chung</span>',
                                        'category' => '<span class="badge bg-info ms-2">Khuyến mãi danh mục</span>',
                                        'product' => '<span class="badge bg-warning ms-2">Khuyến mãi sản phẩm</span>'
                                    ];
                                    echo $typeLabels[$promotion['promotion_type']] ?? '';
                                    ?>
                                </div>
                                
                                <div class="row text-muted mb-3">
                                    <div class="col-6">
                                        <small>
                                            <i class="fas fa-calendar-plus me-1"></i>
                                            Bắt đầu: <?php echo date('d/m/Y', strtotime($promotion['start_date'])); ?>
                                        </small>
                                    </div>
                                    <div class="col-6">
                                        <small>
                                            <i class="fas fa-calendar-minus me-1"></i>
                                            Kết thúc: <?php echo date('d/m/Y', strtotime($promotion['end_date'])); ?>
                                        </small>
                                    </div>
                                </div>
                                
                                <button class="btn btn-success w-100" onclick="viewPromotion(<?php echo $promotion['promotion_id']; ?>)">
                                    <i class="fas fa-eye me-2"></i>Xem chi tiết
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

    <!-- All Promotions -->
    <section>
        <h2 class="h3 mb-4">
            <i class="fas fa-list me-2"></i>Tất cả khuyến mãi
        </h2>
        
        <?php if (empty($recentPromotions)): ?>
            <div class="text-center py-5">
                <i class="fas fa-gift fa-3x text-muted mb-3"></i>
                <h4 class="text-muted">Chưa có khuyến mãi nào</h4>
                <p class="text-muted">Hãy quay lại sau để xem những ưu đãi mới nhất!</p>
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($recentPromotions as $promotion): ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card h-100 shadow-sm <?php echo $promotion['is_active'] ? 'border-primary' : 'border-secondary'; ?>">
                            <div class="card-img-top bg-light d-flex align-items-center justify-content-center" 
                                 style="height: 180px;">
                                <i class="fas fa-gift fa-3x text-muted"></i>
                            </div>
                            
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($promotion['title']); ?></h5>
                                
                                <?php if ($promotion['description']): ?>
                                    <p class="card-text text-muted">
                                        <?php 
                                        $excerpt = strip_tags($promotion['description']);
                                        echo strlen($excerpt) > 80 ? substr($excerpt, 0, 80) . '...' : $excerpt;
                                        ?>
                                    </p>
                                <?php endif; ?>
                                
                                <div class="mb-3">
                                    <span class="badge bg-danger">
                                        -<?php echo $promotion['discount_percent']; ?>%
                                    </span>
                                    
                                    <?php 
                                    $typeLabels = [
                                        'general' => '<span class="badge bg-primary ms-2">Chung</span>',
                                        'category' => '<span class="badge bg-info ms-2">Danh mục</span>',
                                        'product' => '<span class="badge bg-warning ms-2">Sản phẩm</span>'
                                    ];
                                    echo $typeLabels[$promotion['promotion_type']] ?? '';
                                    ?>
                                    
                                    <span class="badge bg-<?php echo $promotion['is_active'] ? 'success' : 'secondary'; ?> ms-2">
                                        <?php echo $promotion['is_active'] ? 'Hoạt động' : 'Không hoạt động'; ?>
                                    </span>
                                </div>
                                
                                <div class="text-muted mb-3">
                                    <small>
                                        <i class="fas fa-calendar me-1"></i>
                                        <?php echo date('d/m/Y', strtotime($promotion['start_date'])); ?> - 
                                        <?php echo date('d/m/Y', strtotime($promotion['end_date'])); ?>
                                    </small>
                                </div>
                            </div>
                            
                            <div class="card-footer bg-transparent">
                                <button class="btn btn-outline-primary btn-sm w-100" 
                                        onclick="viewPromotion(<?php echo $promotion['promotion_id']; ?>)">
                                    <i class="fas fa-eye me-1"></i>Xem chi tiết
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</div>

<!-- Promotion Detail Modal -->
<div class="modal fade" id="promotionModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="promotionModalTitle">Chi tiết khuyến mãi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="promotionModalBody">
                <!-- Content will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                <button type="button" class="btn btn-primary" onclick="sharePromotion()">
                    <i class="fas fa-share-alt me-1"></i>Chia sẻ
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let currentPromotion = null;

function viewPromotion(promotionId) {
    // For now, we'll show a simple alert. In a real implementation, 
    // you would fetch the promotion details from the server
    alert('Chức năng xem chi tiết khuyến mãi sẽ được cập nhật sớm!');
    
    // Example of how it would work with AJAX:
    /*
    fetch('promotion_actions.php?action=get&promotion_id=' + promotionId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                currentPromotion = data.data;
                showPromotionModal(data.data);
            } else {
                alert('Lỗi: ' + data.message);
            }
        });
    */
}

function showPromotionModal(promotion) {
    const modal = new bootstrap.Modal(document.getElementById('promotionModal'));
    const title = document.getElementById('promotionModalTitle');
    const body = document.getElementById('promotionModalBody');
    
    title.textContent = promotion.title;
    
    const discountText = promotion.discount_percent > 0 
        ? `Giảm ${promotion.discount_percent}%`
        : promotion.discount_amount > 0 
            ? `Giảm ${Number(promotion.discount_amount).toLocaleString('vi-VN')}đ`
            : 'Không giảm giá';
    
    body.innerHTML = `
        ${promotion.thumbnail ? `<img src="${BASE_URL}/uploads/promotions/${promotion.thumbnail}" class="img-fluid mb-3 rounded" alt="Thumbnail">` : ''}
        
        <div class="mb-3">
            <span class="badge bg-${promotion.is_active ? 'success' : 'secondary'} me-2">
                ${promotion.is_active ? 'Hoạt động' : 'Không hoạt động'}
            </span>
            <span class="badge bg-danger">${discountText}</span>
        </div>
        
        <div class="mb-3">
            <strong>Thời gian:</strong><br>
            Từ: ${new Date(promotion.start_date).toLocaleDateString('vi-VN')}<br>
            Đến: ${new Date(promotion.end_date).toLocaleDateString('vi-VN')}
        </div>
        
        ${promotion.description ? `<p class="mb-3"><strong>Mô tả:</strong> ${promotion.description}</p>` : ''}
        
        <div class="mb-3">
            <strong>Nội dung chi tiết:</strong><br>
            ${promotion.content}
        </div>
    `;
    
    modal.show();
}

function sharePromotion() {
    if (currentPromotion) {
        if (navigator.share) {
            navigator.share({
                title: currentPromotion.title,
                text: currentPromotion.description || currentPromotion.title,
                url: window.location.href
            });
        } else {
            // Fallback: copy URL to clipboard
            navigator.clipboard.writeText(window.location.href).then(function() {
                alert('Đã sao chép link khuyến mãi vào clipboard!');
            });
        }
    }
}
</script>

<?php include __DIR__ . '/footer.php'; ?>
