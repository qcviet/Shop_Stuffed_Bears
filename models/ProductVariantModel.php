<?php
/**
 * Product Variant Model
 * Handles CRUD operations for product_variants (size, price, stock)
 */

class ProductVariantModel {
	private $conn;
	private $table_name = "product_variants";

	public function __construct($db) {
		$this->conn = $db;
	}

	public function getByProductId($product_id) {
		$query = "SELECT variant_id, product_id, size, price, stock FROM " . $this->table_name . " WHERE product_id = :product_id ORDER BY variant_id ASC";
		$stmt = $this->conn->prepare($query);
		$stmt->bindParam(":product_id", $product_id);
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	public function create($product_id, $size, $price, $stock) {
		$query = "INSERT INTO " . $this->table_name . " (product_id, size, price, stock) VALUES (:product_id, :size, :price, :stock)";
		$stmt = $this->conn->prepare($query);
		$stmt->bindParam(":product_id", $product_id);
		$stmt->bindParam(":size", $size);
		$stmt->bindParam(":price", $price);
		$stmt->bindParam(":stock", $stock);
		return $stmt->execute();
	}

	public function update($variant_id, $size, $price, $stock) {
		$query = "UPDATE " . $this->table_name . " SET size = :size, price = :price, stock = :stock WHERE variant_id = :variant_id";
		$stmt = $this->conn->prepare($query);
		$stmt->bindParam(":variant_id", $variant_id);
		$stmt->bindParam(":size", $size);
		$stmt->bindParam(":price", $price);
		$stmt->bindParam(":stock", $stock);
		return $stmt->execute();
	}

	public function delete($variant_id) {
		$query = "DELETE FROM " . $this->table_name . " WHERE variant_id = :variant_id";
		$stmt = $this->conn->prepare($query);
		$stmt->bindParam(":variant_id", $variant_id);
		return $stmt->execute();
	}

	public function deleteByProductId($product_id) {
		$query = "DELETE FROM " . $this->table_name . " WHERE product_id = :product_id";
		$stmt = $this->conn->prepare($query);
		$stmt->bindParam(":product_id", $product_id);
		return $stmt->execute();
	}
}
?>

