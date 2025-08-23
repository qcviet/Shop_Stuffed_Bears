<?php
/**
 * User Profile Page
 */
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../controller/AppController.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'user') {
    header('Location: ' . BASE_URL . '/login');
    exit;
}

$error = '';
$success = '';

// Get user data
$appController = new AppController();
$user = $appController->getUserById($_SESSION['user_id']);

if (!$user) {
    header('Location: ' . BASE_URL . '/login');
    exit;
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['profile_update'])) {
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    
    // Validation
    if (empty($full_name) || empty($email)) {
        $error = 'Vui lòng điền đầy đủ thông tin bắt buộc';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email không hợp lệ';
    } else {
        try {
            // Check if email is already used by another user
            if ($email !== $user['email'] && $appController->isEmailExists($email)) {
                $error = 'Email đã được sử dụng bởi tài khoản khác';
            } else {
                // Update user data
                $updateData = [
                    'full_name' => $full_name,
                    'email' => $email,
                    'phone' => $phone,
                    'address' => $address
                ];
                
                if ($appController->updateUser($_SESSION['user_id'], $updateData)) {
                    $success = 'Cập nhật thông tin thành công!';
                    // Refresh user data
                    $user = $appController->getUserById($_SESSION['user_id']);
                    $_SESSION['full_name'] = $user['full_name'];
                    $_SESSION['email'] = $user['email'];
                } else {
                    $error = 'Không thể cập nhật thông tin. Vui lòng thử lại.';
                }
            }
        } catch (Exception $e) {
            $error = 'Lỗi: ' . $e->getMessage();
        }
    }
}

// Handle cancel order from profile orders tab
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_order_id'])) {
    $cancelId = intval($_POST['cancel_order_id']);
    if ($cancelId > 0) {
        $appController->cancelUserOrder($_SESSION['user_id'], $cancelId);
        header('Location: ' . BASE_URL . '/?page=profile#orders');
        exit;
    }
}

// Get user orders
$userOrders = $appController->getUserOrders($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hồ sơ cá nhân - Shop Gau Yeu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- User CSS Files -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/variables.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/user-profile.css">
</head>
<body class="user-profile">
    <div class="user-profile__container">
        <div class="user-profile__header">
            <div class="container">
                <div class="user-profile__breadcrumb">
                    <a href="<?php echo BASE_URL; ?>" class="user-profile__breadcrumb-link">
                        <i class="bi bi-house"></i> Trang chủ
                    </a>
                    <span class="user-profile__breadcrumb-separator">/</span>
                    <span class="user-profile__breadcrumb-current">Hồ sơ cá nhân</span>
                </div>
            </div>
        </div>

        <div class="container">
            <div class="row">
                <!-- Sidebar -->
                <div class="col-lg-3">
                    <div class="user-profile__sidebar">
                        <div class="user-profile__user-info">
                            <div class="user-profile__avatar">
                                <i class="bi bi-person-circle"></i>
                            </div>
                            <h3 class="user-profile__username"><?php echo htmlspecialchars($user['username']); ?></h3>
                            <p class="user-profile__user-role">Khách hàng</p>
                        </div>
                        
                        <nav class="user-profile__nav">
                            <a href="#profile" class="user-profile__nav-link user-profile__nav-link--active" data-tab="profile">
                                <i class="bi bi-person"></i> Thông tin cá nhân
                            </a>
                            <a href="#orders" class="user-profile__nav-link" data-tab="orders">
                                <i class="bi bi-bag"></i> Đơn hàng của tôi
                            </a>
                            <a href="#security" class="user-profile__nav-link" data-tab="security">
                                <i class="bi bi-shield-lock"></i> Bảo mật
                            </a>
                            <a href="<?php echo BASE_URL; ?>/logout" class="user-profile__nav-link user-profile__nav-link--logout">
                                <i class="bi bi-box-arrow-right"></i> Đăng xuất
                            </a>
                        </nav>
                    </div>
                </div>

                <!-- Main Content -->
                <div class="col-lg-9">
                    <!-- Profile Tab -->
                    <div class="user-profile__tab-content" id="profile-tab">
                        <div class="user-profile__card">
                            <div class="user-profile__card-header">
                                <h2 class="user-profile__card-title">
                                    <i class="bi bi-person"></i> Thông tin cá nhân
                                </h2>
                            </div>
                            
                            <div class="user-profile__card-body">
                                <?php if ($error): ?>
                                    <div class="user-profile__alert user-profile__alert--error" role="alert">
                                        <i class="bi bi-exclamation-triangle-fill"></i> <?php echo htmlspecialchars($error); ?>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($success): ?>
                                    <div class="user-profile__alert user-profile__alert--success" role="alert">
                                        <i class="bi bi-check-circle-fill"></i> <?php echo htmlspecialchars($success); ?>
                                    </div>
                                <?php endif; ?>
                                
                                <form method="POST" action="" class="user-profile__form" id="profileForm">
                                    <input type="hidden" name="profile_update" value="1">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="user-profile__form-group">
                                                <label for="username" class="user-profile__label">Tên đăng nhập</label>
                                                <input type="text" class="user-profile__input" id="username" 
                                                       value="<?php echo htmlspecialchars($user['username']); ?>" readonly>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="user-profile__form-group">
                                                <label for="full_name" class="user-profile__label">
                                                    Họ và tên <span class="user-profile__required">*</span>
                                                </label>
                                                <input type="text" class="user-profile__input" id="full_name" name="full_name" 
                                                       value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="user-profile__form-group">
                                                <label for="email" class="user-profile__label">
                                                    Email <span class="user-profile__required">*</span>
                                                </label>
                                                <input type="email" class="user-profile__input" id="email" name="email" 
                                                       value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="user-profile__form-group">
                                                <label for="phone" class="user-profile__label">Số điện thoại</label>
                                                <input type="tel" class="user-profile__input" id="phone" name="phone" 
                                                       value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="user-profile__form-group">
                                        <label for="address" class="user-profile__label">Địa chỉ</label>
                                        <textarea class="user-profile__textarea" id="address" name="address" rows="3"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                                    </div>
                                    
                                    <div class="user-profile__form-actions">
                                        <button type="submit" class="user-profile__button user-profile__button--primary">
                                            <i class="bi bi-check-circle"></i> Cập nhật thông tin
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Orders Tab -->
                    <div class="user-profile__tab-content" id="orders-tab" style="display: none;">
                        <style>
                            .status-badge { padding: 4px 10px; border-radius: 999px; font-size: 12px; font-weight: 600; }
                            .status-pending { background-color: #fff3cd; color: #856404; }
                            .status-confirmed { background-color: #dbeafe; color: #1d4ed8; }
                            .status-shipping { background-color: #cce5ff; color: #004085; }
                            .status-delivered { background-color: #d4edda; color: #155724; }
                            .status-cancelled { background-color: #f8d7da; color: #721c24; }
                            .payment-pending { background-color: #fff3cd; color: #856404; }
                            .payment-paid { background-color: #d4edda; color: #155724; }
                            .order-item { border: 1px solid #e9ecef; border-radius: 10px; padding: 16px; margin-bottom: 12px; }
                        </style>
                        <div class="user-profile__card">
                            <div class="user-profile__card-header">
                                <h2 class="user-profile__card-title mb-0">
                                    <i class="bi bi-bag"></i> Đơn hàng của tôi
                                </h2>
                            </div>
                            <div class="user-profile__card-body">
                                <?php if (empty($userOrders)): ?>
                                    <div class="text-center py-4 text-muted">Chưa có đơn hàng nào.</div>
                                <?php else: ?>
                                    <?php foreach ($userOrders as $order): ?>
                                        <div class="order-item">
                                            <div class="row align-items-center">
                                                <div class="col-md-3">
                                                    <div class="fw-semibold mb-1">Đơn hàng #<?php echo (int)$order['order_id']; ?></div>
                                                    <small class="text-muted"><i class="bi bi-calendar"></i> <?php echo date('d/m/Y H:i', strtotime($order['order_date'])); ?></small>
                                                </div>
                                                <div class="col-md-3">
                                                    <strong class="text-primary"><?php echo number_format((float)$order['total_amount'], 0, ',', '.'); ?> ₫</strong>
                                                </div>
                                                <div class="col-md-3">
                                                    <?php
                                                    $statusClass = '';
                                                    switch ($order['status']) {
                                                        case 'Chờ xác nhận': $statusClass = 'status-pending'; break;
                                                        case 'Đã xác nhận': $statusClass = 'status-confirmed'; break;
                                                        case 'Đang giao': $statusClass = 'status-shipping'; break;
                                                        case 'Đã giao': $statusClass = 'status-delivered'; break;
                                                        case 'Đã hủy': $statusClass = 'status-cancelled'; break;
                                                    }
                                                    ?>
                                                    <span class="status-badge <?php echo $statusClass; ?>"><?php echo htmlspecialchars($order['status']); ?></span>
                                                </div>
                                                <div class="col-md-3">
                                                    <?php $paymentClass = ($order['payment_status'] === 'Đã thanh toán') ? 'payment-paid' : 'payment-pending'; ?>
                                                    <span class="status-badge <?php echo $paymentClass; ?>"><?php echo htmlspecialchars($order['payment_status']); ?></span>
                                                </div>
                                            </div>
                                            <div class="mt-3">
                                                <button type="button" class="btn btn-outline-primary btn-sm js-view-order" data-order-id="<?php echo (int)$order['order_id']; ?>"><i class="bi bi-eye"></i> Xem chi tiết</button>
                                                <?php if ($order['status'] === 'Chờ xác nhận'): ?>
                                                    <form method="post" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn hủy đơn hàng này?');">
                                                        <input type="hidden" name="cancel_order_id" value="<?php echo (int)$order['order_id']; ?>">
                                                        <button class="btn btn-outline-danger btn-sm"><i class="bi bi-x-circle"></i> Hủy đơn</button>
                                                    </form>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <!-- Security Tab -->
                    <div class="user-profile__tab-content" id="security-tab" style="display: none;">
                        <div class="user-profile__card">
                            <div class="user-profile__card-header">
                                <h2 class="user-profile__card-title">
                                    <i class="bi bi-shield-lock"></i> Bảo mật tài khoản
                                </h2>
                            </div>
                            
                            <div class="user-profile__card-body">
                                <div class="user-profile__security-section">
                                    <h3 class="user-profile__section-title">Đổi mật khẩu</h3>
                                    <form method="POST" action="" class="user-profile__form" id="passwordForm">
                                        <div class="user-profile__form-group">
                                            <label for="current_password" class="user-profile__label">Mật khẩu hiện tại</label>
                                            <input type="password" class="user-profile__input" id="current_password" name="current_password" required>
                                        </div>
                                        
                                        <div class="user-profile__form-group">
                                            <label for="new_password" class="user-profile__label">Mật khẩu mới</label>
                                            <input type="password" class="user-profile__input" id="new_password" name="new_password" required>
                                        </div>
                                        
                                        <div class="user-profile__form-group">
                                            <label for="confirm_new_password" class="user-profile__label">Xác nhận mật khẩu mới</label>
                                            <input type="password" class="user-profile__input" id="confirm_new_password" name="confirm_new_password" required>
                                        </div>
                                        
                                        <div class="user-profile__form-actions">
                                            <button type="submit" class="user-profile__button user-profile__button--primary">
                                                <i class="bi bi-shield-check"></i> Đổi mật khẩu
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- User JavaScript Files -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo BASE_URL; ?>/assets/js/user-profile.js"></script>
    <script>document.body.setAttribute('data-base-url','<?php echo BASE_URL; ?>');</script>
</body>
</html> 