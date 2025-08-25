<?php
/**
 * Base Controller Class
 * Provides common functionality and database connection for all controllers
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

abstract class BaseController {
    protected $db;
    protected static $hasOrderColorColumn = null;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    // Database connection check
    public function isConnected() {
        return $this->db !== null;
    }

    // Common validation methods
    public function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    public function validatePassword($password) {
        return strlen($password) >= 6;
    }

    public function validatePrice($price) {
        return is_numeric($price) && $price >= 0;
    }

    public function validateStock($stock) {
        return is_numeric($stock) && $stock >= 0;
    }

    // Error handling
    public function getLastError() {
        return $this->db ? null : "Database connection failed";
    }

    // Check if order_items table has color_name column
    protected function hasOrderColorColumn() {
        if (self::$hasOrderColorColumn !== null) { 
            return self::$hasOrderColorColumn; 
        }
        try {
            $stmt = $this->db->query("SHOW COLUMNS FROM order_items LIKE 'color_name'");
            self::$hasOrderColorColumn = $stmt && $stmt->rowCount() > 0;
        } catch (Exception $e) {
            self::$hasOrderColorColumn = false;
        }
        return self::$hasOrderColorColumn;
    }
}
?>
