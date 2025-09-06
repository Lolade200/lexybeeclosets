<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'database.php';

// Database connection check
if (!$conn || $conn->connect_error) {
    die("<p style='color:red; text-align:center;'>Database connection failed: " . htmlspecialchars($conn->connect_error) . "</p>");
}

$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$search_results = [];

if (!empty($search_query)) {
    // ✅ Fetch ALL search results (no pagination)
    $search_sql = "SELECT * FROM products WHERE name LIKE ? OR category LIKE ? OR description LIKE ?";
    $stmt = $conn->prepare($search_sql);
    $like = "%" . $search_query . "%";
    $stmt->bind_param("sss", $like, $like, $like);
    $stmt->execute();
    $search_results = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

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

if ($product_id > 0 && empty($search_query) && !$selected_product) {
    echo "<p>Product not found.</p>";
    exit;
}

$all_products = [];
$total_pages = 0;

if (empty($search_query) && $product_id === 0) {
    // Count total products
    $total_sql = "SELECT COUNT(*) as total FROM products";
    $total_result = $conn->query($total_sql);
    $total_row = $total_result->fetch_assoc();
    $total_products = $total_row['total'];
    $total_pages = ceil($total_products / $limit);

    // Fetch paginated products
    $all_sql = "SELECT * FROM products LIMIT ?, ?";
    $all_stmt = $conn->prepare($all_sql);
    $all_stmt->bind_param("ii", $offset, $limit);
    $all_stmt->execute();
    $all_products = $all_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $all_stmt->close();
}

$category = $selected_product['category'] ?? '';

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
    $totalStock = array_sum(array_column($variants, 'stock'));
    ?>

    <div class="product">
      <img id="variantImage<?= $product['id'] ?>" 
           src="<?= htmlspecialchars($product['image']) ?>" 
           alt="<?= htmlspecialchars($product['name']) ?>" 
           onerror="this.src='default-image.jpg'">
           
      <h2><?= htmlspecialchars($product['name']) ?></h2>
      <p><strong>Category:</strong> <?= htmlspecialchars($product['category']) ?></p>
      <p><strong>Availability:</strong> <?= htmlspecialchars($product['availability']) ?></p>
      <p><?= nl2br(htmlspecialchars($product['description'])) ?></p>

      <p><strong>Available Colors:</strong></p>
      <div class="color-swatches">
        <?php foreach ($colors as $color => $image): ?>
          <span class="color-swatch"
                style="background-color: <?= htmlspecialchars($color) ?>; border: 1px solid #ccc;"
                onclick='showSizes(<?= $product["id"] ?>, <?= $variant_json ?>, "<?= $color ?>", "<?= $image ?>")'
                title="<?= htmlspecialchars($color) ?>">
          </span>
        <?php endforeach; ?>
      </div>

      <div id="sizeSection<?= $product['id'] ?>" style="margin-top:15px;">
        <div id="sizeButtons<?= $product['id'] ?>" class="size-buttons"></div>
        <p><strong>Selected Size:</strong> <span id="selectedSize<?= $product['id'] ?>">None</span></p>
        <p><strong>Stock:</strong> <span id="selectedStock<?= $product['id'] ?>"><?= $totalStock ?></span></p>
        <p><strong>Price:</strong> ₦<span id="selectedPrice<?= $product['id'] ?>">0.00</span></p>
        <button class="add-to-cart buy-now" onclick="window.location.href='login.php'">
          <i class="fas fa-bolt"></i> Buy Now
        </button>
      </div>
    </div>
    <?php
}

// ✅ Related products with pagination
$related_products = [];
$total_related_pages = 0;

if ($selected_product) {
    // Count total related products
    $related_count_sql = "SELECT COUNT(*) as total FROM products WHERE category = ? AND id != ?";
    $related_count_stmt = $conn->prepare($related_count_sql);
    $related_count_stmt->bind_param("si", $category, $product_id);
    $related_count_stmt->execute();
    $related_total_row = $related_count_stmt->get_result()->fetch_assoc();
    $related_total = $related_total_row['total'];
    $total_related_pages = ceil($related_total / $limit);
    $related_count_stmt->close();

    // Fetch related products
    $related_sql = "SELECT * FROM products WHERE category = ? AND id != ? LIMIT ?, ?";
    $related_stmt = $conn->prepare($related_sql);
    $related_stmt->bind_param("siii", $category, $product_id, $offset, $limit);
    $related_stmt->execute();
    $related_products = $related_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $related_stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Browse and buy quality products from LexybeeClosets. Search products by category, name, or description.">
  <title><?= $selected_product ? htmlspecialchars($selected_product['name']) . ' - LexybeeClosets' : 'Search Products - LexybeeClosets' ?></title>
  <link rel="stylesheet" href="product2.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    .pagination {
      text-align:center;
      margin:30px 0 60px;
    }
    .pagination a, .pagination span {
      margin: 0 5px;
      padding: 8px 12px;
      background-color: #eee;
      color: #000;
      text-decoration: none;
      border-radius: 4px;
      font-weight: bold;
    }
    .pagination a.active {
      background-color: #333;
      color: #fff;
    }
    .pagination span.disabled {
      background-color: #ccc;
      color: #666;
      cursor: not-allowed;
    }
    /* ✅ Force search results vertical */
    .catalog.search-results {
      display: flex;
      flex-direction: column;
      gap: 20px;
      align-items: center;
    }
    .catalog.search-results .product {
      width: 90%;
      max-width: 600px;
    }
  </style>
</head>
<body>

<header>
  <div class="logo">
    <img src="logoimg.jpg" alt="Lexxybee Logo">
    <h2>LexybeeClosets</h2>
  </div>
  <form class="search-bar" method="GET" action="" role="search" aria-label="Product Search">
    <input type="text" name="search" placeholder="Search products..." value="<?= htmlspecialchars($search_query) ?>" aria-label="Search products">
    <?php if ($product_id > 0): ?>
      <input type="hidden" name="id" value="<?= $product_id ?>">
    <?php endif; ?>
    <button type="submit"><i class="fas fa-search"></i></button>
  </form>
</header>

<?php if (!empty($search_query)): ?>
  <h2 style="text-align:center;">Search Results for "<?= htmlspecialchars($search_query) ?>"</h2>
  <div class="catalog search-results">
    <?php if (count($search_results) > 0): ?>
      <?php foreach ($search_results as $product) { renderProductCard($conn, $product); } ?>
    <?php else: ?>
      <p style="text-align:center;">No products found.</p>
    <?php endif; ?>
  </div>
<?php endif; ?>

<?php if (!empty($all_products)): ?>
  <h1 style="text-align:center;">🛍️ All Products</h1>
  <div class="catalog">
    <?php foreach ($all_products as $product) { renderProductCard($conn, $product); } ?>
  </div>

  <!-- Pagination after all products -->
  <div class="pagination">
    <?php if ($page > 1): ?>
      <a href="?page=<?= $page-1 ?>">« Prev</a>
    <?php else: ?>
      <span class="disabled">« Prev</span>
    <?php endif; ?>

    <?php for ($i = 1; $i <= max(1, $total_pages); $i++): ?>
      <a href="?page=<?= $i ?>" class="<?= $i == $page ? 'active' : '' ?>"><?= $i ?></a>
    <?php endfor; ?>

    <?php if ($page < $total_pages): ?>
      <a href="?page=<?= $page+1 ?>">Next »</a>
    <?php else: ?>
      <span class="disabled">Next »</span>
    <?php endif; ?>
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

  <!-- ✅ Pagination under Related Products -->
  <div class="pagination">
    <?php if ($page > 1): ?>
      <a href="?id=<?= $product_id ?>&page=<?= $page-1 ?>">« Prev</a>
    <?php else: ?>
      <span class="disabled">« Prev</span>
    <?php endif; ?>

    <?php for ($i = 1; $i <= max(1, $total_related_pages); $i++): ?>
      <a href="?id=<?= $product_id ?>&page=<?= $i ?>" class="<?= $i == $page ? 'active' : '' ?>"><?= $i ?></a>
    <?php endfor; ?>

    <?php if ($page < $total_related_pages): ?>
      <a href="?id=<?= $product_id ?>&page=<?= $page+1 ?>">Next »</a>
    <?php else: ?>
      <span class="disabled">Next »</span>
    <?php endif; ?>
  </div>
<?php endif; ?>

<?php include 'footer.php' ?>

<script>
function showSizes(productId, variants, color, image) {
  const imgElem = document.getElementById('variantImage' + productId);
  if (imgElem) imgElem.src = image;

  let filtered = variants.filter(v => v.color === color);
  let sizeButtons = document.getElementById('sizeButtons' + productId);
  if (!sizeButtons) return;
  sizeButtons.innerHTML = '';

  filtered.forEach(variant => {
    let btn = document.createElement('button');
    btn.textContent = variant.size;
    btn.onclick = function () {
      let sizeElem = document.getElementById('selectedSize' + productId);
      let stockElem = document.getElementById('selectedStock' + productId);
      let priceElem = document.getElementById('selectedPrice' + productId);

      if (sizeElem) sizeElem.textContent = variant.size;
      if (stockElem) stockElem.textContent = variant.stock;
      if (priceElem) priceElem.textContent = parseFloat(variant.price).toFixed(2);
    };
    sizeButtons.appendChild(btn);
  });
}
</script>

</body>
</html>
<?php $conn->close(); ?>
