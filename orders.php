<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'database.php'; // Assumes $conn is defined here

// 👤 Get user session info
$full_name = $_SESSION['full_name'] ?? 'Admin';

// 📅 Filter by year and month
$currentYear = date('Y');
$currentMonth = date('m');

$yearFilter = isset($_GET['year']) ? intval($_GET['year']) : $currentYear;
$monthFilter = isset($_GET['month']) ? intval($_GET['month']) : $currentMonth;

// 📦 Pagination setup
$limit = 10;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

// 📊 Total filtered orders count
$totalOrders = 0;
$totalResult = $conn->query("
    SELECT COUNT(*) AS total 
    FROM orders 
    WHERE YEAR(created_at) = $yearFilter AND MONTH(created_at) = $monthFilter
");

if ($totalResult) {
    $totalRow = $totalResult->fetch_assoc();
    $totalOrders = (int)$totalRow['total'];
} else {
    die("Query Error (count): " . $conn->error);
}

$totalPages = ceil($totalOrders / $limit);

// 📥 Fetch filtered paginated orders
$query = $conn->query("
    SELECT order_id, full_name, user_address, delivery_location, total_price, created_at, receipt_image, phone_number 
    FROM orders 
    WHERE YEAR(created_at) = $yearFilter AND MONTH(created_at) = $monthFilter 
    ORDER BY created_at DESC 
    LIMIT $limit OFFSET $offset
");

if (!$query) {
    die("Query Error (orders): " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>All Orders</title>
  <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap');

:root {
  --primary: #f0c040;
  --primary-hover: #e0ac30;
  --bg: #f5f7fa;
  --text: #333;
  --muted: #666;
  --card-bg: #fff;
  --card-shadow: rgba(0, 0, 0, 0.06);
  --header-bg: #1e2a38;
  --footer-bg:  #1e2a38;
  --font-family: "Inter", "Segoe UI", sans-serif;
  --radius: 10px;
  --transition: 0.3s ease;
  
}

* {
  box-sizing: border-box;
  margin: 0;
  padding: 0;
  font-family: var(--font-family);
}

body {
  background: var(--bg);
  color: var(--text);
  overflow-x: hidden;
}

a {
  text-decoration: none;
  color: white;
}

/* Header */
header {
  width: 100%;
  background: var(--header-bg);
  color: white;
  padding: 25px 20px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 20px;
  flex-wrap: wrap;
  position: relative;
}

.logo {
  display: flex;
  align-items: center;
  gap: 10px;
}

.logo img {
  width: 40px;
  height: 40px;
}



/* Navigation */
nav {
  display: flex;
  align-items: center;
  gap: 16px;
}

nav a {
  color: white;
  font-weight: 500;
  font-size: 15px;
}

nav a:hover {
  color: var(--primary);
}


.cart-dot {
  display: none;
  position: absolute;
  top: -6px;
  right: -6px;
  min-width: 16px;
  height: 16px;
  padding: 0 4px;
  background-color: red;
  color: white;
  border-radius: 50%;
  font-size: 11px;
  font-weight: bold;
  display: flex;
  align-items: center;
  justify-content: center;
}

/* Hamburger Menu */
.hamburger {
  display: none;
  background: none;
  border: none;
  font-size: 22px;
  color: var(--primary);
  cursor: pointer;
}



    h1 {
      text-align: center;
      margin-bottom: 30px;
      color: #333;
    }


    .order-grid {
      display: flex;
      flex-wrap: wrap;
      gap: 20px;
      justify-content: flex-start;
    }

    .order-card {
      background: #fff;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.05);
      padding: 20px;
      width: 100%;
      max-width: 400px;
      transition: transform 0.3s ease;
    }

    .order-card:hover {
      transform: translateY(-5px);
    }

    .order-header {
      font-size: 18px;
      font-weight: bold;
      margin-bottom: 10px;
      color: #1e3a8a;
    }

    .order-detail {
      margin-bottom: 8px;
      font-size: 14px;
      color: #555;
    }

    .order-detail strong {
      color: #333;
    }

    .order-image {
      width: 100%;
      height: 180px;
      object-fit: cover;
      border-radius: 8px;
      margin-top: 12px;
    }



    footer {
  background: var(--footer-bg);
  color: #eee;
  padding: 40px 30px;
  font-size: 14px;
}

footer h4 {
  margin-top: 0;
  color: var(--primary);
}

.footer-grid {
  display: grid;
grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 30px;
}

.footer-note {
  margin-top: 20px;
  font-style: italic;
  color: #ccc;
}

.footer-bottom {
  text-align: center;
  margin-top: 30px;
  font-size: 12px;
  color: #aaa;
}

footer i {
  margin-right: 8px;
  color: var(--primary);
}

.footer-grid ul li {
  margin-bottom: 10px;
}

.footer-grid a {
  color: var(--primary);
  text-decoration: none;
}

.footer-grid a:hover {
  text-decoration: underline;
}


    @media (max-width: 600px) {
      .order-card {
        max-width: 100%;
      }
    }
  </style>
  </style>
</head>
<body>

<header>
  <div class="logo">
    <img src="logoimg.jpg" alt="Lexxybee Logo">
    <h2>LexybeeClosets</h2>
  </div>
  <nav>
    <span style="font-weight: bold; color: white; margin-right: 15px; font-size: 15px;">
      <?= htmlspecialchars($full_name) ?>
    </span>
  </nav>
</header>

<h1><i class="fas fa-box"></i> All Orders</h1>

<div class="order-grid">
  <?php while ($row = $query->fetch_assoc()): ?>
    <div class="order-card">
      <div class="order-header">Order #<?= htmlspecialchars($row['order_id']) ?></div>
      <div class="order-detail"><strong>Buyer:</strong> <?= htmlspecialchars($row['full_name']) ?></div>
      <div class="order-detail"><strong>Phone Number:</strong> <?= htmlspecialchars($row['phone_number'] ?? 'N/A') ?></div>
      <div class="order-detail"><strong>Address:</strong> <?= htmlspecialchars($row['user_address']) ?></div>
      <div class="order-detail"><strong>Delivery:</strong> <?= htmlspecialchars($row['delivery_location']) ?></div>
      <div class="order-detail"><strong>Total:</strong> ₦<?= number_format($row['total_price'], 2) ?></div>
      <div class="order-detail"><strong>Date:</strong> <?= date("M j, Y g:i A", strtotime($row['created_at'])) ?></div>
      <img src="<?= htmlspecialchars($row['receipt_image']) ?>" alt="Receipt" class="order-image" />
    </div>
  <?php endwhile; ?>
</div>

<div style="text-align: center; margin-top: 30px;">
  <div style="margin-bottom: 10px; font-weight: bold; color: #1e3a8a;">
    Page <?= $page ?> of <?= $totalPages ?>
  </div>

  <?php if ($totalPages > 1): ?>
    <?php for ($i = 1; $i <= $totalPages; $i++): 
      $isActive = ($i == $page);
    ?>
      <a href="?year=<?= $yearFilter ?>&month=<?= $monthFilter ?>&page=<?= $i ?>" style="
        display: inline-block;
        margin: 0 5px;
        padding: 8px 14px;
        background-color: <?= $isActive ? '#1e3a8a' : '#eee' ?>;
        color: <?= $isActive ? '#fff' : '#333' ?>;
        border-radius: 6px;
        text-decoration: none;
        font-weight: bold;
      ">
        <?= $i ?>
      </a>
    <?php endfor; ?>
  <?php endif; ?>
</div>

<form method="GET" style="text-align: center; margin: 30px 0;">
  <label for="year">Year:</label>
  <select name="year" id="year" style="padding: 6px 10px; margin-right: 10px;">
    <?php for ($y = $currentYear; $y >= 2020; $y--): ?>
      <option value="<?= $y ?>" <?= $yearFilter == $y ? 'selected' : '' ?>><?= $y ?></option>
    <?php endfor; ?>
  </select>

  <label for="month">Month:</label>
  <select name="month" id="month" style="padding: 6px 10px;">
    <?php for ($m = 1; $m <= 12; $m++): 
      $monthName = date('F', mktime(0, 0, 0, $m, 10));
    ?>
      <option value="<?= $m ?>" <?= $monthFilter == $m ? 'selected' : '' ?>><?= $monthName ?></option>
    <?php endfor; ?>
  </select>

  <button type="submit" style="padding: 6px 14px; background-color: #1e3a8a; color: white; border: none; border-radius: 6px;">Filter</button>
</form>

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
