<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'database.php';

// Pagination setup
$limit = 7;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// Only show men's clothing category
$sql = "
    SELECT p.id, p.name, p.description, p.image, p.category
    FROM products p
    WHERE LOWER(p.category) = 'men''s clothing'
    ORDER BY p.created_at DESC
    LIMIT ? OFFSET ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();

// Count total men's clothing
$count_sql = "
    SELECT COUNT(*) AS total
    FROM products
    WHERE LOWER(category) = 'men''s clothing'
";
$count_result = $conn->query($count_sql);
$total_products = $count_result->fetch_assoc()['total'];
$total_pages = ($limit > 0) ? ceil($total_products / $limit) : 1;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Men's Clothing - LexybeeClosets</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <!-- Stylesheets -->
  <link rel="stylesheet" href="category2.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
  <!-- 🔷 Header Section -->
  <header>
    <div class="logo">
      <img src="logoimg.jpg" alt="Lexxybee Logo">
      <h2>LexybeeClosets</h2>
    </div>

    <!-- 🍔 Mobile Menu Toggle -->
    <button class="hamburger" onclick="toggleMenu()">
      <i class="fas fa-bars"></i>
    </button>

    <!-- 📌 Navigation Links -->
    <nav id="mobileMenu" class="nav-links">
       <a href="product_display.php">Home</a>
 
      <a href="about.php">About</a>
      <a href="logout.php">Logout</a>
    </nav>
  </header>

  <!-- 🛒 Cart Icon -->
  <a href="#" id="cartIcon" class="cart-icon">
    <i class="fas fa-shopping-cart"></i>
    <span id="cartCount" class="cart-count">0</span>
  </a>

  <!-- 🧺 Cart Panel -->
  <div id="cartPanel" class="cart-panel">
    <h3>Your Cart</h3>
    <div id="cartItems"></div>
    <button id="buyNowBtn">Buy Now</button>
  </div>

  <!-- 👔 Men's Clothing Product Grid -->
  <section class="products">
    <h2 style="text-align:center;">Men's Clothing Collection</h2>
    <div class="product-grid">
      <?php if ($result && $result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
          <?php
            $product_id = (int)$row['id'];
            $image      = !empty($row['image']) ? htmlspecialchars($row['image']) : "uploads/default.jpg";
            $name       = htmlspecialchars($row['name'] ?? '');
            $desc       = htmlspecialchars($row['description'] ?? '');
            $category   = htmlspecialchars($row['category'] ?? '');

            // Fetch minimum price + stock
            $default_sql  = "SELECT MIN(price) AS price, SUM(stock) AS stock FROM product_variants WHERE product_id = ?";
            $default_stmt = $conn->prepare($default_sql);
            $default_stmt->bind_param("i", $product_id);
            $default_stmt->execute();
            $default_result = $default_stmt->get_result()->fetch_assoc();
            $default_price = $default_result ? number_format($default_result['price'], 2) : '0.00';
            $default_stock = $default_result ? $default_result['stock'] : 0;
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
          ?>
          <div class="product-card" data-id="<?php echo $product_id; ?>">
            <img src="<?php echo $image; ?>" alt="<?php echo $name; ?>">
            <h4><?php echo $name; ?></h4>
            <p><?php echo $desc; ?></p>
            <p><strong>Category:</strong> <?php echo $category; ?></p>

            <?php echo $color_html; ?>

            <div class="size-options"></div>
            <p><strong>Selected Size:</strong> <span class="selected-size">None</span></p>
            <p><strong>Stock:</strong> <span class="selected-stock"><?php echo $default_stock ?: 'Out of stock'; ?></span></p>
            <p><strong>Price:</strong> ₦<span class="selected-price"><?php echo $default_price; ?></span></p>

            <!-- Add to Cart -->
            <form method="POST" action="add_to_cart.php" onsubmit="return prepareCartData(<?php echo $product_id; ?>)">
              <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
              <input type="hidden" name="selected_color" class="selected-color-input">
              <input type="hidden" name="selected_size" class="selected-size-input">
              <input type="number" class="quantity-input" min="1" value="1" style="width:60px;" placeholder="Qty">
              <button type="submit" class="btn1"><i class="fas fa-cart-plus"></i> Add to Cart</button>
            </form>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <p style="grid-column: 1 / -1; text-align:center;">No products found in this category.</p>
      <?php endif; ?>
    </div>
  </section>

  <!-- 📄 Pagination -->
  <div class="pagination-wrapper">
    <div class="pagination">
      <?php
      if ($total_pages > 1) {
        for ($i = 1; $i <= $total_pages; $i++) {
          $active = ($i === $page) ? 'class="active-page"' : '';
          echo "<a href='?page=$i' $active>$i</a>";
        }
      }
      ?>
    </div>
  </div>

  <!-- 🔚 Footer -->
  <?php include 'footer.php'; ?>

<script>
// Reset cart on load
document.addEventListener('DOMContentLoaded', () => {
  localStorage.removeItem('cartData');
  cart = [];
  updateCartUI();
});

// Color click → load sizes
document.addEventListener('click', function(e) {
  if (e.target.classList.contains('color-btn')) {
    const productId = e.target.dataset.product;
    const color = e.target.dataset.color;
    const card = e.target.closest('.product-card');
    const sizeOptionsDiv = card.querySelector('.size-options');
    const selectedColorInput = card.querySelector('.selected-color-input');
    selectedColorInput.value = color;

    fetch(`get_sizes.php?product_id=${productId}&color=${encodeURIComponent(color)}`)
      .then(res => res.json())
      .then(data => {
        if (data.length > 0) {
          let sizesHtml = '';
          data.forEach(item => {
            const extraStyle = item.stock == 0 ? 'style="opacity:0.5;"' : '';
            sizesHtml += `<button class="size-btn" data-price="${item.price}" data-stock="${item.stock}" data-size="${item.size}" ${extraStyle}>${item.size}</button>`;
          });
          sizeOptionsDiv.innerHTML = sizesHtml;
        } else {
          sizeOptionsDiv.innerHTML = 'No sizes available for this color.';
        }
      });
  }
});

// Size click → update price/stock
document.addEventListener('click', function(e) {
  if (e.target.classList.contains('size-btn')) {
    const btn = e.target;
    const card = btn.closest('.product-card');
    card.querySelector('.selected-price').textContent = btn.dataset.price;
    card.querySelector('.selected-size').textContent = btn.dataset.size;
    card.querySelector('.selected-size-input').value = btn.dataset.size;
    card.querySelector('.selected-stock').textContent = btn.dataset.stock == 0 ? 'Out of stock' : btn.dataset.stock;
  }
});

let cart = [];
function prepareCartData(productId) {
  const card = document.querySelector(`.product-card[data-id='${productId}']`);
  const color = card.querySelector('.selected-color-input').value;
  const size = card.querySelector('.selected-size-input').value;
  const name = card.querySelector('h4').textContent;
  const image = card.querySelector('img').src;
  const price = card.querySelector('.selected-price').textContent;
  const stockText = card.querySelector('.selected-stock').textContent;
  const stock = stockText === "Out of stock" ? 0 : parseInt(stockText);
  const quantity = parseInt(card.querySelector('.quantity-input').value);

  if (!color || !size || size === 'None') {
    alert('Please select a color and size.');
    return false;
  }
  if (stock === 0) {
    alert('Product out of stock.');
    return false;
  }
  if (quantity > stock) {
    alert(`We only have ${stock} of this item in stock.`);
    return false;
  }
  const exists = cart.some(item =>
    item.productId === productId && item.color === color && item.size === size
  );
  if (exists) {
    alert('This product is already in your cart.');
    return false;
  }
  cart.push({ productId, name, color, size, image, price, quantity });
  localStorage.setItem('cartData', JSON.stringify(cart));
  updateCartUI();
  return false;
}

function updateCartUI() {
  document.getElementById('cartCount').textContent = cart.length;
  const cartItemsDiv = document.getElementById('cartItems');
  cartItemsDiv.innerHTML = '';
  cart.forEach((item, index) => {
    const div = document.createElement('div');
    div.className = 'cart-item';
    div.innerHTML = `
      <img src="${item.image}" alt="${item.name}">
      <div>
        <p>${item.name}</p>
        <p>${item.color} / ${item.size}</p>
        <p>₦${item.price}</p>
        <p>Qty: ${item.quantity}</p>
      </div>
      <button onclick="removeCartItem(${index})">X</button>
    `;
    cartItemsDiv.appendChild(div);
  });
}
function removeCartItem(index) {
  cart.splice(index, 1);
  localStorage.setItem('cartData', JSON.stringify(cart));
  updateCartUI();
}

// Cart panel toggle
document.getElementById('cartIcon').addEventListener('click', () => {
  document.getElementById('cartPanel').classList.toggle('active');
});

// Checkout
document.getElementById('buyNowBtn').addEventListener('click', () => {
  window.location.href = 'checkout.php';
});

function toggleMenu() {
  const menu = document.getElementById('mobileMenu');
  menu.style.display = menu.style.display === 'none' ? 'block' : 'none';
}
</script>
</body>
</html>
