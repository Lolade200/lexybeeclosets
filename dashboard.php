<?php

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 🔐 Admin access control
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role']) !== 'admin') {
    header("Location: login.php");
    exit;
}

require_once 'database.php'; // ✅ Fixed missing semicolon

// 📊 Connect to database
//$connection = new mysqli("localhost", "root", "", "bbbb"); // ✅ Added this line to define $connection
//if ($connection->connect_error) {
    //die("Connection failed: " . $connection->connect_error);
//}

// 🧮 Initialize variables
$userCount       = 0;
$totalMoney      = 0.00;
$orderCount      = 0;
$newOrderCount   = 0;
$newUserCount    = 0;
$recentProducts  = [];
$newUsers        = [];
$growthLabels    = [];
$growthCounts    = [];

// 📈 Product growth data
$query = $conn->query("
    SELECT DATE(created_at) AS date, COUNT(*) AS count
    FROM products
    GROUP BY DATE(created_at)
    ORDER BY DATE(created_at) ASC
");

if ($query && $query->num_rows > 0) {
    while ($row = $query->fetch_assoc()) {
        $growthLabels[] = $row['date'];
        $growthCounts[] = $row['count'];
    }
}

// 👥 Total users
$userResult = $conn->query("SELECT COUNT(*) AS total FROM users");
if ($userResult && $userResult->num_rows > 0) {
    $row = $userResult->fetch_assoc();
    $userCount = (int)$row['total'];
}

// 💰 Total money (only from received orders)
$moneyResult = $conn->query("SELECT SUM(total_price) AS total FROM orders WHERE status = 'Received'");
if ($moneyResult && $moneyResult->num_rows > 0) {
    $row = $moneyResult->fetch_assoc();
    $totalMoney = (float)$row['total'];
}

// 📦 Total orders
$orderResult = $conn->query("SELECT COUNT(*) AS total FROM orders");
if ($orderResult && $orderResult->num_rows > 0) {
    $row = $orderResult->fetch_assoc();
    $orderCount = (int)$row['total'];
}

// 🔔 Unnotified orders count
$res = $conn->query("SELECT COUNT(*) AS c FROM orders WHERE notified = 0");
if ($res && $res->num_rows > 0) {
    $row = $res->fetch_assoc();
    $newOrderCount = (int)$row['c'];
}

// 🧾 Latest unnotified orders (up to 10)
$productQuery = $conn->query("SELECT id, products FROM orders WHERE notified = 0 ORDER BY created_at DESC LIMIT 10");
if ($productQuery && $productQuery->num_rows > 0) {
    while ($row = $productQuery->fetch_assoc()) {
        $productData = json_decode($row['products'], true);
        $orderId = (int)$row['id'];

        if (is_array($productData)) {
            foreach ($productData as $item) {
                $productId = intval($item['productId'] ?? 0);
                $size = $conn->real_escape_string($item['size'] ?? '');
                $color = $conn->real_escape_string($item['color'] ?? '');
                $quantity = intval($item['quantity'] ?? 1);

                // 🔍 Lookup price from product_variants
                $priceResult = $conn->query("
                    SELECT price FROM product_variants 
                    WHERE product_id = $productId AND size = '$size' AND color = '$color' LIMIT 1
                ");

                $price = 0;
                if ($priceResult && $priceResult->num_rows > 0) {
                    $priceRow = $priceResult->fetch_assoc();
                    $price = number_format((float)$priceRow['price'], 2);
                }

                $recentProducts[] = [
                    'name'     => htmlspecialchars($item['name'] ?? 'Unknown'),
                    'price'    => $price,
                    'image'    => htmlspecialchars($item['image'] ?? 'uploads/default.jpg'),
                    'quantity' => $quantity
                ];
            }
        }

        // ✅ Mark this order as notified (but do NOT touch stock here)
        $conn->query("UPDATE orders SET notified = 1 WHERE id = $orderId");
    }
}

// 👤 Unnotified users count
$res2 = $conn->query("SELECT COUNT(*) AS c FROM users WHERE notified = 0");
if ($res2 && $res2->num_rows > 0) {
    $row = $res2->fetch_assoc();
    $newUserCount = (int)$row['c'];
}

// 👤 Latest unnotified users (up to 5)
$newUserQuery = $conn->query("SELECT id, full_name FROM users WHERE notified = 0 ORDER BY created_at DESC LIMIT 5");
if ($newUserQuery && $newUserQuery->num_rows > 0) {
    while ($row = $newUserQuery->fetch_assoc()) {
        $newUsers[] = htmlspecialchars($row['full_name'] ?? 'New user');

        // ✅ Mark this user as notified
        $userId = (int)$row['id'];
        $conn->query("UPDATE users SET notified = 1 WHERE id = $userId");
    }
}

// 🔴 Helper: show badge if there’s any new item
$showNotification = ($newOrderCount + $newUserCount) > 0;

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>LexyBee Admin Dashboard</title>
  <link rel="stylesheet" href="dashboard.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
  <div class="dashboard">
    <!-- Sidebar -->
    <aside class="sidebar">
      <div class="admin-profile">
        <div class="profile-img"></div>
        <input type="file" id="profile-upload" hidden />
        <h3 class="admin-name"><?= htmlspecialchars($_SESSION['full_name']) ?></h3>
        <p class="admin-role">Store Manager</p>
      </div>

      <nav class="sidebar-nav">
        <a href="#"><i class="fas fa-gauge-high"></i> Dashboard</a>

      <a href="DeleteOrder.php"><i class="fas fa-cart-shopping"></i> Delete Order</a>
<a href="orders.php"><i class="fas fa-check-circle"></i> Confirm Payment</a>
 
<a href="test.php"><i class="fas fa-cloud-upload-alt"></i> Upload Product</a>
<a href="upload_latest_product.php"><i class="fas fa-star"></i> Upload Latest Product</a>
<a href="update_product.php" class="update-link">
  <i class="fas fa-edit"></i> Update product
</a>

<a href="index.php"><i class="fas fa-home"></i> Home</a>

     <a href="logout.php" style="
  background-color: #f44336;
  color: white;
  padding: 10px 18px;
  border-radius: 6px;
  font-weight: bold;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  box-shadow: 0 2px 6px rgba(0,0,0,0.1);
  transition: background 0.3s ease;
">
  <i class="fas fa-sign-out-alt"></i> Logout
</a>

      </nav>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
      <header class="topbar">
        <div class="brand">
          <img src="logoimg.jpg" alt="LexyBee Logo" class="brand-logo" />
          <h1>LEXYBEE Admin</h1>
        </div>

        <div class="topbar-right">
          <div class="search">
            <i class="fas fa-magnifying-glass"></i>
            <input type="text" placeholder="Search..." />
          </div>

         

          <div class="notifications" onclick="toggleDropdown()"; >
        <i class="fas fa-bell" style="font-size: 32px; color: #f0c040; cursor: pointer;"></i>

            <span class="badge" style="background: <?= $showNotification ? 'red' : 'transparent'; ?>;">
              <?= $showNotification ? ($newOrderCount + $newUserCount) : ''; ?>
            </span>

            <div class="dropdown" id="notificationDropdown">
              <?php if ($newOrderCount > 0 && !empty($recentProducts)): ?>
                <p><strong>New Order Received</strong></p>
                <?php foreach ($recentProducts as $product): ?>
                  <div  style="display:flex;align-items:center;gap:10px;margin-bottom:10px;">
                    <img src="<?= $product['image']; ?>" alt="<?= $product['name']; ?>" style="width:40px;height:40px;object-fit:cover;border-radius:6px;">
                    <div>
                      <strong><?= $product['name']; ?></strong><br>
                      <span>₦<?= $product['price']; ?></span>
                    </div>
                  </div>
                <?php endforeach; ?>
              <?php endif; ?>

              <?php if ($newUserCount > 0 && !empty($newUsers)): ?>
                <p><strong>New Users Registered</strong></p>
                <ul style="padding-left: 20px;">
                  <?php foreach ($newUsers as $name): ?>
                    <li><?= $name; ?></li>
                  <?php endforeach; ?>
                </ul>
              <?php endif; ?>

              <?php if (!$showNotification): ?>
                <p>No new notifications.</p>
              <?php endif; ?>
            </div>
          </div>

          
        </div>
      </header>

      <section class="charts-grid">
        <div class="chart-card">
          <h2><i class="fas fa-chart-line"></i> Products Growth</h2>
          <canvas id="productChart"></canvas>
        </div>

        <div class="chart-card" style="background: linear-gradient(135deg, #f9f9ff, #e0eaff); border-radius: 10px; padding: 20px;">
          <h2><i class="fas fa-seedling"></i> Customers Growth</h2>
          <div class="user-count" style="font-size: 24px; font-weight: bold; margin: 10px 0; color: #333;">
            Total Users: <?= $userCount; ?>
          </div>
          <canvas id="customerChart"></canvas>
        </div>

        <div class="chart-card">
          <h2><i class="fas fa-wallet"></i> Total Revenue</h2>
          <div class="user-count">₦<?= number_format($totalMoney, 2); ?></div>
          <canvas id="revenueChart"></canvas>
        </div>
      </section>
    </main>
  </div>


<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
function fetchNotifications() {
  fetch('fetch_notifications.php')
    .then(response => response.json())
    .then(data => {
      // 🔔 Update badge
      const badge = document.querySelector('.notifications .badge');
      badge.textContent = data.orderCount > 0 ? data.orderCount : '';

      // 📦 Update dropdown
      const dropdown = document.querySelector('.notifications .dropdown');
      dropdown.innerHTML = `<p><strong>New Order Received</strong></p>`;

      if (Array.isArray(data.products) && data.products.length > 0) {
        data.products.forEach(product => {
          const item = document.createElement('div');
          item.style.display = 'flex';
          item.style.alignItems = 'center';
          item.style.gap = '10px';
          item.style.marginBottom = '10px';

          item.innerHTML = `
            <img src="${product.image}" alt="${product.name}" style="width: 40px; height: 40px; object-fit: cover; border-radius: 6px;">
            <div>
              <strong>${product.name}</strong><br>
              <span>₦${product.price}</span>
            </div>
          `;
          dropdown.appendChild(item);
        });
      } else {
        dropdown.innerHTML += `<p>No recent products found.</p>`;
      }

      dropdown.innerHTML += `
        <hr>
        <p>Inventory low on Item #234</p>
        <p>Customer message: “Need invoice”</p>
        <p style="font-size: 12px; color: #888;">Last updated: ${new Date().toLocaleTimeString()}</p>
      `;
    })
    .catch(error => console.error('Error fetching notifications:', error));
}

// 🔁 Poll every 30 seconds
setInterval(fetchNotifications, 30000);
window.addEventListener('load', fetchNotifications);

// 🔽 Toggle dropdown
function toggleDropdown() {
  const dropdown = document.getElementById('notificationDropdown');
  dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
}

// 🧠 Hide dropdown if user clicks outside
document.addEventListener('click', function(event) {
  const notification = document.querySelector('.notifications');
  const dropdown = document.getElementById('notificationDropdown');
  if (!notification.contains(event.target)) {
    dropdown.style.display = 'none';
  }
});

// 📈 Render Product Growth Chart
window.addEventListener('load', function () {
  const ctx = document.getElementById('productChart').getContext('2d');

  const productChart = new Chart(ctx, {
    type: 'doughnut',
    data: {
      labels: <?= json_encode($growthLabels) ?>,
      datasets: [{
        label: 'Products Added',
        data: <?= json_encode($growthCounts) ?>,
        backgroundColor: [
          '#007bff', '#28a745', '#ffc107', '#dc3545', '#17a2b8',
          '#6610f2', '#fd7e14', '#6f42c1', '#20c997', '#e83e8c'
        ],
        borderColor: '#fff',
        borderWidth: 2
      }]
    },
    options: {
      responsive: true,
      cutout: '60%',
      plugins: {
        legend: {
          position: 'bottom',
          labels: {
            color: '#444',
            boxWidth: 12,
            padding: 15
          }
        },
        title: {
          display: true,
          text: 'Product Growth by Date',
          font: { size: 16 },
          color: '#333'
        },
        tooltip: {
          callbacks: {
            label: ctx => `${ctx.label}: ${ctx.parsed} products`
          },
          backgroundColor: '#fff',
          titleColor: '#000',
          bodyColor: '#333',
          borderColor: '#ccc',
          borderWidth: 1
        }
      }
    }
  });
});
</script>

</body>
</html>

