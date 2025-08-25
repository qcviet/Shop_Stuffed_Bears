<?php
/**
 * Category Controller Class
 * Handles all category-related operations
 */

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/CategoryModel.php';

class CategoryController extends BaseController {
    private $categoryModel;

    public function __construct() {
        parent::__construct();
        if ($this->isConnected()) {
            $this->categoryModel = new CategoryModel($this->db);
        }
    }

    public function createCategory($category_name, $description = null) {
        if (!$this->isConnected()) return false;
        
        if ($this->categoryModel->nameExists($category_name)) {
            throw new Exception("Category name already exists");
        }
        
        return $this->categoryModel->create($category_name, $description);
    }

    public function getCategoryById($category_id) {
        if (!$this->isConnected()) return false;
        return $this->categoryModel->getById($category_id);
    }

    public function getAllCategories() {
        if (!$this->isConnected()) return false;
        return $this->categoryModel->getAll();
    }

    public function getCategoriesWithProductCount() {
        if (!$this->isConnected()) return false;
        return $this->categoryModel->getWithProductCount();
    }

    public function updateCategory($category_id, $category_name, $description = null) {
        if (!$this->isConnected()) return false;
        return $this->categoryModel->update($category_id, $category_name, $description);
    }

    public function deleteCategory($category_id) {
        if (!$this->isConnected()) return false;
        return $this->categoryModel->delete($category_id);
    }

    public function getCategoriesCount() {
        if (!$this->isConnected()) return false;
        return $this->categoryModel->getCount();
    }
}
?>
