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
        });
    </script>
</body>
</html> 