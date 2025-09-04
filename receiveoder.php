<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 🧑‍💼 Get admin name
$full_name = $_SESSION['full_name'] ?? 'Admin';

// 🔐 Access control
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role']) !== 'admin') {
    header("Location: login.php");
    exit;
}

// 📊 Connect to database
$connection = new mysqli("localhost", "root", "", "bbb");
if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

$errorMessage = '';
$orderItems = [];
$orderId = 0;
$orderCode = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 🔍 Check Order
    if (isset($_POST['order_id'])) {
        $orderCode = $connection->real_escape_string($_POST['order_id']);
        $orderQuery = $connection->query("SELECT id, products, stock_updated, status FROM orders WHERE order_id = '$orderCode'");

        if ($orderQuery && $orderQuery->num_rows > 0) {
            $order = $orderQuery->fetch_assoc();
            $orderItems = json_decode($order['products'], true);
            $orderId = intval($order['id']);
        } else {
            $errorMessage = "❌ Order not found.";
        }
    }

    // ✅ Confirm Order
    if (isset($_POST['confirm_order_id'])) {
        $orderId = intval($_POST['confirm_order_id']);
        $orderQuery = $connection->query("SELECT products, stock_updated FROM orders WHERE id = $orderId");

        if ($orderQuery && $orderQuery->num_rows > 0) {
            $order = $orderQuery->fetch_assoc();
            $items = json_decode($order['products'], true);

            if ((int)$order['stock_updated'] === 0) {
                foreach ($items as $item) {
                    $productId = intval($item['productId']);
                    $size = $connection->real_escape_string($item['size']);
                    $color = $connection->real_escape_string($item['color']);

                    // 👇 Reduce stock by 1 only
                    $connection->query("
                        UPDATE product_variants 
                        SET stock = CASE WHEN stock >= 1 THEN stock - 1 ELSE stock END
                        WHERE product_id = $productId AND size = '$size' AND color = '$color'
                    ");
                }

                $connection->query("UPDATE orders SET status = 'Received', stock_updated = 1 WHERE id = $orderId");
                $errorMessage = "✅ Order confirmed and stock updated.";
                $orderItems = [];
                $orderCode = '';
            } else {
                $errorMessage = "⚠️ Stock already updated for this order.";
            }
        } else {
            $errorMessage = "❌ Order not found.";
        }
    }
}

$connection->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Confirm Order</title>
  <link rel="stylesheet" href="comfirm.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
</head>
<body>

<header>
  <div class="logo">
    <img src="logoimg.jpg" alt="Lexxybee Logo">
    <h2>LexybeeClosets</h2>
  </div>
  <button class="hamburger" onclick="toggleMenu()"><i class="fas fa-bars"></i></button>
  <nav>
    <span style="font-weight: bold; color: white; margin-right: 15px; font-size: 15px;">
      <?= htmlspecialchars($full_name) ?>
    </span>
  </nav>
</header>

<main class="content">
  <div class="confirm-box">
    <h2><i class="fas fa-check-circle"></i> Confirm Order</h2>
    <form method="POST">
      <input type="text" name="order_id" placeholder="Enter Order Code (e.g. LB-98182)" required />
      <button type="submit">Check Order</button>
    </form>
  </div>

  <?php if (!empty($errorMessage)): ?>
    <div class="order-result"><?= $errorMessage ?></div>
  <?php endif; ?>

  <?php if (!empty($orderItems)): ?>
    <div class="order-result">
      <h3>Products in Order <?= htmlspecialchars($orderCode) ?>:</h3>
      <?php foreach ($orderItems as $item): ?>
        <div class="order-item">
          <?php if (!empty($item['image'])): ?>
            <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>">
          <?php endif; ?>
          <div>
            <p><strong><?= htmlspecialchars($item['name']) ?></strong></p>
            <?php if (isset($item['price'])): ?>
              <p>Price: ₦<?= number_format($item['price'], 2) ?></p>
            <?php endif; ?>
            <?php if (isset($item['quantity'])): ?>
              <p>Quantity: <?= (int)$item['quantity'] ?></p>
            <?php endif; ?>
            <?php if (isset($item['color'])): ?>
              <p>Color: <?= htmlspecialchars($item['color']) ?></p>
            <?php endif; ?>
            <?php if (isset($item['size'])): ?>
              <p>Size: <?= htmlspecialchars($item['size']) ?></p>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>

      <!-- Confirm Order Button -->
      <form method="POST" style="margin-top: 20px;">
        <input type="hidden" name="confirm_order_id" value="<?= htmlspecialchars($orderId) ?>">
        <button type="submit" style="background: #2ecc71; color: white; padding: 10px 15px; border: none; border-radius: 8px; cursor: pointer;">
          <i class="fas fa-box-open"></i> Confirm Order
        </button>
      </form>
    </div>
  <?php endif; ?>
</main>

<footer>
  <!-- Your existing footer remains unchanged -->
</footer>

</body>
</html>
