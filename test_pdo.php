<?php
echo "<h1>PDO MySQL Extension Test</h1>";

// Check if PDO extension is loaded
if (!extension_loaded('pdo')) {
    echo "<h2>❌ PDO extension not loaded</h2>";
    echo "<p>PDO extension is required for database operations.</p>";
    exit;
}

echo "<h2>✅ PDO extension loaded</h2>";

// Check if PDO MySQL driver is available
if (!extension_loaded('pdo_mysql')) {
    echo "<h2>❌ PDO MySQL driver not loaded</h2>";
    echo "<p>This is the main issue. You need to enable the PDO MySQL extension in XAMPP.</p>";
    echo "<h3>Quick Fix:</h3>";
    echo "<ol>";
    echo "<li>Open XAMPP Control Panel</li>";
    echo "<li>Click 'Config' button for Apache</li>";
    echo "<li>Select 'php.ini'</li>";
    echo "<li>Find: <code>;extension=pdo_mysql</code></li>";
    echo "<li>Remove the semicolon: <code>extension=pdo_mysql</code></li>";
    echo "<li>Save and restart Apache</li>";
    echo "</ol>";
    echo "<p><strong>After enabling the extension, refresh this page.</strong></p>";
    exit;
}

echo "<h2>✅ PDO MySQL driver loaded</h2>";

// List available PDO drivers
echo "<h3>Available PDO drivers:</h3>";
$drivers = PDO::getAvailableDrivers();
if (empty($drivers)) {
    echo "<p>No PDO drivers available.</p>";
} else {
    echo "<ul>";
    foreach ($drivers as $driver) {
        echo "<li>$driver</li>";
    }
    echo "</ul>";
}

// Test database connection
echo "<h3>Testing database connection...</h3>";
try {
    $pdo = new PDO("mysql:host=localhost;dbname=dtshopgau;charset=utf8mb4", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<h4>✅ Successfully connected to database 'dtshopgau'</h4>";
    
    // Test a simple query
    $stmt = $pdo->query("SELECT VERSION() as version");
    $version = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<p><strong>MySQL Version:</strong> " . htmlspecialchars($version['version']) . "</p>";
    
    // Check if users table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
    if ($stmt->rowCount() > 0) {
        echo "<h4>✅ Users table exists</h4>";
        
        // Show table structure
        $stmt = $pdo->query("DESCRIBE users");
        echo "<h5>Users table structure:</h5>";
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['Field']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Key']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Default']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Extra']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        echo "<h4>🎉 Everything is working! You can now:</h4>";
        echo "<ul>";
        echo "<li><a href='register'>Register a new user</a></li>";
        echo "<li><a href='login'>Login with existing user</a></li>";
        echo "<li><a href='admin/login'>Access admin panel</a></li>";
        echo "</ul>";
        
    } else {
        echo "<h4>❌ Users table does not exist</h4>";
        echo "<p>You need to initialize the database. Run:</p>";
        echo "<code>php config/init_database.php</code>";
    }
    
} catch (PDOException $e) {
    echo "<h4>❌ Database connection failed</h4>";
    echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    
    if (strpos($e->getMessage(), "Unknown database") !== false) {
        echo "<h5>Database 'dtshopgau' does not exist</h5>";
        echo "<p>You need to create the database first:</p>";
        echo "<ol>";
        echo "<li>Open phpMyAdmin: <a href='http://localhost/phpmyadmin' target='_blank'>http://localhost/phpmyadmin</a></li>";
        echo "<li>Create a new database named 'dtshopgau'</li>";
        echo "<li>Or run: <code>php config/init_database.php</code></li>";
        echo "</ol>";
    }
}

echo "<hr>";
echo "<p><strong>Next steps:</strong></p>";
echo "<ol>";
echo "<li>If PDO MySQL is not enabled, follow the instructions above</li>";
echo "<li>If database doesn't exist, create it or run the init script</li>";
echo "<li>If everything works, test user registration/login</li>";
echo "</ol>";
?> 