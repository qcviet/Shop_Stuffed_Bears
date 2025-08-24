<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../controller/AppController.php';

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Initialize app controller (it handles database connection internally)
$appController = new AppController();

// Check if user is logged in (you may need to implement proper session management)
$currentUser = [
    'username' => 'Admin',
    'full_name' => 'Administrator'
];

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Shop Gau Yeu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .sidebar {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            min-height: 100vh;
            color: white;
        }
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 12px 20px;
            border-radius: 8px;
            margin: 2px 10px;
            transition: all 0.3s ease;
        }
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: white;
            background: rgba(255, 255, 255, 0.1);
        }
        .sidebar .nav-link i {
            margin-right: 10px;
            width: 20px;
        }
        .main-content {
            padding: 20px;
        }
        .top-bar {
            background: white;
            padding: 15px 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        .content-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }
        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .stats-card h3 {
            margin: 0;
            font-size: 2rem;
        }
        .stats-card p {
            margin: 0;
            opacity: 0.8;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 px-0">
                <div class="sidebar">
                    <div class="p-3">
                        <h5 class="mb-0">
                            <i class="bi bi-shield-lock"></i> Admin Panel
                        </h5>
                        <small class="text-muted">Shop Gau Yeu</small>
                    </div>
                    
                    <nav class="nav flex-column">
                        <a class="nav-link active" href="index.php">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                        <a class="nav-link" href="index.php?page=products">
                            <i class="bi bi-box"></i> Products
                        </a>
                        <a class="nav-link" href="index.php?page=categories">
                            <i class="bi bi-tags"></i> Categories
                        </a>
                        <a class="nav-link" href="index.php?page=orders">
                            <i class="bi bi-cart"></i> Orders
                        </a>
                         <a class="nav-link" href="index.php?page=reports">
                             <i class="bi bi-graph-up"></i> Reports
                         </a>
                        <a class="nav-link" href="index.php?page=users">
                            <i class="bi bi-people"></i> Users
                        </a>
                        <a class="nav-link" href="index.php?page=posts">
                            <i class="bi bi-newspaper"></i> Bài viết
                        </a>
                        <a class="nav-link" href="index.php?page=promotions">
                            <i class="bi bi-gift"></i> Khuyến mãi
                        </a>
                        <a class="nav-link" href="index.php?page=settings">
                            <i class="bi bi-gear"></i> Settings
                        </a>
                        <hr class="my-3">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>">
                            <i class="bi bi-house"></i> View Site
                        </a>
                        <a class="nav-link" href="logout.php">
                            <i class="bi bi-box-arrow-right"></i> Logout
                        </a>
                    </nav>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-9 col-lg-10">
                <div class="main-content">
                    <!-- Top Bar -->
                    <div class="top-bar d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0">Dashboard</h4>
                            <small class="text-muted">Welcome back, <?php echo htmlspecialchars($currentUser['full_name'] ?? $currentUser['username'] ?? 'Admin'); ?>!</small>
                        </div>
                        <div class="text-end">
                            <small class="text-muted">Last login: <?php echo date('M d, Y H:i'); ?></small>
                        </div>
                    </div>
                    
                    <!-- Stats Cards -->
                    <div class="row mb-4">
                        <?php
                        // Get real statistics from database
                        $stats = $appController && $appController->isConnected() ? $appController->getDashboardStatistics() : [
                            'products' => 0,
                            'users' => 0,
                            'orders' => ['total' => 0, 'total_revenue' => 0]
                        ];
                        ?>
                        <div class="col-md-3">
                            <div class="stats-card">
                                <h3><?php echo $stats['products'] ?? 0; ?></h3>
                                <p>Total Products</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stats-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                                <h3><?php echo $stats['orders']['total'] ?? 0; ?></h3>
                                <p>Total Orders</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stats-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                                <h3><?php echo $stats['users'] ?? 0; ?></h3>
                                <p>Total Users</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stats-card" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                                <h3><?php echo $stats['orders']['total_revenue'] ?? 0; ?>₫</h3>
                                <p>Total Revenue</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Content Area -->
                    <div class="content-card">
                        <?php
                        $page = $_GET['page'] ?? 'dashboard';
                        
                        switch ($page) {
                            case 'dashboard':
                                include 'pages/dashboard-content.php';
                                break;
                            case 'products':
                                include 'pages/products.php';
                                break;
                            case 'categories':
                                include 'pages/categories.php';
                                break;
                            case 'orders':
                                include 'pages/orders.php';
                                break;
                             case 'reports':
                                 include 'pages/reports.php';
                                 break;
                            case 'users':
                                include 'pages/users.php';
                                break;
                            case 'posts':
                                include 'pages/posts.php';
                                break;
                            case 'promotions':
                                include 'pages/promotions.php';
                                break;
                            case 'settings':
                                include 'pages/settings.php';
                                break;
                            default:
                                include 'pages/dashboard-content.php';
                                break;
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add/Edit Post Modal -->
    <div class="modal fade" id="addPostModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="postModalTitle">Thêm Bài viết mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="postForm">
                    <div class="modal-body">
                        <input type="hidden" id="post_id" name="post_id">
                        
                        <div class="mb-3">
                            <label for="title" class="form-label">Tiêu đề *</label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="content" class="form-label">Nội dung *</label>
                            <textarea class="form-control" id="content" name="content" rows="10" required></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="thumbnail" class="form-label">Ảnh đại diện</label>
                            <input type="file" class="form-control" id="thumbnail" name="thumbnail" accept="image/*">
                            <div id="currentThumbnail" class="mt-2"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                        <button type="submit" class="btn btn-primary">Lưu</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View Post Modal -->
    <div class="modal fade" id="viewPostModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Xem Bài viết</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="postContent"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add/Edit Promotion Modal -->
    <div class="modal fade" id="addPromotionModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="promotionModalTitle">Thêm Khuyến mãi mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="promotionForm">
                    <div class="modal-body">
                        <input type="hidden" id="promotion_id" name="promotion_id">
                        
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="promotion_title" class="form-label">Tiêu đề *</label>
                                    <input type="text" class="form-control" id="promotion_title" name="title" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="is_active" class="form-label">Trạng thái</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" checked>
                                        <label class="form-check-label" for="is_active">
                                            Hoạt động
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Mô tả ngắn</label>
                            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="discount_percent" class="form-label">Giảm giá (%)</label>
                                    <input type="number" class="form-control" id="discount_percent" name="discount_percent" 
                                           min="0" max="100" step="0.01" value="0">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="start_date" class="form-label">Ngày bắt đầu *</label>
                                    <input type="date" class="form-control" id="start_date" name="start_date" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="end_date" class="form-label">Ngày kết thúc *</label>
                                    <input type="date" class="form-control" id="end_date" name="end_date" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="promotion_type" class="form-label">Loại khuyến mãi *</label>
                                    <select class="form-control" id="promotion_type" name="promotion_type" required>
                                        <option value="general">Khuyến mãi chung</option>
                                        <option value="category">Khuyến mãi danh mục</option>
                                        <option value="product">Khuyến mãi sản phẩm</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3" id="target_selection_container">
                            <label for="target_id" class="form-label">Mục tiêu</label>
                            <select class="form-control" id="target_id" name="target_id" required>
                                <option value="">Chọn mục tiêu</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                        <button type="submit" class="btn btn-primary">Lưu</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View Promotion Modal -->
    <div class="modal fade" id="viewPromotionModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Xem Khuyến mãi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="promotionContent"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script src="assets/js/admin-crud.js"></script>
    <script>
        // Define BASE_URL for JavaScript
        const BASE_URL = '<?php echo BASE_URL; ?>';
        
        // Highlight active nav link
        document.addEventListener('DOMContentLoaded', function() {
            const currentPage = '<?php echo $_GET['page'] ?? 'dashboard'; ?>';
            const navLinks = document.querySelectorAll('.nav-link');
            
            navLinks.forEach(link => {
                link.classList.remove('active');
                if (link.getAttribute('href').includes(currentPage) || 
                    (currentPage === 'dashboard' && link.getAttribute('href') === 'index.php')) {
                    link.classList.add('active');
                }
            });

            // Set default dates for promotion form
            const today = new Date().toISOString().split('T')[0];
            const nextMonth = new Date();
            nextMonth.setMonth(nextMonth.getMonth() + 1);
            
            if (document.getElementById('start_date')) {
                document.getElementById('start_date').value = today;
            }
            if (document.getElementById('end_date')) {
                document.getElementById('end_date').value = nextMonth.toISOString().split('T')[0];
            }
        });

        // Post-specific JavaScript
        let isPostEditMode = false;

        if (document.getElementById('postForm')) {
            document.getElementById('postForm').addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                formData.append('action', isPostEditMode ? 'update' : 'create');
                
                fetch('actions/post_actions.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        location.reload();
                    } else {
                        alert('Lỗi: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Có lỗi xảy ra');
                });
            });
        }

        function viewPost(postId) {
            fetch(`actions/post_actions.php?action=get&post_id=${postId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const post = data.data;
                        document.getElementById('postContent').innerHTML = `
                            <h4>${post.title}</h4>
                            ${post.thumbnail ? `<img src="${BASE_URL}/uploads/posts/${post.thumbnail}" class="img-fluid mb-3" alt="Thumbnail">` : ''}
                            <div class="mb-3">
                                <small class="text-muted">Ngày tạo: ${new Date(post.created_at).toLocaleDateString('vi-VN')}</small>
                            </div>
                            <div>${post.content}</div>
                        `;
                        new bootstrap.Modal(document.getElementById('viewPostModal')).show();
                    } else {
                        alert('Lỗi: ' + data.message);
                    }
                });
        }

        function editPost(postId) {
            isPostEditMode = true;
            document.getElementById('postModalTitle').textContent = 'Chỉnh sửa Bài viết';
            document.getElementById('post_id').value = postId;
            
            fetch(`actions/post_actions.php?action=get&post_id=${postId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const post = data.data;
                        document.getElementById('title').value = post.title;
                        document.getElementById('content').value = post.content;
                        
                        if (post.thumbnail) {
                            document.getElementById('currentThumbnail').innerHTML = `
                                <img src="${BASE_URL}/uploads/posts/${post.thumbnail}" style="max-width: 200px; height: auto;">
                            `;
                        }
                        
                        new bootstrap.Modal(document.getElementById('addPostModal')).show();
                    } else {
                        alert('Lỗi: ' + data.message);
                    }
                });
        }

        function deletePost(postId) {
            if (confirm('Bạn có chắc chắn muốn xóa bài viết này?')) {
                const formData = new FormData();
                formData.append('action', 'delete');
                formData.append('post_id', postId);
                
                fetch('actions/post_actions.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        location.reload();
                    } else {
                        alert('Lỗi: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Có lỗi xảy ra');
                });
            }
        }

        // Promotion-specific JavaScript
        let isPromotionEditMode = false;

        if (document.getElementById('promotionForm')) {
            document.getElementById('promotionForm').addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                formData.append('action', isPromotionEditMode ? 'update' : 'create');
                
                fetch('actions/promotion_actions.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        location.reload();
                    } else {
                        alert('Lỗi: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Có lỗi xảy ra');
                });
            });
        }

        function viewPromotion(promotionId) {
            fetch(`actions/promotion_actions.php?action=get&promotion_id=${promotionId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const promotion = data.data;
                        const discountText = promotion.discount_percent > 0 
                            ? `Giảm ${promotion.discount_percent}%`
                            : 'Không giảm giá';
                        
                        document.getElementById('promotionContent').innerHTML = `
                            <h4>${promotion.title}</h4>
                            <div class="mb-3">
                                <span class="badge bg-${promotion.is_active ? 'success' : 'secondary'}">
                                    ${promotion.is_active ? 'Hoạt động' : 'Không hoạt động'}
                                </span>
                                <span class="badge bg-info ms-2">${discountText}</span>
                                <span class="badge bg-primary ms-2">${promotion.promotion_type === 'general' ? 'Chung' : promotion.promotion_type === 'category' ? 'Danh mục' : 'Sản phẩm'}</span>
                            </div>
                            <div class="mb-3">
                                <small class="text-muted">
                                    Từ: ${new Date(promotion.start_date).toLocaleDateString('vi-VN')} 
                                    đến: ${new Date(promotion.end_date).toLocaleDateString('vi-VN')}
                                </small>
                            </div>
                            ${promotion.description ? `<p class="mb-3"><strong>Mô tả:</strong> ${promotion.description}</p>` : ''}
                        `;
                        new bootstrap.Modal(document.getElementById('viewPromotionModal')).show();
                    } else {
                        alert('Lỗi: ' + data.message);
                    }
                });
        }

        function editPromotion(promotionId) {
            isPromotionEditMode = true;
            document.getElementById('promotionModalTitle').textContent = 'Chỉnh sửa Khuyến mãi';
            document.getElementById('promotion_id').value = promotionId;
            
            fetch(`actions/promotion_actions.php?action=get&promotion_id=${promotionId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const promotion = data.data;
                        document.getElementById('promotion_title').value = promotion.title;
                        document.getElementById('description').value = promotion.description || '';
                        document.getElementById('discount_percent').value = promotion.discount_percent;
                        document.getElementById('start_date').value = promotion.start_date;
                        document.getElementById('end_date').value = promotion.end_date;
                        document.getElementById('is_active').checked = promotion.is_active == 1;
                        
                        // Set promotion type and target
                        document.getElementById('promotion_type').value = promotion.promotion_type;
                        document.getElementById('target_id').value = promotion.target_id;

                        // Update target selection container visibility
                        const targetSelectionContainer = document.getElementById('target_selection_container');
                        if (promotion.promotion_type === 'category') {
                            targetSelectionContainer.style.display = 'block';
                        } else if (promotion.promotion_type === 'product') {
                            targetSelectionContainer.style.display = 'block';
                        } else {
                            targetSelectionContainer.style.display = 'none';
                        }
                        
                        new bootstrap.Modal(document.getElementById('addPromotionModal')).show();
                    } else {
                        alert('Lỗi: ' + data.message);
                    }
                });
        }

        function togglePromotionStatus(promotionId) {
            const formData = new FormData();
            formData.append('action', 'toggle_status');
            formData.append('promotion_id', promotionId);
            
            fetch('actions/promotion_actions.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert('Lỗi: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Có lỗi xảy ra');
            });
        }

        function deletePromotion(promotionId) {
            if (confirm('Bạn có chắc chắn muốn xóa khuyến mãi này?')) {
                const formData = new FormData();
                formData.append('action', 'delete');
                formData.append('promotion_id', promotionId);
                
                fetch('actions/promotion_actions.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        location.reload();
                    } else {
                        alert('Lỗi: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Có lỗi xảy ra');
                });
            }
        }

        // Handle promotion type change
        if (document.getElementById('promotion_type')) {
            document.getElementById('promotion_type').addEventListener('change', function() {
                const promotionType = this.value;
                const targetContainer = document.getElementById('target_selection_container');
                const targetSelect = document.getElementById('target_id');
                
                if (promotionType === 'general') {
                    targetContainer.style.display = 'none';
                    targetSelect.required = false;
                } else {
                    targetContainer.style.display = 'block';
                    targetSelect.required = true;
                    
                    // Clear previous options
                    targetSelect.innerHTML = '<option value="">Chọn mục tiêu</option>';
                    
                    if (promotionType === 'category') {
                        // Fetch and populate categories
                        fetch('actions/category_actions.php?action=list')
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    data.data.forEach(category => {
                                        const option = document.createElement('option');
                                        option.value = category.category_id;
                                        option.textContent = category.category_name;
                                        targetSelect.appendChild(option);
                                    });
                                }
                            });
                    } else if (promotionType === 'product') {
                        // Fetch and populate products
                        fetch('actions/product_actions.php?action=list')
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    data.data.forEach(product => {
                                        const option = document.createElement('option');
                                        option.value = product.product_id;
                                        option.textContent = product.product_name;
                                        targetSelect.appendChild(option);
                                    });
                                }
                            });
                    }
                }
            });
        }

        // Reset forms when modals are closed
        if (document.getElementById('addPostModal')) {
            document.getElementById('addPostModal').addEventListener('hidden.bs.modal', function () {
                document.getElementById('postForm').reset();
                document.getElementById('post_id').value = '';
                document.getElementById('currentThumbnail').innerHTML = '';
                document.getElementById('postModalTitle').textContent = 'Thêm Bài viết mới';
                isPostEditMode = false;
            });
        }

        if (document.getElementById('addPromotionModal')) {
            document.getElementById('addPromotionModal').addEventListener('hidden.bs.modal', function () {
                document.getElementById('promotionForm').reset();
                document.getElementById('promotion_id').value = '';
                document.getElementById('promotionModalTitle').textContent = 'Thêm Khuyến mãi mới';
                isPromotionEditMode = false;
                
                // Reset default dates
                const today = new Date().toISOString().split('T')[0];
                const nextMonth = new Date();
                nextMonth.setMonth(nextMonth.getMonth() + 1);
                
                if (document.getElementById('start_date')) {
                    document.getElementById('start_date').value = today;
                }
                if (document.getElementById('end_date')) {
                    document.getElementById('end_date').value = nextMonth.toISOString().split('T')[0];
                }

                // Reset promotion type and target selection
                if (document.getElementById('promotion_type')) {
                    document.getElementById('promotion_type').value = 'general';
                }
                if (document.getElementById('target_id')) {
                    document.getElementById('target_id').value = '';
                }
                if (document.getElementById('target_selection_container')) {
                    document.getElementById('target_selection_container').style.display = 'none';
                }
            });
        }
    </script>
</body>
</html> 