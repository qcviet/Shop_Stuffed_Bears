<?php
/**
 * Order Model
 * Handles all order-related database operations
 */

class OrderModel {
    private $conn;
    private $table_name = "orders";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Monthly revenue and order count for last N months
    public function getMonthlyRevenueAndCount($months = 12) {
        $query = "SELECT DATE_FORMAT(order_date, '%Y-%m') AS ym,
                         SUM(CASE WHEN payment_status = 'Đã thanh toán' THEN total_amount ELSE 0 END) AS revenue,
                         COUNT(*) AS orders
                  FROM " . $this->table_name . "
                  GROUP BY ym
                  ORDER BY ym DESC
                  LIMIT :months";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':months', (int)$months, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        // Reverse to chronological order
        return array_reverse($rows);
    }
    // Create new order
    public function create($user_id, $total_amount, $status = 'Chờ xác nhận', $payment_status = 'Chưa thanh toán') {
        $query = "INSERT INTO " . $this->table_name . " 
                  (user_id, total_amount, status, payment_status) 
                  VALUES (:user_id, :total_amount, :status, :payment_status)";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(":user_id", $user_id);
        $stmt->bindParam(":total_amount", $total_amount);
        $stmt->bindParam(":status", $status);
        $stmt->bindParam(":payment_status", $payment_status);
        
        return $stmt->execute();
    }

    // Get order by ID
    public function getById($order_id) {
        $query = "SELECT o.*, u.username, u.full_name, u.email 
                  FROM " . $this->table_name . " o 
                  LEFT JOIN users u ON o.user_id = u.user_id 
                  WHERE o.order_id = :order_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":order_id", $order_id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Get orders by user
    public function getByUser($user_id, $limit = null, $offset = null) {
        $query = "SELECT o.*, u.username, u.full_name 
                  FROM " . $this->table_name . " o 
                  LEFT JOIN users u ON o.user_id = u.user_id 
                  WHERE o.user_id = :user_id 
                  ORDER BY o.order_date DESC";
        
        if ($limit) {
            $query .= " LIMIT :limit";
            if ($offset) {
                $query .= " OFFSET :offset";
            }
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        
        if ($limit) {
            $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
            if ($offset) {
                $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
            }
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get all orders
    public function getAll($limit = null, $offset = null, $status = null) {
        $query = "SELECT o.*, u.username, u.full_name, u.email 
                  FROM " . $this->table_name . " o 
                  LEFT JOIN users u ON o.user_id = u.user_id";
        
        $params = [];
        
        if ($status) {
            $query .= " WHERE o.status = :status";
            $params[':status'] = $status;
        }
        
        $query .= " ORDER BY o.order_date DESC";
        
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

    // Update order status
    public function updateStatus($order_id, $status) {
        $query = "UPDATE " . $this->table_name . " SET status = :status WHERE order_id = :order_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":status", $status);
        $stmt->bindParam(":order_id", $order_id);
        return $stmt->execute();
    }

    // Update payment status
    public function updatePaymentStatus($order_id, $payment_status) {
        $query = "UPDATE " . $this->table_name . " SET payment_status = :payment_status WHERE order_id = :order_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":payment_status", $payment_status);
        $stmt->bindParam(":order_id", $order_id);
        return $stmt->execute();
    }

    // Delete order
    public function delete($order_id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE order_id = :order_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":order_id", $order_id);
        return $stmt->execute();
    }

    // Get order count
    public function getCount($user_id = null, $status = null) {
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name;
        $params = [];
        
        if ($user_id || $status) {
            $query .= " WHERE";
            $conditions = [];
            
            if ($user_id) {
                $conditions[] = "user_id = :user_id";
                $params[':user_id'] = $user_id;
            }
            
            if ($status) {
                $conditions[] = "status = :status";
                $params[':status'] = $status;
            }
            
            $query .= " " . implode(" AND ", $conditions);
        }
        
        $stmt = $this->conn->prepare($query);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'];
    }

    // Get order statistics
    public function getStatistics() {
        $query = "SELECT 
                    COUNT(*) as total_orders,
                    SUM(CASE WHEN status = 'Chờ xác nhận' THEN 1 ELSE 0 END) as pending_orders,
                    SUM(CASE WHEN status = 'Đang giao' THEN 1 ELSE 0 END) as shipping_orders,
                    SUM(CASE WHEN status = 'Đã giao' THEN 1 ELSE 0 END) as completed_orders,
                    SUM(CASE WHEN status = 'Đã hủy' THEN 1 ELSE 0 END) as cancelled_orders,
                    SUM(CASE WHEN payment_status = 'Đã thanh toán' THEN total_amount ELSE 0 END) as total_revenue
                  FROM " . $this->table_name;
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Get recent orders
    public function getRecentOrders($limit = 10) {
        $query = "SELECT o.*, u.username, u.full_name 
                  FROM " . $this->table_name . " o 
                  LEFT JOIN users u ON o.user_id = u.user_id 
                  ORDER BY o.order_date DESC 
                  LIMIT :limit";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?> 