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
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background: #f0f2f5;
      margin: 0;
      padding: 20px;
    }
    .catalog {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
      gap: 20px;
    }
    .product {
      background: #fff;
      border-radius: 10px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.05);
      padding: 15px;
    }
    .product img {
      width: 100%;
      border-radius: 6px;
      margin-bottom: 10px;
    }
    .product h2 {
      font-size: 16px;
      margin: 0 0 8px;
    }
    .product p {
      font-size: 13px;
      margin: 4px 0;
      color: #444;
    }
    .color-swatch {
      display: inline-block;
      width: 18px;
      height: 18px;
      border-radius: 50%;
      margin: 3px;
      border: 1px solid #ccc;
      cursor: pointer;
    }
    .size-buttons {
      display: flex;
      flex-wrap: wrap;
      gap: 6px;
      margin-top: 10px;
    }
    .size-btn {
      background: #f8f9fa;
      border: 1px solid #007bff;
      color: #007bff;
      padding: 4px 10px;
      border-radius: 4px;
      font-size: 12px;
      cursor: pointer;
    }
    .size-btn:hover {
      background: #007bff;
      color: white;
    }
    .add-to-cart {
      margin-top: 10px;
      background: #28a745;
      color: white;
      padding: 6px 12px;
      border-radius: 4px;
      font-size: 13px;
      cursor: pointer;
      border: none;
      width: 100%;
    }
  </style>
</head>
<body>
<?php include 'header.php'?>
<h1 style="text-align:center;">🛍️ Product Details</h1>

<!-- ✅ Selected Product Card -->
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

<h2 style="margin-top:40px;">Related Products in "<?= htmlspecialchars($category) ?>"</h2>

<!-- ✅ Related Product Cards -->
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
        <button class="add-to-cart" onclick="addToCart(<?= $product['id'] ?>)">🛒 Add to Cart</button>
      </div>

      <?php $variant_stmt->close(); ?>
    </div>
  <?php endwhile; ?>
</div>

<?php $conn->close(); ?>
<?php include 'footer.php'?>
<script>
function showSizes(productId, variants, color, image) {
  const imageTag = document.getElementById('variantImage' + productId);
  const sizeContainer = document.getElementById('sizeButtons' + productId);
  const selectedSize = document.getElementById('selectedSize' + productId);
  const selectedStock = document.getElementById('selectedStock' + productId);
  const selectedPrice = document.getElementById('selectedPrice' + productId);

  imageTag.src = image;
  selectedSize.textContent = 'None';
  selectedStock.textContent = '0';
  selectedPrice.textContent = '0.00';
  sizeContainer.innerHTML = '';

  const filtered = variants.filter(v => v.product_id == productId && v.color === color);

  filtered.forEach(v => {
    const btn = document.createElement('button');
    btn.textContent = v.size;
    btn.className = 'size-btn';
    btn.onclick = () => {
      selectedSize.textContent = v.size;
      selectedStock.textContent = v.stock;
      selectedPrice.textContent = parseFloat(v.price).toFixed(2);
    };
    sizeContainer.appendChild(btn);
  });
}

function addToCart(productId) {
  const size = document.getElementById('selectedSize' + productId).textContent;
  const stock = document.getElementById('selectedStock' + productId).textContent;
  const price = document.getElementById('selectedPrice' + productId).textContent;

  if (size === 'None') {
    alert("Please select a size before adding to cart.");
    return;
  }

  alert(`Added to cart:\nProduct ID: ${productId}\nSize: ${size}\nStock: ${stock}\nPrice: ₦${price}`);
}
</script>

</body>
</html>
