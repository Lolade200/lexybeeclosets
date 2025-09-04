<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'database.php';

// Use the global connection
//global $conn;
//$result = $conn->query("SELECT * FROM users");

// Or use the function
// $db = getDBConnection();
// $result = $db->query("SELECT * FROM users");

// Database connection
// $servername = "localhost";
// $username = "root";
// $password = "";
// $dbname = "bbbb";

// $conn = new mysqli($servername, $username, $password, $dbname);
// if ($conn->connect_error) {
//     die("Connection failed: " . $conn->connect_error);
// }

// Pagination setup
$limit = 6; 
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// Search term
$searchTerm = isset($_GET['search']) ? trim(strtolower($_GET['search'])) : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Lexxybee Store</title>
    <link rel="stylesheet" href="first.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>

<header>
    <div class="logo">
        <img src="logoimg.jpg" alt="Lexxybee Logo">
        <h2>LexybeeClosets</h2>
    </div>

    <form class="search-bar" method="GET" action="">
        <input type="text" name="search" placeholder="Search products..." value="<?php echo htmlspecialchars($searchTerm); ?>" />
        <button type="submit"><i class="fas fa-search"></i></button>
    </form>

    <!-- Hamburger Button -->
    <button class="hamburger" onclick="toggleMenu()">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Navigation Links -->
    <nav class="nav-links">
        <a href="login.php">Login</a>
        <a href="signup.php">Signup</a>
        <a href="about.php">About</a>
    </nav>
</header>

<section class="slider">
    <div class="slider-left">
        <h2>Welcome to <br> LexybeeClosets</h2>
        <p>Discover the latest arrivals and shop your favorites</p>
        <a href="login.php" class="shop-btn">Shop Now</a>
    </div>
    <div class="slider-right">
        <div class="slider-wrapper">
            <div class="slider-container">
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
    </div>
</section>

<div class="main-wrapper">
    <div class="main-flex">
        <aside class="categories">
            <details open>
                <summary>All Categories</summary>
                <ul>
                    <li><a href="Household Items.php">Household Items</a></li>
                    <li><a href="Bags.php">Bags</a></li>
                    <li><a href="Kiddies.php">Kiddies</a></li>
                    <li><a href="Footwears.php">Footwears</a></li>
                    <li><a href="Men's clothing.php">Men's Clothing</a></li>
                    <li><a href="Womens-clothing.php">Women's Clothing</a></li>
                    <li><a href="Watches-glasses.php">Watches/Glasses</a></li>
                    <li><a href="male -female-undies.php">Underwears (Male & Female)</a></li>
                    <li><a href="Turtlenecks.php">Turtlenecks</a></li>
                    <li><a href="Night-wears.php">Night wears</a></li>
                    <li><a href="Giveaway-discount.php">Giveaway/Discount</a></li>
                </ul>
            </details>
        </aside>

        <section class="products" id="productList">
            <?php
            $sql = "
                SELECT products.id, products.name, products.description, products.image, products.category,
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

            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $product_id = $row['id'];
                    $image = !empty($row["image"]) ? $row["image"] : "uploads/default.jpg";
                    $name = htmlspecialchars($row["name"]);
                    $desc = htmlspecialchars($row["description"]);
                    $category = htmlspecialchars($row["category"]);
                    $price = number_format($row["price"], 2);
                    echo "<div class='product-card'>
                            <img src='$image' alt='$name'>
                            <h4>$name</h4>
                            <p>$desc</p>
                            <p><strong>Category:</strong> $category</p>
                            <p class='price'>₦$price</p>
                            <div class='actions'>
                                <a href='product2.php?id=$product_id'>
                                    <button><i class='fas fa-eye'></i> View</button>
                                </a>
                            </div>
                          </div>";
                }
            } else {
                echo "<p>No products found.</p>";
            }
            $stmt->close();

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

            $total_pages = ceil($total_products / $limit);
            ?>
        </section>
    </div>
</div>

<div class="pagination-wrapper">
    <div class="pagination">
        <?php
        if ($total_pages > 1) {
            for ($i = 1; $i <= $total_pages; $i++) {
                $active = $i === $page ? 'class="active-page"' : '';
                $query = http_build_query(array_merge($_GET, ['page' => $i]));
                echo "<a href='?$query' $active>$i</a>";
            }
        }
        ?>
    </div>
</div>

<div id="cart-display" style="display: none; padding: 20px; background: #fff; margin: 30px 0; border-radius: 10px; box-shadow: 0 2px 6px rgba(0,0,0,0.1);">
    <h3>🛒 Your Cart</h3>
    <div id="cart-items"></div>
</div>

<?php include 'footer.php'; ?>

<script src="first.js">


</script>

</body>
</html>
