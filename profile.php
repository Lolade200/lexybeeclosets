<?php
session_start();
require 'database.php'; // MySQLi connection

// ✅ Check if user is logged in
if (!isset($_SESSION['full_name'])) {
  header("Location: login.php");
  exit();
}

$fullName = $_SESSION['full_name'];

// ✅ Fetch orders for the logged-in user
$stmt = $conn->prepare("SELECT * FROM orders WHERE full_name = ?");
$stmt->bind_param("s", $fullName);
$stmt->execute();
$result = $stmt->get_result();
$orders = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Purchase History</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      margin: 0;
      padding: 0;
      background: #f4f4f4;
    }

    .container {
      max-width: 1000px;
      margin: 30px auto;
      padding: 20px;
      background: #fff;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }

    h2 {
      text-align: center;
      margin-bottom: 30px;
      color: #333;
    }

    .order-card {
      border: 1px solid #ddd;
      border-radius: 8px;
      padding: 20px;
      margin-bottom: 25px;
      background: #fafafa;
    }

    .order-header {
      display: flex;
      flex-wrap: wrap;
      justify-content: space-between;
      gap: 10px;
      margin-bottom: 15px;
    }

    .order-header span {
      font-weight: bold;
      color: #444;
    }

    .product-list {
      display: flex;
      flex-wrap: wrap;
      gap: 15px;
      margin-top: 10px;
    }

    .product-item {
      flex: 1 1 220px;
      background: #fff;
      border: 1px solid #ccc;
      border-radius: 6px;
      padding: 10px;
      box-sizing: border-box;
    }

    .product-item img {
      width: 100%;
      height: auto;
      border-radius: 4px;
      margin-bottom: 8px;
    }

    .receipt-img {
      max-width: 100%;
      margin-top: 15px;
      border-radius: 6px;
    }

    @media (max-width: 600px) {
      .order-header {
        flex-direction: column;
      }

      .product-list {
        flex-direction: column;
      }

      .product-item {
        width: 100%;
      }
    }
  </style>
</head>
<body>
  <div class="container">
    <h2>🧾 My Purchase History</h2>

    <?php if (count($orders) > 0): ?>
      <?php foreach ($orders as $order): ?>
        <div class="order-card">
          <div class="order-header">
            <span>Order ID: <?= htmlspecialchars($order['order_id']) ?></span>
            <span>Status: <?= htmlspecialchars($order['status']) ?></span>
            <span>Total: ₦<?= number_format($order['total_price'], 2) ?></span>
            <span>Date: <?= htmlspecialchars($order['created_at']) ?></span>
          </div>

          <p><strong>Delivery:</strong> <?= htmlspecialchars($order['user_address']) ?> → <?= htmlspecialchars($order['delivery_location']) ?></p>
          <p><strong>Customer:</strong> <?= htmlspecialchars($order['full_name']) ?> (<?= htmlspecialchars($order['phone_number']) ?>)</p>

          <div class="product-list">
            <?php
              $products = json_decode($order['products'], true);
              if (is_array($products)) {
                foreach ($products as $product) {
                  $productId = $product['productId'];
                  $productQuery = $conn->prepare("SELECT * FROM products WHERE id = ?");
                  $productQuery->bind_param("i", $productId);
                  $productQuery->execute();
                  $productResult = $productQuery->get_result();
                  $productDetails = $productResult->fetch_assoc();

                  echo '<div class="product-item">';
                  if (!empty($productDetails['image'])) {
                    echo '<img src="' . htmlspecialchars($productDetails['image']) . '" alt="' . htmlspecialchars($productDetails['name']) . '">';
                  }
                  echo '<p><strong>' . htmlspecialchars($productDetails['name']) . '</strong></p>';
                  echo '<p>Category: ' . htmlspecialchars($productDetails['category']) . '</p>';
                  echo '<p>Description: ' . htmlspecialchars($productDetails['description']) . '</p>';
                  echo '<p>Color: ' . htmlspecialchars($product['color']) . '</p>';
                  echo '</div>';
                }
              } else {
                echo '<p>No product details available.</p>';
              }
            ?>
          </div>

          <?php if (!empty($order['receipt_image'])): ?>
            <img src="<?= htmlspecialchars($order['receipt_image']) ?>" alt="Receipt" class="receipt-img">
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <p>No purchase history found.</p>
    <?php endif; ?>
  </div>
</body>
</html>
