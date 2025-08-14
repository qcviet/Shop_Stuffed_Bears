<?php
/**
 * Product Model
 * Handles all product-related database operations
 */

class ProductModel {
    private $conn;
    private $table_name = "products";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create new product
    public function create($category_id, $product_name, $description, $price, $stock = 0) {
        $query = "INSERT INTO " . $this->table_name . " 
                  (category_id, product_name, description, price, stock) 
                  VALUES (:category_id, :product_name, :description, :price, :stock)";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(":category_id", $category_id);
        $stmt->bindParam(":product_name", $product_name);
        $stmt->bindParam(":description", $description);
        $stmt->bindParam(":price", $price);
        $stmt->bindParam(":stock", $stock);
        
        return $stmt->execute();
    }

    // Get product by ID
    public function getById($product_id) {
        $query = "SELECT p.*, c.category_name,
                         GROUP_CONCAT(pi.image_url ORDER BY pi.image_id ASC) AS images
                  FROM " . $this->table_name . " p 
                  LEFT JOIN categories c ON p.category_id = c.category_id 
                  LEFT JOIN product_images pi ON pi.product_id = p.product_id
                  WHERE p.product_id = :product_id
                  GROUP BY p.product_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":product_id", $product_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $row['images'] = !empty($row['images']) ? explode(',', $row['images']) : [];
        }
        return $row;
    }

    // Get all products
    public function getAll($limit = null, $offset = null, $category_id = null) {
        $query = "SELECT p.*, c.category_name,
                         (SELECT pi.image_url FROM product_images pi WHERE pi.product_id = p.product_id ORDER BY pi.image_id ASC LIMIT 1) AS image_url
                  FROM " . $this->table_name . " p 
                  LEFT JOIN categories c ON p.category_id = c.category_id";
        
        $params = [];
        
        if ($category_id) {
            $query .= " WHERE p.category_id = :category_id";
            $params[':category_id'] = $category_id;
        }
        
        $query .= " ORDER BY p.created_at DESC";
        
        if ($limit) {
            $query .= " LIMIT :limit";
            $params[':limit'] = $limit;
            if ($offset) {
                $query .= " OFFSET :offset";
                $params[':offset'] = $offset;
            }
        }
        
        $stmt = $this->conn->prepare($query);
        
        foreach ($params as $key => $value) {
            if ($key == ':limit' || $key == ':offset') {
                $stmt->bindValue($key, $value, PDO::PARAM_INT);
            } else {
                $stmt->bindValue($key, $value);
            }
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get products by category
    public function getByCategory($category_id, $limit = null, $offset = null) {
        return $this->getAll($limit, $offset, $category_id);
    }

    // Search products
    public function search($search_term, $limit = null, $offset = null) {
        $query = "SELECT p.*, c.category_name 
                  FROM " . $this->table_name . " p 
                  LEFT JOIN categories c ON p.category_id = c.category_id 
                  WHERE p.product_name LIKE :search_term 
                  OR p.description LIKE :search_term 
                  OR c.category_name LIKE :search_term 
                  ORDER BY p.created_at DESC";
        
        if ($limit) {
            $query .= " LIMIT :limit";
            if ($offset) {
                $query .= " OFFSET :offset";
            }
        }
        
        $stmt = $this->conn->prepare($query);
        
        $search_pattern = "%$search_term%";
        $stmt->bindParam(":search_term", $search_pattern);
        
        if ($limit) {
            $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
            if ($offset) {
                $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
            }
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Update product
    public function update($product_id, $data) {
        $allowed_fields = ['category_id', 'product_name', 'description', 'price', 'stock'];
        $set_clause = [];
        $params = [':product_id' => $product_id];
        
        foreach ($data as $field => $value) {
            if (in_array($field, $allowed_fields)) {
                $set_clause[] = "$field = :$field";
                $params[":$field"] = $value;
            }
        }
        
        if (empty($set_clause)) {
            return false;
        }
        
        $query = "UPDATE " . $this->table_name . " SET " . implode(', ', $set_clause) . " WHERE product_id = :product_id";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute($params);
    }

    // Delete product
    public function delete($product_id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE product_id = :product_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":product_id", $product_id);
        return $stmt->execute();
    }

    // Update stock
    public function updateStock($product_id, $new_stock) {
        $query = "UPDATE " . $this->table_name . " SET stock = :stock WHERE product_id = :product_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":stock", $new_stock);
        $stmt->bindParam(":product_id", $product_id);
        return $stmt->execute();
    }

    // Get product count
    public function getCount($category_id = null) {
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name;
        $params = [];
        
        if ($category_id) {
            $query .= " WHERE category_id = :category_id";
            $params[':category_id'] = $category_id;
        }
        
        $stmt = $this->conn->prepare($query);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'];
    }

    // Get low stock products
    public function getLowStock($threshold = 10) {
        $query = "SELECT p.*, c.category_name 
                  FROM " . $this->table_name . " p 
                  LEFT JOIN categories c ON p.category_id = c.category_id 
                  WHERE p.stock <= :threshold 
                  ORDER BY p.stock ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":threshold", $threshold);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get new products
    public function getNewProducts($limit = 10) {
        $query = "SELECT p.*, c.category_name 
                  FROM " . $this->table_name . " p 
                  LEFT JOIN categories c ON p.category_id = c.category_id 
                  ORDER BY p.created_at DESC 
                  LIMIT :limit";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get products with images
    public function getWithImages($product_id) {
        $query = "SELECT p.*, c.category_name, GROUP_CONCAT(pi.image_url) as images 
                  FROM " . $this->table_name . " p 
                  LEFT JOIN categories c ON p.category_id = c.category_id 
                  LEFT JOIN product_images pi ON p.product_id = pi.product_id 
                  WHERE p.product_id = :product_id 
                  GROUP BY p.product_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":product_id", $product_id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result && $result['images']) {
            $result['images'] = explode(',', $result['images']);
        } else {
            $result['images'] = [];
        }
        
        return $result;
    }

    // Get image rows for a product
    public function getImages($product_id) {
        $query = "SELECT image_id, image_url FROM product_images WHERE product_id = :product_id ORDER BY image_id ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":product_id", $product_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Add an image row for a product
    public function addImage($product_id, $image_url) {
        $query = "INSERT INTO product_images (product_id, image_url) VALUES (:product_id, :image_url)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":product_id", $product_id);
        $stmt->bindParam(":image_url", $image_url);
        return $stmt->execute();
    }

    // Delete an image by id
    public function deleteImage($image_id) {
        $query = "DELETE FROM product_images WHERE image_id = :image_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":image_id", $image_id);
        return $stmt->execute();
    }
}
?> 