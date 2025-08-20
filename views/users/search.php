<?php
// Include necessary models
require_once __DIR__ . '/../../models/ProductModel.php';
require_once __DIR__ . '/../../models/CategoryModel.php';

// Get search parameters
$search_query = $_GET['search'] ?? '';
$page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
$per_page = 12;
$offset = ($page - 1) * $per_page;

// Initialize models
$database = new Database();
$db = $database->getConnection();
$productModel = new ProductModel($db);
$categoryModel = new CategoryModel($db);

// Get search results
$search_results = [];
$total_results = 0;

if (!empty($search_query)) {
    $search_results = $productModel->searchProducts($search_query, '', $per_page, $offset);
    $total_results = $productModel->getSearchCount($search_query, '');
}

$total_pages = ceil($total_results / $per_page);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kết quả tìm kiếm - Shop Gau Yeu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/variables.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/header.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/footer.css">
    <style>
        .search-results {
            padding: 40px 0;
            min-height: 60vh;
        }
        
        .search-header {
            background: #f8f9fa;
            padding: 30px 0;
            margin-bottom: 30px;
        }
        
        .search-summary {
            font-size: 18px;
            color: #666;
            margin-bottom: 20px;
        }
        
        .search-filters {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .filter-form {
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .filter-form input {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            flex: 1;
            min-width: 200px;
        }
        
        .filter-form button {
            padding: 8px 20px;
            background: var(--secondary-color);
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .filter-form button:hover {
            background: #0056b3;
        }
        
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .product-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }
        
        .product-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        
        .product-info {
            padding: 15px;
        }
        
        .product-title {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 8px;
            color: #333;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .product-price {
            font-size: 18px;
            font-weight: bold;
            color: var(--secondary-color);
            margin-bottom: 10px;
        }
        
        .product-category {
            font-size: 12px;
            color: #666;
            margin-bottom: 10px;
        }
        
        .add-to-cart-btn {
            width: 100%;
            padding: 8px;
            background: var(--secondary-color);
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .add-to-cart-btn:hover {
            background: #0056b3;
        }
        
        .no-results {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }
        
        .no-results i {
            font-size: 48px;
            color: #ddd;
            margin-bottom: 20px;
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 30px;
        }
        
        .pagination a,
        .pagination span {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            text-decoration: none;
            color: #333;
            transition: all 0.3s;
        }
        
        .pagination a:hover,
        .pagination .current {
            background: var(--secondary-color);
            color: white;
            border-color: var(--secondary-color);
        }
        
        @media (max-width: 768px) {
            .filter-form {
                flex-direction: column;
                align-items: stretch;
            }
            
            .product-grid {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
                gap: 15px;
            }
            
            .search-header {
                padding: 20px 0;
            }
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="search-header">
        <div class="container">
            <h1>Kết quả tìm kiếm</h1>
            <?php if (!empty($search_query)): ?>
                <div class="search-summary">
                    Tìm kiếm "<?php echo htmlspecialchars($search_query); ?>" - Tìm thấy <?php echo $total_results; ?> sản phẩm
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="container">
        <div class="search-filters">
            <form class="filter-form" method="get" action="<?php echo BASE_URL; ?>">
                <input type="hidden" name="page" value="search">
                <input type="text" name="search" placeholder="Tìm kiếm sản phẩm..." 
                       value="<?php echo htmlspecialchars($search_query); ?>">
                <button type="submit">Tìm kiếm</button>
            </form>
        </div>
        
        <?php if (empty($search_results)): ?>
            <div class="no-results">
                <i class="bi bi-search"></i>
                <h3>Không tìm thấy sản phẩm</h3>
                <p>Hãy thử tìm kiếm với từ khóa khác.</p>
                <a href="<?php echo BASE_URL; ?>" class="btn btn-primary">Về trang chủ</a>
            </div>
        <?php else: ?>
            <div class="product-grid">
                <?php foreach ($search_results as $product): ?>
                    <div class="product-card">
                        <img src="<?php 
                            $img = $product['image'] ?? '';
                            $src = (strpos($img, 'http') === 0) ? $img : (BASE_URL . '/' . $img);
                            echo $src;
                        ?>" 
                             alt="<?php echo htmlspecialchars($product['product_name']); ?>" 
                             class="product-image">
                        <div class="product-info">
                            <h3 class="product-title"><?php echo htmlspecialchars($product['product_name']); ?></h3>
                            <div class="product-category"><?php echo htmlspecialchars($product['category_name']); ?></div>
                            <div class="product-price"><?php echo number_format($product['price'], 0, ',', '.'); ?> VNĐ</div>
                            <button class="add-to-cart-btn" onclick="addToCart(<?php echo $product['product_id']; ?>)">
                                <i class="bi bi-cart-plus"></i> Thêm vào giỏ
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=search&search=<?php echo urlencode($search_query); ?>&p=<?php echo $page - 1; ?>">
                            <i class="bi bi-chevron-left"></i> Trước
                        </a>
                    <?php endif; ?>
                    
                    <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                        <?php if ($i == $page): ?>
                            <span class="current"><?php echo $i; ?></span>
                        <?php else: ?>
                            <a href="?page=search&search=<?php echo urlencode($search_query); ?>&p=<?php echo $i; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endif; ?>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                        <a href="?page=search&search=<?php echo urlencode($search_query); ?>&p=<?php echo $page + 1; ?>">
                            Sau <i class="bi bi-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    
    <?php include 'footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function addToCart(productId) {
            // Add to cart functionality
            fetch('<?php echo BASE_URL; ?>/ajax_handler.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=add_to_cart&product_id=' + productId
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Đã thêm sản phẩm vào giỏ hàng!');
                } else {
                    alert('Có lỗi xảy ra: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Có lỗi xảy ra khi thêm vào giỏ hàng');
            });
        }
    </script>
</body>
</html>
