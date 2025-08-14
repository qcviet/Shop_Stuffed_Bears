<?php
class Database {
    private $host = "localhost";
    private $db_name = "dtshopgau";
    private $username = "root";
    private $password = "";
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            // Check if PDO MySQL extension is available
            if (!extension_loaded('pdo_mysql')) {
                throw new Exception("PDO MySQL extension is not loaded. Please enable it in php.ini");
            }
            
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4",
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            echo "Database connection failed: " . $exception->getMessage();
        } catch(Exception $exception) {
            echo "Configuration error: " . $exception->getMessage();
        }
        return $this->conn;
    }
}
?> 