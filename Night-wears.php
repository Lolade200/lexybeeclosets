<?php 
require_once 'database.php';
// Database connection
//$servername = "localhost";
//$username   = "root";
//$password   = "";
//$dbname     = "bbbb";

//$conn = new mysqli($servername, $username, $password, $dbname);
//if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

// Pagination setup
$limit  = 6;
$page   = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Category filter
$categoryFilter = "Night Wears"; // <-- updated category

// Count total products
$countSql = "SELECT COUNT(DISTINCT p.id) AS total FROM products p WHERE p.category = ?";
$countStmt = $conn->prepare($countSql);
$countStmt->bind_param("s", $categoryFilter);
$countStmt->execute();
$totalProducts = $countStmt->get_result()->fetch_assoc()['total'];
$countStmt->close();
$totalPages = ceil($totalProducts / $limit);

// Main query
$sql = "
    SELECT 
        p.id, p.name, p.description, p.image, p.category,
        COALESCE(MIN(v.price), 0) AS price
    FROM products p
    LEFT JOIN product_variants v ON p.id = v.product_id
    WHERE p.category = ?
    GROUP BY p.id
    ORDER BY p.created_at DESC
    LIMIT ? OFFSET ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sii", $categoryFilter, $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Night Wears</title>
<link rel="stylesheet" href="category.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<header>
<div class="logo">
    <img src="logoimg.jpg" alt="Lexxybee Logo">
    <h2>Lexybeeclosets</h2>
</div>
<nav>
    <a href="#">Login</a>
    <a href="#">Signup</a>
    <a href="#">About</a>
</nav>
</header>

<h1>Night Wears</h1>

<div class="product-grid">
<?php if ($result && $result->num_rows > 0): ?>
    <?php while ($row = $result->fetch_assoc()): ?>
        <?php
            $product_id = (int)$row['id'];
            $image      = !empty($row['image']) ? htmlspecialchars($row['image']) : "uploads/default.jpg";
            $name       = htmlspecialchars($row['name'] ?? '');
            $desc       = htmlspecialchars($row['description'] ?? '');
            $category   = htmlspecialchars($row['category'] ?? '');

            // Fetch default price and stock (first variant)
            $default_sql  = "SELECT price, stock FROM product_variants WHERE product_id = ? ORDER BY price ASC LIMIT 1";
            $default_stmt = $conn->prepare($default_sql);
            $default_stmt->bind_param("i", $product_id);
            $default_stmt->execute();
            $default_result = $default_stmt->get_result()->fetch_assoc();
            $default_price = $default_result ? number_format($default_result['price'], 2) : '0.00';
            $default_stock = $default_result ? $default_result['stock'] : '0';
            $default_stmt->close();

            // Fetch distinct colors
            $color_sql  = "SELECT DISTINCT color FROM product_variants WHERE product_id = ?";
            $color_stmt = $conn->prepare($color_sql);
            $color_stmt->bind_param("i", $product_id);
            $color_stmt->execute();
            $color_result = $color_stmt->get_result();

            $color_html = '<div class="color-options">';
            while ($color_row = $color_result->fetch_assoc()) {
                $color = htmlspecialchars($color_row['color'] ?? '');
                if ($color !== '') {
                    $color_html .= "<button class='color-btn' style='background-color:{$color};' data-color='{$color}' data-product='{$product_id}'></button>";
                }
            }
            $color_html .= "</div>";
            $color_stmt->close();

            $loginUrl = "login.php?next=" . urlencode("product.php?id=" . $product_id);
        ?>
        <div class="product-card" data-id="<?php echo $product_id; ?>">
            <img src="<?php echo $image; ?>" alt="<?php echo $name; ?>" width="200">
            <h4><?php echo $name; ?></h4>
            <p><?php echo $desc; ?></p>
            <p><strong>Category:</strong> <?php echo $category; ?></p>
            
            <?php echo $color_html; ?>

            <div class="size-options"></div>
            <div class="stock-price-info">
                <strong>Price:</strong> ₦<?php echo $default_price; ?> | 
                <strong>Stock:</strong> <?php echo $default_stock; ?>
            </div>

            <a href="<?php echo $loginUrl; ?>" class="buy-now-btn">
                <i class="fas fa-bolt"></i> Buy Now
            </a>
        </div>
    <?php endwhile; ?>
<?php else: ?>
    <p style="grid-column: 1 / -1; text-align:center;">No products found in this category.</p>
<?php endif; ?>
</div>

<!-- Pagination -->
<div class="pagination">
<?php for ($i = 1; $i <= $totalPages; $i++): ?>
    <a href="?page=<?php echo $i; ?>" class="<?php echo ($i === $page) ? 'active' : ''; ?>">
        <?php echo $i; ?>
    </a>
<?php endfor; ?>
</div>

<?php include 'footer.php'; ?>

<script>
// Color click: load sizes and update price
document.querySelectorAll('.color-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const color = this.dataset.color;
        const productId = this.dataset.product;
        const card = this.closest('.product-card');
        const sizeOptionsDiv = card.querySelector('.size-options');
        const stockPriceDiv = card.querySelector('.stock-price-info');

        fetch(`get_sizes.php?product_id=${productId}&color=${encodeURIComponent(color)}`)
            .then(res => res.json())
            .then(data => {
                if (data.length > 0) {
                    let sizesHtml = '';
                    data.forEach(item => {
                        sizesHtml += `<button class="size-btn" data-price="${item.price}" data-stock="${item.stock}">${item.size}</button>`;
                    });
                    sizeOptionsDiv.innerHTML = sizesHtml;

                    // Update price to first size of selected color
                    stockPriceDiv.innerHTML = `<strong>Price:</strong> ₦${data[0].price} | <strong>Stock:</strong> ${data[0].stock}`;

                    // Size button click
                    sizeOptionsDiv.querySelectorAll('.size-btn').forEach(sizeBtn => {
                        sizeBtn.addEventListener('click', function() {
                            const price = this.dataset.price;
                            const stock = this.dataset.stock;
                            stockPriceDiv.innerHTML = `<strong>Price:</strong> ₦${price} | <strong>Stock:</strong> ${stock}`;
                        });
                    });
                } else {
                    sizeOptionsDiv.innerHTML = 'No sizes available for this color.';
                    stockPriceDiv.innerHTML = '';
                }
            });
    });
});
</script>

<?php
$stmt->close();
$conn->close();
?>
</body>
</html>
