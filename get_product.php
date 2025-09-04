<?php
//$servername = "localhost";
//$username = "root";
//$password = "";
//$dbname = "bb";

//$conn = new mysqli($servername, $username, "", $dbname);
//if ($conn->connect_error) {
  //die("Connection failed: " . $conn->connect_error);
//}
require_once 'database.php';
$searchTerm = isset($_POST['search']) ? trim(strtolower($_POST['search'])) : '';

// Unified query
$sql = "
  SELECT 
    products.id,
    products.name,
    products.description,
    products.image,
    products.category,
    MIN(product_variants.price) AS price
  FROM products
  LEFT JOIN product_variants ON products.id = product_variants.product_id
  WHERE (
    ? = '' OR
    LOWER(products.name) LIKE CONCAT('%', ?, '%') OR
    LOWER(products.description) LIKE CONCAT('%', ?, '%') OR
    LOWER(products.category) LIKE CONCAT('%', ?, '%') OR
    SOUNDEX(products.name) = SOUNDEX(?) OR
    SOUNDEX(products.category) = SOUNDEX(?)
  )
  GROUP BY products.id
  ORDER BY products.created_at DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssss", $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    $product_id = $row['id'];
    $image = !empty($row["image"]) ? $row["image"] : "uploads/default.jpg";
    $name = htmlspecialchars($row["name"]);
    $desc = htmlspecialchars($row["description"]);
    $category = htmlspecialchars($row["category"]);
    $price = number_format($row["price"], 2);

    // Fetch distinct colors
    $color_sql = "SELECT DISTINCT color FROM product_variants WHERE product_id = ?";
    $color_stmt = $conn->prepare($color_sql);
    $color_stmt->bind_param("i", $product_id);
    $color_stmt->execute();
    $color_result = $color_stmt->get_result();

    $color_html = '<div class="color-options">';
    while ($color_row = $color_result->fetch_assoc()) {
      $color = htmlspecialchars($color_row['color']);
      $color_html .= "<button class='color-btn' style='background-color:$color;' data-color='$color' data-product='$product_id'></button>";
    }
    $color_html .= '</div>';

    echo "<div class='product-card' data-id='$product_id'>
            <img src='$image' alt='$name'>
            <h4>$name</h4>
            <p>$desc</p>
            <p><strong>Category:</strong> $category</p>
            <p class='price'>₦$price</p>
            $color_html
            <div class='size-options'></div>
            <div class='stock-price-info'></div>
            <div class='actions'>
              <button class='add-to-cart-btn'><i class='fas fa-cart-plus'></i> Add to Cart</button>
            </div>
          </div>";
    $color_stmt->close();
  }
} else {
  echo "<p>No products found.</p>";
}

$stmt->close();
$conn->close();
?>
