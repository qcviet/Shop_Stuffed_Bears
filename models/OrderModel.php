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
        $query = "SELECT o.*, u.username, u.full_name, u.email, u.phone, u.address 
                  FROM " . $this->table_name . " o 
                  LEFT JOIN users u ON o.user_id = u.user_id 
                  WHERE o.order_id = :order_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":order_id", $order_id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Get order items with variant and product info
    public function getItemsByOrder($order_id) {
        // Detect if color_name column exists
        $hasColor = false;
        try {
            $chk = $this->conn->query("SHOW COLUMNS FROM order_items LIKE 'color_name'");
            $hasColor = $chk && $chk->rowCount() > 0;
        } catch (Exception $e) {
            $hasColor = false;
        }
        $colorSelect = $hasColor ? "oi.color_name" : "NULL AS color_name";
        $query = "SELECT oi.order_item_id, oi.order_id, oi.variant_id, oi.quantity, oi.price, $colorSelect,
                         v.size, p.product_id, p.product_name,
                         (SELECT pi.image_url FROM product_images pi WHERE pi.product_id = p.product_id ORDER BY pi.image_id ASC LIMIT 1) AS image_url
                  FROM order_items oi
                  JOIN product_variants v ON oi.variant_id = v.variant_id
                  JOIN products p ON v.product_id = p.product_id
                  WHERE oi.order_id = :order_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":order_id", $order_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
    public function getAll($limit = null, $offset = null, $status = null, $payment_status = null) {
        $query = "SELECT o.*, u.username, u.full_name, u.email 
                  FROM " . $this->table_name . " o 
                  LEFT JOIN users u ON o.user_id = u.user_id";

        $params = [];
        $conditions = [];

        if ($status) {
            $conditions[] = "o.status = :status";
            $params[':status'] = $status;
        }
        if ($payment_status) {
            $conditions[] = "o.payment_status = :payment_status";
            $params[':payment_status'] = $payment_status;
        }

        if (!empty($conditions)) {
            $query .= " WHERE " . implode(' AND ', $conditions);
        }

        $query .= " ORDER BY o.order_date DESC";

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
        try {
            $this->conn->beginTransaction();

            // Delete dependent order items first to satisfy FK constraints
            $stmtItems = $this->conn->prepare("DELETE FROM order_items WHERE order_id = :order_id");
            $stmtItems->bindParam(":order_id", $order_id);
            $stmtItems->execute();

            // Delete the order record
            $stmtOrder = $this->conn->prepare("DELETE FROM " . $this->table_name . " WHERE order_id = :order_id");
            $stmtOrder->bindParam(":order_id", $order_id);
            $ok = $stmtOrder->execute();

            $this->conn->commit();
            return $ok;
        } catch (Exception $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            return false;
        }
    }

    // Get order count
    public function getCount($user_id = null, $status = null, $payment_status = null) {
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name;
        $params = [];

        if ($user_id || $status || $payment_status) {
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

            if ($payment_status) {
                $conditions[] = "payment_status = :payment_status";
                $params[':payment_status'] = $payment_status;
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
                    SUM(CASE WHEN status = 'Đã xác nhận' THEN 1 ELSE 0 END) as confirmed_orders,
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

    // Search orders with enhanced functionality
    public function search($search_term, $limit = null, $offset = null) {
        return $this->searchOrders($search_term, '', '', $limit, $offset);
    }

    // Search orders with filters
    public function searchOrders($search_query = '', $status = '', $payment_status = '', $limit = null, $offset = null) {
        $query = "SELECT o.*, u.username, u.full_name, u.email, u.phone, u.address 
                  FROM " . $this->table_name . " o 
                  LEFT JOIN users u ON o.user_id = u.user_id 
                  WHERE 1=1";
        
        $params = [];
        
        // Add search query condition with improved fuzzy search
        if (!empty($search_query)) {
            $query .= " AND (
                o.order_id LIKE :search_query
                OR o.order_id LIKE :search_start
                OR u.username LIKE :search_query
                OR u.username LIKE :search_start
                OR u.username LIKE :search_end
                OR u.username LIKE :search_words
                OR u.full_name LIKE :search_query
                OR u.full_name LIKE :search_start
                OR u.full_name LIKE :search_end
                OR u.email LIKE :search_query
                OR u.email LIKE :search_start
                OR u.email LIKE :search_end
                OR u.phone LIKE :search_query
            )";
            $params[':search_query'] = '%' . $search_query . '%';
            $params[':search_start'] = $search_query . '%';
            $params[':search_end'] = '%' . $search_query;
            $params[':search_words'] = '%' . str_replace(' ', '%', $search_query) . '%';
        }
        
        // Add status filter
        if (!empty($status)) {
            $query .= " AND o.status = :status";
            $params[':status'] = $status;
        }
        
        // Add payment status filter
        if (!empty($payment_status)) {
            $query .= " AND o.payment_status = :payment_status";
            $params[':payment_status'] = $payment_status;
        }
        
        $query .= " ORDER BY o.order_date DESC";
        
        if ($limit) {
            $query .= " LIMIT :limit";
            if ($offset) {
                $query .= " OFFSET :offset";
            }
        }
        
        $stmt = $this->conn->prepare($query);
        
        // Bind search and filter parameters first
        foreach ($params as $key => $value) {
            $stmt->bindParam($key, $value);
        }
        
        // Bind LIMIT and OFFSET parameters separately
        if ($limit) {
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            if ($offset) {
                $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            }
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get search count for pagination
    public function getSearchCount($search_query = '', $status = '', $payment_status = '') {
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " o 
                  LEFT JOIN users u ON o.user_id = u.user_id 
                  WHERE 1=1";
        
        $params = [];
        
        // Add search query condition with improved fuzzy search
        if (!empty($search_query)) {
            $query .= " AND (
                o.order_id LIKE :search_query
                OR o.order_id LIKE :search_start
                OR u.username LIKE :search_query
                OR u.username LIKE :search_start
                OR u.username LIKE :search_end
                OR u.username LIKE :search_words
                OR u.full_name LIKE :search_query
                OR u.full_name LIKE :search_start
                OR u.full_name LIKE :search_end
                OR u.email LIKE :search_query
                OR u.email LIKE :search_start
                OR u.email LIKE :search_end
                OR u.phone LIKE :search_query
            )";
            $params[':search_query'] = '%' . $search_query . '%';
            $params[':search_start'] = $search_query . '%';
            $params[':search_end'] = '%' . $search_query;
            $params[':search_words'] = '%' . str_replace(' ', '%', $search_query) . '%';
        }
        
        // Add status filter
        if (!empty($status)) {
            $query .= " AND o.status = :status";
            $params[':status'] = $status;
        }
        
        // Add payment status filter
        if (!empty($payment_status)) {
            $query .= " AND o.payment_status = :payment_status";
            $params[':payment_status'] = $payment_status;
        }
        
        $stmt = $this->conn->prepare($query);
        
        // Bind all parameters
        foreach ($params as $key => $value) {
            $stmt->bindParam($key, $value);
        }
        
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'];
    }
}
?> 