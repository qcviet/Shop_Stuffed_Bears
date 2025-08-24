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
                        (SELECT GROUP_CONCAT(DISTINCT CONCAT(v3.variant_id, ':', v3.size, ':', v3.price) ORDER BY v3.price ASC SEPARATOR '|') FROM product_variants v3 WHERE v3.product_id = p.product_id) AS variants_summary,
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

    /**
     * Get distinct colors available for a category
     */
    public function getColorsForCategory($category_id) {
        $query = "SELECT DISTINCT pc.color_name 
                  FROM product_colors pc 
                  JOIN products p ON p.product_id = pc.product_id 
                  WHERE p.category_id = :cid 
                  ORDER BY pc.color_name ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':cid', $category_id);
        $stmt->execute();
        return array_map(function($r) { return $r['color_name']; }, $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    /**
     * Get all distinct sizes available across all products
     */
    public function getAllSizes() {
        $query = "SELECT DISTINCT v.size 
                  FROM product_variants v 
                  ORDER BY v.size ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return array_map(function($r) { return $r['size']; }, $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    /**
     * Get all distinct colors available across all products
     */
    public function getAllColors() {
        $query = "SELECT DISTINCT pc.color_name 
                  FROM product_colors pc 
                  ORDER BY pc.color_name ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return array_map(function($r) { return $r['color_name']; }, $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    // Search products with enhanced functionality
    public function search($search_term, $limit = null, $offset = null) {
        return $this->searchProducts($search_term, '', $limit, $offset);
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

    // Search products with category filter
    public function searchProducts($search_query = '', $category_id = '', $limit = null, $offset = null) {
        $query = "SELECT 
                        p.*, 
                        c.category_name,
                        (SELECT MIN(v.price) FROM product_variants v WHERE v.product_id = p.product_id) AS price,
                        (SELECT v1.variant_id FROM product_variants v1 WHERE v1.product_id = p.product_id ORDER BY v1.price ASC LIMIT 1) AS min_variant_id,
                        (SELECT COALESCE(SUM(v2.stock), 0) FROM product_variants v2 WHERE v2.product_id = p.product_id) AS stock,
                        (SELECT GROUP_CONCAT(DISTINCT CONCAT(v3.variant_id, ':', v3.size, ':', v3.price) ORDER BY v3.price ASC SEPARATOR '|') FROM product_variants v3 WHERE v3.product_id = p.product_id) AS variants_summary,
                        (SELECT pi.image_url FROM product_images pi WHERE pi.product_id = p.product_id ORDER BY pi.image_id ASC LIMIT 1) AS image_url
                  FROM " . $this->table_name . " p 
                  LEFT JOIN categories c ON p.category_id = c.category_id
                  WHERE 1=1";
        
        $params = [];
        
        // Add search query condition with improved fuzzy search
        if (!empty($search_query)) {
            $query .= " AND (
                p.product_name LIKE :search_query 
                OR p.product_name LIKE :search_start 
                OR p.product_name LIKE :search_end 
                OR p.product_name LIKE :search_words
                OR p.description LIKE :search_query 
                OR p.description LIKE :search_start 
                OR p.description LIKE :search_end
            )";
            $params[':search_query'] = '%' . $search_query . '%';
            $params[':search_start'] = $search_query . '%';
            $params[':search_end'] = '%' . $search_query;
            $params[':search_words'] = '%' . str_replace(' ', '%', $search_query) . '%';
        }
        
        // Add category filter
        if (!empty($category_id)) {
            $query .= " AND p.category_id = :category_id";
            $params[':category_id'] = $category_id;
        }
        
        $query .= " ORDER BY p.created_at DESC";
        
        // Add pagination
        if ($limit) {
            $query .= " LIMIT :limit";
            if ($offset) {
                $query .= " OFFSET :offset";
            }
        }
        
        $stmt = $this->conn->prepare($query);
        
        // Bind search and filter parameters first
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        // Bind LIMIT and OFFSET parameters separately
        if ($limit) {
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            if ($offset) {
                $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            }
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get search count for pagination
    public function getSearchCount($search_query = '', $category_id = '') {
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " p WHERE 1=1";
        
        $params = [];
        
        // Add search query condition with improved fuzzy search
        if (!empty($search_query)) {
            $query .= " AND (
                p.product_name LIKE :search_query 
                OR p.product_name LIKE :search_start 
                OR p.product_name LIKE :search_end 
                OR p.product_name LIKE :search_words
                OR p.description LIKE :search_query 
                OR p.description LIKE :search_start 
                OR p.description LIKE :search_end
            )";
            $params[':search_query'] = '%' . $search_query . '%';
            $params[':search_start'] = $search_query . '%';
            $params[':search_end'] = '%' . $search_query;
            $params[':search_words'] = '%' . str_replace(' ', '%', $search_query) . '%';
        }
        
        // Add category filter
        if (!empty($category_id)) {
            $query .= " AND p.category_id = :category_id";
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

    /**
     * Get products with applied promotions
     */
    public function getProductsWithPromotions($limit = 10, $offset = 0, $category_id = null) {
        try {
            $sql = "SELECT p.*, c.category_name, 
                           COALESCE(prom.discount_percent, 0) as discount_percent,
                           prom.promotion_type,
                           prom.title as promotion_title
                    FROM products p
                    LEFT JOIN categories c ON p.category_id = c.category_id
                    LEFT JOIN (
                        SELECT pr.*, 
                               CASE 
                                   WHEN pr.promotion_type = 'general' THEN 1
                                   WHEN pr.promotion_type = 'category' AND pr.target_id = p.category_id THEN 1
                                   WHEN pr.promotion_type = 'product' AND pr.target_id = p.product_id THEN 1
                                   ELSE 0
                               END as is_applicable
                        FROM promotions pr
                        WHERE pr.is_active = 1 
                        AND pr.start_date <= CURDATE() 
                        AND pr.end_date >= CURDATE()
                    ) prom ON (
                        prom.promotion_type = 'general' OR
                        (prom.promotion_type = 'category' AND prom.target_id = p.category_id) OR
                        (prom.promotion_type = 'product' AND prom.target_id = p.product_id)
                    )";
            
            if ($category_id) {
                $sql .= " WHERE p.category_id = :category_id";
            }
            
            $sql .= " ORDER BY p.created_at DESC LIMIT :limit OFFSET :offset";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            
            if ($category_id) {
                $stmt->bindValue(':category_id', $category_id, PDO::PARAM_INT);
            }
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("ProductModel getProductsWithPromotions error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get product with applied promotions by ID
     */
    public function getProductWithPromotionsById($product_id) {
        try {
            $sql = "SELECT p.*, c.category_name, 
                           COALESCE(prom.discount_percent, 0) as discount_percent,
                           prom.promotion_type,
                           prom.title as promotion_title,
                           prom.description as promotion_description
                    FROM products p
                    LEFT JOIN categories c ON p.category_id = c.category_id
                    LEFT JOIN (
                        SELECT pr.*
                        FROM promotions pr
                        WHERE pr.is_active = 1 
                        AND pr.start_date <= CURDATE() 
                        AND pr.end_date >= CURDATE()
                        AND (
                            pr.promotion_type = 'general' OR
                            (pr.promotion_type = 'category' AND pr.target_id = :category_id) OR
                            (pr.promotion_type = 'product' AND pr.target_id = :product_id)
                        )
                        ORDER BY pr.discount_percent DESC
                        LIMIT 1
                    ) prom ON 1=1
                    WHERE p.product_id = :product_id";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':product_id', $product_id, PDO::PARAM_INT);
            $stmt->bindValue(':category_id', $product_id, PDO::PARAM_INT); // This will be updated with actual category_id
            
            $stmt->execute();
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($product) {
                // Get the actual category_id and update the query
                $category_id = $product['category_id'];
                $stmt = $this->conn->prepare("
                    SELECT p.*, c.category_name, 
                           COALESCE(prom.discount_percent, 0) as discount_percent,
                           prom.promotion_type,
                           prom.title as promotion_title,
                           prom.description as promotion_description
                    FROM products p
                    LEFT JOIN categories c ON p.category_id = c.category_id
                    LEFT JOIN (
                        SELECT pr.*
                        FROM promotions pr
                        WHERE pr.is_active = 1 
                        AND pr.start_date <= CURDATE() 
                        AND pr.end_date >= CURDATE()
                        AND (
                            pr.promotion_type = 'general' OR
                            (pr.promotion_type = 'category' AND pr.target_id = :category_id) OR
                            (pr.promotion_type = 'product' AND pr.target_id = :product_id)
                        )
                        ORDER BY pr.discount_percent DESC
                        LIMIT 1
                    ) prom ON 1=1
                    WHERE p.product_id = :product_id
                ");
                $stmt->bindValue(':product_id', $product_id, PDO::PARAM_INT);
                $stmt->bindValue(':category_id', $category_id, PDO::PARAM_INT);
                $stmt->execute();
                $product = $stmt->fetch(PDO::FETCH_ASSOC);
            }
            
            return $product;
        } catch (PDOException $e) {
            error_log("ProductModel getProductWithPromotionsById error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Calculate discounted price for a product
     */
    public function calculateDiscountedPrice($product_id) {
        try {
            $product = $this->getProductWithPromotionsById($product_id);
            if (!$product) {
                return null;
            }
            
            // Get the base price from variants
            $stmt = $this->conn->prepare("
                SELECT MIN(price) as min_price, MAX(price) as max_price 
                FROM product_variants 
                WHERE product_id = :product_id
            ");
            $stmt->bindValue(':product_id', $product_id, PDO::PARAM_INT);
            $stmt->execute();
            $priceInfo = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$priceInfo) {
                return null;
            }
            
            $discount_percent = $product['discount_percent'] ?? 0;
            
            $result = [
                'original_min_price' => $priceInfo['min_price'],
                'original_max_price' => $priceInfo['max_price'],
                'discount_percent' => $discount_percent,
                'discounted_min_price' => $priceInfo['min_price'] * (1 - $discount_percent / 100),
                'discounted_max_price' => $priceInfo['max_price'] * (1 - $discount_percent / 100),
                'promotion_title' => $product['promotion_title'] ?? null,
                'promotion_description' => $product['promotion_description'] ?? null
            ];
            
            return $result;
        } catch (PDOException $e) {
            error_log("ProductModel calculateDiscountedPrice error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Search products with promotions included
     */
    public function searchProductsWithPromotions($search_query = '', $category_id = '', $limit = null, $offset = null) {
        $query = "SELECT 
                        p.*, 
                        c.category_name,
                        (SELECT MIN(v.price) FROM product_variants v WHERE v.product_id = p.product_id) AS price,
                        (SELECT v1.variant_id FROM product_variants v1 WHERE v1.product_id = p.product_id ORDER BY v1.price ASC LIMIT 1) AS min_variant_id,
                        (SELECT COALESCE(SUM(v2.stock), 0) FROM product_variants v2 WHERE v2.product_id = p.product_id) AS stock,
                        (SELECT GROUP_CONCAT(DISTINCT CONCAT(v3.variant_id, ':', v3.size, ':', v3.price) ORDER BY v3.price ASC SEPARATOR '|') FROM product_variants v3 WHERE v3.product_id = p.product_id) AS variants_summary,
                        (SELECT pi.image_url FROM product_images pi WHERE pi.product_id = p.product_id ORDER BY pi.image_id ASC LIMIT 1) AS image_url,
                        COALESCE(prom.discount_percent, 0) as discount_percent,
                        prom.promotion_type,
                        prom.title as promotion_title
                  FROM " . $this->table_name . " p 
                  LEFT JOIN categories c ON p.category_id = c.category_id
                  LEFT JOIN (
                      SELECT pr.*
                      FROM promotions pr
                      WHERE pr.is_active = 1 
                      AND pr.start_date <= CURDATE() 
                      AND pr.end_date >= CURDATE()
                  ) prom ON (
                      prom.promotion_type = 'general' OR
                      (prom.promotion_type = 'category' AND prom.target_id = p.category_id) OR
                      (prom.promotion_type = 'product' AND prom.target_id = p.product_id)
                  )
                  WHERE 1=1";
        
        $params = [];
        
        // Add search query condition with improved fuzzy search
        if (!empty($search_query)) {
            $query .= " AND (
                p.product_name LIKE :search_query 
                OR p.product_name LIKE :search_start 
                OR p.product_name LIKE :search_end 
                OR p.product_name LIKE :search_words
                OR p.description LIKE :search_query 
                OR p.description LIKE :search_start 
                OR p.description LIKE :search_end
            )";
            $params[':search_query'] = '%' . $search_query . '%';
            $params[':search_start'] = $search_query . '%';
            $params[':search_end'] = '%' . $search_query;
            $params[':search_words'] = '%' . str_replace(' ', '%', $search_query) . '%';
        }
        
        // Add category filter
        if (!empty($category_id)) {
            $query .= " AND p.category_id = :category_id";
            $params[':category_id'] = $category_id;
        }
        
        $query .= " ORDER BY p.created_at DESC";
        
        // Add pagination
        if ($limit) {
            $query .= " LIMIT :limit";
            if ($offset) {
                $query .= " OFFSET :offset";
            }
        }
        
        $stmt = $this->conn->prepare($query);
        
        // Bind search and filter parameters first
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        // Bind LIMIT and OFFSET parameters separately
        if ($limit) {
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            if ($offset) {
                $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            }
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get total count of all products with filters
     */
    public function getAllProductsCount($min = null, $max = null, $sizes = [], $colors = []) {
        $query = "SELECT COUNT(DISTINCT p.product_id) as total
                  FROM " . $this->table_name . " p 
                  WHERE 1=1";
        
        $params = [];
        
        // Add price filters
        if ($min !== null) {
            $query .= " AND (SELECT MIN(v.price) FROM product_variants v WHERE v.product_id = p.product_id) >= :min";
            $params[':min'] = $min;
        }
        
        if ($max !== null) {
            $query .= " AND (SELECT MIN(v.price) FROM product_variants v WHERE v.product_id = p.product_id) <= :max";
            $params[':max'] = $max;
        }
        
        // Add size filters
        if (!empty($sizes)) {
            $placeholders = [];
            foreach ($sizes as $i => $size) {
                $placeholders[] = ":size_" . $i;
                $params[':size_' . $i] = $size;
            }
            $query .= " AND EXISTS (
                SELECT 1 FROM product_variants v 
                WHERE v.product_id = p.product_id 
                AND v.size IN (" . implode(',', $placeholders) . ")
            )";
        }
        
        // Add color filters
        if (!empty($colors)) {
            $placeholders = [];
            foreach ($colors as $i => $color) {
                $placeholders[] = ":color_" . $i;
                $params[':color_' . $i] = $color;
            }
            $query .= " AND EXISTS (
                SELECT 1 FROM product_colors pc 
                WHERE pc.product_id = p.product_id 
                AND pc.color_name IN (" . implode(',', $placeholders) . ")
            )";
        }
        
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }

    /**
     * Get total count of products in a category with filters
     */
    public function getCategoryProductsCount($category_id, $min = null, $max = null, $sizes = [], $colors = []) {
        $query = "SELECT COUNT(DISTINCT p.product_id) as total
                  FROM " . $this->table_name . " p 
                  WHERE p.category_id = :category_id";
        
        $params = [':category_id' => $category_id];
        
        // Add price filters
        if ($min !== null) {
            $query .= " AND (SELECT MIN(v.price) FROM product_variants v WHERE v.product_id = p.product_id) >= :min";
            $params[':min'] = $min;
        }
        
        if ($max !== null) {
            $query .= " AND (SELECT MIN(v.price) FROM product_variants v WHERE v.product_id = p.product_id) <= :max";
            $params[':max'] = $max;
        }
        
        // Add size filters
        if (!empty($sizes)) {
            $placeholders = [];
            foreach ($sizes as $i => $size) {
                $placeholders[] = ":size_" . $i;
                $params[':size_' . $i] = $size;
            }
            $query .= " AND EXISTS (
                SELECT 1 FROM product_variants v 
                WHERE v.product_id = p.product_id 
                AND v.size IN (" . implode(',', $placeholders) . ")
            )";
        }
        
        // Add color filters
        if (!empty($colors)) {
            $placeholders = [];
            foreach ($colors as $i => $color) {
                $placeholders[] = ":color_" . $i;
                $params[':color_' . $i] = $color;
            }
            $query .= " AND EXISTS (
                SELECT 1 FROM product_colors pc 
                WHERE pc.product_id = p.product_id 
                AND pc.color_name IN (" . implode(',', $placeholders) . ")
            )";
        }
        
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }

    /**
     * Get all products with promotions included
     */
    public function getAllProductsWithPromotions($min = null, $max = null, $sizes = [], $colors = [], $limit = null, $offset = null) {
        $query = "SELECT 
                        p.*, 
                        c.category_name,
                        (SELECT MIN(v.price) FROM product_variants v WHERE v.product_id = p.product_id) AS price,
                        (SELECT v1.variant_id FROM product_variants v1 WHERE v1.product_id = p.product_id ORDER BY v1.price ASC LIMIT 1) AS min_variant_id,
                        (SELECT COALESCE(SUM(v2.stock), 0) FROM product_variants v2 WHERE v2.product_id = p.product_id) AS stock,
                        (SELECT GROUP_CONCAT(DISTINCT CONCAT(v3.variant_id, ':', v3.size, ':', v3.price) ORDER BY v3.price ASC SEPARATOR '|') FROM product_variants v3 WHERE v3.product_id = p.product_id) AS variants_summary,
                        (SELECT pi.image_url FROM product_images pi WHERE pi.product_id = p.product_id ORDER BY pi.image_id ASC LIMIT 1) AS image_url,
                        COALESCE(prom.discount_percent, 0) as discount_percent,
                        prom.promotion_type,
                        prom.title as promotion_title
                  FROM " . $this->table_name . " p 
                  LEFT JOIN categories c ON p.category_id = c.category_id
                  LEFT JOIN (
                      SELECT pr.*
                      FROM promotions pr
                      WHERE pr.is_active = 1 
                      AND pr.start_date <= CURDATE() 
                      AND pr.end_date >= CURDATE()
                  ) prom ON (
                      prom.promotion_type = 'general' OR
                      (prom.promotion_type = 'category' AND prom.target_id = p.category_id) OR
                      (prom.promotion_type = 'product' AND prom.target_id = p.product_id)
                  )
                  WHERE 1=1";
        
        $params = [];
        
        // Add price filters
        if ($min !== null) {
            $query .= " AND (SELECT MIN(v.price) FROM product_variants v WHERE v.product_id = p.product_id) >= :min";
            $params[':min'] = $min;
        }
        
        if ($max !== null) {
            $query .= " AND (SELECT MIN(v.price) FROM product_variants v WHERE v.product_id = p.product_id) <= :max";
            $params[':max'] = $max;
        }
        
        // Add size filters
        if (!empty($sizes)) {
            $placeholders = [];
            foreach ($sizes as $i => $size) {
                $placeholders[] = ":size_" . $i;
                $params[':size_' . $i] = $size;
            }
            $query .= " AND EXISTS (
                SELECT 1 FROM product_variants v 
                WHERE v.product_id = p.product_id 
                AND v.size IN (" . implode(',', $placeholders) . ")
            )";
        }
        
        // Add color filters
        if (!empty($colors)) {
            $placeholders = [];
            foreach ($colors as $i => $color) {
                $placeholders[] = ":color_" . $i;
                $params[':color_' . $i] = $color;
            }
            $query .= " AND EXISTS (
                SELECT 1 FROM product_colors pc 
                WHERE pc.product_id = p.product_id 
                AND pc.color_name IN (" . implode(',', $placeholders) . ")
            )";
        }
        
        $query .= " ORDER BY p.created_at DESC";
        
        // Add pagination
        if ($limit) {
            $query .= " LIMIT :limit";
            if ($offset) {
                $query .= " OFFSET :offset";
            }
        }
        
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        // Bind LIMIT and OFFSET parameters separately
        if ($limit) {
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            if ($offset) {
                $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            }
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get products by category with promotions included
     */
    public function getByCategoryWithPromotions($category_id, $min = null, $max = null, $sizes = [], $colors = [], $limit = null, $offset = null) {
        $query = "SELECT 
                        p.*, 
                        c.category_name,
                        (SELECT MIN(v.price) FROM product_variants v WHERE v.product_id = p.product_id) AS price,
                        (SELECT v1.variant_id FROM product_variants v1 WHERE v1.product_id = p.product_id ORDER BY v1.price ASC LIMIT 1) AS min_variant_id,
                        (SELECT COALESCE(SUM(v2.stock), 0) FROM product_variants v2 WHERE v2.product_id = p.product_id) AS stock,
                        (SELECT GROUP_CONCAT(DISTINCT CONCAT(v3.variant_id, ':', v3.size, ':', v3.price) ORDER BY v3.price ASC SEPARATOR '|') FROM product_variants v3 WHERE v3.product_id = p.product_id) AS variants_summary,
                        (SELECT pi.image_url FROM product_images pi WHERE pi.product_id = p.product_id ORDER BY pi.image_id ASC LIMIT 1) AS image_url,
                        COALESCE(prom.discount_percent, 0) as discount_percent,
                        prom.promotion_type,
                        prom.title as promotion_title
                  FROM " . $this->table_name . " p 
                  LEFT JOIN categories c ON p.category_id = c.category_id
                  LEFT JOIN (
                      SELECT pr.*
                      FROM promotions pr
                      WHERE pr.is_active = 1 
                      AND pr.start_date <= CURDATE() 
                      AND pr.end_date >= CURDATE()
                  ) prom ON (
                      prom.promotion_type = 'general' OR
                      (prom.promotion_type = 'category' AND prom.target_id = p.category_id) OR
                      (prom.promotion_type = 'product' AND prom.target_id = p.product_id)
                  )
                  WHERE p.category_id = :category_id";
        
        $params = [':category_id' => $category_id];
        
        // Add price filters
        if ($min !== null) {
            $query .= " AND (SELECT MIN(v.price) FROM product_variants v WHERE v.product_id = p.product_id) >= :min";
            $params[':min'] = $min;
        }
        
        if ($max !== null) {
            $query .= " AND (SELECT MIN(v.price) FROM product_variants v WHERE v.product_id = p.product_id) <= :max";
            $params[':max'] = $max;
        }
        
        // Add size filters
        if (!empty($sizes)) {
            $placeholders = [];
            foreach ($sizes as $i => $size) {
                $placeholders[] = ":size_" . $i;
                $params[':size_' . $i] = $size;
            }
            $query .= " AND EXISTS (
                SELECT 1 FROM product_variants v 
                WHERE v.product_id = p.product_id 
                AND v.size IN (" . implode(',', $placeholders) . ")
            )";
        }
        
        // Add color filters
        if (!empty($colors)) {
            $placeholders = [];
            foreach ($colors as $i => $color) {
                $placeholders[] = ":color_" . $i;
                $params[':color_' . $i] = $color;
            }
            $query .= " AND EXISTS (
                SELECT 1 FROM product_colors pc 
                WHERE pc.product_id = p.product_id 
                AND pc.color_name IN (" . implode(',', $placeholders) . ")
            )";
        }
        
        $query .= " ORDER BY p.created_at DESC";
        
        // Add pagination
        if ($limit) {
            $query .= " LIMIT :limit";
            if ($offset) {
                $query .= " OFFSET :offset";
            }
        }
        
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        // Bind LIMIT and OFFSET parameters separately
        if ($limit) {
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            if ($offset) {
                $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            }
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Calculate discounted price for a product with variants
     */
    public function calculateDiscountedPriceForProduct($product) {
        if (!$product || !isset($product['discount_percent']) || $product['discount_percent'] <= 0) {
            return null;
        }
        
        $discount_percent = $product['discount_percent'];
        $original_price = $product['price'] ?? 0;
        
        if ($original_price <= 0) {
            return null;
        }
        
        $discounted_price = $original_price * (1 - $discount_percent / 100);
        
        return [
            'original_price' => $original_price,
            'discount_percent' => $discount_percent,
            'discounted_price' => $discounted_price,
            'promotion_title' => $product['promotion_title'] ?? null
        ];
    }

    /**
     * Get newest products with promotions included
     */
    public function getNewestProductsWithPromotions($limit = 10, $offset = 0, $category_id = null) {
        try {
            $query = "SELECT 
                            p.*, 
                            c.category_name,
                            (SELECT MIN(v.price) FROM product_variants v WHERE v.product_id = p.product_id) AS price,
                            (SELECT v1.variant_id FROM product_variants v1 WHERE v1.product_id = p.product_id ORDER BY v1.price ASC LIMIT 1) AS min_variant_id,
                            (SELECT COALESCE(SUM(v2.stock), 0) FROM product_variants v2 WHERE v2.product_id = p.product_id) AS stock,
                            (SELECT GROUP_CONCAT(DISTINCT CONCAT(v3.variant_id, ':', v3.size, ':', v3.price) ORDER BY v3.price ASC SEPARATOR '|') FROM product_variants v3 WHERE v3.product_id = p.product_id) AS variants_summary,
                            (SELECT pi.image_url FROM product_images pi WHERE pi.product_id = p.product_id ORDER BY pi.image_id ASC LIMIT 1) AS image_url,
                            COALESCE(prom.discount_percent, 0) as discount_percent,
                            prom.promotion_type,
                            prom.title as promotion_title
                      FROM " . $this->table_name . " p 
                      LEFT JOIN categories c ON p.category_id = c.category_id
                      LEFT JOIN (
                          SELECT pr.*
                          FROM promotions pr
                          WHERE pr.is_active = 1 
                          AND pr.start_date <= CURDATE() 
                          AND pr.end_date >= CURDATE()
                      ) prom ON (
                          prom.promotion_type = 'general' OR
                          (prom.promotion_type = 'category' AND prom.target_id = p.category_id) OR
                          (prom.promotion_type = 'product' AND prom.target_id = p.product_id)
                      )
                      WHERE 1=1";
            
            $params = [];
            
            // Add category filter
            if ($category_id) {
                $query .= " AND p.category_id = :category_id";
                $params[':category_id'] = $category_id;
            }
            
            $query .= " ORDER BY p.created_at DESC";
            
            // Add pagination
            if ($limit) {
                $query .= " LIMIT :limit";
                if ($offset) {
                    $query .= " OFFSET :offset";
                }
            }
            
            $stmt = $this->conn->prepare($query);
            
            // Bind parameters
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            // Bind LIMIT and OFFSET parameters separately
            if ($limit) {
                $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
                if ($offset) {
                    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
                }
            }
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("ProductModel getNewestProductsWithPromotions error: " . $e->getMessage());
            return [];
        }
    }


}
?> 