<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'database.php'; // Assumes $conn is defined here

// Use session name or default to "Guest"
$full_name = !empty($_SESSION['full_name']) ? $_SESSION['full_name'] : 'Guest';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_address      = trim($_POST['user_address'] ?? '');
    $delivery_location = trim($_POST['delivery_location'] ?? '');
    $rawProducts       = $_POST['products'] ?? '';
    $phone_number      = trim($_POST['phone_number'] ?? '');

    if (!$user_address || !$delivery_location || !$rawProducts || !$phone_number) {
        echo "<script>alert('Please fill in all required fields.'); history.back();</script>";
        exit;
    }

    // Handle receipt upload
    $receipt_path = '';
    if (!empty($_FILES['receipt_image']['name']) && $_FILES['receipt_image']['error'] === UPLOAD_ERR_OK) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $filename     = preg_replace("/[^a-zA-Z0-9\._-]/", "_", basename($_FILES["receipt_image"]["name"]));
        $receipt_path = $target_dir . time() . "_" . $filename;

        if (!move_uploaded_file($_FILES["receipt_image"]["tmp_name"], $receipt_path)) {
            echo "<script>alert('Failed to upload receipt. Please try again.'); history.back();</script>";
            exit;
        }
    } else {
        echo "<script>alert('Please upload your receipt image.'); history.back();</script>";
        exit;
    }

    $order_id        = 'LB-' . rand(10000, 99999);
    $orderedProducts = json_decode($rawProducts, true);

    if (json_last_error() !== JSON_ERROR_NONE || !is_array($orderedProducts)) {
        echo "<script>alert('Invalid product data.'); history.back();</script>";
        exit;
    }

    $finalProducts = [];
    $total_price   = 0;

    foreach ($orderedProducts as $item) {
        $productId = intval($item['productId'] ?? 0);
        $name      = $item['name'] ?? '';
        $color     = strtolower(trim($item['color'] ?? ''));
        $size      = strtolower(trim($item['size'] ?? ''));
        $quantity  = intval($item['quantity'] ?? 1);
        $image     = $item['image'] ?? '';

        $price = 0;
        $priceQuery = $conn->prepare("
            SELECT price FROM product_variants 
            WHERE product_id = ? AND LOWER(size) = ? AND LOWER(color) = ? 
            LIMIT 1
        ");
        if ($priceQuery) {
            $priceQuery->bind_param("iss", $productId, $size, $color);
            $priceQuery->execute();
            $result = $priceQuery->get_result();
            if ($result && $result->num_rows > 0) {
                $price = (float)$result->fetch_assoc()['price'];
            } else {
                $fallback = $conn->prepare("SELECT price FROM product_variants WHERE product_id = ? LIMIT 1");
                if ($fallback) {
                    $fallback->bind_param("i", $productId);
                    $fallback->execute();
                    $fallbackResult = $fallback->get_result();
                    if ($fallbackResult && $fallbackResult->num_rows > 0) {
                        $price = (float)$fallbackResult->fetch_assoc()['price'];
                    }
                    $fallback->close();
                }
            }
            $priceQuery->close();
        }

        $itemTotal    = $price * $quantity;
        $total_price += $itemTotal;

        $finalProducts[] = compact('productId', 'name', 'color', 'size', 'quantity', 'price', 'image');
    }

    $productsJson = json_encode($finalProducts, JSON_UNESCAPED_UNICODE);

    $conn->begin_transaction();

    // Insert order
    $stmt = $conn->prepare("
        INSERT INTO orders 
        (order_id, full_name, phone_number, user_address, delivery_location, total_price, products, receipt_image, status, notified, stock_updated) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Received', 0, 0)
    ");
    $stmt->bind_param(
        "ssssisss",
        $order_id,
        $full_name,
        $phone_number,
        $user_address,
        $delivery_location,
        $total_price,
        $productsJson,
        $receipt_path
    );

    if (!$stmt->execute()) {
        echo "MySQL error: " . $stmt->error;
        $conn->rollback();
        exit;
    }

    // ✅ Reduce stock for each item
    foreach ($finalProducts as $product) {
        $updateStock = $conn->prepare("
            UPDATE product_variants 
            SET stock = stock - ? 
            WHERE product_id = ? AND LOWER(size) = ? AND LOWER(color) = ? AND stock >= ?
        ");
        if ($updateStock) {
            $qty = $product['quantity'];
            $updateStock->bind_param("iissi", $qty, $product['productId'], $product['size'], $product['color'], $qty);
            $updateStock->execute();
            $updateStock->close();
        }
    }

    $conn->commit();
    // ✅ Redirect back to product_display.php instead of dashboard.php
    echo "<script>
            alert('Order placed successfully! Your Order ID is $order_id');
            window.location.href='product_display.php';
          </script>";
    $stmt->close();
    exit;
}

// Fetch user's past orders
$orders = [];
$stmt = $conn->prepare("
    SELECT products, order_id, created_at 
    FROM orders 
    WHERE full_name = ? 
    ORDER BY created_at DESC
");
$stmt->bind_param("s", $full_name);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $row['products'] = json_decode($row['products'], true);
    $orders[] = $row;
}
$stmt->close();
$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Checkout - Lexybee Store</title>
  <link rel="stylesheet" href="checkout.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>

<header>
  <div class="logo">
    <img src="bee.png" alt="Lexxybee Logo">
    <h2>Lexybeeclosets</h2>
  </div>
    
  <nav>
    <?php if ($full_name): ?>
      <span>Welcome, <?php echo htmlspecialchars($full_name); ?>!</span>
    <?php endif; ?>
     <a href="index.php" style="text-decoration:none; color:white">Home</a>
  </nav>
</header>

<h2>Your Checkout Summary</h2>
<form id="checkout-form" method="POST" action="checkout.php" enctype="multipart/form-data">
  <div class="checkout-container">

    <!-- Dynamic Selected Products -->
    <div id="checkout-products"></div>
    <h3 id="checkout-total">Total Price: ₦0</h3>

    <!-- User Address Input -->
    <div class="form-group">
      <label for="user-address">Location:</label>
      <input type="text" id="user-address" name="user_address" placeholder="Enter your address" required>
    </div>

    <!-- Phone Number Input -->
    <div class="form-group">
      <label for="phone-number">Phone Number:</label>
      <input type="tel" id="phone-number" name="phone_number" placeholder="Enter your phone number" required>
    </div>

    <!-- Delivery Location Selector -->
    <div class="form-group">
      <label for="delivery-location-select">Delivery Method:</label>
      <select id="delivery-location-select" name="delivery_location" required>
        <option value="">Select delivery method</option>
        <option value="Ikeja">Local Pickup</option>
        <option value="Surulere">Within Lagos</option>
        <option value="Surulere">Outside Lagos</option>
      </select>
    </div>

    <!-- Delivery Policy -->
    <h3>Delivery Policy</h3>
    <table class="delivery-policy">
      <thead>
        <tr>
          <th>Policy</th>
          <th>Details</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>Delivery Days</td>
          <td>Tuesday and Thursday</td>
        </tr>
        <tr>
          <td>Shipping Fee</td>
          <td>The shipping fee depends on the location</td>
        </tr>
        <tr>
          <td>Note</td>
          <td>Please, always remember to add a ₦50 charge on every order of ₦10,000 and above.</td>
        </tr>
        <tr>
          <td>Return Policy</td>
          <td>No refunds or exchanges after pickup</td>
        </tr>
      </tbody>
    </table>

    <!-- Account Payment Info -->
    <div class="payment-info">
      <h4>Account Payment Info</h4>
      <p><strong>Bank:</strong> Opay</p>
      <p><strong>Account No.:</strong> 7033581634</p>
      <p><strong>Account Name:</strong> Adedulu Bolanle Damilola</p>
      <hr>
      <p><strong>Bank:</strong> Palmpay</p>
      <p><strong>Account No.:</strong> 7033581634</p>
      <p><strong>Account Name:</strong> Adedulu Bolanle Damilola</p>
    </div>

    <!-- Receipt Image Upload -->
    <label for="receipt-upload">Upload Receipt Image:</label>
    <input type="file" id="receipt-upload" name="receipt_image" accept="image/*" required>

    <!-- Hidden Inputs for Cart Data -->
    <input type="hidden" name="products" id="products-data">
    <input type="hidden" name="total_price" id="total-price-data">

    <!-- Buy All Button -->
    <button type="submit" class="buy-all-btn">
      <i class="fas fa-shopping-bag"></i> Buy All
    </button>

  </div>
</form>


<!-- Past Orders -->
<?php if (!empty($orders)): ?>
  <div class="purchase-history">
    <h3><i class="fas fa-box"></i> Products You've Bought</h3>
    <?php foreach ($orders as $order): ?>
      <div class="order-block">
        <p><strong>Order ID:</strong> <?php echo htmlspecialchars($order['order_id']); ?> 
        <span style="float:right;"><em><?php echo date("d M Y", strtotime($order['created_at'])); ?></em></span></p>
        <ul>
          <?php foreach ($order['products'] as $product): ?>
            <li style="display: flex; align-items: center; gap: 15px; margin-bottom: 10px;">
              <?php if (!empty($product['image'])): ?>
                <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" style="width: 60px; height: 60px; object-fit: cover; border-radius: 6px;">
              <?php endif; ?>
              <div>
                <strong><?php echo htmlspecialchars($product['name']); ?></strong>
                <?php if (!empty($product['color'])): ?> - Color: <?php echo htmlspecialchars($product['color']); ?><?php endif; ?>
                <?php if (!empty($product['size'])): ?> - Size: <?php echo htmlspecialchars($product['size']); ?><?php endif; ?>
                <?php if (!empty($product['price'])): ?> - Price: ₦<?php echo htmlspecialchars($product['price']); ?><?php endif; ?>
              </div>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endforeach; ?>
  </div>
<?php else: ?>
  <p style="margin: 20px 0; color: #555;">You haven’t bought anything yet.</p>
<?php endif; ?>

<!-- Footer -->
<footer>
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
        <li><i class="fas fa-map-marker-alt"></i> 2, Lubecker Crescent, Fish pond bus-stop, Agric Ikorodu, Lagos, Nigeria</li>
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


<script>
document.addEventListener('DOMContentLoaded', () => {
  const cartData = JSON.parse(localStorage.getItem('cartData')) || [];
  const container = document.getElementById('checkout-products');
  const totalPriceEl = document.getElementById('checkout-total');

  if (cartData.length === 0) {
    container.innerHTML = '<p style="text-align:center;">Your cart is empty.</p>';
    totalPriceEl.textContent = 'Total Price: ₦0';
    return;
  }

  let total = 0;
  cartData.forEach(item => {
    const price = parseFloat(item.price.replace(/,/g, ''));
    total += price;

    const div = document.createElement('div');
    div.className = 'checkout-item';
    div.innerHTML = `
      <img src="${item.image}" alt="${item.name}" style="width:60px;height:60px;object-fit:cover;border-radius:6px;">
      <div class="checkout-details">
        <p><strong>${item.name}</strong></p>
        <p>Color: ${item.color}</p>
        <p>Size: ${item.size}</p>
        <p>Price: ₦${item.price}</p>
      </div>
    `;
    container.appendChild(div);
  });

  totalPriceEl.textContent = `Total Price: ₦${total.toLocaleString()}`;
  document.getElementById('products-data').value = JSON.stringify(cartData);
  document.getElementById('total-price-data').value = total.toFixed(2);
});

document.getElementById('checkout-form').addEventListener('submit', function (e) {
  const cartData = JSON.parse(localStorage.getItem('cartData')) || [];
  if (cartData.length === 0) {
    alert("Your cart is empty. Please add products before checking out.");
    e.preventDefault();
  }
});
</script>

</body>
</html>
