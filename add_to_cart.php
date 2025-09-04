<?php
session_start();
include 'db.php'; // or reuse your connection code

echo "<h2>Your Cart</h2>";

if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    echo "<p>Your cart is empty.</p>";
} else {
    foreach ($_SESSION['cart'] as $product_id => $details) {
        $sql = "SELECT name, image FROM products WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $product = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        echo "<div class='cart-item'>
                <img src='{$product['image']}' width='100'>
                <p>{$product['name']}</p>
                <p>Quantity: {$details['quantity']}</p>
              </div>";
    }
}
?>
