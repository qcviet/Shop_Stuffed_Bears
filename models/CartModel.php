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

    // Get cart with items (joined to variants and products)
    public function getCartWithItems($user_id) {
        $query = "SELECT c.*, ci.cart_item_id, ci.variant_id, ci.quantity,
                         p.product_id, p.product_name, v.size, v.price, v.stock, cat.category_name,
                         (SELECT pi.image_url FROM product_images pi WHERE pi.product_id = p.product_id ORDER BY pi.image_id ASC LIMIT 1) AS image_url,
                         (SELECT GROUP_CONCAT(pc.color_name ORDER BY pc.color_id SEPARATOR ', ') FROM product_colors pc WHERE pc.product_id = p.product_id) AS color_names
                  FROM " . $this->table_name . " c
                  LEFT JOIN cart_items ci ON c.cart_id = ci.cart_id
                  LEFT JOIN product_variants v ON ci.variant_id = v.variant_id
                  LEFT JOIN products p ON v.product_id = p.product_id
                  LEFT JOIN categories cat ON p.category_id = cat.category_id
                  WHERE c.user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Add item to cart (by variant)
    public function addItem($cart_id, $variant_id, $quantity = 1) {
        // Check if item already exists in cart
        $existing_item = $this->getCartItem($cart_id, $variant_id);
        
        if ($existing_item) {
            // Update quantity
            $new_quantity = $existing_item['quantity'] + $quantity;
            return $this->updateItemQuantity($existing_item['cart_item_id'], $new_quantity);
        } else {
            // Add new item
            $query = "INSERT INTO cart_items (cart_id, variant_id, quantity) VALUES (:cart_id, :variant_id, :quantity)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":cart_id", $cart_id);
            $stmt->bindParam(":variant_id", $variant_id);
            $stmt->bindParam(":quantity", $quantity);
            return $stmt->execute();
        }
    }

    // Get cart item by variant
    public function getCartItem($cart_id, $variant_id) {
        $query = "SELECT * FROM cart_items WHERE cart_id = :cart_id AND variant_id = :variant_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":cart_id", $cart_id);
        $stmt->bindParam(":variant_id", $variant_id);
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

    // Get cart total (using variant price)
    public function getCartTotal($cart_id) {
        $query = "SELECT SUM(ci.quantity * v.price) as total
                  FROM cart_items ci
                  JOIN product_variants v ON ci.variant_id = v.variant_id
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

    // Check if variant is in cart
    public function isVariantInCart($cart_id, $variant_id) {
        $query = "SELECT COUNT(*) as count FROM cart_items WHERE cart_id = :cart_id AND variant_id = :variant_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":cart_id", $cart_id);
        $stmt->bindParam(":variant_id", $variant_id);
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

    // Validate cart items (check stock at variant level)
    public function validateCart($cart_id) {
        $query = "SELECT ci.*, v.stock, v.size, p.product_name
                  FROM cart_items ci
                  JOIN product_variants v ON ci.variant_id = v.variant_id
                  JOIN products p ON v.product_id = p.product_id
                  WHERE ci.cart_id = :cart_id AND ci.quantity > v.stock";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":cart_id", $cart_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Update cart item quantities based on available stock (variant level)
    public function updateQuantitiesToStock($cart_id) {
        $query = "UPDATE cart_items ci
                  JOIN product_variants v ON ci.variant_id = v.variant_id
                  SET ci.quantity = LEAST(ci.quantity, v.stock)
                  WHERE ci.cart_id = :cart_id AND ci.quantity > v.stock";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":cart_id", $cart_id);
        return $stmt->execute();
    }
}
?> 