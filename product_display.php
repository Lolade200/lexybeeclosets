<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'database.php';

// Updated pagination: 24 per laptop, 12 per mobile
$default_limit = 24;
$mobile_limit  = 12;
$page          = isset($_GET['page']) && is_numeric($_GET['page']) ? intval($_GET['page']) : 1;

// Detect mobile via GET param if needed, fallback to desktop
$limit  = (isset($_GET['mobile']) && $_GET['mobile']) ? $mobile_limit : $default_limit;
$offset = ($page - 1) * $limit;

$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';
$searchTerm = strtolower($searchTerm);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Lexxybee Store</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="mainpage.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap'); 

/* ================== Variables ================== */
:root {
  --primary: #f0c040;
  --bg: #f5f7fa;
  --text: #333;
  --header-bg: #1e2a38;
  --footer-bg: #222;
  --font-family: "Inter", "Segoe UI", sans-serif;
}

/* ================== Global Reset ================== */
* {
  box-sizing: border-box;
  margin: 0;
  padding: 0;
  font-family: var(--font-family);
}

body {
  background: var(--bg);
  color: var(--text);
  overflow-x: hidden;
  transition: all 0.3s ease;
}

a {
  text-decoration: none;
  color: white;
}

/* ================== Buttons ================== */
.btn,
.actions button,
.btn1,
.search-bar button,
.shop-btn,
.pagination a {
  background: var(--primary);
  color: white;
  border: none;
  cursor: pointer;
  border-radius: 8px;
  font-weight: bold;
  transition: background 0.3s ease, transform 0.2s ease;
}

.btn:hover,
.actions button:hover,
.btn1:hover,
.search-bar button:hover,
.shop-btn:hover,
.pagination a:hover {
  background: #d9a82d;
}

.btn i,
.actions button i,
.btn1 i {
  font-size: 16px;
  margin-right: 6px;
}

/* ================== Header ================== */
header {
  width: 100%;
  background: var(--header-bg);
  color: white;
  padding: 20px 30px;
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: space-between;
  gap: 15px;
  position: relative;
}

.logo {
  display: flex;
  align-items: center;
  gap: 10px;
  flex: 1 1 auto;
}

.logo img {
  width: 40px;
  height: 40px;
}

.search-bar {
  flex: 2 1 auto;
  display: flex;
  align-items: center;
  gap: 10px;
  max-width: 600px;
  margin: 10px auto;
  min-width: 250px;
}

.search-bar input {
  flex: 1;
  padding: 16px 18px;
  border-radius: 8px;
  border: none;
  font-size: 16px;
}

.search-bar button {
  padding: 16px 20px;
  font-size: 16px;
}

/* Hamburger Menu */
.hamburger {
  display: none;
  background: none;
  border: none;
  font-size: 24px;
  color: var(--primary);
  cursor: pointer;
}

.nav-links {
  display: flex;
  gap: 40px;
  align-items: center;
  flex: 1 1 auto;
  flex-wrap: wrap;
  justify-content: flex-end;
}

.nav-links a {
  color: white;
  font-weight: 500;
}

.nav-links.show {
  display: flex;
  flex-direction: column;
  position: absolute;
  top: 70px;
  right: 30px;
  background: var(--header-bg);
  padding: 20px;
  border-radius: 8px;
  z-index: 1000;
}

/* Categories Select Styling */
.categories-select {
  position: relative;
  display: flex;
  align-items: center;
  margin-left: 15px;
  flex: 1 1 auto;
  max-width: 220px;
}

.categories-select select {
  padding: 10px;
  font-size: 15px;
  font-weight: bold;
  background-color: #fff;
  color: #333;
  border: 1px solid #ccc;
  border-radius: 4px;
  width: 200px;
  max-width: 100%;
  appearance: none;
}

.categories-select::after {
  content: "▼";
  font-size: 12px;
  color: #333;
  position: absolute;
  right: 12px;
  pointer-events: none;
}

/* ================== Slider ================== */
.slider {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 40px;
  gap: 30px;
  background: #fdfdfd;
}

.slider-left {
  flex: 1;
}

.slider-left h2 {
  font-size: 2rem;
  margin-bottom: 10px;
}

.slider-left p {
  font-size: 1rem;
  margin-bottom: 20px;
  color: #555;
}

.shop-btn {
  padding: 10px 20px;
  border-radius: 6px;
  font-weight: bold;
  text-decoration: none;
}

.slider-right {
  flex: 2;
  overflow: hidden;
}

.slider-container {
  display: flex;
  transition: transform 0.5s ease-in-out;
}

.slide {
  flex: 0 0 100%;
  padding: 5px;
  box-sizing: border-box;
}

.slide img {
  width: 100%;
  height: 350px;
  object-fit: contain;
  border-radius: 8px;
}

/* ================== Products ================== */
.products {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 20px;
  padding: 20px;
  margin: 20px 10px;
}

.product-card {
  background: white;
  border: 1px solid #ddd;
  border-radius: 10px;
  padding: 18px;
  text-align: center;
  box-shadow: 0 4px 8px rgba(0,0,0,0.08);
  display: flex;
  flex-direction: column;
  align-items: center;
  min-height: 360px;
  transition: 0.3s ease;
}

.product-card:hover {
  box-shadow: 0 4px 15px rgba(0,0,0,0.1);
  transform: translateY(-5px);
}

.product-card img {
  max-width: 100%;
  height: 220px;
  object-fit: cover;
  border-radius: 8px;
  margin-bottom: 12px;
}

.product-card h4 {
  font-size: 18px;
  margin: 5px 0;
  font-weight: 600;
  color: var(--header-bg);
}

.product-card h4 a {
  color: inherit;
  text-decoration: none;
}

.product-card h4 a:hover { text-decoration: underline; }

.product-card p {
  margin: 6px 0;
  font-size: 14px;
  color: #555;
}

/* ================== Responsive ================== */
@media (max-width: 1024px) {
  .products { grid-template-columns: repeat(3, 1fr); }
}

@media (max-width: 768px) {
  .hamburger { display: block; }
  .nav-links { display: none; }
  .nav-links.show { gap: 15px; padding: 15px; right: 20px; }
  header { flex-direction: column; align-items: flex-start; padding: 20px; }
  .search-bar { flex-direction: column; width: 100%; }
  .search-bar input, .search-bar button { width: 100%; }
  .slider { flex-direction: column; padding: 20px; gap: 15px; }
  .slide img { height: 300px; }
  .slider-left h2 { font-size: 1.5rem; }
  .slider-left p { font-size: 0.9rem; }
  .shop-btn { padding: 8px 12px; font-size: 14px; }
  .pagination-wrapper { width: 100%; padding: 15px; }
  .pagination { gap: 8px; }

  /* ✅ Product cards mobile styling */
  .products { 
    grid-template-columns: repeat(2, 1fr); 
    gap: 0; /* remove gaps so borders separate cards */
  }

  .product-card {
    width: 100%;
    min-height: auto;
    padding: 15px;
    border-radius: 0;
    box-shadow: none; /* remove shadow */
    border: 1px solid #ccc; /* use lines */
    border-left: none;
    border-top: none;
  }

  /* Ensure grid lines show properly */
  .product-card:nth-child(2n+1) {
    border-left: 1px solid #ccc;
  }

  .product-card:nth-child(-n+2) {
    border-top: 1px solid #ccc;
  }

  .product-card img {
    width: 100%;
    height: auto;
    object-fit: contain;
  }
}
  </style>
</head>

<body>

<header>
  <div class="logo">
    <img src="logoimg.jpg" alt="Lexxybee Logo">
    <h2>LexybeeClosets</h2>
  </div>

  <form class="search-bar" method="GET" action="">
    <input type="text" name="search" placeholder="Search products..." 
           value="<?php echo htmlspecialchars($searchTerm); ?>" />
    <button type="submit"><i class="fas fa-search"></i></button>
  </form>

  <button class="hamburger" onclick="toggleMenu()">
    <i class="fas fa-bars"></i>
  </button>

  <nav id="mobileMenu" class="nav-links">
    <a href="logout.php" style="margin-left:20px;">Logout</a>
    <a href="profile.php">My Account</a>
    <div class="categories-select">
      <select id="categorySelect" onchange="location = this.value;">
        <option value="#">Category</option>
        <option value="Household Items2.php">Household Items</option>
        <option value="Bagsmain.php">Bags</option>
        <option value="Kiddies2.php">Kiddies</option>
        <option value="Footwears2.php">Footwears</option>
        <option value="male-female undies2.php">Male and Female Underwear</option>
        <option value="Men's Clothing2.php">Men's Clothing</option>
        <option value="Women's Clothing2.php">Women's Clothing</option>
        <option value="watches-glasses2.php">Watches/Glasses</option>
        <option value="Giveaway-discount.php">Giveaway-discount</option>
      </select>
    </div>
  </nav>
</header>

<section class="slider">
  <div class="slider-left">
    <h2>Welcome to <br> LexybeeClosets</h2>
    <p>Discover the latest arrivals and shop your favorites</p>
    <a href="login.php" class="shop-btn">Shop Now</a>
  </div>
  <div class="slider-right">
    <div class="slider-container" id="sliderContainer">
      <?php
      $latest_sql    = "SELECT id, name, image FROM products ORDER BY created_at DESC LIMIT 9";
      $latest_result = $conn->query($latest_sql);

      if ($latest_result && $latest_result->num_rows > 0) {
        while ($latest = $latest_result->fetch_assoc()) {
          $product_id = $latest['id'];
          $image      = !empty($latest["image"]) ? $latest["image"] : "uploads/default.jpg";
          $name       = htmlspecialchars($latest["name"]);
          echo "<div class='slide'><a href='product3.php?id=$product_id'><img src='$image' alt='$name'></a></div>";
        }
      } else {
        echo "<div class='slide'><img src='placeholder.jpg' alt='No products yet'></div>";
      }
      ?>
    </div>
  </div>
</section>

<h1 style="text-align: center; margin-top: 40px; margin-bottom: 20px;">All Products</h1>

<section class="products" id="productList">
<?php
$sql = "
  SELECT products.id, products.name, products.description, products.image,
         MIN(product_variants.price) AS price
  FROM products
  LEFT JOIN product_variants ON products.id = product_variants.product_id
  WHERE (? = '' OR
         LOWER(products.name) LIKE CONCAT('%', ?, '%') OR
         LOWER(products.description) LIKE CONCAT('%', ?, '%') OR
         LOWER(products.category) LIKE CONCAT('%', ?, '%'))
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
  WHERE (? = '' OR
         LOWER(products.name) LIKE CONCAT('%', ?, '%') OR
         LOWER(products.description) LIKE CONCAT('%', ?, '%') OR
         LOWER(products.category) LIKE CONCAT('%', ?, '%'))
";
$count_stmt = $conn->prepare($count_sql);
$count_stmt->bind_param("ssss", $searchTerm, $searchTerm, $searchTerm, $searchTerm);
$count_stmt->execute();
$count_result   = $count_stmt->get_result();
$total_products = $count_result->fetch_assoc()['total'];
$count_stmt->close();

$total_pages = ($limit > 0) ? ceil($total_products / $limit) : 1;

if ($result && $result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    $product_id = $row['id'];
    $image      = !empty($row["image"]) ? $row["image"] : "uploads/default.jpg";
    $name       = htmlspecialchars($row["name"]);
    $desc       = htmlspecialchars($row["description"]);
    $price      = number_format($row["price"], 2);

    echo "
      <div class='product-card' data-id='$product_id'>
        <a href='product3.php?id=$product_id'><img src='$image' alt='$name'></a>
        <h4><a href='product3.php?id=$product_id'>$name</a></h4>
        <p>$desc</p>
        <p><strong>Price:</strong> ₦$price</p>
      </div>";
  }
} else {
  echo "<p>No products found.</p>";
}
?>
</section>

<div class="pagination-wrapper">
  <div class="pagination">
    <?php
    if ($total_pages > 1) {
      for ($i = 1; $i <= $total_pages; $i++) {
        $active = ($i === $page) ? 'class="active-page"' : '';
        $query  = http_build_query(array_merge($_GET, ['page' => $i]));
        echo "<a href='?$query' $active>$i</a>";
      }
    }
    ?>
  </div>
</div>

<footer>
  <div class="footer-grid">
    <div>
      <h4><i class="fas fa-university"></i> Account Details</h4>
      <p>
        <strong>Bank:</strong> Opay<br>
        <strong>Account No.:</strong> 7033581634<br>
        <strong>Account Name:</strong> Adedulu Bolanle Damilola
      </p>
      <p class="footer-note">
        <i class="fas fa-exclamation-triangle"></i> Any goods left unpicked is at owner's risk<br>
        <i class="fas fa-ban"></i> NO REFUNDS after payment<br>
        <i class="fas fa-exchange-alt"></i> NO EXCHANGE after pickup
      </p>
    </div>
    <div>
      <h4><i class="fas fa-link"></i> Quick Links</h4>
      <ul style="list-style: none; padding-left: 0;">
        <li><i class="fas fa-home"></i> <a href="index.php" style="color: #eee; text-decoration: none;">Home</a></li>
        <li><i class="fas fa-info-circle"></i> <a href="about.php" style="color: #eee; text-decoration: none;">About</a></li>
        <li><i class="fas fa-sign-in-alt"></i> <a href="login.php" style="color: #eee; text-decoration: none;">Login</a></li>
        <li><i class="fas fa-user-plus"></i> <a href="signup.php" style="color: #eee; text-decoration: none;">Signup</a></li>
      </ul>
    </div>
    <div>
      <h4><i class="fas fa-address-book"></i> Contact</h4>
      <ul style="list-style: none; padding-left: 0;">
        <li><i class="fas fa-envelope"></i> lexxybeeenterprises@gmail.com</li>
        <li><i class="fas fa-phone"></i> +234 902 360 7302</li>
        <li><i class="fab fa-instagram"></i> lexxybee_closet</li>
      </ul>
    </div>
  </div>
</footer>

<script>
function toggleMenu() {
  document.getElementById("mobileMenu").classList.toggle("show");
}

// Slider auto-rotation
let index = 0;
const slides = document.querySelectorAll(".slide");
const container = document.getElementById("sliderContainer");

function showSlide(i) {
  container.style.transform = `translateX(${-i * 100}%)`;
}

function autoSlide() {
  index = (index + 1) % slides.length;
  showSlide(index);
}

setInterval(autoSlide, 4000);
</script>

</body>
</html>
