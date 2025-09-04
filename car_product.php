<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "bb";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get productId from URL
$productId = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($productId <= 0) {
    die("Invalid product.");
}

// Fetch product details from database
$stmt = $conn->prepare("SELECT * FROM products WHERE id=?");
$stmt->bind_param("i", $productId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Product not found.");
}

$product = $result->fetch_assoc();
$image = !empty($product['image']) ? $product['image'] : 'uploads/default.jpg';
$name = htmlspecialchars($product['name']);
$description = htmlspecialchars($product['description']);

// Fetch available colors for this product
$color_stmt = $conn->prepare("SELECT DISTINCT color FROM product_variants WHERE product_id=?");
$color_stmt->bind_param("i", $productId);
$color_stmt->execute();
$color_result = $color_stmt->get_result();
$colors = [];
while ($row = $color_result->fetch_assoc()) {
    $colors[] = $row['color'];
}
$color_stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?php echo $name; ?> - Lexxybee Store</title>
<link rel="stylesheet" href="first.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
.product-detail {
    max-width: 900px;
    margin: 40px auto;
    display: flex;
    flex-wrap: wrap;
    gap: 30px;
}
.product-detail img {
    width: 400px;
    object-fit: cover;
    border: 1px solid #ccc;
    border-radius: 10px;
}
.product-info {
    flex: 1;
}
.color-options button, .size-options button {
    margin: 5px;
    cursor: pointer;
}
.color-options button {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    border: 2px solid #ccc;
}
.size-options button {
    padding: 5px 10px;
}
.add-cart {
    margin-top: 20px;
    padding: 10px 20px;
    background-color: #28a745;
    color: white;
    border: none;
    cursor: pointer;
}
</style>
</head>
<body>

<?php include 'header.php'; ?>

<div class="product-detail">
    <img src="<?php echo $image; ?>" alt="<?php echo $name; ?>">
    <div class="product-info">
        <h2><?php echo $name; ?></h2>
        <p><?php echo $description; ?></p>
        <div class="color-options">
            <p><strong>Colors:</strong></p>
            <?php foreach ($colors as $color): ?>
                <button class="color-btn" style="background-color: <?php echo $color; ?>" data-color="<?php echo $color; ?>"></button>
            <?php endforeach; ?>
        </div>
        <div class="size-options">
            <p><strong>Sizes:</strong></p>
        </div>
        <p><strong>Selected Size:</strong> <span class="selected-size">None</span></p>
        <p><strong>Stock:</strong> <span class="selected-stock">0</span></p>
        <p><strong>Price:</strong> ₦<span class="selected-price">0.00</span></p>
        <form method="POST" action="add_to_cart.php" onsubmit="return prepareCartData(<?php echo $productId; ?>)">
            <input type="hidden" name="product_id" value="<?php echo $productId; ?>">
            <input type="hidden" name="selected_color" class="selected-color-input">
            <input type="hidden" name="selected_size" class="selected-size-input">
            <button type="submit" class="add-cart"><i class="fas fa-cart-plus"></i> Add to Cart</button>
        </form>
    </div>
</div>

<script>
// Load sizes dynamically based on color selection
document.querySelectorAll('.color-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const color = this.dataset.color;
        document.querySelector('.selected-color-input').value = color;

        fetch(`get_sizes.php?product_id=<?php echo $productId; ?>&color=${encodeURIComponent(color)}`)
            .then(res => res.json())
            .then(data => {
                const sizeDiv = document.querySelector('.size-options');
                sizeDiv.innerHTML = '';
                if (data.length > 0) {
                    data.forEach(item => {
                        const btn = document.createElement('button');
                        btn.textContent = item.size;
                        btn.dataset.size = item.size;
                        btn.dataset.stock = item.stock;
                        btn.dataset.price = item.price;
                        btn.addEventListener('click', function() {
                            document.querySelector('.selected-size').textContent = item.size;
                            document.querySelector('.selected-stock').textContent = item.stock;
                            document.querySelector('.selected-price').textContent = item.price;
                            document.querySelector('.selected-size-input').value = item.size;
                        });
                        sizeDiv.appendChild(btn);
                    });
                } else {
                    sizeDiv.textContent = 'No sizes available for this color.';
                }
            });
    });
});

// Add to cart from this page
let cart = JSON.parse(localStorage.getItem('cartData')) || [];
function prepareCartData(productId) {
    const color = document.querySelector('.selected-color-input').value;
    const size = document.querySelector('.selected-size-input').value;
    const name = document.querySelector('.product-info h2').textContent;
    const image = document.querySelector('.product-detail img').src;
    const price = document.querySelector('.selected-price').textContent;

    if (!color || !size || size === 'None') {
        alert('Please select both a color and size.');
        return false;
    }

    cart.push({ productId, name, color, size, image, price });
    localStorage.setItem('cartData', JSON.stringify(cart));
    alert('Added to cart!');
    return false;
}
</script>

<?php include 'footer.php'; ?>
</body>
</html>
