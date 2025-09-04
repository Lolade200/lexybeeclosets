<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = "localhost";
$user = "root";
$password = "";
$dbname = "bbbb";

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Handle search
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$search_results = [];
if (!empty($search_query)) {
    $search_sql = "SELECT * FROM products WHERE name LIKE ? OR category LIKE ? OR description LIKE ?";
    $stmt = $conn->prepare($search_sql);
    $like = "%" . $search_query . "%";
    $stmt->bind_param("sss", $like, $like, $like);
    $stmt->execute();
    $search_results = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

// Get product ID
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch selected product if ID is given
$selected_product = null;
if ($product_id > 0) {
    $product_sql = "SELECT * FROM products WHERE id = ?";
    $product_stmt = $conn->prepare($product_sql);
    $product_stmt->bind_param("i", $product_id);
    $product_stmt->execute();
    $product_result = $product_stmt->get_result();
    $selected_product = $product_result->fetch_assoc();
    $product_stmt->close();
}

// Only show "Product not found" if ID is given, no search, and no product found
if ($product_id > 0 && empty($search_query) && !$selected_product) {
  echo "<p>Product not found.</p>";
  exit;
}

// Default: fetch all products if no search and no product selected
$all_products = [];
if (empty($search_query) && $product_id === 0) {
    $all_sql = "SELECT * FROM products";
    $all_result = $conn->query($all_sql);
    $all_products = $all_result->fetch_all(MYSQLI_ASSOC);
}

$category = $selected_product['category'] ?? '';

// Function to fetch variants and render a product card
function renderProductCard($conn, $product) {
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
    $variant_stmt->close();
    $variant_json = json_encode($variants, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
    ?>

    <!DOCTYPE html>
    <html lang="en">
    <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>Document</title>
    </head>
   

    <div class="product">
      <img id="variantImage<?= $product['id'] ?>" src="<?= htmlspecialchars($product['image']) ?>" alt="Product Image">
      <h2><?= htmlspecialchars($product['name']) ?></h2>
      <p><strong>Category:</strong> <?= htmlspecialchars($product['category']) ?></p>
      <p><strong>Availability:</strong> <?= htmlspecialchars($product['availability']) ?></p>
      <p><?= nl2br(htmlspecialchars($product['description'])) ?></p>

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
        <button class="add-to-cart buy-now" onclick="window.location.href='login.php'">
          <i class="fas fa-bolt"></i> Buy Now
        </button>
      </div>
    </div>
    <?php
}

// Fetch related products
$related_products = [];
if ($selected_product) {
    $related_sql = "SELECT * FROM products WHERE category = ? AND id != ? LIMIT 4";
    $related_stmt = $conn->prepare($related_sql);
    $related_stmt->bind_param("si", $category, $product_id);
    $related_stmt->execute();
    $related_products = $related_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $related_stmt->close();
}
?>
<!DOCTYPE html>

<html>
<head>
  <title><?= $selected_product ? htmlspecialchars($selected_product['name']) : 'Search Results' ?> - Product Page</title>
  <link rel="stylesheet" href="product2.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<!-- ✅ Header -->
<header>
  <div class="logo">
    <img src="logoimg.jpg" alt="Lexxybee Logo">
    <h2>LexybeeClosets</h2>
  </div>
  <form class="search-bar" method="GET" action="">
    <input type="text" name="search" placeholder="Search products..." value="<?= htmlspecialchars($search_query) ?>">
    <?php if ($product_id > 0): ?>
      <input type="hidden" name="id" value="<?= $product_id ?>">
    <?php endif; ?>
    <button type="submit"><i class="fas fa-search"></i></button>
  </form>
</header>

<?php if (!empty($search_query)): ?>
  <h2 style="text-align:center;">Search Results for "<?= htmlspecialchars($search_query) ?>"</h2>
  <div class="catalog">
    <?php if (count($search_results) > 0): ?>
      <?php foreach ($search_results as $product) { renderProductCard($conn, $product); } ?>
    <?php else: ?>
      <p style="text-align:center;">No products found.</p>
    <?php endif; ?>
  </div>

  <!-- Auto-reset after 5 seconds -->
  <script>
    setTimeout(function() {
      <?php if ($product_id > 0): ?>
        window.location.href = 'product2.php?id=<?= $product_id ?>';
      <?php else: ?>
        window.location.href = 'product2.php';
      <?php endif; ?>
    }, 5000);
  </script>
<?php endif; ?>

<?php if (!empty($all_products)): ?>
<h1 style="text-align:center;">🛍️ All Products</h1>
<div class="catalog">
  <?php foreach ($all_products as $product) { renderProductCard($conn, $product); } ?>
</div>
<?php endif; ?>

<?php if ($selected_product): ?>
<h1 style="text-align:center;">🛍️ Product Details</h1>
<div class="catalog">
  <?php renderProductCard($conn, $selected_product); ?>
</div>

<h2 style="margin-top:40px; text-align:center;">Related Products</h2>
<div class="catalog">
  <?php if (count($related_products) > 0): ?>
    <?php foreach ($related_products as $product) { renderProductCard($conn, $product); } ?>
  <?php else: ?>
    <p style="text-align:center;">No related products found.</p>
  <?php endif; ?>
</div>
<?php endif; ?>
<?php include 'footer.php'?>
<script>
function showSizes(productId, variants, color, image) {
  document.getElementById('variantImage' + productId).src = image;
  let filtered = variants.filter(v => v.color === color);
  let sizeButtons = document.getElementById('sizeButtons' + productId);
  sizeButtons.innerHTML = '';
  filtered.forEach(variant => {
    let btn = document.createElement('button');
    btn.textContent = variant.size;
    btn.onclick = function() {
      document.getElementById('selectedSize' + productId).textContent = variant.size;
      document.getElementById('selectedStock' + productId).textContent = variant.stock;
      document.getElementById('selectedPrice' + productId).textContent = parseFloat(variant.price).toFixed(2);
    };
    sizeButtons.appendChild(btn);
  });
}
</script>

</body>
</html>
<?php $conn->close(); ?>
