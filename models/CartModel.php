<?php
/**
 * Cart Model
 * Handles all cart-related database operations
 */

class CartModel {
    private $conn;
    private $table_name = "cart";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create new cart for user
    public function create($user_id) {
        $query = "INSERT INTO " . $this->table_name . " (user_id) VALUES (:user_id)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        return $stmt->execute();
    }

    // Get cart by user ID
    public function getByUser($user_id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Get cart with items
    public function getCartWithItems($user_id) {
        $query = "SELECT c.*, ci.cart_item_id, ci.product_id, ci.quantity,
                         p.product_name, p.price, p.stock, cat.category_name
                  FROM " . $this->table_name . " c
                  LEFT JOIN cart_items ci ON c.cart_id = ci.cart_id
                  LEFT JOIN products p ON ci.product_id = p.product_id
                  LEFT JOIN categories cat ON p.category_id = cat.category_id
                  WHERE c.user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Add item to cart
    public function addItem($cart_id, $product_id, $quantity = 1) {
        // Check if item already exists in cart
        $existing_item = $this->getCartItem($cart_id, $product_id);
        
        if ($existing_item) {
            // Update quantity
            $new_quantity = $existing_item['quantity'] + $quantity;
            return $this->updateItemQuantity($existing_item['cart_item_id'], $new_quantity);
        } else {
            // Add new item
            $query = "INSERT INTO cart_items (cart_id, product_id, quantity) VALUES (:cart_id, :product_id, :quantity)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":cart_id", $cart_id);
            $stmt->bindParam(":product_id", $product_id);
            $stmt->bindParam(":quantity", $quantity);
            return $stmt->execute();
        }
    }

    // Get cart item
    public function getCartItem($cart_id, $product_id) {
        $query = "SELECT * FROM cart_items WHERE cart_id = :cart_id AND product_id = :product_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":cart_id", $cart_id);
        $stmt->bindParam(":product_id", $product_id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Update item quantity
    public function updateItemQuantity($cart_item_id, $quantity) {
        if ($quantity <= 0) {
            return $this->removeItem($cart_item_id);
        }
        
        $query = "UPDATE cart_items SET quantity = :quantity WHERE cart_item_id = :cart_item_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":quantity", $quantity);
        $stmt->bindParam(":cart_item_id", $cart_item_id);
        return $stmt->execute();
    }

    // Remove item from cart
    public function removeItem($cart_item_id) {
        $query = "DELETE FROM cart_items WHERE cart_item_id = :cart_item_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":cart_item_id", $cart_item_id);
        return $stmt->execute();
    }

    // Clear cart
    public function clearCart($cart_id) {
        $query = "DELETE FROM cart_items WHERE cart_id = :cart_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":cart_id", $cart_id);
        return $stmt->execute();
    }

    // Get cart total
    public function getCartTotal($cart_id) {
        $query = "SELECT SUM(ci.quantity * p.price) as total
                  FROM cart_items ci
                  JOIN products p ON ci.product_id = p.product_id
                  WHERE ci.cart_id = :cart_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":cart_id", $cart_id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }

    // Get cart item count
    public function getCartItemCount($cart_id) {
        $query = "SELECT SUM(quantity) as count FROM cart_items WHERE cart_id = :cart_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":cart_id", $cart_id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] ?? 0;
    }

    // Check if product is in cart
    public function isProductInCart($cart_id, $product_id) {
        $query = "SELECT COUNT(*) as count FROM cart_items WHERE cart_id = :cart_id AND product_id = :product_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":cart_id", $cart_id);
        $stmt->bindParam(":product_id", $product_id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] > 0;
    }

    // Get or create cart for user
    public function getOrCreateCart($user_id) {
        $cart = $this->getByUser($user_id);
        
        if (!$cart) {
            $this->create($user_id);
            $cart = $this->getByUser($user_id);
        }
        
        return $cart;
    }

    // Validate cart items (check stock)
    public function validateCart($cart_id) {
        $query = "SELECT ci.*, p.product_name, p.stock
                  FROM cart_items ci
                  JOIN products p ON ci.product_id = p.product_id
                  WHERE ci.cart_id = :cart_id AND ci.quantity > p.stock";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":cart_id", $cart_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Update cart item quantities based on available stock
    public function updateQuantitiesToStock($cart_id) {
        $query = "UPDATE cart_items ci
                  JOIN products p ON ci.product_id = p.product_id
                  SET ci.quantity = LEAST(ci.quantity, p.stock)
                  WHERE ci.cart_id = :cart_id AND ci.quantity > p.stock";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":cart_id", $cart_id);
        return $stmt->execute();
    }
}
?> 