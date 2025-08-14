<?php
/**
 * Requirements Check Script
 * Checks if all necessary components are available
 */

echo "<h1>Shop Gau Yeu - Requirements Check</h1>\n";
echo "<pre>\n";

// Check PHP version
echo "🔍 Checking PHP version...\n";
if (version_compare(PHP_VERSION, '7.4.0', '>=')) {
    echo "✅ PHP version: " . PHP_VERSION . " (OK)\n";
} else {
    echo "❌ PHP version: " . PHP_VERSION . " (Requires 7.4.0 or higher)\n";
}

// Check PDO extension
echo "\n🔍 Checking PDO extension...\n";
if (extension_loaded('pdo')) {
    echo "✅ PDO extension is loaded\n";
} else {
    echo "❌ PDO extension is NOT loaded\n";
    echo "   Solution: Enable PDO in php.ini\n";
}

// Check PDO MySQL driver
echo "\n🔍 Checking PDO MySQL driver...\n";
if (extension_loaded('pdo_mysql')) {
    echo "✅ PDO MySQL driver is loaded\n";
} else {
    echo "❌ PDO MySQL driver is NOT loaded\n";
    echo "   Solution: Enable pdo_mysql in php.ini\n";
    echo "   Find: ;extension=pdo_mysql\n";
    echo "   Change to: extension=pdo_mysql\n";
}

// Check MySQL extension (alternative)
echo "\n🔍 Checking MySQL extension...\n";
if (extension_loaded('mysqli')) {
    echo "✅ MySQLi extension is loaded (alternative available)\n";
} else {
    echo "❌ MySQLi extension is NOT loaded\n";
}

// Check if we can connect to MySQL
echo "\n🔍 Testing MySQL connection...\n";
try {
    $host = "localhost";
    $username = "root";
    $password = "";
    
    // Try PDO first
    if (extension_loaded('pdo_mysql')) {
        $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $username, $password);
        echo "✅ MySQL connection successful (PDO)\n";
        
        // Check if database exists
        $stmt = $pdo->query("SHOW DATABASES LIKE 'shopgauyeu'");
        if ($stmt->rowCount() > 0) {
            echo "✅ Database 'shopgauyeu' exists\n";
        } else {
            echo "⚠️ Database 'shopgauyeu' does not exist (will be created)\n";
        }
    } 
    // Try MySQLi as alternative
    elseif (extension_loaded('mysqli')) {
        $mysqli = new mysqli($host, $username, $password);
        if ($mysqli->connect_error) {
            echo "❌ MySQL connection failed: " . $mysqli->connect_error . "\n";
        } else {
            echo "✅ MySQL connection successful (MySQLi)\n";
            
            // Check if database exists
            $result = $mysqli->query("SHOW DATABASES LIKE 'shopgauyeu'");
            if ($result->num_rows > 0) {
                echo "✅ Database 'shopgauyeu' exists\n";
            } else {
                echo "⚠️ Database 'shopgauyeu' does not exist (will be created)\n";
            }
        }
    } else {
        echo "❌ No MySQL drivers available\n";
    }
    
} catch (Exception $e) {
    echo "❌ MySQL connection failed: " . $e->getMessage() . "\n";
}

// Check file permissions
echo "\n🔍 Checking file permissions...\n";
$config_file = __DIR__ . '/config/config.php';
if (file_exists($config_file)) {
    echo "✅ Config file exists\n";
    if (is_readable($config_file)) {
        echo "✅ Config file is readable\n";
    } else {
        echo "❌ Config file is not readable\n";
    }
} else {
    echo "❌ Config file not found\n";
}

// Check data.sql file
echo "\n🔍 Checking SQL file...\n";
$sql_file = __DIR__ . '/data.sql';
if (file_exists($sql_file)) {
    echo "✅ data.sql file exists\n";
    if (is_readable($sql_file)) {
        echo "✅ data.sql file is readable\n";
    } else {
        echo "❌ data.sql file is not readable\n";
    }
} else {
    echo "❌ data.sql file not found\n";
}

echo "\n📋 Summary:\n";
echo "================\n";

$issues = [];

if (!extension_loaded('pdo_mysql')) {
    $issues[] = "PDO MySQL driver not enabled";
}

if (!extension_loaded('mysqli') && !extension_loaded('pdo_mysql')) {
    $issues[] = "No MySQL drivers available";
}

if (empty($issues)) {
    echo "✅ All requirements met! Database setup should work.\n";
    echo "\n🚀 Next steps:\n";
    echo "1. Run: http://localhost/shopgauyeu/config/init_database.php\n";
    echo "2. Test: http://localhost/shopgauyeu/test_database.php\n";
    echo "3. Access admin: http://localhost/shopgauyeu/admin\n";
} else {
    echo "❌ Issues found:\n";
    foreach ($issues as $issue) {
        echo "   - " . $issue . "\n";
    }
    echo "\n🔧 Please fix the issues above before proceeding.\n";
}

echo "</pre>\n";

echo "<h2>Quick Fix for PDO MySQL:</h2>\n";
echo "<ol>\n";
echo "<li>Open XAMPP Control Panel</li>\n";
echo "<li>Click 'Config' for Apache</li>\n";
echo "<li>Select 'php.ini'</li>\n";
echo "<li>Find: <code>;extension=pdo_mysql</code></li>\n";
echo "<li>Remove semicolon: <code>extension=pdo_mysql</code></li>\n";
echo "<li>Save and restart Apache</li>\n";
echo "</ol>\n";

echo "<h2>Alternative Manual Setup:</h2>\n";
echo "<ol>\n";
echo "<li>Open phpMyAdmin: <a href='http://localhost/phpmyadmin'>http://localhost/phpmyadmin</a></li>\n";
echo "<li>Create database: <code>shopgauyeu</code></li>\n";
echo "<li>Import file: <code>data.sql</code></li>\n";
echo "<li>Run initialization script</li>\n";
echo "</ol>\n";
?> 