<?php
// Enable error reporting for debugging

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "bbbb";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Pagination setup (changed to 8)
$limit =7;
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
    <div class="slider-track" id="sliderTrack">
      <?php
      // Optional PHP loop for featured items
      ?>
    </div>
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
    // Product query
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

    // Count total products for pagination
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

    // ✅ Calculate total pages BEFORE using it
    $total_pages = ($limit > 0) ? ceil($total_products / $limit) : 1;

    // Display products
    if ($result && $result->num_rows > 0) {
      while ($row = $result->fetch_assoc()) {
        $product_id = $row['id'];
        $image = !empty($row["image"]) ? $row["image"] : "uploads/default.jpg";
        $name = htmlspecialchars($row["name"]);
        $desc = htmlspecialchars($row["description"]);
        $price = number_format($row["price"], 2);

        // Get available colors
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

        echo "<div class='product-card' data-id='$product_id'>
                <img src='$image' alt='$name'>
                <h4>$name</h4>
                <p>$desc</p>
                <p><strong>Colors:</strong></p>
                $color_html
                <div class='size-options'></div>
                <p><strong>Selected Size:</strong> <span class='selected-size'>None</span></p>
                <p><strong>Stock:</strong> <span class='selected-stock'>0</span></p>
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

  <!-- 📄 Pagination -->
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
// Restore cart on load
document.addEventListener('DOMContentLoaded', () => {
  cart = JSON.parse(localStorage.getItem('cartData')) || [];
  updateCartUI();
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
            const disabled = item.stock == 0 ? 'disabled style="opacity:0.5;cursor:not-allowed;"' : '';
            sizesHtml += `<button class="size-btn" data-price="${item.price}" data-stock="${item.stock}" data-size="${item.size}" ${disabled}>${item.size}</button>`;
          });
          sizeOptionsDiv.innerHTML = sizesHtml;

          card.querySelectorAll('.size-btn').forEach(sizeBtn => {
            sizeBtn.addEventListener('click', function () {
              if (this.disabled) return;

              const price = this.dataset.price;
              const stock = this.dataset.stock;
              const size = this.dataset.size;

              card.querySelector('.selected-price').textContent = price;
              card.querySelector('.selected-stock').textContent = stock;
              card.querySelector('.selected-size').textContent = size;
              card.querySelector('.selected-size-input').value = size;
            });
          });
        } else {
          sizeOptionsDiv.innerHTML = 'No sizes available for this color.';
        }
      });
  });
});

let cart = [];

// ✅ Updated prepareCartData with quantity validation
function prepareCartData(productId) {
  const card = document.querySelector(`.product-card[data-id='${productId}']`);
  const color = card.querySelector('.selected-color-input').value;
  const size = card.querySelector('.selected-size-input').value;
  const name = card.querySelector('h4').textContent;
  const image = card.querySelector('img').src;
  const price = card.querySelector('.selected-price').textContent;
  const stock = parseInt(card.querySelector('.selected-stock').textContent);
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
  const panel = document.getElementById('cartPanel');
  panel.classList.toggle('active');
});

document.getElementById('buyNowBtn').addEventListener('click', () => {
  window.location.href = 'checkout.php';
});

function toggleMenu() {
  const menu = document.getElementById('mobileMenu');
  menu.style.display = menu.style.display === 'none' ? 'block' : 'none';
}

// ✅ Auto-slide functionality
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
