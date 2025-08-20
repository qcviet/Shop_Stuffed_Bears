<?php
/**
 * Database Initialization Script
 * Creates database and tables for Shop Gau Yeu
 */

require_once 'config.php';

class DatabaseInitializer {
    private $host;
    private $username;
    private $password;
    private $db_name;

    public function __construct() {
        $this->host = "localhost";
        $this->username = "root";
        $this->password = "";
        $this->db_name = "dtshopgau";
    }

    public function initialize() {
        try {
            // Connect to MySQL without selecting database
            $pdo = new PDO("mysql:host=$this->host;charset=utf8mb4", $this->username, $this->password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Create database if it doesn't exist
            $this->createDatabase($pdo);
            
            // Connect to the specific database
            $pdo = new PDO("mysql:host=$this->host;dbname=$this->db_name;charset=utf8mb4", $this->username, $this->password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Create tables
            $this->createTables($pdo);
            
            // Insert sample data
            $this->insertSampleData($pdo);

            echo "✅ Database initialized successfully!\n";
            return true;

        } catch (PDOException $e) {
            echo "❌ Database initialization failed: " . $e->getMessage() . "\n";
            return false;
        }
    }

    private function createDatabase($pdo) {
        $sql = "CREATE DATABASE IF NOT EXISTS `$this->db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
        $pdo->exec($sql);
        echo "✅ Database '$this->db_name' created/verified\n";
    }

    private function createTables($pdo) {
        // Read and execute the SQL file
        $sql_file = __DIR__ . '/../data.sql';
        
        if (file_exists($sql_file)) {
            $sql = file_get_contents($sql_file);
            $pdo->exec($sql);
            echo "✅ Tables created successfully\n";
        } else {
            echo "❌ SQL file not found: $sql_file\n";
        }
    }

    private function insertSampleData($pdo) {
        // Insert sample categories
        $categories = [
            ['Gấu Bông', 'Teddy bears and plush toys'],
            ['Blind Box', 'Mystery boxes and collectibles'],
            ['Quà Tặng', 'Gift sets and special items'],
            ['Hoạt Hình', 'Cartoon and anime items'],
            ['Phụ Kiện', 'Accessories and small items']
        ];

        $stmt = $pdo->prepare("INSERT IGNORE INTO categories (category_name, description) VALUES (?, ?)");
        foreach ($categories as $category) {
            $stmt->execute($category);
        }
        echo "✅ Sample categories inserted\n";

        // Insert sample products (price/stock are derived from variants, so not stored here)
        $products = [
            [1, 'Cute Teddy Bear', 'A soft and cuddly teddy bear perfect for children'],
            [1, 'Large Plush Bear', 'Big fluffy bear for decoration'],
            [2, 'Blind Box Mystery', 'Surprise collectible figure'],
            [2, 'Anime Blind Box', 'Japanese anime character figures'],
            [3, 'Gift Set Special', 'Beautiful gift package with multiple items'],
            [3, 'Birthday Gift Box', 'Perfect birthday present'],
            [4, 'Cartoon Character', 'Popular cartoon character plush'],
            [4, 'Anime Figure', 'High-quality anime figure'],
            [5, 'Keychain Set', 'Cute keychains collection'],
            [5, 'Phone Case', 'Decorative phone case']
        ];

        $stmt = $pdo->prepare("INSERT IGNORE INTO products (category_id, product_name, description) VALUES (?, ?, ?)");
        foreach ($products as $product) {
            $stmt->execute($product);
        }
        echo "✅ Sample products inserted\n";

        // Insert sample admin user
        $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT IGNORE INTO users (username, password, email, full_name, role) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute(['admin', $admin_password, 'admin@shopgauyeu.com', 'Administrator', 'admin']);
        echo "✅ Admin user created (username: admin, password: admin123)\n";

        // Insert sample regular user
        $user_password = password_hash('user123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT IGNORE INTO users (username, password, email, full_name, role) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute(['user', $user_password, 'user@example.com', 'Sample User', 'user']);
        echo "✅ Sample user created (username: user, password: user123)\n";
    }
}

// Run initialization if this file is executed directly
if (basename(__FILE__) == basename($_SERVER['SCRIPT_NAME'])) {
    $initializer = new DatabaseInitializer();
    $initializer->initialize();
}
?> 