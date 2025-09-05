<?php
require_once 'database.php';
//$conn = new mysqli("localhost", "root", "", "bbbb");
//if ($conn->connect_error) {
//  die("Connection failed: " . $conn->connect_error);
//}

$upload_dir = "uploads";
if (!file_exists($upload_dir)) {
  mkdir($upload_dir, 0777, true);
}

$product = [];
$variants = [];
$all_products = [];

$search_query = $_GET['search'] ?? '';
$selected_id = $_GET['product_id'] ?? '';

$where = $search_query ? "WHERE name LIKE '%$search_query%' OR id = '$search_query'" : "";
$result = $conn->query("SELECT * FROM products $where ORDER BY created_at DESC");

while ($row = $result->fetch_assoc()) {
  $all_products[] = $row;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $product_id = intval($_POST['product_id']);
  $name = $_POST['product_name'];
  $description = $_POST['description'];
  $availability = $_POST['availability'];
  $category = $_POST['category'];
  $created_at = $_POST['created_at'] ?? date('Y-m-d H:i:s');
  $main_path = $_POST['existing_image'];

  if (isset($_FILES['main_image']) && $_FILES['main_image']['error'] === UPLOAD_ERR_OK) {
    $main_name = basename($_FILES['main_image']['name']);
    $main_tmp = $_FILES['main_image']['tmp_name'];
    $main_path = $upload_dir . "/" . uniqid("main_") . "_" . $main_name;
    move_uploaded_file($main_tmp, $main_path);
  }

  $stmt = $conn->prepare("UPDATE products SET name=?, description=?, image=?, availability=?, category=?, created_at=? WHERE id=?");
  $stmt->bind_param("ssssssi", $name, $description, $main_path, $availability, $category, $created_at, $product_id);
  $stmt->execute();
  $stmt->close();

  $conn->query("DELETE FROM product_variants WHERE product_id = $product_id");

  function restructureFilesArray($fileArray) {
    $result = [];
    foreach ($fileArray['name'] as $index => $fields) {
      foreach ($fields as $fieldName => $value) {
        $result[$index][$fieldName]['name'] = $fileArray['name'][$index][$fieldName];
        $result[$index][$fieldName]['type'] = $fileArray['type'][$index][$fieldName];
        $result[$index][$fieldName]['tmp_name'] = $fileArray['tmp_name'][$index][$fieldName];
        $result[$index][$fieldName]['error'] = $fileArray['error'][$index][$fieldName];
        $result[$index][$fieldName]['size'] = $fileArray['size'][$index][$fieldName];
      }
    }
    return $result;
  }

  $colors = $_POST['colors'] ?? [];
  $restructuredFiles = restructureFilesArray($_FILES['colors'] ?? []);

  foreach ($colors as $index => $colorData) {
    $color = $colorData['name'];
    $color_image_path = '';
    $variant_created_at = $_POST['variant_created_at'] ?? date('Y-m-d H:i:s');

    $colorImageFile = $restructuredFiles[$index]['image'] ?? null;
    if ($colorImageFile && $colorImageFile['error'] === UPLOAD_ERR_OK) {
      $color_image_name = basename($colorImageFile['name']);
      $color_image_tmp = $colorImageFile['tmp_name'];
      $color_image_path = $upload_dir . "/" . uniqid("color_") . "_" . $color_image_name;
      move_uploaded_file($color_image_tmp, $color_image_path);
    }

    $sizes = $colorData['sizes'] ?? [];
    $prices = $colorData['prices'] ?? [];
    $stocks = $colorData['stocks'] ?? [];

    for ($i = 0; $i < count($sizes); $i++) {
      $size = $sizes[$i];
      $price = $prices[$i];
      $stock = $stocks[$i];

      $variant_stmt = $conn->prepare("INSERT INTO product_variants (product_id, size, color, price, stock, color_image, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)");
      $variant_stmt->bind_param("issdiss", $product_id, $size, $color, $price, $stock, $color_image_path, $variant_created_at);
      $variant_stmt->execute();
      $variant_stmt->close();
    }
  }

  echo "<p style='color:green;'>✅ Product updated successfully!</p>";
} elseif ($selected_id) {
  $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
  $stmt->bind_param("i", $selected_id);
  $stmt->execute();
  $product = $stmt->get_result()->fetch_assoc();
  $stmt->close();

  $variant_stmt = $conn->prepare("SELECT * FROM product_variants WHERE product_id = ?");
  $variant_stmt->bind_param("i", $selected_id);
  $variant_stmt->execute();
  $variants = $variant_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
  $variant_stmt->close();
}
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Product Manager</title>
  <style>
body {
  font-family: Arial;
  margin: 20px;
  background: #f4f4f4;
}

/* Scrollable containers */
form.update-form,
.product-list {
  background: #fff;
  padding: 20px;
  border-radius: 8px;
  margin-bottom: 20px;
  box-shadow: 0 0 10px rgba(0,0,0,0.1);
  max-height: 500px;         /* Limit height */
  overflow-y: auto;          /* Enable vertical scroll */
  scrollbar-width: thin;     /* Firefox scrollbar */
  scrollbar-color: #ccc #fff;
}

/* WebKit scrollbar styling */
form.update-form::-webkit-scrollbar,
.product-list::-webkit-scrollbar {
  width: 8px;
}
form.update-form::-webkit-scrollbar-thumb,
.product-list::-webkit-scrollbar-thumb {
  background-color: #ccc;
  border-radius: 4px;
}

label {
  display: block;
  margin-top: 10px;
  font-weight: bold;
}

input, select, textarea {
  width: 100%;
  padding: 8px;
  margin-top: 5px;
  border: 1px solid #ccc;
  border-radius: 4px;
}

button {
  padding: 8px 12px;
  margin-top: 10px;
  background: #3498db;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

button:hover {
  background: #2980b9;
}

.product-list ul {
  list-style: none;
  padding: 0;
}

.product-list li {
  padding: 8px;
  border-bottom: 1px solid #ddd;
  display: flex;
  justify-content: space-between;
}

.color-block {
  border: 1px solid #ccc;
  padding: 10px;
  margin-top: 10px;
  background: #fefefe;
  border-radius: 6px;
}

table {
  width: 100%;
  margin-top: 10px;
  border-collapse: collapse;
}

th, td {
  border: 1px solid #ccc;
  padding: 6px;
  text-align: left;
}

th {
  background: #ecf0f1;
}

  </style>
</head>
<body>

<h2>🔍 Search Products</h2>
<h2 >    <a href="index.php">Home</a></h2>
<form method="GET">
  <input type="text" name="search" placeholder="Search by name or ID" value="<?= htmlspecialchars($search_query) ?>">
  <button type="submit">Search</button>
</form>
<div class="product-list">
  <h3>📋 Product List</h3>
  <?php foreach ($all_products as $p): ?>
    <div style="border:1px solid #ccc; padding:15px; margin-bottom:10px; background:#fff; border-radius:6px;">
      <p><strong>ID:</strong> <?= $p['id'] ?></p>
      <p><strong>Name:</strong> <?= htmlspecialchars($p['name']) ?></p>
      <p><strong>Description:</strong> <?= htmlspecialchars($p['description']) ?></p>
      <p><strong>Availability:</strong> <?= $p['availability'] ?></p>
      <p><strong>Category:</strong> <?= $p['category'] ?></p>
      <p><strong>Created At:</strong> <?= $p['created_at'] ?></p>
      <p><strong>Image:</strong><br>
        <?php if (!empty($p['image'])): ?>
          <img src="<?= htmlspecialchars($p['image']) ?>" alt="Product Image" style="max-width:200px; border:1px solid #ddd;">
        <?php else: ?>
          <em>No image uploaded</em>
        <?php endif; ?>
      </p>
      <p><a href="?product_id=<?= $p['id'] ?>">✏️ Edit Product</a></p>
    </div>
  <?php endforeach; ?>
</div>


<?php if (!empty($product)): ?>
<form method="POST" enctype="multipart/form-data" class="update-form">
  <h2>✏️ Update Product</h2>
  <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
  <input type="hidden" name="existing_image" value="<?= $product['image'] ?>">

  <label>Product Name:</label>
  <input type="text" name="product_name" value="<?= htmlspecialchars($product['name']) ?>" required>

  <label>Description:</label>
  <textarea name="description" required><?= htmlspecialchars($product['description']) ?></textarea>

  <label>Main Image:</label>
  <input type="file" name="main_image" accept="image/*">
  <p>Current: <?= $product['image'] ?></p>

  <label>Availability:</label>
  <select name="availability" required>
    <option value="Available" <?= $product['availability'] === 'Available' ? 'selected' : '' ?>>Available</option>
    <option value="Out of Stock" <?= $product['availability'] === 'Out of Stock' ? 'selected' : '' ?>>Out of Stock</option>
  </select>

  <label>Category:</label>
  <input type="text" name="category" value="<?= htmlspecialchars($product['category'])
    ?>" required>
      <label>Created At:</label>
  <input type="datetime-local" name="created_at" value="<?= date('Y-m-d\TH:i', strtotime($product['created_at'])) ?>" required>

  <h3>🧩 Product Variants</h3>
  <div id="colorBlocks"></div>
  <button type="button" onclick="addColorBlock()">➕ Add Color</button>
  <br><br>
  <button type="submit">✅ Update Product</button>
</form>
<?php endif; ?>

<script>
const variants = <?= json_encode($variants) ?>;

function addColorBlock(data = {}) {
  const container = document.getElementById("colorBlocks");
  const colorIndex = container.children.length;

  const block = document.createElement("div");
  block.classList.add("color-block");
  block.setAttribute("data-color", data.color || `color-${colorIndex}`);
  block.innerHTML = `
    <label>Color Name:</label>
    <input type="text" name="colors[${colorIndex}][name]" value="${data.color || ''}" required>

    <label>Color Image:</label>
    <input type="file" name="colors[${colorIndex}][image]" accept="image/*">

    <label>Variant Created At:</label>
    <input type="datetime-local" name="variant_created_at" value="${new Date().toISOString().slice(0,16)}">

    <h4>Sizes for this color</h4>
    <table id="sizeTable_${colorIndex}">
      <tr><th>Size</th><th>Price</th><th>Stock</th><th>Action</th></tr>
    </table>
    <button type="button" onclick="addSizeRow(${colorIndex})">➕ Add Size</button>
    <hr>
  `;
  container.appendChild(block);

  if (data.sizes && data.sizes.length) {
    for (let i = 0; i < data.sizes.length; i++) {
      addSizeRow(colorIndex, data.sizes[i], data.prices[i], data.stocks[i]);
    }
  }
}

function addSizeRow(colorIndex, size = '', price = '', stock = '') {
  const table = document.getElementById(`sizeTable_${colorIndex}`);
  const row = table.insertRow(-1);
  row.innerHTML = `
    <td><input type="text" name="colors[${colorIndex}][sizes][]" value="${size}" required></td>
    <td><input type="number" name="colors[${colorIndex}][prices][]" value="${price}" step="0.01" required></td>
    <td><input type="number" name="colors[${colorIndex}][stocks][]" value="${stock}" required></td>
    <td><button type="button" onclick="removeRow(this)">❌ Remove</button></td>
  `;
}

function removeRow(button) {
  button.closest("tr").remove();
}

// Group variants by color
const grouped = {};
variants.forEach(v => {
  if (!grouped[v.color]) {
    grouped[v.color] = { color: v.color, sizes: [], prices: [], stocks: [] };
  }
  grouped[v.color].sizes.push(v.size);
  grouped[v.color].prices.push(v.price);
  grouped[v.color].stocks.push(v.stock);
});

// Preload existing variants
Object.values(grouped).forEach(data => addColorBlock(data));
</script>
</body>
</html>
