<?php
/**
 * Cart Controller Class
 * Handles all cart-related operations
 */

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/CartModel.php';
require_once __DIR__ . '/../models/OrderModel.php';

class CartController extends BaseController {
    private $cartModel;
    private $orderModel;

    public function __construct() {
        parent::__construct();
        if ($this->isConnected()) {
            $this->cartModel = new CartModel($this->db);
            $this->orderModel = new OrderModel($this->db);
        }
    }

    public function getOrCreateCart($user_id) {
        if (!$this->isConnected()) return false;
        return $this->cartModel->getOrCreateCart($user_id);
    }

    public function getCartWithItems($user_id) {
        if (!$this->isConnected()) return false;
        return $this->cartModel->getCartWithItems($user_id);
    }

    public function addToCart($user_id, $variant_id, $quantity = 1, $color_name = null) {
        if (!$this->isConnected()) return false;
        
        $cart = $this->cartModel->getOrCreateCart($user_id);
        if (!$cart) return false;
        
        return $this->cartModel->addItem($cart['cart_id'], $variant_id, $quantity, $color_name);
    }

    public function updateCartItemQuantity($cart_item_id, $quantity) {
        if (!$this->isConnected()) return false;
        return $this->cartModel->updateItemQuantity($cart_item_id, $quantity);
    }

    public function removeFromCart($cart_item_id) {
        if (!$this->isConnected()) return false;
        return $this->cartModel->removeItem($cart_item_id);
    }

    public function clearCart($user_id) {
        if (!$this->isConnected()) return false;
        
        $cart = $this->cartModel->getByUser($user_id);
        if (!$cart) return false;
        
        return $this->cartModel->clearCart($cart['cart_id']);
    }

    public function getCartTotal($user_id) {
        if (!$this->isConnected()) return false;
        
        $cart = $this->cartModel->getByUser($user_id);
        if (!$cart) return 0;
        
        return $this->cartModel->getCartTotal($cart['cart_id']);
    }

    public function getCartItemCount($user_id) {
        if (!$this->isConnected()) return false;
        
        $cart = $this->cartModel->getByUser($user_id);
        if (!$cart) return 0;
        
        return $this->cartModel->getCartItemCount($cart['cart_id']);
    }

    /**
     * Checkout cart and create order
     * - Inserts into orders and order_items
     * - Decrements variant stock
     * - Clears cart
     * Returns created order_id on success, or false on failure
     */
    public function checkoutCart($user_id, $payment_method = 'COD', $markPaid = false, $discounted_total = null) {
        if (!$this->isConnected()) return false;

        // Get cart and items
        $cart = $this->cartModel->getByUser($user_id);
        if (!$cart) return false;
        $cartItems = $this->cartModel->getCartWithItems($user_id);
        if (empty($cartItems)) return false;

        try {
            $this->db->beginTransaction();

            // Validate stock and compute total using FOR UPDATE
            $total = 0;
            $preparedVariantStmt = $this->db->prepare("SELECT variant_id, product_id, price, stock FROM product_variants WHERE variant_id = :vid FOR UPDATE");
            foreach ($cartItems as $item) {
                $variantId = (int)$item['variant_id'];
                $quantity = (int)$item['quantity'];
                if ($quantity <= 0) { throw new Exception('Invalid cart quantity'); }

                $preparedVariantStmt->execute([':vid' => $variantId]);
                $variantRow = $preparedVariantStmt->fetch(PDO::FETCH_ASSOC);
                if (!$variantRow) { throw new Exception('Variant not found'); }
                if ((int)$variantRow['stock'] < $quantity) { throw new Exception('Insufficient stock for variant #' . $variantId); }

                $lineTotal = $quantity * (float)$variantRow['price'];
                $total += $lineTotal;
            }

            // Use discounted total if provided, otherwise use calculated total
            $finalTotal = $discounted_total !== null ? (float)$discounted_total : $total;

            // Create order
            $status = 'Chờ xác nhận';
            $payment_status = $markPaid ? 'Đã thanh toán' : 'Chưa thanh toán';
            if (!$this->orderModel->create($user_id, $finalTotal, $status, $payment_status)) {
                throw new Exception('Failed to create order');
            }
            $orderId = (int)$this->db->lastInsertId();

            // Insert order items and decrement stock
            $insertSql = $this->hasOrderColorColumn()
                ? "INSERT INTO order_items (order_id, variant_id, quantity, price, color_name) VALUES (:oid, :vid, :qty, :price, :color_name)"
                : "INSERT INTO order_items (order_id, variant_id, quantity, price) VALUES (:oid, :vid, :qty, :price)";
            $insertItemStmt = $this->db->prepare($insertSql);
            $decrementStockStmt = $this->db->prepare("UPDATE product_variants SET stock = stock - :qty WHERE variant_id = :vid");

            foreach ($cartItems as $item) {
                $variantId = (int)$item['variant_id'];
                $quantity = (int)$item['quantity'];
                $colorName = isset($item['color_name']) ? $item['color_name'] : null;

                // Fetch current price for accuracy
                $priceStmt = $this->db->prepare("SELECT price FROM product_variants WHERE variant_id = :vid");
                $priceStmt->execute([':vid' => $variantId]);
                $price = (float)($priceStmt->fetchColumn());

                $params = [
                    ':oid' => $orderId,
                    ':vid' => $variantId,
                    ':qty' => $quantity,
                    ':price' => $price,
                ];
                if ($this->hasOrderColorColumn()) { $params[':color_name'] = $colorName; }
                $insertItemStmt->execute($params);

                $decrementStockStmt->execute([
                    ':qty' => $quantity,
                    ':vid' => $variantId,
                ]);
            }

            // Clear cart
            $this->cartModel->clearCart($cart['cart_id']);

            $this->db->commit();
            return $orderId;
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return false;
        }
    }
}
?>
