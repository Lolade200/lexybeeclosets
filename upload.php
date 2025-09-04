<?php
// db.php — Database connection
$servername = "localhost";
$username = "root"; // Default for XAMPP
$password = "";     // Default for XAMPP
$database = "lexy";

$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Optional date filter
$whereClause = "";
if (!empty($_GET['from']) && !empty($_GET['to'])) {
    $from = $_GET['from'];
    $to = $_GET['to'];
    $whereClause = "WHERE p.date_uploaded BETWEEN '$from 00:00:00' AND '$to 23:59:59'";
}

// Display products with category name and date
$result = $conn->query("
    SELECT p.*, c.name AS category_name
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    $whereClause
    ORDER BY p.date_uploaded DESC
");

echo "<h2>All Products</h2>";
echo "<form method='GET'>
        <label>From:</label>
        <input type='date' name='from' required>
        <label>To:</label>
        <input type='date' name='to' required>
        <button type='submit'>Filter</button>
      </form><br>";

echo "<div class='table-container'><table border='1' cellpadding='10'>
        <tr>
            <th>Image</th>
            <th>Name</th>
            <th>Category</th>
            <th>Price</th>
            <th>Date Uploaded</th>
            <th>Actions</th>
        </tr>";

while ($row = $result->fetch_assoc()) {
    $formattedDate = date("F j, Y g:i A", strtotime($row['date_uploaded']));
    echo "<tr>
            <td><img src='{$row['image']}' width='60' /></td>
            <td>{$row['name']}</td>
            <td>{$row['category_name']}</td>
            <td>₦{$row['price']}</td>
            <td>{$formattedDate}</td>
            <td>
                <a href='edit_product.php?id={$row['id']}' class='action-btn edit-btn'>Edit</a>
                <a href='delete_product.php?id={$row['id']}' class='action-btn delete-btn' onclick='return confirm(\"Are you sure you want to delete this product?\")'>Delete</a>
            </td>
          </tr>";
}
echo "</table></div>";
?>

<!DOCTYPE html>
<html>
<head>
  <title>Admin Product Upload</title>
  <link rel="stylesheet" href="upload.css">
  <style>
    body { font-family: Arial; padding: 20px; background: #f4f4f4; }
    form, table { background: #fff; padding: 20px; border-radius: 8px; margin-bottom: 30px; }
    input, select, textarea { width: 100%; margin-bottom: 10px; padding: 8px; }
    button { padding: 10px 20px; background: #007bff; color: white; border: none; cursor: pointer; }
    img { width: 60px; border-radius: 4px; }
    table { width: 100%; border-collapse: collapse; }
    th, td { padding: 10px; border: 1px solid #ccc; text-align: left; }
    h2 { margin-bottom: 20px; }
    .action-btn {
      padding: 8px 16px;
      margin-right: 5px;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      text-decoration: none;
      font-size: 14px;
    }
    .edit-btn { background-color: #28a745; color: white; }
    .delete-btn { background-color: #dc3545; color: white; }
    .table-container {
      max-height: 400px;
      overflow-y: auto;
      border-radius: 8px;
      box-shadow: inset 0 0 5px rgba(0,0,0,0.1);
    }
  </style>
</head>
<body>

<h2>Upload New Product</h2>
<form action="create_product.php" method="POST" enctype="multipart/form-data">
  <label for="category">Category:</label>
  <select name="category" required>
    <option value="">--Select Category--</option>
    <?php
    $catResult = $conn->query("SELECT id, name FROM categories ORDER BY name ASC");
    while ($cat = $catResult->fetch_assoc()) {
        echo "<option value='{$cat['id']}'>{$cat['name']}</option>";
    }
    ?>
  </select>

  <input type="text" name="name" placeholder="Product Name" required />
  <textarea name="description" placeholder="Description" required></textarea>
  <input type="number" name="price" placeholder="Price (₦)" step="0.01" required />
  <input type="text" name="sizes" placeholder="Sizes (e.g. S, M, L)" />
  <input type="text" name="colors" placeholder="Colors (e.g. Red, Blue)" />
  <input type="file" name="image" accept="image/*" required />
  <button type="submit">Upload Product</button>
</form>

</body>
</html>
