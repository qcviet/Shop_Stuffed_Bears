<?php

class PostModel {
    private $conn;
    private $table = 'posts';

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Get all posts with pagination
     */
    public function getAll($limit = 10, $offset = 0, $search = '') {
        try {
            $sql = "SELECT * FROM {$this->table}";
            $params = [];
            
            if (!empty($search)) {
                $sql .= " WHERE title LIKE :search OR content LIKE :search";
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
            error_log("PostModel getAll error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get post by ID
     */
    public function getById($id) {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE post_id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("PostModel getById error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get recent posts
     */
    public function getRecent($limit = 5) {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM {$this->table} ORDER BY created_at DESC LIMIT :limit");
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("PostModel getRecent error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Create new post
     */
    public function create($title, $content, $thumbnail = null) {
        try {
            $stmt = $this->conn->prepare("
                INSERT INTO {$this->table} (title, content, thumbnail) 
                VALUES (:title, :content, :thumbnail)
            ");
            
            $stmt->bindParam(':title', $title, PDO::PARAM_STR);
            $stmt->bindParam(':content', $content, PDO::PARAM_STR);
            $stmt->bindParam(':thumbnail', $thumbnail, PDO::PARAM_STR);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("PostModel create error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update post
     */
    public function update($id, $title, $content, $thumbnail = null) {
        try {
            $sql = "UPDATE {$this->table} SET title = :title, content = :content";
            $params = [':id' => $id, ':title' => $title, ':content' => $content];
            
            if ($thumbnail !== null) {
                $sql .= ", thumbnail = :thumbnail";
                $params[':thumbnail'] = $thumbnail;
            }
            
            $sql .= " WHERE post_id = :id";
            
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("PostModel update error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete post
     */
    public function delete($id) {
        try {
            $stmt = $this->conn->prepare("DELETE FROM {$this->table} WHERE post_id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("PostModel delete error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get total count of posts
     */
    public function getTotalCount($search = '') {
        try {
            $sql = "SELECT COUNT(*) as total FROM {$this->table}";
            $params = [];
            
            if (!empty($search)) {
                $sql .= " WHERE title LIKE :search OR content LIKE :search";
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
            error_log("PostModel getTotalCount error: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Search posts
     */
    public function search($query, $limit = 10) {
        try {
            $stmt = $this->conn->prepare("
                SELECT * FROM {$this->table} 
                WHERE title LIKE :query OR content LIKE :query 
                ORDER BY created_at DESC 
                LIMIT :limit
            ");
            
            $searchTerm = "%{$query}%";
            $stmt->bindParam(':query', $searchTerm, PDO::PARAM_STR);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("PostModel search error: " . $e->getMessage());
            return [];
        }
    }
}
