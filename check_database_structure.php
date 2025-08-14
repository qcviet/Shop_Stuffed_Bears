<?php
// Check database structure and diagnose the column issue
echo "<h1>Database Structure Check</h1>";

// Check if PDO MySQL driver is available
if (!extension_loaded('pdo_mysql')) {
    echo "<h2>❌ PDO MySQL Driver Missing</h2>";
    echo "<p>The PDO MySQL driver is not enabled. This is required for the application to work.</p>";
    echo "<h3>To fix this in XAMPP:</h3>";
    echo "<ol>";
    echo "<li>Open XAMPP Control Panel</li>";
    echo "<li>Click 'Config' button for Apache</li>";
    echo "<li>Select 'php.ini'</li>";
    echo "<li>Find the line: <code>;extension=pdo_mysql</code></li>";
    echo "<li>Remove the semicolon (;) to uncomment it: <code>extension=pdo_mysql</code></li>";
    echo "<li>Save the file and restart Apache</li>";
    echo "</ol>";
    echo "<p>After enabling the driver, refresh this page.</p>";
    exit;
}

echo "<h2>✅ PDO MySQL Driver Available</h2>";

// Try to connect to database
try {
    $pdo = new PDO("mysql:host=localhost;dbname=dtshopgau;charset=utf8mb4", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<h2>✅ Connected to database 'dtshopgau'</h2>";
    
    // Check if users table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
    if ($stmt->rowCount() > 0) {
        echo "<h3>✅ Users table exists</h3>";
        
        // Show table structure
        echo "<h4>Users table structure:</h4>";
        $stmt = $pdo->query("DESCRIBE users");
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
        
        // Check if username column exists
        $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'username'");
        if ($stmt->rowCount() > 0) {
            echo "<h3>✅ Username column exists</h3>";
        } else {
            echo "<h3>❌ Username column missing!</h3>";
            echo "<p>This explains the error. The username column is missing from the users table.</p>";
        }
        
        // Show sample data
        echo "<h4>Sample users data:</h4>";
        $stmt = $pdo->query("SELECT * FROM users LIMIT 5");
        if ($stmt->rowCount() > 0) {
            echo "<table border='1' style='border-collapse: collapse;'>";
            $first = true;
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                if ($first) {
                    echo "<tr>";
                    foreach (array_keys($row) as $key) {
                        echo "<th>" . htmlspecialchars($key) . "</th>";
                    }
                    echo "</tr>";
                    $first = false;
                }
                echo "<tr>";
                foreach ($row as $value) {
                    echo "<td>" . htmlspecialchars($value) . "</td>";
                }
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p>No users found in the table.</p>";
        }
        
    } else {
        echo "<h3>❌ Users table does not exist</h3>";
        echo "<p>The users table needs to be created. Run the database initialization script after enabling PDO MySQL driver.</p>";
    }
    
} catch (PDOException $e) {
    echo "<h2>❌ Database connection failed</h2>";
    echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    
    if (strpos($e->getMessage(), "Unknown database") !== false) {
        echo "<h3>Database 'dtshopgau' does not exist</h3>";
        echo "<p>You need to create the database first:</p>";
        echo "<ol>";
        echo "<li>Open phpMyAdmin (http://localhost/phpmyadmin)</li>";
        echo "<li>Create a new database named 'dtshopgau'</li>";
        echo "<li>Or run the database initialization script after enabling PDO MySQL driver</li>";
        echo "</ol>";
    }
}
?> 