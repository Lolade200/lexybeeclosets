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
require_once 'database.php'; // This should define $connection

$errorMessage = '';
$orderItems = [];
$orderId = 0;
$orderCode = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 🔍 Check Order
    if (isset($_POST['order_id'])) {
        $orderCode = $connection->real_escape_string($_POST['order_id']);
        $orderQuery = $connection->query("SELECT id, products FROM orders WHERE order_id = '$orderCode'");

        if ($orderQuery && $orderQuery->num_rows > 0) {
            $order = $orderQuery->fetch_assoc();
            $orderItems = json_decode($order['products'], true);
            $orderId = intval($order['id']);
        } else {
            $errorMessage = "❌ Order not found.";
        }
    }

    // 🗑️ Delete Order and Restore Stock
    if (isset($_POST['delete_order_id'])) {
        $orderId = intval($_POST['delete_order_id']);
        $orderQuery = $connection->query("SELECT products FROM orders WHERE id = $orderId");

        if ($orderQuery && $orderQuery->num_rows > 0) {
            $order = $orderQuery->fetch_assoc();
            $items = json_decode($order['products'], true);

            foreach ($items as $item) {
                $productId = intval($item['productId']);
                $size = strtolower($connection->real_escape_string($item['size']));
                $color = strtolower($connection->real_escape_string($item['color']));
                $quantity = isset($item['quantity']) ? intval($item['quantity']) : 1;

                $connection->query("
                    UPDATE product_variants 
                    SET stock = stock + $quantity
                    WHERE product_id = $productId AND LOWER(size) = '$size' AND LOWER(color) = '$color'
                ");
            }

            // 🧹 Delete the order
            $connection->query("DELETE FROM orders WHERE id = $orderId");
            $errorMessage = "🗑️ Order deleted and stock restored.";
            $orderItems = [];
            $orderCode = '';
        } else {
            $errorMessage = "❌ Order not found.";
        }
    }
}

// ✅ Safely close the connection
if (isset($connection) && $connection instanceof mysqli) {
    $connection->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Delete Order</title>
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
    <h2><i class="fas fa-trash-alt"></i> Delete Order</h2>
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

      <!-- Delete Order Button -->
      <form method="POST" style="margin-top: 20px;">
        <input type="hidden" name="delete_order_id" value="<?= htmlspecialchars($orderId) ?>">
        <button type="submit" style="background: #e74c3c; color: white; padding: 10px 15px; border: none; border-radius: 8px; cursor: pointer;">
          <i class="fas fa-trash-alt"></i> Delete Order
        </button>
      </form>
    </div>
  <?php endif; ?>
</main>

<footer>
  <!-- Same footer as before -->
  <div class="footer-grid">
    <div>
      <h4><i class="fas fa-university"></i> Account Details</h4>
      <p><strong>Bank:</strong> Opay<br>
         <strong>Account No.:</strong> 7033581634<br>
         <strong>Account Name:</strong> Adedulu Bolanle Damilola</p>
      <p class="footer-note">
        <i class="fas fa-exclamation-triangle"></i> Any goods left unpicked is at owner's risk<br>
        <i class="fas fa-ban"></i> NO REFUNDS after payment<br>
        <i class="fas fa-exchange-alt"></i> NO EXCHANGE after pickup
      </p>
    </div>
    <div>
      <h4><i class="fas fa-link"></i> Quick Links</h4>
      <ul style="list-style: none; padding-left: 0;">
       
        <li><i class="fas fa-info-circle"></i> <a href="about.php" style="color: #eee; text-decoration: none;">About</a></li>
        <li><i class="fas fa-sign-in-alt"></i> <a href="login.php" style="color: #eee; text-decoration: none;">Login</a></li>
        <li><i class="fas fa-user-plus"></i> <a href="signup.php" style="color: #eee; text-decoration: none;">Signup</a></li>
      </ul>
    </div>
    <div>
      <h4><i class="fas fa-address-book"></i> Contact</h4>
      <ul style="list-style: none; padding-left: 0;">
        <li><i class="fas fa-map-marker-alt"></i>  2, Lubecker Crescent, Fish pond bus-stop, Agric Ikorodu, Lagos, Nigeria</li>
        <li><i class="fas fa-phone"></i> +23407033581634 / +23408066693304</li>
    <li>
  <a href="https://www.facebook.com/share/16dJPSAcNC/" target="_blank" style="color: #eee;">
    <i class="fab fa-facebook"></i> info@lexybeeclosets.com
  </a>
</li>
<li>
  <i class="fab fa-telegram-plane"></i>
  <a href="https://t.me/+1pFD0r4g2k9hNjQ0" target="_blank" style="color: #eee;">
    Telegram
  </a>
</li>

        <li><i class="fas fa-store"></i> Lexybee Closets</li>
        <li>
  <i class="fab fa-whatsapp"></i>
  <a href="https://chat.whatsapp.com/LY8miQLuLtE7EQyZ92bc3e?mode=ems_copy_t" target="_blank" style="color: #eee;">
    Join our WhatsApp Group
  </a>
</li>

        <li><i class="fas fa-clock"></i> Opening: Mondays - Fridays, 9am - 6pm</li>
      </ul>
    </div>
  </div>
  <div class="footer-bottom">
    © Lexybee Closets. All Rights Reserved. Powered By 3Core Technology Limited
  </div>
</footer>

</body>
</html>
