<section class="products" id="productList">
<?php
require_once 'database.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Fetch all product variants with stock = 0
$sql = "
  SELECT pv.id, pv.product_id, pv.size, pv.color, pv.price, pv.stock, pv.color_image, pv.created_at,
         p.name AS product_name, p.image AS product_image
  FROM product_variants pv
  JOIN products p ON pv.product_id = p.id
  WHERE pv.stock = 0
  ORDER BY pv.created_at DESC
";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Out of Stock Products</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      margin: 0;
      padding: 0;
      background-color: #f9f9f9;
    }
    header {
      background-color: #333;
      color: #fff;
      padding: 15px;
      text-align: center;
    }
    .scroll-container {
      max-height: 80vh;
      overflow-y: auto;
      padding: 20px;
    }
    .variant-card {
      background: #fff;
      border-radius: 8px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
      padding: 15px;
      margin-bottom: 20px;
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      gap: 20px;
    }
    .variant-card img {
      width: 120px;
      height: auto;
      border-radius: 6px;
    }
    .variant-details {
      flex: 1;
      min-width: 200px;
    }
    .variant-details p {
      margin: 6px 0;
    }
    .out-of-stock-label {
      color: red;
      font-weight: bold;
    }
    @media (max-width: 600px) {
      .variant-card {
        flex-direction: column;
        align-items: flex-start;
      }
      .variant-card img {
        width: 100%;
        max-width: 300px;
      }
    }
  </style>
</head>
<body>

<header>
  <h1><i class="fas fa-exclamation-circle"></i> Out of Stock Products</h1>
</header>

<div class="scroll-container">
  <?php if ($result && $result->num_rows > 0): ?>
    <?php while ($row = $result->fetch_assoc()): ?>
      <div class="variant-card">
        <img src="<?= htmlspecialchars($row['product_image']) ?>" alt="<?= htmlspecialchars($row['product_name']) ?>">
        <div class="variant-details">
          <h3><?= htmlspecialchars($row['product_name']) ?></h3>
          <p><strong>Product ID:</strong> <?= htmlspecialchars($row['product_id']) ?></p>
          <p><strong>Size:</strong> <?= htmlspecialchars($row['size']) ?></p>
          <p><strong>Color:</strong> <?= htmlspecialchars($row['color']) ?></p>
          <p><strong>Price:</strong> ₦<?= number_format($row['price'], 2) ?></p>
          <p><strong>Status:</strong> <span class="out-of-stock-label">Out of Stock</span></p>
          <p><strong>Last Updated:</strong> <?= htmlspecialchars($row['created_at']) ?></p>
        </div>
      </div>
    <?php endwhile; ?>
  <?php else: ?>
    <p style="text-align:center; font-weight:bold;">🎉 All products are currently in stock!</p>
  <?php endif; ?>
</div>

</body>
</html>

<?php $conn->close(); ?>
