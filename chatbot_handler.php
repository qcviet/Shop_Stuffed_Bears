<?php
/**
 * Chatbot Handler - Optimized Version
 * Xử lý các yêu cầu từ chatbot và tích hợp với database
 */

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include configuration files
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'models/ProductModel.php';
require_once 'models/CategoryModel.php';

// Set content type to JSON
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Initialize database connection
$database = new Database();
$conn = $database->getConnection();

// Simple rate limiting
$rateLimitKey = 'chatbot_' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
$rateLimitFile = sys_get_temp_dir() . '/chatbot_rate_limit.json';

if (!file_exists($rateLimitFile)) {
    file_put_contents($rateLimitFile, json_encode([]));
}

$rateLimits = json_decode(file_get_contents($rateLimitFile), true);
$currentTime = time();

// Clean old entries
$rateLimits = array_filter($rateLimits, function($timestamp) use ($currentTime) {
    return $currentTime - $timestamp < 60; // 1 minute window
});

// Check rate limit (max 30 requests per minute)
if (isset($rateLimits[$rateLimitKey]) && count($rateLimits[$rateLimitKey]) >= 30) {
    http_response_code(429);
    echo json_encode(['error' => 'Rate limit exceeded. Please try again later.']);
    exit();
}

// Add current request
if (!isset($rateLimits[$rateLimitKey])) {
    $rateLimits[$rateLimitKey] = [];
}
$rateLimits[$rateLimitKey][] = $currentTime;
file_put_contents($rateLimitFile, json_encode($rateLimits));

// Get request method and action
$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? $_GET['action'] : '';

// Simple cache system
$cacheDir = sys_get_temp_dir() . '/chatbot_cache/';
if (!is_dir($cacheDir)) {
    mkdir($cacheDir, 0755, true);
}

// Handle different actions
switch ($action) {
    case 'search_products':
        handleProductSearch();
        break;
    case 'get_categories':
        handleGetCategories();
        break;
    case 'get_product_suggestions':
        handleGetProductSuggestions();
        break;
    case 'save_conversation':
        handleSaveConversation();
        break;
    case 'get_faq':
        handleGetFAQ();
        break;
    case 'track_analytics':
        handleTrackAnalytics();
        break;
    case 'get_analytics':
        handleGetAnalytics();
        break;
    default:
        echo json_encode(['error' => 'Invalid action']);
        break;
}

/**
 * Handle product search with caching
 */
function handleProductSearch() {
    global $conn, $cacheDir;
    
    $query = isset($_POST['query']) ? trim($_POST['query']) : '';
    $limit = isset($_POST['limit']) ? (int)$_POST['limit'] : 5;
    
    if (empty($query)) {
        echo json_encode(['error' => 'Query is required']);
        return;
    }
    
    // Check cache first
    $cacheKey = 'search_' . md5($query . $limit);
    $cacheFile = $cacheDir . $cacheKey . '.json';
    
    if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < 300) { // 5 minutes cache
        $cachedData = json_decode(file_get_contents($cacheFile), true);
        echo json_encode($cachedData);
        return;
    }
    
    try {
        $productModel = new ProductModel($conn);
        $products = $productModel->searchProducts($query, $limit);
        
        $formattedProducts = [];
        foreach ($products as $product) {
            $formattedProducts[] = [
                'id' => $product['product_id'],
                'name' => $product['product_name'],
                'price' => number_format($product['price'], 0, ',', '.') . 'đ',
                'image' => $product['image_url'] ? BASE_URL . '/uploads/products/' . $product['image_url'] : BASE_URL . '/assets/images/default-product.jpg',
                'url' => BASE_URL . '/index.php?page=product&id=' . $product['product_id'],
                'category' => $product['category_name']
            ];
        }
        
        $result = [
            'success' => true,
            'products' => $formattedProducts,
            'count' => count($formattedProducts),
            'cached' => false
        ];
        
        // Save to cache
        file_put_contents($cacheFile, json_encode($result));
        
        echo json_encode($result);
        
    } catch (Exception $e) {
        error_log("Chatbot search error: " . $e->getMessage());
        echo json_encode(['error' => 'Database error occurred']);
    }
}

/**
 * Handle get categories with caching
 */
function handleGetCategories() {
    global $conn, $cacheDir;
    
    // Check cache first
    $cacheFile = $cacheDir . 'categories.json';
    
    if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < 1800) { // 30 minutes cache
        $cachedData = json_decode(file_get_contents($cacheFile), true);
        echo json_encode($cachedData);
        return;
    }
    
    try {
        $categoryModel = new CategoryModel($conn);
        $categories = $categoryModel->getAll();
        
        $formattedCategories = [];
        foreach ($categories as $category) {
            $formattedCategories[] = [
                'id' => $category['category_id'],
                'name' => $category['category_name'],
                'url' => BASE_URL . '/index.php?page=category&id=' . $category['category_id']
            ];
        }
        
        $result = [
            'success' => true,
            'categories' => $formattedCategories,
            'cached' => false
        ];
        
        // Save to cache
        file_put_contents($cacheFile, json_encode($result));
        
        echo json_encode($result);
        
    } catch (Exception $e) {
        error_log("Chatbot categories error: " . $e->getMessage());
        echo json_encode(['error' => 'Database error occurred']);
    }
}

/**
 * Handle get product suggestions with caching
 */
function handleGetProductSuggestions() {
    global $conn, $cacheDir;
    
    $category_id = isset($_POST['category_id']) ? (int)$_POST['category_id'] : null;
    $limit = isset($_POST['limit']) ? (int)$_POST['limit'] : 3;
    
    // Check cache first
    $cacheKey = 'suggestions_' . ($category_id ?? 'all') . '_' . $limit;
    $cacheFile = $cacheDir . $cacheKey . '.json';
    
    if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < 600) { // 10 minutes cache
        $cachedData = json_decode(file_get_contents($cacheFile), true);
        echo json_encode($cachedData);
        return;
    }
    
    try {
        $productModel = new ProductModel($conn);
        
        if ($category_id) {
            $products = $productModel->getByCategory($category_id, $limit);
        } else {
            $products = $productModel->getAll($limit);
        }
        
        $formattedProducts = [];
        foreach ($products as $product) {
            $formattedProducts[] = [
                'id' => $product['product_id'],
                'name' => $product['product_name'],
                'price' => number_format($product['price'], 0, ',', '.') . 'đ',
                'image' => $product['image_url'] ? BASE_URL . '/uploads/products/' . $product['image_url'] : BASE_URL . '/assets/images/default-product.jpg',
                'url' => BASE_URL . '/index.php?page=product&id=' . $product['product_id']
            ];
        }
        
        $result = [
            'success' => true,
            'products' => $formattedProducts,
            'cached' => false
        ];
        
        // Save to cache
        file_put_contents($cacheFile, json_encode($result));
        
        echo json_encode($result);
        
    } catch (Exception $e) {
        error_log("Chatbot suggestions error: " . $e->getMessage());
        echo json_encode(['error' => 'Database error occurred']);
    }
}

/**
 * Handle save conversation with validation
 */
function handleSaveConversation() {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!$data || !isset($data['messages'])) {
        echo json_encode(['error' => 'Invalid conversation data']);
        return;
    }
    
    $conversation = $data['messages'];
    $analytics = $data['analytics'] ?? [];
    $session_id = $data['sessionId'] ?? session_id();
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    
    // Validate conversation data
    if (!is_array($conversation) || count($conversation) === 0) {
        echo json_encode(['error' => 'Empty conversation']);
        return;
    }
    
    // Sanitize conversation data
    $sanitizedConversation = [];
    foreach ($conversation as $message) {
        if (isset($message['type']) && isset($message['content'])) {
            $sanitizedConversation[] = [
                'type' => htmlspecialchars($message['type']),
                'content' => htmlspecialchars($message['content']),
                'timestamp' => $message['timestamp'] ?? date('Y-m-d H:i:s')
            ];
        }
    }
    
    // Save to database or file
    $log_data = [
        'timestamp' => date('Y-m-d H:i:s'),
        'session_id' => $session_id,
        'user_id' => $user_id,
        'conversation' => $sanitizedConversation,
        'analytics' => $analytics,
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
    ];
    
    // Save to database (if you have a conversations table)
    try {
        // You can implement database saving here
        // For now, we'll save to a log file
        $log_file = 'chatbot_logs/' . date('Y-m-d') . '_conversations.json';
        
        if (!is_dir('chatbot_logs')) {
            mkdir('chatbot_logs', 0755, true);
        }
        
        $existing_logs = [];
        if (file_exists($log_file)) {
            $existing_logs = json_decode(file_get_contents($log_file), true) ?? [];
        }
        
        $existing_logs[] = $log_data;
        
        // Keep only last 1000 conversations per day
        if (count($existing_logs) > 1000) {
            $existing_logs = array_slice($existing_logs, -1000);
        }
        
        file_put_contents($log_file, json_encode($existing_logs, JSON_PRETTY_PRINT));
        
        echo json_encode([
            'success' => true,
            'message' => 'Conversation saved',
            'conversation_id' => uniqid()
        ]);
        
    } catch (Exception $e) {
        error_log("Chatbot save conversation error: " . $e->getMessage());
        echo json_encode(['error' => 'Failed to save conversation']);
    }
}

/**
 * Handle get FAQ
 */
function handleGetFAQ() {
    $faqs = [
        [
            'question' => 'Làm thế nào để mua hàng?',
            'answer' => 'Bạn có thể mua hàng bằng cách: 1) Chọn sản phẩm, 2) Thêm vào giỏ hàng, 3) Thanh toán online hoặc khi nhận hàng.',
            'category' => 'shopping'
        ],
        [
            'question' => 'Thời gian giao hàng là bao lâu?',
            'answer' => 'Thời gian giao hàng từ 1-3 ngày tùy thuộc vào khu vực. Miễn phí ship cho đơn hàng từ 500.000đ.',
            'category' => 'shipping'
        ],
        [
            'question' => 'Chính sách đổi trả như thế nào?',
            'answer' => 'Chúng tôi chấp nhận đổi trả trong vòng 7 ngày với sản phẩm còn nguyên vẹn. Phí vận chuyển đổi trả: 30.000đ.',
            'category' => 'returns'
        ],
        [
            'question' => 'Có thể thanh toán bằng cách nào?',
            'answer' => 'Chúng tôi chấp nhận: Thanh toán online (VNPay, Momo), Thanh toán khi nhận hàng (COD), Chuyển khoản ngân hàng.',
            'category' => 'payment'
        ],
        [
            'question' => 'Sản phẩm có bảo hành không?',
            'answer' => 'Tất cả sản phẩm đều có bảo hành chất lượng. Nếu có lỗi từ nhà sản xuất, chúng tôi sẽ đổi mới hoặc hoàn tiền.',
            'category' => 'warranty'
        ]
    ];
    
    echo json_encode([
        'success' => true,
        'faqs' => $faqs,
        'total' => count($faqs)
    ]);
}

/**
 * Handle track analytics
 */
function handleTrackAnalytics() {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!$data || !isset($data['event'])) {
        echo json_encode(['error' => 'Invalid analytics data']);
        return;
    }
    
    $event = $data['event'];
    $eventData = $data['data'] ?? null;
    $sessionId = $data['sessionId'] ?? session_id();
    $timestamp = $data['timestamp'] ?? date('Y-m-d H:i:s');
    
    // Save analytics to file
    $analytics_file = 'chatbot_logs/' . date('Y-m-d') . '_analytics.json';
    
    if (!is_dir('chatbot_logs')) {
        mkdir('chatbot_logs', 0755, true);
    }
    
    $analytics_data = [
        'timestamp' => $timestamp,
        'session_id' => $sessionId,
        'event' => $event,
        'data' => $eventData,
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
    ];
    
    $existing_analytics = [];
    if (file_exists($analytics_file)) {
        $existing_analytics = json_decode(file_get_contents($analytics_file), true) ?? [];
    }
    
    $existing_analytics[] = $analytics_data;
    
    // Keep only last 5000 analytics events per day
    if (count($existing_analytics) > 5000) {
        $existing_analytics = array_slice($existing_analytics, -5000);
    }
    
    file_put_contents($analytics_file, json_encode($existing_analytics, JSON_PRETTY_PRINT));
    
    echo json_encode([
        'success' => true,
        'message' => 'Analytics tracked'
    ]);
}

/**
 * Handle get analytics (for admin dashboard)
 */
function handleGetAnalytics() {
    // This would typically require admin authentication
    // For now, we'll return basic stats
    
    $today = date('Y-m-d');
    $conversations_file = 'chatbot_logs/' . $today . '_conversations.json';
    $analytics_file = 'chatbot_logs/' . $today . '_analytics.json';
    
    $stats = [
        'date' => $today,
        'conversations' => 0,
        'messages' => 0,
        'events' => 0,
        'popular_features' => [],
        'response_time' => 0
    ];
    
    // Count conversations
    if (file_exists($conversations_file)) {
        $conversations = json_decode(file_get_contents($conversations_file), true) ?? [];
        $stats['conversations'] = count($conversations);
        
        foreach ($conversations as $conv) {
            $stats['messages'] += count($conv['conversation'] ?? []);
        }
    }
    
    // Count events
    if (file_exists($analytics_file)) {
        $analytics = json_decode(file_get_contents($analytics_file), true) ?? [];
        $stats['events'] = count($analytics);
        
        // Count popular features
        $featureCounts = [];
        foreach ($analytics as $event) {
            if ($event['event'] === 'feature_used' && isset($event['data'])) {
                $feature = $event['data'];
                $featureCounts[$feature] = ($featureCounts[$feature] ?? 0) + 1;
            }
        }
        
        arsort($featureCounts);
        $stats['popular_features'] = array_slice($featureCounts, 0, 5, true);
    }
    
    echo json_encode([
        'success' => true,
        'stats' => $stats
    ]);
}

/**
 * Get shipping information
 */
function getShippingInfo() {
    return [
        'domestic' => [
            'time' => '1-3 ngày',
            'cost' => '20.000đ - 50.000đ',
            'free_shipping' => 'Đơn hàng từ 500.000đ'
        ],
        'international' => [
            'time' => '7-15 ngày',
            'cost' => 'Liên hệ để biết thêm chi tiết'
        ]
    ];
}

/**
 * Get payment methods
 */
function getPaymentMethods() {
    return [
        'online' => [
            'VNPay',
            'Momo',
            'ZaloPay',
            'Chuyển khoản ngân hàng'
        ],
        'cod' => 'Thanh toán khi nhận hàng (COD)'
    ];
}

/**
 * Get return policy
 */
function getReturnPolicy() {
    return [
        'time_limit' => '7 ngày',
        'conditions' => [
            'Sản phẩm còn nguyên vẹn',
            'Còn đầy đủ phụ kiện',
            'Không áp dụng với sản phẩm đã sử dụng'
        ],
        'shipping_cost' => '30.000đ',
        'process' => [
            'Liên hệ hỗ trợ',
            'Gửi ảnh sản phẩm',
            'Đóng gói và gửi về',
            'Kiểm tra và xử lý'
        ]
    ];
}

/**
 * Clean old cache files
 */
function cleanOldCache() {
    global $cacheDir;
    
    if (!is_dir($cacheDir)) {
        return;
    }
    
    $files = glob($cacheDir . '*.json');
    $currentTime = time();
    
    foreach ($files as $file) {
        if ($currentTime - filemtime($file) > 3600) { // 1 hour
            unlink($file);
        }
    }
}

// Clean old cache files (run occasionally)
if (rand(1, 100) === 1) { // 1% chance
    cleanOldCache();
}
?>
