<?php
require_once 'database.php'; // or your connection logic

$product_id = intval($_GET['product_id']);
$color = $_GET['color'];

$sql = "SELECT size, price, stock FROM product_variants WHERE product_id = ? AND color = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $product_id, $color);
$stmt->execute();
$result = $stmt->get_result();

$sizes = [];
while ($row = $result->fetch_assoc()) {
    $sizes[] = $row;
}
echo json_encode($sizes);
?>
