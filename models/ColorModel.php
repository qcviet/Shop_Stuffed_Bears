<?php
/**
 * Color Model
 * Manages colors and variant-color relations
 */

class ColorModel {
	private $conn;

	public function __construct($db) {
		$this->conn = $db;
	}

	public function getAll() {
		$query = "SELECT color_id, color_name FROM colors ORDER BY color_name ASC";
		$stmt = $this->conn->prepare($query);
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	public function getById($color_id) {
		$query = "SELECT color_id, color_name FROM colors WHERE color_id = :id";
		$stmt = $this->conn->prepare($query);
		$stmt->bindParam(':id', $color_id, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetch(PDO::FETCH_ASSOC);
	}

	public function create($color_name) {
		$query = "INSERT INTO colors (color_name) VALUES (:name)";
		$stmt = $this->conn->prepare($query);
		$stmt->bindParam(':name', $color_name);
		return $stmt->execute();
	}

	public function update($color_id, $color_name) {
		$query = "UPDATE colors SET color_name = :name WHERE color_id = :id";
		$stmt = $this->conn->prepare($query);
		$stmt->bindParam(':id', $color_id, PDO::PARAM_INT);
		$stmt->bindParam(':name', $color_name);
		return $stmt->execute();
	}

	public function delete($color_id) {
		// Optional: also delete variant_colors references to satisfy FK
		$delLinks = $this->conn->prepare("DELETE FROM variant_colors WHERE color_id = :id");
		$delLinks->execute([':id' => $color_id]);
		$stmt = $this->conn->prepare("DELETE FROM colors WHERE color_id = :id");
		$stmt->bindParam(':id', $color_id, PDO::PARAM_INT);
		return $stmt->execute();
	}

	public function getByVariantIds($variantIds) {
		$result = [];
		$variantIds = array_values(array_filter(array_map('intval', (array)$variantIds)));
		if (count($variantIds) === 0) { return $result; }
		$placeholders = implode(',', array_fill(0, count($variantIds), '?'));
		$sql = "SELECT vc.variant_id, c.color_id, c.color_name
				FROM variant_colors vc
				JOIN colors c ON c.color_id = vc.color_id
				WHERE vc.variant_id IN ($placeholders)";
		$stmt = $this->conn->prepare($sql);
		$stmt->execute($variantIds);
		$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
		foreach ($rows as $row) {
			$vid = (int)$row['variant_id'];
			if (!isset($result[$vid])) { $result[$vid] = []; }
			$result[$vid][] = [
				'color_id' => (int)$row['color_id'],
				'color_name' => $row['color_name'],
			];
		}
		return $result;
	}

	public function setColorsForVariant($variantId, $colorIds) {
		$variantId = (int)$variantId;
		$colorIds = array_values(array_filter(array_map('intval', (array)$colorIds)));
		// Delete existing
		$del = $this->conn->prepare("DELETE FROM variant_colors WHERE variant_id = :vid");
		$del->execute([':vid' => $variantId]);
		// Insert new
		if (count($colorIds) === 0) { return true; }
		$ins = $this->conn->prepare("INSERT INTO variant_colors (variant_id, color_id) VALUES (:vid, :cid)");
		foreach ($colorIds as $cid) {
			$ins->execute([':vid' => $variantId, ':cid' => $cid]);
		}
		return true;
	}
}
?>


