<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Include database connection
  require_once 'database.php'; // ✅ Fixed missing semicolon

  $upload_dir = "uploads";
  if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
  }

  // Collect and sanitize input
  $name = trim($_POST['product_name'] ?? '');
  $description = trim($_POST['description'] ?? '');
  $availability = $_POST['availability'] ?? '';
  $category = $_POST['category'] ?? '';

  // Validate required fields
  $missing = [];
  if ($name === '') $missing[] = "Product Name";
  if ($description === '') $missing[] = "Description";
  if ($availability === '') $missing[] = "Availability";
  if ($category === '') $missing[] = "Category";
  if (!empty($missing)) {
    die("❌ Missing required fields: " . implode(", ", $missing));
  }

  // Handle main image upload
  if (isset($_FILES['main_image']) && $_FILES['main_image']['error'] === UPLOAD_ERR_OK) {
    $main_name = basename($_FILES['main_image']['name']);
    $main_tmp = $_FILES['main_image']['tmp_name'];
    $main_path = $upload_dir . "/" . uniqid("main_") . "_" . $main_name;
    move_uploaded_file($main_tmp, $main_path);
  } else {
    die("❌ No main image uploaded.");
  }

  // Insert product into database
  $stmt = $conn->prepare("INSERT INTO products (name, description, image, availability, category, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
  $stmt->bind_param("sssss", $name, $description, $main_path, $availability, $category);
  $stmt->execute();
  $product_id = $stmt->insert_id;
  $stmt->close();

  // Helper function to restructure $_FILES array
  function restructureFilesArray($fileArray) {
    $result = [];
    if (!empty($fileArray['name']) && is_array($fileArray['name'])) {
      foreach ($fileArray['name'] as $index => $fields) {
        foreach ($fields as $fieldName => $value) {
          $result[$index][$fieldName] = [
            'name' => $fileArray['name'][$index][$fieldName],
            'type' => $fileArray['type'][$index][$fieldName],
            'tmp_name' => $fileArray['tmp_name'][$index][$fieldName],
            'error' => $fileArray['error'][$index][$fieldName],
            'size' => $fileArray['size'][$index][$fieldName]
          ];
        }
      }
    }
    return $result;
  }

  // Handle color variants
  $colors = $_POST['colors'] ?? [];
  $restructuredFiles = restructureFilesArray($_FILES['colors'] ?? []);

  if (!empty($colors) && is_array($colors)) {
    foreach ($colors as $index => $colorData) {
      $color = trim($colorData['name'] ?? '');
      $color_image_path = '';

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
        $size = trim($sizes[$i] ?? '');
        $price = $prices[$i] ?? '';
        $stock = $stocks[$i] ?? '';

        if ($size !== '' && $price !== '' && $stock !== '') {
          $variant_stmt = $conn->prepare("INSERT INTO product_variants (product_id, size, color, price, stock, color_image) VALUES (?, ?, ?, ?, ?, ?)");
          $variant_stmt->bind_param("issdis", $product_id, $size, $color, $price, $stock, $color_image_path);
          $variant_stmt->execute();
          $variant_stmt->close();
        }
      }
    }
  }

  $conn->close();
  echo "✅ Product and variants uploaded successfully!";
}
?>



<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Upload Product</title>
  <style>
    @keyframes fadeInUp {
      from { opacity: 0; transform: translateY(30px); }
      to { opacity: 1; transform: translateY(0); }
    }

    @keyframes pulse {
      0% { transform: scale(1); }
      50% { transform: scale(1.02); }
      100% { transform: scale(1); }
    }

    body {
      font-family: 'Segoe UI', sans-serif;
      background: #f4f6f8;
      padding: 40px;
      min-height: 100vh;
      display: flex;
      justify-content: center;
      align-items: flex-start;
    }

    form {
      background: #fff;
      padding: 40px;
      border-radius: 12px;
      max-width: 900px;
      width: 100%;
      box-shadow: 0 4px 20px rgba(0,0,0,0.05);
      animation: fadeInUp 0.6s ease-out;
      overflow-y: auto;
      max-height: 90vh;
    }

    h2, h3, h4 {
      margin-bottom: 20px;
      color: #1e1e2f;
    }

    label {
      font-weight: bold;
      margin-top: 15px;
      display: block;
      font-size: 15px;
      color: #333;
    }

    input, textarea, select {
      width: 100%;
      padding: 10px;
      margin-top: 8px;
      border-radius: 6px;
      border: 1px solid #ccc;
      font-size: 15px;
    }

    textarea {
      resize: vertical;
      min-height: 80px;
    }

    table {
      width: 100%;
      margin-top: 20px;
      border-collapse: collapse;
    }

    th, td {
      border: 1px solid #ddd;
      padding: 10px;
      vertical-align: top;
      font-size: 14px;
    }

    th {
      background: #f0f0f0;
    }

    button {
      background: #f0c040;
      color: white;
      padding: 10px 16px;
      border: none;
      border-radius: 6px;
      margin-top: 20px;
      cursor: pointer;
      font-weight: bold;
      font-size: 15px;
      animation: pulse 1.5s infinite;
      transition: background 0.3s ease;
    }

    button:hover {
      background: #d9a82d;
    }

    .color-block {
      margin-bottom: 30px;
      padding: 20px;
      background: #fafafa;
      border-radius: 8px;
      box-shadow: 0 0 5px rgba(0,0,0,0.05);
    }
  </style>
</head>
<body>

<form action="upload_latest_product.php" method="POST" enctype="multipart/form-data">
  <h2>Upload Product</h2>

  <label>Category:</label>
  <select name="category" required>
    <option value="Household Items">Household Items</option>
    <option value="Bags">Bags</option>
    <option value="Kiddies">Kiddies</option>
    <option value="Footwears">Footwears</option>
    <option value="Men's Clothing">Men's Clothing</option>
    <option value="Women's Clothing">Women's Clothing</option>
    <option value="Watches/Glasses">Watches/Glasses</option>
    <option value="Underwears(Male & Female)">Underwears(Male & Female)</option>
    <option value="Turtlenecks">Turtlenecks</option>
    <option value="Night wears">Night wears</option>
    <option value="Giveaway/Discount">Giveaway/Discount</option>
  </select>

  <label>Product Name:</label>
  <input type="text" name="product_name" required>

  <label>Description:</label>
  <textarea name="description" required></textarea>

  <label>Main Image:</label>
  <input type="file" name="main_image" accept="image/*" required>

  <label>Availability:</label>
  <select name="availability" required>
    <option value="Available">Available</option>
    <option value="Out of Stock">Out of Stock</option>
    <option value="Coming Soon">Coming Soon</option>
  </select>

  <h3>Product Variants</h3>
  <div id="colorBlocks"></div>
  <button type="button" onclick="addColorBlock()">➕ Add Color</button>

  <br><br>
  <button type="submit">Upload Product</button>
</form>

<script>
function addColorBlock() {
  const container = document.getElementById("colorBlocks");
  const colorIndex = container.children.length;

  const block = document.createElement("div");
  block.classList.add("color-block");
  block.style.marginBottom = "30px";
  block.innerHTML = `
    <label>Color Name:</label>
    <input type="text" name="colors[${colorIndex}][name]" required>

    <label>Color Image:</label>
    <input type="file" name="colors[${colorIndex}][image]" accept="image/*" required>

    <h4>Sizes for this color</h4>
    <table id="sizeTable_${colorIndex}">
      <tr>
        <th>Size</th>
        <th>Price</th>
        <th>Stock</th>
        <th>Action</th>
      </tr>
    </table>
    <button type="button" onclick="addSizeRow(${colorIndex})">➕ Add Size</button>
    <hr>
  `;
  container.appendChild(block);
  addSizeRow(colorIndex); // Add one default row
}

function addSizeRow(colorIndex) {
  const table = document.getElementById(`sizeTable_${colorIndex}`);
  const row = table.insertRow(-1);
  row.innerHTML = `
    <td><input type="text" name="colors[${colorIndex}][sizes][]" required></td>
    <td><input type="number" name="colors[${colorIndex}][prices][]" step="0.01" required></td>
    <td><input type="number" name="colors[${colorIndex}][stocks][]" required></td>
    <td><button type="button" onclick="removeRow(this)">🗑️ Remove</button></td>
  `;
}

function removeRow(button) {
  const row = button.parentNode.parentNode;
  row.parentNode.removeChild(row);
}
</script>

</body>
</html>
