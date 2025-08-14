<?php
/**
 * User Registration Page
 */
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../controller/AppController.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if already logged in
if (isset($_SESSION['user_id']) && ($_SESSION['role'] ?? '') === 'user') {
    header('Location: ' . BASE_URL);
    exit;
}

$error = '';
$success = '';

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $email = trim($_POST['email'] ?? '');
    $full_name = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    
    // Validation
    if (empty($username) || empty($password) || empty($confirm_password) || empty($email) || empty($full_name)) {
        $error = 'Vui lòng điền đầy đủ thông tin bắt buộc';
    } elseif ($password !== $confirm_password) {
        $error = 'Mật khẩu xác nhận không khớp';
    } elseif (strlen($password) < 6) {
        $error = 'Mật khẩu phải có ít nhất 6 ký tự';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email không hợp lệ';
    } else {
        try {
            $appController = new AppController();
            
            if ($appController->isConnected()) {
                // Check if username already exists
                if ($appController->isUsernameExists($username)) {
                    $error = 'Tên đăng nhập đã tồn tại';
                } elseif ($appController->isEmailExists($email)) {
                    $error = 'Email đã được sử dụng';
                } else {
                    // Create user data
                    $userData = [
                        'username' => $username,
                        'password' => $password,
                        'email' => $email,
                        'full_name' => $full_name,
                        'phone' => $phone,
                        'role' => 'user'
                    ];
                    
                    if ($appController->createUser($userData)) {
                        $success = 'Đăng ký thành công! Bạn có thể đăng nhập ngay bây giờ.';
                        // Clear form data
                        $_POST = [];
                    } else {
                        $error = 'Không thể tạo tài khoản. Vui lòng thử lại.';
                    }
                }
            } else {
                $error = 'Không thể kết nối cơ sở dữ liệu';
            }
        } catch (Exception $e) {
            $error = 'Lỗi: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký - Shop Gau Yeu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- User CSS Files -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/variables.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/user-auth.css">
</head>
<body class="user-auth">
    <div class="user-auth__container">
        <div class="user-auth__card">
            <div class="user-auth__header">
                <h2 class="user-auth__title">
                    <i class="bi bi-person-plus"></i> Đăng ký tài khoản
                </h2>
                <p class="user-auth__subtitle">Tạo tài khoản mới để mua sắm tại Shop Gau Yeu</p>
            </div>
            
            <div class="user-auth__body">
                <?php if ($error): ?>
                    <div class="user-auth__alert user-auth__alert--error" role="alert">
                        <i class="bi bi-exclamation-triangle-fill"></i> <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="user-auth__alert user-auth__alert--success" role="alert">
                        <i class="bi bi-check-circle-fill"></i> <?php echo htmlspecialchars($success); ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="" class="user-auth__form" id="userRegisterForm">
                    <div class="user-auth__form-group">
                        <label for="username" class="user-auth__label">
                            <i class="bi bi-person"></i> Tên đăng nhập <span class="user-auth__required">*</span>
                        </label>
                        <input type="text" class="user-auth__input" id="username" name="username" 
                               placeholder="Nhập tên đăng nhập" value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="user-auth__form-group">
                        <label for="full_name" class="user-auth__label">
                            <i class="bi bi-person-badge"></i> Họ và tên <span class="user-auth__required">*</span>
                        </label>
                        <input type="text" class="user-auth__input" id="full_name" name="full_name" 
                               placeholder="Nhập họ và tên" value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="user-auth__form-group">
                        <label for="email" class="user-auth__label">
                            <i class="bi bi-envelope"></i> Email <span class="user-auth__required">*</span>
                        </label>
                        <input type="email" class="user-auth__input" id="email" name="email" 
                               placeholder="Nhập email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="user-auth__form-group">
                        <label for="phone" class="user-auth__label">
                            <i class="bi bi-telephone"></i> Số điện thoại
                        </label>
                        <input type="tel" class="user-auth__input" id="phone" name="phone" 
                               placeholder="Nhập số điện thoại" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                    </div>
                    
                    <div class="user-auth__form-group">
                        <label for="password" class="user-auth__label">
                            <i class="bi bi-lock"></i> Mật khẩu <span class="user-auth__required">*</span>
                        </label>
                        <div class="user-auth__password-group">
                            <input type="password" class="user-auth__input" id="password" name="password" 
                                   placeholder="Nhập mật khẩu" required>
                            <button type="button" class="user-auth__password-toggle" id="togglePassword">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="user-auth__form-group">
                        <label for="confirm_password" class="user-auth__label">
                            <i class="bi bi-lock-fill"></i> Xác nhận mật khẩu <span class="user-auth__required">*</span>
                        </label>
                        <div class="user-auth__password-group">
                            <input type="password" class="user-auth__input" id="confirm_password" name="confirm_password" 
                                   placeholder="Nhập lại mật khẩu" required>
                            <button type="button" class="user-auth__password-toggle" id="toggleConfirmPassword">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="user-auth__form-group">
                        <div class="user-auth__checkbox-group">
                            <input type="checkbox" class="user-auth__checkbox" id="agree_terms" name="agree_terms" required>
                            <label class="user-auth__checkbox-label" for="agree_terms">
                                Tôi đồng ý với <a href="<?php echo BASE_URL; ?>/terms" class="user-auth__link">điều khoản sử dụng</a> và 
                                <a href="<?php echo BASE_URL; ?>/privacy" class="user-auth__link">chính sách bảo mật</a>
                            </label>
                        </div>
                    </div>
                    
                    <button type="submit" class="user-auth__button user-auth__button--primary">
                        <i class="bi bi-person-plus"></i> Đăng ký
                    </button>
                </form>
                
                <div class="user-auth__divider">
                    <span>hoặc</span>
                </div>
                
                <div class="user-auth__links">
                    <a href="<?php echo BASE_URL; ?>/login" class="user-auth__link">
                        <i class="bi bi-person-circle"></i> Đã có tài khoản? Đăng nhập
                    </a>
                </div>
                
                <div class="user-auth__footer">
                    <a href="<?php echo BASE_URL; ?>" class="user-auth__back-link">
                        <i class="bi bi-arrow-left"></i> Quay về trang chủ
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- User JavaScript Files -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo BASE_URL; ?>/assets/js/user-auth.js"></script>
</body>
</html>