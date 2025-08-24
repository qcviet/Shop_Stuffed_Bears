<?php

class PromotionModel {
    private $conn;
    private $table = 'promotions';

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Get all promotions with pagination
     */
    public function getAll($limit = 10, $offset = 0, $search = '') {
        try {
            $sql = "SELECT * FROM {$this->table}";
            $params = [];
            
            if (!empty($search)) {
                $sql .= " WHERE title LIKE :search OR description LIKE :search";
                $params[':search'] = "%{$search}%";
            }
            
            $sql .= " ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            
            if (!empty($search)) {
                $stmt->bindValue(':search', "%{$search}%", PDO::PARAM_STR);
            }
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("PromotionModel getAll error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get promotion by ID
     */
    public function getById($id) {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE promotion_id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("PromotionModel getById error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get active promotions
     */
    public function getActive() {
        try {
            $currentDate = date('Y-m-d');
            $stmt = $this->conn->prepare("
                SELECT * FROM {$this->table} 
                WHERE is_active = 1 
                AND start_date <= :current_date 
                AND end_date >= :current_date 
                ORDER BY created_at DESC
            ");
            $stmt->bindParam(':current_date', $currentDate, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("PromotionModel getActive error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get active promotions by category
     */
    public function getActiveByCategory($categoryId) {
        try {
            $currentDate = date('Y-m-d');
            $stmt = $this->conn->prepare("
                SELECT * FROM {$this->table} 
                WHERE is_active = 1 
                AND start_date <= :current_date 
                AND end_date >= :current_date 
                AND (promotion_type = 'category' AND target_id = :category_id)
                ORDER BY created_at DESC
            ");
            $stmt->bindParam(':current_date', $currentDate, PDO::PARAM_STR);
            $stmt->bindParam(':category_id', $categoryId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("PromotionModel getActiveByCategory error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get active promotions by product
     */
    public function getActiveByProduct($productId) {
        try {
            $currentDate = date('Y-m-d');
            $stmt = $this->conn->prepare("
                SELECT * FROM {$this->table} 
                WHERE is_active = 1 
                AND start_date <= :current_date 
                AND end_date >= :current_date 
                AND (promotion_type = 'product' AND target_id = :product_id)
                ORDER BY created_at DESC
            ");
            $stmt->bindParam(':current_date', $currentDate, PDO::PARAM_STR);
            $stmt->bindParam(':product_id', $productId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("PromotionModel getActiveByProduct error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get active general promotions
     */
    public function getActiveGeneral() {
        try {
            $currentDate = date('Y-m-d');
            $stmt = $this->conn->prepare("
                SELECT * FROM {$this->table} 
                WHERE is_active = 1 
                AND start_date <= :current_date 
                AND end_date >= :current_date 
                AND promotion_type = 'general'
                ORDER BY created_at DESC
            ");
            $stmt->bindParam(':current_date', $currentDate, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("PromotionModel getActiveGeneral error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get recent promotions
     */
    public function getRecent($limit = 5) {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM {$this->table} ORDER BY created_at DESC LIMIT :limit");
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("PromotionModel getRecent error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Create new promotion
     */
    public function create($data) {
        try {
            $stmt = $this->conn->prepare("
                INSERT INTO {$this->table} (
                    title, description, 
                    discount_percent, promotion_type, target_id,
                    start_date, end_date, is_active
                ) VALUES (
                    :title, :description,
                    :discount_percent, :promotion_type, :target_id,
                    :start_date, :end_date, :is_active
                )
            ");
            
            $stmt->bindParam(':title', $data['title'], PDO::PARAM_STR);
            $stmt->bindParam(':description', $data['description'], PDO::PARAM_STR);
            $stmt->bindParam(':discount_percent', $data['discount_percent'], PDO::PARAM_STR);
            $stmt->bindParam(':promotion_type', $data['promotion_type'], PDO::PARAM_STR);
            $stmt->bindParam(':target_id', $data['target_id'], PDO::PARAM_INT);
            $stmt->bindParam(':start_date', $data['start_date'], PDO::PARAM_STR);
            $stmt->bindParam(':end_date', $data['end_date'], PDO::PARAM_STR);
            $stmt->bindParam(':is_active', $data['is_active'], PDO::PARAM_BOOL);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("PromotionModel create error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update promotion
     */
    public function update($id, $data) {
        try {
            $stmt = $this->conn->prepare("
                UPDATE {$this->table} SET 
                    title = :title, 
                    description = :description,
                    discount_percent = :discount_percent, 
                    promotion_type = :promotion_type,
                    target_id = :target_id,
                    start_date = :start_date, 
                    end_date = :end_date, 
                    is_active = :is_active
                WHERE promotion_id = :id
            ");
            
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':title', $data['title'], PDO::PARAM_STR);
            $stmt->bindParam(':description', $data['description'], PDO::PARAM_STR);
            $stmt->bindParam(':discount_percent', $data['discount_percent'], PDO::PARAM_STR);
            $stmt->bindParam(':promotion_type', $data['promotion_type'], PDO::PARAM_STR);
            $stmt->bindParam(':target_id', $data['target_id'], PDO::PARAM_INT);
            $stmt->bindParam(':start_date', $data['start_date'], PDO::PARAM_STR);
            $stmt->bindParam(':end_date', $data['end_date'], PDO::PARAM_STR);
            $stmt->bindParam(':is_active', $data['is_active'], PDO::PARAM_BOOL);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("PromotionModel update error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete promotion
     */
    public function delete($id) {
        try {
            $stmt = $this->conn->prepare("DELETE FROM {$this->table} WHERE promotion_id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("PromotionModel delete error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Toggle promotion status
     */
    public function toggleStatus($id) {
        try {
            $stmt = $this->conn->prepare("
                UPDATE {$this->table} 
                SET is_active = NOT is_active 
                WHERE promotion_id = :id
            ");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("PromotionModel toggleStatus error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get total count of promotions
     */
    public function getTotalCount($search = '') {
        try {
            $sql = "SELECT COUNT(*) as total FROM {$this->table}";
            $params = [];
            
            if (!empty($search)) {
                $sql .= " WHERE title LIKE :search OR description LIKE :search";
                $params[':search'] = "%{$search}%";
            }
            
            $stmt = $this->conn->prepare($sql);
            
            if (!empty($search)) {
                $stmt->bindValue(':search', "%{$search}%", PDO::PARAM_STR);
            }
            
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'] ?? 0;
        } catch (PDOException $e) {
            error_log("PromotionModel getTotalCount error: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Search promotions
     */
    public function search($query, $limit = 10) {
        try {
            $stmt = $this->conn->prepare("
                SELECT * FROM {$this->table} 
                WHERE title LIKE :query OR description LIKE :query 
                ORDER BY created_at DESC 
                LIMIT :limit
            ");
            
            $searchTerm = "%{$query}%";
            $stmt->bindParam(':query', $searchTerm, PDO::PARAM_STR);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("PromotionModel search error: " . $e->getMessage());
            return [];
        }
    }
}
