<?php
/**
 * Product Controller Class
 * Handles all product-related operations
 */

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/ProductModel.php';

class ProductController extends BaseController {
    private $productModel;

    public function __construct() {
        parent::__construct();
        if ($this->isConnected()) {
            $this->productModel = new ProductModel($this->db);
        }
    }

    public function createProduct($category_id, $product_name, $description) {
        if (!$this->isConnected()) return false;
        return $this->productModel->create($category_id, $product_name, $description);
    }

    public function getProductById($product_id) {
        if (!$this->isConnected()) return false;
        return $this->productModel->getById($product_id);
    }

    public function getAllProducts($limit = null, $offset = null, $category_id = null) {
        if (!$this->isConnected()) return false;
        return $this->productModel->getAll($limit, $offset, $category_id);
    }

    public function getProductsByCategory($category_id, $limit = null, $offset = null) {
        if (!$this->isConnected()) return false;
        return $this->productModel->getByCategory($category_id, $limit, $offset);
    }

    public function searchProducts($search_term, $limit = null, $offset = null) {
        if (!$this->isConnected()) return false;
        return $this->productModel->search($search_term, $limit, $offset);
    }

    public function updateProduct($product_id, $data) {
        if (!$this->isConnected()) return false;
        return $this->productModel->update($product_id, $data);
    }

    public function deleteProduct($product_id) {
        if (!$this->isConnected()) return false;
        return $this->productModel->delete($product_id);
    }

    public function getNewProducts($limit = 10) {
        if (!$this->isConnected()) return false;
        return $this->productModel->getNewProducts($limit);
    }

    public function getLowStockProducts($threshold = 10) {
        if (!$this->isConnected()) return false;
        return $this->productModel->getLowStock($threshold);
    }

    public function getProductsCount() {
        if (!$this->isConnected()) return false;
        return $this->productModel->getCount();
    }

    public function getSearchSuggestions($query) {
        if (!$this->isConnected()) return false;
        
        try {
            $suggestions = $this->productModel->searchProducts($query, '', 5, 0);
            
            // Format suggestions for autocomplete
            $formattedSuggestions = [];
            foreach ($suggestions as $product) {
                $formattedSuggestions[] = [
                    'product_id' => $product['product_id'],
                    'product_name' => $product['product_name'],
                    'category_name' => $product['category_name'],
                    'price' => $product['price']
                ];
            }
            
            return $formattedSuggestions;
        } catch (Exception $e) {
            return false;
        }
    }
}
?>
