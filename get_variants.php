<?php
require_once 'database.php';
$product_id = intval($_GET['product_id']);
$stmt = $conn->prepare("SELECT size, color, price, stock FROM product_variants WHERE product_id=? ORDER BY id ASC");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
$variants = [];
while($row = $result->fetch_assoc()) { $variants[] = $row; }
echo json_encode($variants);
?>
