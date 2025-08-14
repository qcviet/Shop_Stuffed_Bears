<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Database Setup Test</h2>";

// Test 1: Check PHP Extensions
echo "<h3>1. PHP Extensions Check:</h3>";
if (extension_loaded('pdo_mysql')) {
    echo "✅ PDO MySQL extension is loaded<br>";
} else {
    echo "❌ PDO MySQL extension is NOT loaded<br>";
    echo "<strong>Solution:</strong> Enable PDO MySQL extension in php.ini<br>";
    echo "Find ';extension=pdo_mysql' and remove the semicolon<br><br>";
}

// Test 2: Check MySQL Server
echo "<h3>2. MySQL Server Check:</h3>";
try {
    $pdo = new PDO("mysql:host=localhost", "root", "");
    echo "✅ MySQL server is running<br>";
} catch (PDOException $e) {
    echo "❌ Cannot connect to MySQL server: " . $e->getMessage() . "<br>";
    echo "<strong>Solution:</strong> Start MySQL in XAMPP Control Panel<br><br>";
}

// Test 3: Check Database Exists
echo "<h3>3. Database Check:</h3>";
try {
    $pdo = new PDO("mysql:host=localhost", "root", "");
    $stmt = $pdo->query("SHOW DATABASES LIKE 'dtshopgau'");
    if ($stmt->rowCount() > 0) {
        echo "✅ Database 'dtshopgau' exists<br>";
    } else {
        echo "❌ Database 'dtshopgau' does not exist<br>";
        echo "<strong>Solution:</strong> Create database 'dtshopgau' in phpMyAdmin<br><br>";
    }
} catch (PDOException $e) {
    echo "❌ Cannot check database: " . $e->getMessage() . "<br>";
}

// Test 4: Check Tables
echo "<h3>4. Tables Check:</h3>";
try {
    $pdo = new PDO("mysql:host=localhost;dbname=dtshopgau", "root", "");
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (count($tables) > 0) {
        echo "✅ Database has " . count($tables) . " tables<br>";
        echo "Tables: " . implode(', ', $tables) . "<br>";
        
        // Check if users table exists
        if (in_array('users', $tables)) {
            echo "✅ Users table exists<br>";
            
            // Check if users table has data
            $stmt = $pdo->query("SELECT COUNT(*) FROM users");
            $count = $stmt->fetchColumn();
            echo "Users in database: " . $count . "<br>";
        } else {
            echo "❌ Users table does not exist<br>";
            echo "<strong>Solution:</strong> Import data.sql file in phpMyAdmin<br><br>";
        }
    } else {
        echo "❌ Database is empty<br>";
        echo "<strong>Solution:</strong> Import data.sql file in phpMyAdmin<br><br>";
    }
} catch (PDOException $e) {
    echo "❌ Cannot check tables: " . $e->getMessage() . "<br>";
}

// Test 5: Test AppController
echo "<h3>5. AppController Test:</h3>";
try {
    require_once 'config/config.php';
    require_once 'config/database.php';
    require_once 'controller/AppController.php';
    
    $appController = new AppController();
    if ($appController->isConnected()) {
        echo "✅ AppController can connect to database<br>";
    } else {
        echo "❌ AppController cannot connect to database<br>";
    }
} catch (Exception $e) {
    echo "❌ AppController error: " . $e->getMessage() . "<br>";
}

echo "<h3>Summary:</h3>";
echo "If you see any ❌ errors above, follow the solutions provided.<br>";
echo "After fixing all issues, the login and registration forms will work properly.<br><br>";

echo "<h3>Quick Setup Steps:</h3>";
echo "1. Enable PDO MySQL extension in php.ini<br>";
echo "2. Start MySQL in XAMPP Control Panel<br>";
echo "3. Create database 'dtshopgau' in phpMyAdmin<br>";
echo "4. Import data.sql file in phpMyAdmin<br>";
echo "5. Restart Apache in XAMPP Control Panel<br>";
echo "6. Test login page: <a href='http://localhost/shopgauyeu/index.php?page=login'>Login Page</a><br>";
?> 