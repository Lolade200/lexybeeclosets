<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'database.php';

// Pagination setup
$limit = 7;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// Search term
$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';
$searchTerm = strtolower($searchTerm);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Lexxybee Store</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <!-- Stylesheets -->
  <link rel="stylesheet" href="mainpage.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>

  <!-- 🔷 Header Section -->
  <header>
    <div class="logo">
      <img src="logoimg.jpg" alt="Lexxybee Logo">
      <h2>LexybeeClosets</h2>
    </div>

    <!-- 🔍 Search Bar -->
    <form class="search-bar" method="GET" action="">
      <input type="text" name="search" placeholder="Search products..." value="<?php echo htmlspecialchars($searchTerm); ?>" />
      <button type="submit"><i class="fas fa-search"></i></button>
    </form>

    <!-- 🍔 Mobile Menu Toggle -->
    <button class="hamburger" onclick="toggleMenu()">
      <i class="fas fa-bars"></i>
    </button>

    <!-- 📌 Navigation Links -->
    <nav id="mobileMenu" class="nav-links">
      <a href="logout.php">Logout</a>
      <a href="about.php">About</a>
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

  <!-- 🎞️ Slider Track -->
  <div class="slider-container">
    <div class="slider-track" id="sliderTrack"></div>
  </div>

  <!-- 🖼️ Slider Section -->
  <section class="slider">
    <div class="slider-left">
      <h2>Welcome to <br> LexybeeClosets</h2>
      <p>Discover the latest arrivals and shop your favorites</p>
      <a href="login.php" class="shop-btn">Shop Now</a>
    </div>

    <div class="slider-right">
      <div class="slider-container" id="sliderContainer">
        <?php
        $latest_sql = "SELECT id, name, image FROM products ORDER BY created_at DESC LIMIT 9";
        $latest_result = $conn->query($latest_sql);
        if ($latest_result && $latest_result->num_rows > 0) {
          while ($latest = $latest_result->fetch_assoc()) {
            $product_id = $latest['id'];
            $image = !empty($latest["image"]) ? $latest["image"] : "uploads/default.jpg";
            $name = htmlspecialchars($latest["name"]);
            echo "<div class='slide'>
                    <a href='product2.php?id=$product_id'>
                      <img src='$image' alt='$name'>
                    </a>
                  </div>";
          }
        } else {
          echo "<div class='slide'><img src='placeholder.jpg' alt='No products yet'></div>";
        }
        ?>
      </div>
    </div>
  </section>

  <!-- 🛍️ Product Grid -->
  <section class="products" id="productList">
  <?php
  $sql = "
    SELECT products.id, products.name, products.description, products.image,
           MIN(product_variants.price) AS price
    FROM products
    LEFT JOIN product_variants ON products.id = product_variants.product_id
    WHERE (
        ? = '' OR
        LOWER(products.name) LIKE CONCAT('%', ?, '%') OR
        LOWER(products.description) LIKE CONCAT('%', ?, '%') OR
        LOWER(products.category) LIKE CONCAT('%', ?, '%')
    )
    GROUP BY products.id
    ORDER BY products.created_at DESC
    LIMIT ? OFFSET ?
  ";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("ssssii", $searchTerm, $searchTerm, $searchTerm, $searchTerm, $limit, $offset);
  $stmt->execute();
  $result = $stmt->get_result();

  $count_sql = "
    SELECT COUNT(DISTINCT products.id) AS total
    FROM products
    LEFT JOIN product_variants ON products.id = product_variants.product_id
    WHERE (
        ? = '' OR
        LOWER(products.name) LIKE CONCAT('%', ?, '%') OR
        LOWER(products.description) LIKE CONCAT('%', ?, '%') OR
        LOWER(products.category) LIKE CONCAT('%', ?, '%')
    )
  ";
  $count_stmt = $conn->prepare($count_sql);
  $count_stmt->bind_param("ssss", $searchTerm, $searchTerm, $searchTerm, $searchTerm);
  $count_stmt->execute();
  $count_result = $count_stmt->get_result();
  $total_products = $count_result->fetch_assoc()['total'];
  $count_stmt->close();

  $total_pages = ($limit > 0) ? ceil($total_products / $limit) : 1;

  if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
      $product_id = $row['id'];
      $image = !empty($row["image"]) ? $row["image"] : "uploads/default.jpg";
      $name = htmlspecialchars($row["name"]);
      $desc = htmlspecialchars($row["description"]);
      $price = number_format($row["price"], 2);

      $color_sql = "SELECT DISTINCT color FROM product_variants WHERE product_id = ?";
      $color_stmt = $conn->prepare($color_sql);
      $color_stmt->bind_param("i", $product_id);
      $color_stmt->execute();
      $color_result = $color_stmt->get_result();

      $color_html = '<div class="color-options">';
      while ($color_row = $color_result->fetch_assoc()) {
        $color = htmlspecialchars($color_row['color']);
        $color_html .= "<button class='color-btn' style='background-color:{$color};' data-color='{$color}' data-product='{$product_id}'></button>";
      }
      $color_html .= '</div>';
      $color_stmt->close();

      // 🟢 Check total stock
      $stock_sql = "SELECT SUM(stock) AS total_stock FROM product_variants WHERE product_id = ?";
      $stock_stmt = $conn->prepare($stock_sql);
      $stock_stmt->bind_param("i", $product_id);
      $stock_stmt->execute();
      $stock_result = $stock_stmt->get_result();
      $total_stock = $stock_result->fetch_assoc()['total_stock'] ?? 0;
      $stock_stmt->close();

      $display_stock = (is_numeric($total_stock) && $total_stock > 0) ? $total_stock : "Out of stock";

      echo "<div class='product-card' data-id='$product_id'>
              <img src='$image' alt='$name'>
              <h4>$name</h4>
              <p>$desc</p>
              <p><strong>Colors:</strong></p>
              $color_html
              <div class='size-options'></div>
              <p><strong>Selected Size:</strong> <span class='selected-size'>None</span></p>
              <p><strong>Stock:</strong> <span class='selected-stock'>$display_stock</span></p>
              <p><strong>Price:</strong> ₦<span class='selected-price'>$price</span></p>
              <form method='POST' action='add_to_cart.php' onsubmit='return prepareCartData($product_id)'>
                <input type='hidden' name='product_id' value='$product_id'>
                <input type='hidden' name='selected_color' class='selected-color-input'>
                <input type='hidden' name='selected_size' class='selected-size-input'>
                <input type='number' class='quantity-input' min='1' value='1' style='width:60px;' placeholder='Qty'>
                <button type='submit' class='btn1'><i class='fas fa-cart-plus'></i> Add to Cart</button>
              </form>
            </div>";
    }
  } else {
    echo "<p>No products found.</p>";
  }
  ?>
</section>

  <!-- 📄 Pagination (Now placed under products) -->
  <div class="pagination-wrapper">
    <div class="pagination">
      <?php
      if ($total_pages > 1) {
        for ($i = 1; $i <= $total_pages; $i++) {
          $active = ($i === $page) ? 'class="active-page"' : '';
          $query = http_build_query(array_merge($_GET, ['page' => $i]));
          echo "<a href='?$query' $active>$i</a>";
        }
      }
      ?>
    </div>
  </div>

  <!-- 🔚 Footer -->
  <?php include 'footer.php'; ?>


<script>
// Reset cart UI on load
document.addEventListener('DOMContentLoaded', () => {
  localStorage.removeItem('cartData');
  cart = [];
  updateCartUI();

  const cartPanel = document.getElementById('cartPanel');
  const cartItemsDiv = document.getElementById('cartItems');
  const cartCount = document.getElementById('cartCount');
  if (cartPanel) cartPanel.classList.remove('active');
  if (cartItemsDiv) cartItemsDiv.innerHTML = '';
  if (cartCount) cartCount.textContent = '0';
});

document.querySelectorAll('.color-btn').forEach(btn => {
  btn.addEventListener('click', function () {
    const productId = this.dataset.product;
    const color = this.dataset.color;
    const card = this.closest('.product-card');
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

          card.querySelectorAll('.size-btn').forEach(sizeBtn => {
            sizeBtn.addEventListener('click', function () {
              const price = this.dataset.price;
              const stock = this.dataset.stock;
              const size = this.dataset.size;

              card.querySelector('.selected-price').textContent = price;
              card.querySelector('.selected-size').textContent = size;
              card.querySelector('.selected-size-input').value = size;

              if (parseInt(stock) === 0) {
                card.querySelector('.selected-stock').textContent = "Out of stock";
              } else {
                card.querySelector('.selected-stock').textContent = stock;
              }
            });
          });
        } else {
          sizeOptionsDiv.innerHTML = 'No sizes available for this color.';
        }
      });
  });
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
  const quantityInput = card.querySelector('.quantity-input');
  const quantity = quantityInput ? parseInt(quantityInput.value) : 1;

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

document.getElementById('cartIcon').addEventListener('click', () => {
  document.getElementById('cartPanel').classList.toggle('active');
});

document.getElementById('buyNowBtn').addEventListener('click', () => {
  window.location.href = 'checkout.php';
});

function toggleMenu() {
  const menu = document.getElementById('mobileMenu');
  menu.style.display = menu.style.display === 'none' ? 'block' : 'none';
}

// Auto-slide
let currentSlide = 0;
function autoSlide() {
  const container = document.getElementById('sliderContainer');
  const slides = container.querySelectorAll('.slide');
  const totalSlides = slides.length;
  currentSlide = (currentSlide + 1) % totalSlides;
  const offset = -currentSlide * container.offsetWidth;
  container.style.transform = `translateX(${offset}px)`;
}
setInterval(autoSlide, 4000);
</script>
</body>
</html>
