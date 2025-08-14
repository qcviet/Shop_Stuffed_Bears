<?php
/**
 * Admin Login Page
 */
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../controller/AppController.php';

// Use a separate session for admin
if (session_status() === PHP_SESSION_NONE) {
    session_name('admin_session');
    session_start();
}

// Check if already logged in
if (isset($_SESSION['user_id']) && ($_SESSION['role'] ?? '') === 'admin') {
    header('Location: index.php');
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
                
                if ($user && $user['role'] === 'admin') {
                    // Check if admin user is active
                    $status = $user['status'] ?? 'active';
                    if ($status === 'inactive') {
                        $error = 'Tài khoản admin đã bị khóa. Vui lòng liên hệ quản trị viên khác để được hỗ trợ.';
                    } else {
                        // Set admin session
                        $_SESSION['user_id'] = $user['user_id'];
                        $_SESSION['username'] = $user['username'];
                        $_SESSION['role'] = $user['role'];
                        $_SESSION['full_name'] = $user['full_name'];
                        $_SESSION['email'] = $user['email'];
                        
                        // Handle remember me functionality
                        if ($remember) {
                            // Set a secure remember me cookie (30 days)
                            $token = bin2hex(random_bytes(32));
                            $expires = time() + (30 * 24 * 60 * 60); // 30 days
                            
                            // Store token in database (you can create a remember_tokens table)
                            // For now, we'll use a simple cookie approach
                            setcookie('admin_remember', $token, $expires, '/', '', true, true);
                            
                            // Store token hash in session for validation
                            $_SESSION['remember_token'] = password_hash($token, PASSWORD_DEFAULT);
                        }
                        
                        // Redirect to admin dashboard
                        header('Location: index.php');
                        exit;
                    }
                } else {
                    $error = 'Tên đăng nhập hoặc mật khẩu không đúng, hoặc tài khoản không có quyền admin';
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
if (!isset($_SESSION['user_id']) && isset($_COOKIE['admin_remember'])) {
    // Validate remember me token
    // This is a simplified version - in production, you'd validate against database
    // For now, we'll just clear invalid cookies
    if (!isset($_SESSION['remember_token'])) {
        setcookie('admin_remember', '', time() - 3600, '/');
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Shop Gau Yeu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Admin CSS Files -->
    <link rel="stylesheet" href="assets/css/admin-common.css">
    <link rel="stylesheet" href="assets/css/admin-login.css">
</head>
<body class="admin-login-body">
    <div class="admin-login-container">
        <div class="admin-login-header">
            <h3><i class="bi bi-shield-lock"></i> Admin Panel</h3>
            <p>Shop Gau Yeu - Quản trị hệ thống</p>
        </div>
        
        <div class="admin-login-body-content">
            <?php if ($error): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="bi bi-exclamation-triangle-fill"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success" role="alert">
                    <i class="bi bi-check-circle-fill"></i> <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" id="adminLoginForm">
                <div class="admin-mb-3">
                    <label for="username" class="admin-form-label">
                        <i class="bi bi-person"></i> Tên đăng nhập
                    </label>
                    <input type="text" class="admin-form-control" id="username" name="username" 
                           placeholder="Nhập tên đăng nhập" value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required>
                </div>
                
                <div class="admin-mb-3">
                    <label for="password" class="admin-form-label">
                        <i class="bi bi-lock"></i> Mật khẩu
                    </label>
                    <div class="admin-password-input-group">
                        <input type="password" class="admin-form-control" id="password" name="password" 
                               placeholder="Nhập mật khẩu" required>
                        <button type="button" class="admin-password-toggle" id="togglePassword">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>
                
                <div class="admin-form-check">
                    <input type="checkbox" class="admin-form-check-input" id="remember" name="remember">
                    <label class="admin-form-check-label" for="remember">
                        <i class="bi bi-clock"></i> Ghi nhớ đăng nhập (30 ngày)
                    </label>
                </div>
                
                <button type="submit" class="admin-btn admin-btn-primary admin-btn-login">
                    <i class="bi bi-box-arrow-in-right"></i> Đăng nhập Admin
                </button>
            </form>
            
            <div class="admin-security-notice">
                <div class="d-flex align-items-center">
                    <i class="bi bi-shield-check text-warning me-2"></i>
                    <small class="text-muted">
                        <strong>Lưu ý bảo mật:</strong> Chỉ sử dụng tài khoản admin được ủy quyền.
                    </small>
                </div>
            </div>
            
            <div class="admin-back-link">
                <a href="../index.php">
                    <i class="bi bi-arrow-left"></i> Quay về trang chủ
                </a>
            </div>
        </div>
    </div>

    <!-- Admin JavaScript Files -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/admin-common.js"></script>
    <script src="assets/js/admin-login.js"></script>
</body>
</html>