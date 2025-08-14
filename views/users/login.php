<?php
/**
 * User Login Page
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

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);
    
    if (empty($username) || empty($password)) {
        $error = 'Vui lòng nhập tên đăng nhập và mật khẩu';
    } else {
        try {
            $appController = new AppController();
            
            if ($appController->isConnected()) {
                $user = $appController->loginUser($username, $password);
                
                if ($user && $user['role'] === 'user') {
                    // Check if user is active
                    $status = $user['status'] ?? 'active';
                    if ($status === 'inactive') {
                        $error = 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ quản trị viên để được hỗ trợ.';
                    } else {
                        // Set user session
                        $_SESSION['user_id'] = $user['user_id'];
                        $_SESSION['username'] = $user['username'];
                        $_SESSION['role'] = $user['role'];
                        $_SESSION['full_name'] = $user['full_name'];
                        $_SESSION['email'] = $user['email'];
                        
                        // Handle remember me functionality
                        if ($remember) {
                            $token = bin2hex(random_bytes(32));
                            $expires = time() + (30 * 24 * 60 * 60); // 30 days
                            setcookie('user_remember', $token, $expires, '/', '', true, true);
                            $_SESSION['remember_token'] = password_hash($token, PASSWORD_DEFAULT);
                        }
                        
                        // Redirect to home page
                        header('Location: ' . BASE_URL);
                        exit;
                    }
                } else {
                    $error = 'Tên đăng nhập hoặc mật khẩu không đúng';
                }
            } else {
                $error = 'Không thể kết nối cơ sở dữ liệu';
            }
        } catch (Exception $e) {
            $error = 'Lỗi: ' . $e->getMessage();
        }
    }
}

// Check for remember me cookie
if (!isset($_SESSION['user_id']) && isset($_COOKIE['user_remember'])) {
    if (!isset($_SESSION['remember_token'])) {
        setcookie('user_remember', '', time() - 3600, '/');
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - Shop Gau Yeu</title>
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
                    <i class="bi bi-person-circle"></i> Đăng nhập
                </h2>
                <p class="user-auth__subtitle">Chào mừng bạn quay trở lại Shop Gau Yeu</p>
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
                
                <form method="POST" action="" class="user-auth__form" id="userLoginForm">
                    <div class="user-auth__form-group">
                        <label for="username" class="user-auth__label">
                            <i class="bi bi-person"></i> Tên đăng nhập
                        </label>
                        <input type="text" class="user-auth__input" id="username" name="username" 
                               placeholder="Nhập tên đăng nhập" value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="user-auth__form-group">
                        <label for="password" class="user-auth__label">
                            <i class="bi bi-lock"></i> Mật khẩu
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
                        <div class="user-auth__checkbox-group">
                            <input type="checkbox" class="user-auth__checkbox" id="remember" name="remember">
                            <label class="user-auth__checkbox-label" for="remember">
                                <i class="bi bi-clock"></i> Ghi nhớ đăng nhập
                            </label>
                        </div>
                    </div>
                    
                    <button type="submit" class="user-auth__button user-auth__button--primary">
                        <i class="bi bi-box-arrow-in-right"></i> Đăng nhập
                    </button>
                </form>
                
                <div class="user-auth__divider">
                    <span>hoặc</span>
                </div>
                
                <div class="user-auth__links">
                    <a href="<?php echo BASE_URL; ?>/register" class="user-auth__link">
                        <i class="bi bi-person-plus"></i> Tạo tài khoản mới
                    </a>
                    <a href="<?php echo BASE_URL; ?>/forgot-password" class="user-auth__link">
                        <i class="bi bi-question-circle"></i> Quên mật khẩu?
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