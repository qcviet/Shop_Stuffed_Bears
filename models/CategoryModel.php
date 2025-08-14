<?php
/**
 * Category Model
 * Handles all category-related database operations
 */

class CategoryModel {
    private $conn;
    private $table_name = "categories";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create new category
    public function create($category_name, $description = null) {
        $query = "INSERT INTO " . $this->table_name . " (category_name, description) VALUES (:category_name, :description)";
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(":category_name", $category_name);
        $stmt->bindParam(":description", $description);
        
        return $stmt->execute();
    }

    // Get category by ID
    public function getById($category_id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE category_id = :category_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":category_id", $category_id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Get category by name
    public function getByName($category_name) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE category_name = :category_name";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":category_name", $category_name);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Get all categories
    public function getAll() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY category_name ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Update category
    public function update($category_id, $category_name, $description = null) {
        $query = "UPDATE " . $this->table_name . " SET category_name = :category_name, description = :description WHERE category_id = :category_id";
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(":category_name", $category_name);
        $stmt->bindParam(":description", $description);
        $stmt->bindParam(":category_id", $category_id);
        
        return $stmt->execute();
    }

    // Delete category
    public function delete($category_id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE category_id = :category_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":category_id", $category_id);
        return $stmt->execute();
    }

    // Get category count
    public function getCount() {
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'];
    }

    // Check if category name exists
    public function nameExists($category_name, $exclude_id = null) {
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " WHERE category_name = :category_name";
        if ($exclude_id) {
            $query .= " AND category_id != :exclude_id";
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":category_name", $category_name);
        if ($exclude_id) {
            $stmt->bindParam(":exclude_id", $exclude_id);
        }
        
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] > 0;
    }

    // Get categories with product count (optionally paginated)
    public function getWithProductCount($limit = null, $offset = null) {
        $query = "SELECT c.*, COUNT(p.product_id) as product_count 
                  FROM " . $this->table_name . " c 
                  LEFT JOIN products p ON c.category_id = p.category_id 
                  GROUP BY c.category_id 
                  ORDER BY c.category_name ASC";
        if ($limit !== null) {
            $query .= " LIMIT :limit";
            if ($offset !== null) {
                $query .= " OFFSET :offset";
            }
        }
        $stmt = $this->conn->prepare($query);
        if ($limit !== null) {
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            if ($offset !== null) {
                $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
            }
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?> 