<?php
/**
 * Create Admin User Script
 * Use this script to create your first admin user
 * IMPORTANT: Delete this file after creating your admin user for security
 */
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../controller/AppController.php';

// Check if admin user already exists
$database = new Database();
$conn = $database->getConnection();

if (!$conn) {
    die("Database connection failed. Please check your database configuration.");
}

$stmt = $conn->prepare("SELECT COUNT(*) as count FROM users WHERE role = 'admin'");
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);

if ($result['count'] > 0) {
    die("Admin user already exists. Please delete this file for security.");
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $email = trim($_POST['email'] ?? '');
    $full_name = trim($_POST['full_name'] ?? '');
    
    if (empty($username) || empty($password) || empty($email)) {
        $error = 'Vui lòng điền đầy đủ thông tin';
    } else {
        try {
            $appController = new AppController();
            
            if ($appController->isConnected()) {
                $userData = [
                    'username' => $username,
                    'password' => $password,
                    'email' => $email,
                    'full_name' => $full_name,
                    'role' => 'admin'
                ];
                
                if ($appController->createUser($userData)) {
                    $message = 'Tài khoản admin đã được tạo thành công! Bạn có thể đăng nhập ngay bây giờ.';
                } else {
                    $error = 'Không thể tạo tài khoản admin';
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
    <title>Tạo Tài Khoản Admin - Shop Gau Yeu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .create-admin-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            max-width: 500px;
            width: 100%;
        }
        .create-admin-header {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .create-admin-body {
            padding: 40px 30px;
        }
        .form-floating {
            margin-bottom: 20px;
        }
        .form-control {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            padding: 12px 15px;
        }
        .form-control:focus {
            border-color: #e74c3c;
            box-shadow: 0 0 0 0.2rem rgba(231, 76, 60, 0.25);
        }
        .btn-create {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-weight: 600;
            width: 100%;
            margin-top: 10px;
        }
        .btn-create:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(231, 76, 60, 0.4);
        }
        .alert {
            border-radius: 10px;
            border: none;
        }
        .security-warning {
            background: rgba(231, 76, 60, 0.1);
            border: 1px solid rgba(231, 76, 60, 0.3);
            border-radius: 10px;
            padding: 15px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="create-admin-container">
        <div class="create-admin-header">
            <h3><i class="bi bi-shield-exclamation"></i> Tạo Tài Khoản Admin</h3>
            <p>Shop Gau Yeu - Thiết lập ban đầu</p>
        </div>
        
        <div class="create-admin-body">
            <?php if ($message): ?>
                <div class="alert alert-success" role="alert">
                    <i class="bi bi-check-circle"></i> <?php echo htmlspecialchars($message); ?>
                </div>
                <div class="text-center mt-3">
                    <a href="login.php" class="btn btn-success">
                        <i class="bi bi-box-arrow-in-right"></i> Đăng nhập ngay
                    </a>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="bi bi-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if (!$message): ?>
                <form method="POST" action="">
                    <div class="form-floating">
                        <input type="text" class="form-control" id="username" name="username" 
                               placeholder="Tên đăng nhập" value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required>
                        <label for="username">Tên đăng nhập</label>
                    </div>
                    
                    <div class="form-floating">
                        <input type="email" class="form-control" id="email" name="email" 
                               placeholder="Email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                        <label for="email">Email</label>
                    </div>
                    
                    <div class="form-floating">
                        <input type="text" class="form-control" id="full_name" name="full_name" 
                               placeholder="Họ và tên" value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>" required>
                        <label for="full_name">Họ và tên</label>
                    </div>
                    
                    <div class="form-floating">
                        <input type="password" class="form-control" id="password" name="password" 
                               placeholder="Mật khẩu" required>
                        <label for="password">Mật khẩu</label>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-create">
                        <i class="bi bi-shield-plus"></i> Tạo tài khoản Admin
                    </button>
                </form>
                
                <div class="security-warning">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-exclamation-triangle text-danger me-2"></i>
                        <small class="text-muted">
                            <strong>Cảnh báo bảo mật:</strong> Sau khi tạo tài khoản admin, hãy xóa file này ngay lập tức!
                        </small>
                    </div>
                </div>
            <?php endif; ?>
            
            <div class="text-center mt-4">
                <a href="../index.php" class="text-decoration-none">
                    <i class="bi bi-arrow-left"></i> Quay về trang chủ
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 