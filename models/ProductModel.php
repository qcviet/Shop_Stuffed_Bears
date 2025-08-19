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

    // Create new product (price/stock are derived from variants)
    public function create($category_id, $product_name, $description) {
        $query = "INSERT INTO " . $this->table_name . " 
                  (category_id, product_name, description) 
                  VALUES (:category_id, :product_name, :description)";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(":category_id", $category_id);
        $stmt->bindParam(":product_name", $product_name);
        $stmt->bindParam(":description", $description);
        
        return $stmt->execute();
    }

    // Get product by ID
    public function getById($product_id) {
        $query = "SELECT 
                        p.*, 
                        c.category_name,
                        (SELECT MIN(v.price) FROM product_variants v WHERE v.product_id = p.product_id) AS price,
                        (SELECT COALESCE(SUM(v2.stock), 0) FROM product_variants v2 WHERE v2.product_id = p.product_id) AS stock,
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
        $query = "SELECT 
                        p.*, 
                        c.category_name,
                        (SELECT MIN(v.price) FROM product_variants v WHERE v.product_id = p.product_id) AS price,
                        (SELECT v1.variant_id FROM product_variants v1 WHERE v1.product_id = p.product_id ORDER BY v1.price ASC LIMIT 1) AS min_variant_id,
                        (SELECT COALESCE(SUM(v2.stock), 0) FROM product_variants v2 WHERE v2.product_id = p.product_id) AS stock,
                        (SELECT GROUP_CONCAT(DISTINCT v3.price ORDER BY v3.price ASC SEPARATOR ',') FROM product_variants v3 WHERE v3.product_id = p.product_id) AS price_list,
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

    /**
     * Get products by category with optional filters on variant price and size
     * @param int $category_id
     * @param int|null $min_price integer VND
     * @param int|null $max_price integer VND
     * @param array $sizes array of size strings to include
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getByCategoryWithFilters($category_id, $min_price = null, $max_price = null, $sizes = [], $limit = 16, $offset = 0) {
        $query = "SELECT 
                        p.*, 
                        c.category_name,
                        MIN(v.price) AS price,
                        (SELECT v1.variant_id FROM product_variants v1 WHERE v1.product_id = p.product_id ORDER BY v1.price ASC LIMIT 1) AS min_variant_id,
                        COALESCE(SUM(v.stock), 0) AS stock,
                        (SELECT pi.image_url FROM product_images pi WHERE pi.product_id = p.product_id ORDER BY pi.image_id ASC LIMIT 1) AS image_url
                  FROM " . $this->table_name . " p 
                  JOIN product_variants v ON v.product_id = p.product_id
                  LEFT JOIN categories c ON p.category_id = c.category_id
                  WHERE p.category_id = :category_id";
        $params = [':category_id' => $category_id];

        if ($min_price !== null) {
            $query .= " AND v.price >= :min_price";
            $params[':min_price'] = (int)$min_price;
        }
        if ($max_price !== null) {
            $query .= " AND v.price <= :max_price";
            $params[':max_price'] = (int)$max_price;
        }
        if (!empty($sizes)) {
            // Build placeholders for sizes
            $in = [];
            foreach ($sizes as $idx => $sz) {
                $ph = ':size' . $idx;
                $in[] = $ph;
                $params[$ph] = $sz;
            }
            $query .= " AND v.size IN (" . implode(',', $in) . ")";
        }

        $query .= " GROUP BY p.product_id ORDER BY p.created_at DESC LIMIT :limit OFFSET :offset";
        $stmt = $this->conn->prepare($query);
        foreach ($params as $k => $v) {
            if ($k === ':limit' || $k === ':offset') {
                // handled below
                continue;
            }
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get distinct variant sizes available for a category
     */
    public function getSizesForCategory($category_id) {
        $query = "SELECT DISTINCT v.size 
                  FROM product_variants v 
                  JOIN products p ON p.product_id = v.product_id 
                  WHERE p.category_id = :cid 
                  ORDER BY v.size ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':cid', $category_id);
        $stmt->execute();
        return array_map(function($r) { return $r['size']; }, $stmt->fetchAll(PDO::FETCH_ASSOC));
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
        // Only allow base fields; price/stock derived from variants
        $allowed_fields = ['category_id', 'product_name', 'description'];
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
        // Deprecated: stock is derived from variants; method kept for backward compatibility
        return true;
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
        // Derive stock from variants sum
        $query = "SELECT 
                        p.*, 
                        c.category_name,
                        COALESCE(SUM(v.stock), 0) AS total_stock
                  FROM " . $this->table_name . " p
                  LEFT JOIN categories c ON p.category_id = c.category_id
                  LEFT JOIN product_variants v ON v.product_id = p.product_id
                  GROUP BY p.product_id
                  HAVING total_stock <= :threshold
                  ORDER BY total_stock ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":threshold", $threshold, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        // Normalize keys for downstream code: expose 'stock' like before
        foreach ($rows as &$row) {
            $row['stock'] = isset($row['total_stock']) ? (int)$row['total_stock'] : 0;
        }
        return $rows;
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