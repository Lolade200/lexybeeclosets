<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = "localhost";
$user = "root";
$password = "";
$dbname = "lexybee";

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch selected product
$product_sql = "SELECT * FROM products WHERE id = ?";
$product_stmt = $conn->prepare($product_sql);
$product_stmt->bind_param("i", $product_id);
$product_stmt->execute();
$product_result = $product_stmt->get_result();
$selected_product = $product_result->fetch_assoc();

if (!$selected_product) {
  echo "<p>Product not found.</p>";
  exit;
}

$category = $selected_product['category'];
// Pagination setup
$limit = 3; // number of related products per page
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

// Count total related products
$count_sql = "SELECT COUNT(*) as total FROM products WHERE category = ? AND id != ?";
$count_stmt = $conn->prepare($count_sql);
$count_stmt->bind_param("si", $category, $product_id);
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$total_related = $count_result->fetch_assoc()['total'];
$count_stmt->close();

$total_pages = ceil($total_related / $limit);


// Fetch related products
$related_sql = "SELECT * FROM products WHERE category = ? AND id != ? ORDER BY created_at DESC";
$related_stmt = $conn->prepare($related_sql);
$related_stmt->bind_param("si", $category, $product_id);
$related_stmt->execute();
$related_result = $related_stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
  <title><?= htmlspecialchars($selected_product['name']) ?> - Product Page</title>
  <link rel="stylesheet" href="product.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<!-- ✅ Custom Header -->

<header>
  <div class="logo">
    <img src="bee.png" alt="Lexxybee Logo">
    <h2>LEXYBEE</h2>
  </div>
<form class="search-bar" method="GET" action="">
  <input type="text" name="search" placeholder="Search products..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
  <button type="submit"><i class="fas fa-search"></i></button>
</form>

  <button class="hamburger" onclick="toggleMenu()">
    <i class="fas fa-bars"></i>
  </button>
  <nav>
    <a href="#">Login</a>
    <a href="#">Signup</a>
    <a href="#">About</a>
    <span class="cart-icon-wrapper">
  <i class="fas fa-shopping-cart"></i>
  <span class="cart-dot" style="display: none;"></span>
</span>

  </nav>
</header>

<!-- ✅ Product Details -->
<h1 style="text-align:center;">🛍️ Product Details</h1>

<div class="catalog">
  <?php $product = $selected_product; ?>
  <div class="product">
    <img id="variantImage<?= $product['id'] ?>" src="<?= htmlspecialchars($product['image']) ?>" alt="Product Image">
    <h2><?= htmlspecialchars($product['name']) ?></h2>
    <p><strong>Category:</strong> <?= htmlspecialchars($product['category']) ?></p>
    <p><strong>Availability:</strong> <?= htmlspecialchars($product['availability']) ?></p>
    <p><?= nl2br(htmlspecialchars($product['description'])) ?></p>

    <?php
    $variant_sql = "SELECT * FROM product_variants WHERE product_id = ?";
    $variant_stmt = $conn->prepare($variant_sql);
    $variant_stmt->bind_param("i", $product['id']);
    $variant_stmt->execute();
    $variant_result = $variant_stmt->get_result();

    $variants = [];
    $colors = [];

    while ($v = $variant_result->fetch_assoc()) {
      $variants[] = $v;
      if (!isset($colors[$v['color']])) {
        $colors[$v['color']] = $v['color_image'];
      }
    }

    $variant_json = json_encode($variants);
    ?>

    <p><strong>Available Colors:</strong></p>
    <div class="color-swatches">
      <?php foreach ($colors as $color => $image): ?>
        <span class="color-swatch"
              style="background-color: <?= htmlspecialchars($color) ?>;"
              onclick='showSizes(<?= $product["id"] ?>, <?= $variant_json ?>, "<?= $color ?>", "<?= $image ?>")'
              title="<?= htmlspecialchars($color) ?>">
        </span>
      <?php endforeach; ?>
    </div>

    <div id="sizeSection<?= $product['id'] ?>" style="margin-top:15px;">
      <div id="sizeButtons<?= $product['id'] ?>" class="size-buttons"></div>
      <p><strong>Selected Size:</strong> <span id="selectedSize<?= $product['id'] ?>">None</span></p>
      <p><strong>Stock:</strong> <span id="selectedStock<?= $product['id'] ?>">0</span></p>
      <p><strong>Price:</strong> ₦<span id="selectedPrice<?= $product['id'] ?>">0.00</span></p>
      <button class="add-to-cart" onclick="addToCart(<?= $product['id'] ?>)">🛒 Add to Cart</button>
    </div>

    <?php $variant_stmt->close(); ?>
  </div>
</div>

<h2 style="margin-top:40px;">Related Products in <?= htmlspecialchars($category) ?></h2>

<div class="catalog">
  <?php while ($product = $related_result->fetch_assoc()): ?>
    <div class="product">
      <img id="variantImage<?= $product['id'] ?>" src="<?= htmlspecialchars($product['image']) ?>" alt="Product Image">
      <h2><?= htmlspecialchars($product['name']) ?></h2>
      <p><strong>Category:</strong> <?= htmlspecialchars($product['category']) ?></p>
      <p><strong>Availability:</strong> <?= htmlspecialchars($product['availability']) ?></p>
      <p><?= nl2br(htmlspecialchars($product['description'])) ?></p>

      <?php
      $variant_sql = "SELECT * FROM product_variants WHERE product_id = ?";
      $variant_stmt = $conn->prepare($variant_sql);
      $variant_stmt->bind_param("i", $product['id']);
      $variant_stmt->execute();
      $variant_result = $variant_stmt->get_result();

      $variants = [];
      $colors = [];

      while ($v = $variant_result->fetch_assoc()) {
        $variants[] = $v;
        if (!isset($colors[$v['color']])) {
          $colors[$v['color']] = $v['color_image'];
        }
      }

      $variant_json = json_encode($variants);
      ?>

      <p><strong>Available Colors:</strong></p>
      <div class="color-swatches">
        <?php foreach ($colors as $color => $image): ?>
          <span class="color-swatch"
                style="background-color: <?= htmlspecialchars($color) ?>;"
                onclick='showSizes(<?= $product["id"] ?>, <?= $variant_json ?>, "<?= $color ?>", "<?= $image ?>")'
                title="<?= htmlspecialchars($color) ?>">
          </span>
        <?php endforeach; ?>
      </div>

      <div id="sizeSection<?= $product['id'] ?>" style="margin-top:15px;">
        <div id="sizeButtons<?= $product['id'] ?>" class="size-buttons"></div>
        <p><strong>Selected Size:</strong> <span id="selectedSize<?= $product['id'] ?>">None</span></p>
        <p><strong>Stock:</strong> <span id="selectedStock<?= $product['id'] ?>">0</span></p>
        <p><strong>Price:</strong> ₦<span id="selectedPrice<?= $product['id'] ?>">0.00</span></p>
        <button class="add-to-cart" onclick="addToCart(<?= $product['id'] ?>)">
  <i class="fas fa-cart-plus"></i> 
</button>

      </div>

      <?php $variant_stmt->close(); ?>
    </div>
    
  <?php endwhile; ?>
</div>
<div class="pagination">
  <?php for ($i = 1; $i <= $total_pages; $i++): ?>
    <a href="?id=<?= $product_id ?>&page=<?= $i ?>" class="<?= $i == $page ? 'active' : '' ?>">
      <?= $i ?>
    </a>
  <?php endfor; ?>
</div>

<?php $conn->close(); ?>


<!-- ✅ Custom Footer -->

<footer>
  <div class="footer-grid">
    <div>
      <h4><i class="fas fa-university"></i> Account Details</h4>
      <p><strong>BANK:</strong> Sterling Bank<br>
         <strong>Account No.:</strong> 0100286255<br>
         <strong>Account Name:</strong> DAYLIZ STORES NIG. LTD</p>

      <p><strong>OR</strong><br>
         <strong>BANK:</strong> Palmpay<br>
         <strong>Account No.:</strong> 9134946614<br>
         <strong>Account Name:</strong> Akinwon Ayokunle</p>

      <p class="footer-note">
        <i class="fas fa-exclamation-triangle"></i> Any goods left unpicked is at owner's risk<br>
        <i class="fas fa-ban"></i> NO REFUNDS after payment<br>
        <i class="fas fa-exchange-alt"></i> NO EXCHANGE after pickup
      </p>
    </div>

    <div>
      <h4><i class="fas fa-link"></i> Quick Links</h4>
      <ul style="list-style: none; padding-left: 0;">
        <li><i class="fas fa-home"></i> <a href="#" style="color: #eee; text-decoration: none;">Home</a></li>
        <li><i class="fas fa-info-circle"></i> <a href="#" style="color: #eee; text-decoration: none;">About</a></li>
        <li><i class="fas fa-sign-in-alt"></i> <a href="#" style="color: #eee; text-decoration: none;">Login</a></li>
        <li><i class="fas fa-user-plus"></i> <a href="#" style="color: #eee; text-decoration: none;">Signup</a></li>
      </ul>
    </div>

    <div>
      <h4><i class="fas fa-address-book"></i> Contact</h4>
      <ul style="list-style: none; padding-left: 0;">
        <li><i class="fas fa-map-marker-alt"></i> 23, Golden Bimot Plaza, Opp. Musec Filling Station,<br>
            Ashipa Road, Amule bus stop, Ayobo-Ipaja, Lagos, Nigeria</li>
        <li><i class="fas fa-phone"></i> +234 814 687 5777 | +234 913 687 7689</li>
        <li><i class="fas fa-envelope"></i> info@daylizstores.com</li>
        <li><i class="fab fa-instagram"></i> @dayliz_stores</li>
        <li><i class="fas fa-store"></i> Dayliz Collections</li>
        <li><i class="fab fa-whatsapp"></i> Message us via WhatsApp</li>
        <li><i class="fas fa-clock"></i> Opening: Mondays - Fridays, 10:00AM - 5:00PM</li>
      </ul>
    </div>
  </div>

  <div class="footer-bottom">
    © Lexxybee All Right Reserved. Powered By 3Core Technology Limited
  </div>
</footer>

<script src="product.js"></script>