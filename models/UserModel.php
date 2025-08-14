<?php
/**
 * User Model
 * Handles all user-related database operations
 */

class UserModel {
    private $conn;
    private $table_name = "users";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create new user
    public function create($username, $password, $email, $full_name = null, $phone = null, $address = null, $role = 'user') {
        $query = "INSERT INTO " . $this->table_name . " 
                  (username, password, email, full_name, phone, address, role) 
                  VALUES (:username, :password, :email, :full_name, :phone, :address, :role)";
        
        $stmt = $this->conn->prepare($query);
        
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Bind parameters
        $stmt->bindParam(":username", $username);
        $stmt->bindParam(":password", $hashed_password);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":full_name", $full_name);
        $stmt->bindParam(":phone", $phone);
        $stmt->bindParam(":address", $address);
        $stmt->bindParam(":role", $role);
        
        return $stmt->execute();
    }

    // Get user by ID
    public function getById($user_id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Get user by username
    public function getByUsername($username) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE username = :username";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":username", $username);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Get user by email
    public function getByEmail($email) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE email = :email";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $email);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Get all users
    public function getAll($limit = null, $offset = null) {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY created_at DESC";
        
        if ($limit) {
            $query .= " LIMIT :limit";
            if ($offset) {
                $query .= " OFFSET :offset";
            }
        }
        
        $stmt = $this->conn->prepare($query);
        
        if ($limit) {
            $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
            if ($offset) {
                $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
            }
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Update user
    public function update($user_id, $data) {
        $allowed_fields = ['username', 'email', 'full_name', 'phone', 'address', 'role'];
        $set_clause = [];
        $params = [':user_id' => $user_id];
        
        foreach ($data as $field => $value) {
            if (in_array($field, $allowed_fields)) {
                $set_clause[] = "$field = :$field";
                $params[":$field"] = $value;
            }
        }
        
        if (empty($set_clause)) {
            return false;
        }
        
        $query = "UPDATE " . $this->table_name . " SET " . implode(', ', $set_clause) . " WHERE user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute($params);
    }

    // Update password
    public function updatePassword($user_id, $new_password) {
        $query = "UPDATE " . $this->table_name . " SET password = :password WHERE user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt->bindParam(":password", $hashed_password);
        $stmt->bindParam(":user_id", $user_id);
        
        return $stmt->execute();
    }

    // Delete user
    public function delete($user_id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        return $stmt->execute();
    }

    // Verify login
    public function verifyLogin($username, $password) {
        $user = $this->getByUsername($username);
        if ($user && password_verify($password, $user['password'])) {
            // Check if user is active (only active users can login)
            $status = $user['status'] ?? 'active'; // Default to active if status column doesn't exist
            if ($status === 'inactive') {
                return false; // Return false for inactive users
            }
            return $user;
        }
        return false;
    }

    // Get user count
    public function getCount() {
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'];
    }

    // Check if username exists
    public function usernameExists($username, $exclude_id = null) {
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " WHERE username = :username";
        if ($exclude_id) {
            $query .= " AND user_id != :exclude_id";
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":username", $username);
        if ($exclude_id) {
            $stmt->bindParam(":exclude_id", $exclude_id);
        }
        
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] > 0;
    }

    // Check if email exists
    public function emailExists($email, $exclude_id = null) {
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " WHERE email = :email";
        if ($exclude_id) {
            $query .= " AND user_id != :exclude_id";
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $email);
        if ($exclude_id) {
            $stmt->bindParam(":exclude_id", $exclude_id);
        }
        
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] > 0;
    }

    // Check if user is active
    public function isActive($user_id) {
        $user = $this->getById($user_id);
        if ($user) {
            $status = $user['status'] ?? 'active';
            return $status !== 'inactive';
        }
        return false;
    }

    // Get users by status
    public function getByStatus($status, $limit = null, $offset = null) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE COALESCE(status, 'active') = :status ORDER BY created_at DESC";
        
        if ($limit) {
            $query .= " LIMIT :limit";
            if ($offset) {
                $query .= " OFFSET :offset";
            }
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":status", $status);
        
        if ($limit) {
            $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
            if ($offset) {
                $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
            }
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?> 