<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'database.php';

$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch main product
$product = null;
if ($product_id > 0) {
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    $stmt->close();
}

// Fetch related products with price, stock, and color preview
$related_products = [];
if ($product_id > 0 && $product) {
    $stmt = $conn->prepare("
        SELECT p.id, p.name, p.description, p.image,
               MIN(v.price) AS price,
               SUM(v.stock) AS stock
        FROM products p
        LEFT JOIN product_variants v ON p.id = v.product_id
        WHERE p.category = ? AND p.id != ?
        GROUP BY p.id
        LIMIT 24
    ");
    $stmt->bind_param("si", $product['category'], $product_id);
    $stmt->execute();
    $related_products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $product ? htmlspecialchars($product['name']).' - Lexxybee Store' : 'Product - Lexxybee Store'; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap');

:root {
    --primary: #f0c040;
    --primary-dark: #d9a72e;
    --bg: #f5f7fa;
    --text: #333;
    --header-bg: #1e2a38;
    --footer-bg: #222;
    --font-family: "Inter", "Segoe UI", sans-serif;
    --border-radius: 8px;
    --box-shadow: 0 4px 10px rgba(0,0,0,0.08);
}

* { box-sizing: border-box; margin:0; padding:0; font-family: var(--font-family); }
body { background: var(--bg); color: var(--text); overflow-x: hidden; transition: all 0.3s ease; }
a { text-decoration: none; color: inherit; }

header {
    width: 100%;
    background: var(--header-bg);
    color: white;
    padding: 15px 30px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 15px;
}

.logo { display: flex; align-items: center; gap: 10px; }
.logo img { width: 40px; height: 40px; }
.logo h2 { font-size: 20px; white-space: nowrap; }

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
    flex-grow: 0;
    justify-content: flex-end;
    margin-left: auto;
}

.nav-links a { color: white; font-weight: 500; }

.cart-toggle {
    background: var(--primary);
    border: none;
    padding: 10px 18px;
    border-radius: 30px;
    color: #fff;
    font-weight: bold;
    cursor: pointer;
    position: relative;
    font-size: 15px;
    transition: all 0.3s ease;
}
.cart-toggle:hover { background: var(--primary-dark); transform:scale(1.05); }
.cart-count {
    background: red;
    color: #fff;
    border-radius: 50%;
    padding: 3px 8px;
    font-size: 12px;
    position: absolute;
    top: -8px; right: -8px;
}

.menu-close-btn {
    display: none;
}

@media(max-width: 768px) {
    .hamburger {
        display: block;
        align-self: center;
    }
    
    .nav-links {
        display: none; 
        flex-direction: column;
        position: fixed; 
        top: 0;
        right: -250px; 
        width: 250px;
        height: 100%;
        background-color: var(--header-bg);
        padding-top: 60px; 
        box-shadow: -2px 0 5px rgba(0,0,0,0.5);
        z-index: 1000;
        justify-content: flex-start;
        transition: right 0.3s ease-in-out;
    }
    .nav-links a {
        width: 100%;
        text-align: left;
        padding: 15px 25px;
        border-bottom: 1px solid #333;
    }
    .nav-links.show {
        display: flex;
        right: 0; 
    }
    
    .nav-links.show .menu-close-btn {
        display: block;
        background: none;
        border: none;
        font-size: 24px;
        color: var(--primary);
        cursor: pointer;
        position: absolute;
        top: 15px;
        right: 25px;
    }

    .header-buttons {
        display: flex;
        gap: 10px;
        align-items: center;
    }

    .cart-toggle {
        margin-left: 0; 
    }
}

.product-detail { 
    margin:20px auto; 
    padding:20px; 
    max-width:1000px; 
    background:#fff; 
    border-radius:8px; 
    box-shadow:0 2px 6px rgba(0,0,0,0.1); 
}
.product-wrapper { 
    display:flex; 
    flex-direction:column; 
    gap:30px; 
}
.product-image { flex:1; text-align:center; }
.product-info { flex:1; text-align:left; padding:10px; }
.product-image img { max-width:350px; width:100%; border-radius:8px; }

.product-info h1 {
    margin-bottom: 10px;
    font-size: 2.2em;
    line-height: 1.2;
}
.product-info p {
    margin-bottom: 10px;
    line-height: 1.5;
}

.options-container {
    margin-bottom: 15px;
}
.option-row {
    display: flex;
    align-items: center;
    gap: 10px;
}
.option-row h3 {
    font-size: 1.1em;
    font-weight: 600;
    margin: 0;
    color: var(--text);
    white-space: nowrap;
}

.color-options, .size-options {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin: 0;
}
.color-btn {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    border: 2px solid #ccc;
    cursor: pointer;
    transition: all 0.2s ease;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}
.color-btn:hover { transform: scale(1.1); }
.color-btn.selected { 
    box-shadow: 0 2px 6px rgba(0,0,0,0.2);
}

.size-btn {
    min-width: 45px;
    height: 32px;
    border: 2px solid #ccc;
    cursor: pointer;
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 14px;
    background: #fafafa;
    transition: all 0.2s ease;
}
.size-btn:hover { background:#f0f0f0; }
.size-btn.selected { 
    background:#eee;
}

.quantity-selector {
    display: flex;
    align-items: center;
    gap: 5px;
    margin-top: 15px;
}
.quantity-selector button {
    background: var(--bg);
    border: 1px solid #ccc;
    width: 30px;
    height: 30px;
    font-size: 20px;
    font-weight: bold;
    color: var(--text);
    cursor: pointer;
    border-radius: 4px;
    transition: background 0.2s ease;
}
.quantity-selector button:hover {
    background: #e0e0e0;
}
.qty-input { 
    width: 50px; 
    padding: 6px; 
    border: 1px solid #ccc; 
    border-radius: 5px; 
    text-align: center;
    -moz-appearance: textfield; 
}
.qty-input::-webkit-outer-spin-button,
.qty-input::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

#variantInfo { margin:15px 0; font-weight:bold; font-size:15px; color:#444; }

.add-cart-btn {
    background: var(--primary);
    border: none;
    padding: 12px 25px;
    border-radius: 6px;
    color: #fff;
    font-weight: bold;
    cursor: pointer;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
    z-index: 1;
    margin-top: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
}
.add-cart-btn:hover { 
    background: var(--primary-dark);
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}

@keyframes shake {
    0% { transform: translateX(0); }
    25% { transform: translateX(-3px); }
    50% { transform: translateX(3px); }
    75% { transform: translateX(-3px); }
    100% { transform: translateX(0); }
}

.add-cart-btn:hover .fa-shopping-cart {
    animation: shake 0.5s ease-in-out;
}

@media(min-width:768px) { .product-wrapper { flex-direction:row; align-items:flex-start; } }

.related-products { margin:40px auto; max-width:1400px; padding:0 15px; }
.related-products h2 { text-align:center; margin-bottom:20px; }
.product-grid { display:grid; grid-template-columns:repeat(2, 1fr); gap:20px; }
.product-card { background:#fff; padding:15px; border-radius:10px; text-align:center; box-shadow:0 4px 10px rgba(0,0,0,0.08); transition:0.3s; }
.product-card:hover { transform:translateY(-5px); }
.product-card img { 
    width:100%; 
    height:180px; 
    object-fit:cover; 
    border-radius:8px; 
}
.product-card h3, .product-card p { font-weight: bold; }
.product-card h3 { font-size:15px; margin:10px 0; height:40px; overflow:hidden; }
.product-card p { font-size:13px; color:#555; margin-bottom:5px; }
.product-card .price { font-weight:bold; color:#111; margin:8px 0; }

@media(min-width:768px) { .product-grid { grid-template-columns:repeat(3, 1fr); } }
@media(min-width:1024px) { .product-grid { grid-template-columns:repeat(5, 1fr); } }

.cart-panel { 
    position:fixed; 
    top:0; 
    right:-400px; 
    width:350px; 
    height:100%; 
    background:#fff; 
    box-shadow:-3px 0 12px rgba(0,0,0,0.25); 
    transition: right 0.4s ease; 
    display:flex; 
    flex-direction:column; 
    z-index:2000; 
    border-left:3px solid var(--primary); 
}
.cart-panel.active { right:0; }
.cart-header {
    padding: 15px;
    border-bottom: 1px solid #eee;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #fafafa;
}
.cart-header h2 {
    font-size: 1.5em;
    font-weight: 700;
}
.cart-header button {
    background: none;
    border: none;
    font-size: 24px;
    cursor: pointer;
    color: #888;
    transition: color 0.2s ease;
}
.cart-header button:hover {
    color: #000;
}
.cart-footer {
    padding: 15px;
    border-top: 1px solid #eee;
    background: #fafafa;
    display: flex;
    flex-direction: column;
    gap: 10px;
}
.cart-items { flex:1; overflow-y:auto; padding:15px; }
.cart-item {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 1px solid #f0f0f0;
}
.cart-item img { 
    width: 70px; 
    height: 70px; 
    object-fit: cover; 
    border-radius: 6px; 
}
.cart-item-details {
    flex-grow: 1;
    display: flex;
    flex-direction: column;
}
.cart-item-details p {
    margin: 2px 0;
    font-size: 14px;
}
.cart-item-details p.item-name {
    font-weight: 600;
    font-size: 16px;
}
.cart-item-details p.item-price {
    color: var(--primary);
    font-weight: 700;
}
.cart-item .remove-btn { 
    background: #e63946; 
    color: #fff; 
    border: none; 
    padding: 6px 10px; 
    cursor: pointer; 
    border-radius: 4px; 
    font-size: 12px; 
    transition:0.3s; 
    align-self: flex-end;
}
.cart-item .remove-btn:hover { background:#c82333; }
.checkout-btn { 
    background:#f0c040;
    color:#fff; 
    border:none; 
    padding:12px; 
    border-radius:6px; 
    width:100%; 
    font-weight:bold; 
    cursor:pointer; 
    transition:0.3s; 
}
.checkout-btn:hover { background:#d9a72e; }

footer { background: var(--footer-bg); color: #eee; padding: 40px 30px; font-size: 14px; }
footer h4 { color: var(--primary); }
.footer-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 30px; }
.footer-note { margin-top: 20px; font-style: italic; color: #ccc; }
.footer-bottom { text-align: center; margin-top: 30px; font-size: 12px; color: #aaa; }
footer i { margin-right: 8px; color: var(--primary); }
.footer-grid ul li { margin-bottom: 10px; }
.footer-grid a { color: var(--primary); }
.footer-grid a:hover { text-decoration: underline; }
</style>

<header>
    <div class="logo">
        <img src="logoimg.jpg" alt="Lexxybee Logo">
        <h2>LexybeeClosets</h2>
    </div>

    <nav id="mobileMenu" class="nav-links">
        <button class="menu-close-btn" onclick="toggleMenu()">
            <i class="fas fa-xmark"></i>
        </button>
        <a href="product_display.php">Home</a>
        <a href="profile.php">My Account</a>
        <a href="logout.php">Logout</a>
    </nav>
    
    <div class="header-buttons">
        <button id="hamburger-menu" class="hamburger" onclick="toggleMenu()">
            <i class="fas fa-bars"></i>
        </button>
        <button class="cart-toggle" onclick="toggleCart()">
            🛒 Cart <span id="cartCount" class="cart-count">0</span>
        </button>
    </div>
</header>

<?php if ($product): ?>
    <div class="product-detail">
        <div class="product-wrapper">
            <div class="product-image">
                <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
            </div>
            <div class="product-info">
                <h1><?php echo htmlspecialchars($product['name']); ?></h1>
                <p><?php echo htmlspecialchars($product['description']); ?></p>
                <p><strong>Category:</strong> <?php echo htmlspecialchars($product['category']); ?></p>
                <p><strong>Availability:</strong> <?php echo htmlspecialchars($product['availability']); ?></p>
                
                <div class="options-container">
                    <div class="option-row">
                        <h3>Color:</h3>
                        <div class="color-options" id="colorOptions"></div>
                    </div>
                </div>

                <div class="options-container">
                    <div class="option-row">
                        <h3>Size:</h3>
                        <div class="size-options" id="sizeOptions"></div>
                    </div>
                </div>
                
                <p><strong>Selected Sizes:</strong> <span id="selectedSizes">None</span></p>
                <div id="variantInfo"></div>

                <div class="quantity-selector">
                    <button id="decreaseQty">-</button>
                    <input type="number" id="quantityInput" class="qty-input" value="1" min="1">
                    <button id="increaseQty">+</button>
                </div>
                
                <button class="add-cart-btn" onclick="addToCart()">
                    <i class="fas fa-shopping-cart"></i> Add to Cart
                </button>
            </div>
        </div>
    </div>
<?php endif; ?>

<div class="related-products">
    <h2>Related Products</h2>
    <div class="product-grid">
        <?php foreach($related_products as $rel): ?>
            <?php
                $rel_price = isset($rel['price']) && is_numeric($rel['price']) ? number_format($rel['price'], 2) : 'N/A';
                $rel_stock = isset($rel['stock']) && $rel['stock'] > 0 ? $rel['stock'] : 'Out of stock';

                $color_stmt = $conn->prepare("SELECT DISTINCT color FROM product_variants WHERE product_id = ?");
                $color_stmt->bind_param("i", $rel['id']);
                $color_stmt->execute();
                $color_result = $color_stmt->get_result();
                $colors = [];
                while ($c = $color_result->fetch_assoc()) {
                    $colors[] = htmlspecialchars($c['color']);
                }
                $color_stmt->close();
            ?>
            <div class="product-card">
                <a href="product3.php?id=<?php echo $rel['id']; ?>">
                    <img src="<?php echo htmlspecialchars($rel['image']); ?>" alt="<?php echo htmlspecialchars($rel['name']); ?>">
                </a>
                <a href="product3.php?id=<?php echo $rel['id']; ?>">
                    <h3><?php echo htmlspecialchars($rel['name']); ?></h3>
                </a>
                <p><?php echo htmlspecialchars($rel['description']); ?></p>
                <p><strong>Price:</strong> ₦<?php echo $rel_price; ?></p>
                <p><strong>Stock:</strong> <?php echo $rel_stock; ?></p>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<div id="cartPanel" class="cart-panel">
    <div class="cart-header">
        <h2>Your Cart</h2>
        <button onclick="toggleCart()" class="cart-close-btn">✖</button>
    </div>
    <div id="cartItems" class="cart-items"></div>
    <div class="cart-footer">
        <p id="cartTotal"><strong>Total:</strong> ₦0</p>
        <button class="checkout-btn" onclick="window.location.href='checkout.php'">Checkout</button>
    </div>
</div>

<footer>
    <div class="footer-grid">
        <div>
            <h4><i class="fas fa-university"></i> Account Details</h4>
            <p><strong>Bank:</strong> Opay<br>
                <strong>Account No.:</strong> 7033581634<br>
                <strong>Account Name:</strong> Adedulu Bolanle Damilola</p>
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
                <li><i class="fas fa-map-marker-alt"></i>  2, Lubecker Crescent, Fish pond bus-stop, Agric Ikorodu, Lagos, Nigeria</li>
                <li><i class="fas fa-phone"></i> +23407033581634 / +23408066693304</li>
                <li><a href="https://www.facebook.com/share/16dJPSAcNC/" target="_blank" style="color: #eee;"><i class="fab fa-facebook"></i> info@lexybeeclosets.com</a></li>
                <li><i class="fab fa-telegram-plane"></i> <a href="https://t.me/+1pFD0r4g2k9hNjQ0" target="_blank" style="color: #eee;">Telegram</a></li>
                <li><i class="fas fa-store"></i> Lexybee Closets</li>
                <li><i class="fab fa-whatsapp"></i> <a href="https://chat.whatsapp.com/LY8miQLuLtE7EQyZ92bc3e?mode=ems_copy_t" target="_blank" style="color: #eee;">Join our WhatsApp Group</a></li>
                <li><i class="fas fa-clock"></i> Opening: Mondays - Fridays, 9am - 6pm</li>
            </ul>
        </div>
    </div>
    <div class="footer-bottom">
        © Lexybee Closets. All Rights Reserved. Powered By G & S Technology Limited
    </div>
</footer>

<script>
function toggleMenu() {
    const menu = document.getElementById("mobileMenu");
    menu.classList.toggle("show");
}

let variants = [];
let selectedColor = null;
let selectedSizes = [];

fetch(`get_variants.php?product_id=<?php echo $product_id; ?>`)
    .then(res => res.json())
    .then(data => { variants = data; renderColors(); });

function renderColors() {
    const colorContainer = document.getElementById('colorOptions');
    if (!colorContainer) return;
    colorContainer.innerHTML = '';
    const uniqueColors = [...new Map(variants.map(v => [v.color, v])).values()];
    uniqueColors.forEach((v, index) => {
        const btn = document.createElement('div');
        btn.className = 'color-btn';
        btn.style.backgroundColor = v.color.toLowerCase();
        btn.dataset.color = v.color;
        btn.addEventListener('click', () => {
            document.querySelectorAll('.color-btn').forEach(b => {
                b.classList.remove('selected');
                b.style.borderColor = '#ccc'; 
            });
            btn.classList.add('selected');
            btn.style.borderColor = btn.style.backgroundColor; 
            selectedColor = v.color;
            renderSizes(v.color);
        });
        colorContainer.appendChild(btn);
        if (index === 0) {
            btn.click();
        }
    });
}

function renderSizes(color) {
    const sizeContainer = document.getElementById('sizeOptions');
    if (!sizeContainer) return;
    sizeContainer.innerHTML = '';
    const filtered = variants.filter(v => v.color === color);

    selectedSizes = [];
    document.getElementById('selectedSizes').innerText = 'None';

    filtered.forEach((v, index) => {
        const btn = document.createElement('div');
        btn.className = 'size-btn';
        btn.textContent = v.size;
        btn.addEventListener('click', () => {
            document.querySelectorAll('.size-btn').forEach(b => {
                b.classList.remove('selected');
                b.style.borderColor = '#ccc'; 
            });
            btn.classList.add('selected');
            btn.style.borderColor = btn.style.backgroundColor; 
            selectedSizes = [v.size];
            document.getElementById('selectedSizes').innerText = v.size;
            document.getElementById('variantInfo').innerText = `Price: ₦${v.price} | Stock: ${v.stock}`;
        });
        sizeContainer.appendChild(btn);
        if (index === 0) {
            btn.click();
        }
    });

    if (filtered.length > 0) {
        const v = filtered[0];
        document.getElementById('variantInfo').innerText = `Price: ₦${v.price} | Stock: ${v.stock}`;
    }
}

function addToCart() {
    if (!selectedColor || selectedSizes.length === 0) {
        alert("Please select a color and size first!");
        return;
    }
    const qty = parseInt(document.getElementById('quantityInput').value);
    const variant = variants.find(v => v.color === selectedColor && selectedSizes.includes(v.size));
    if (!variant) {
        alert("Selected variant not found.");
        return;
    }
    if (qty > variant.stock) {
        alert(`Only ${variant.stock} in stock`);
        return;
    }
    const cartItem = {
        productId: <?php echo $product['id']; ?>,
        name: "<?php echo addslashes($product['name']); ?>",
        image: "<?php echo addslashes($product['image']); ?>",
        color: selectedColor,
        size: selectedSizes.join(', '),
        price: variant.price,
        stock: variant.stock,
        quantity: qty
    };
    saveToCart(cartItem);
}

function saveToCart(cartItem) {
    let cart = JSON.parse(localStorage.getItem('cartData')) || [];
    let existing = cart.find(item =>
        item.productId === cartItem.productId &&
        item.color === cartItem.color &&
        item.size === cartItem.size
    );
    if (existing) {
        if (existing.stock === 0 || existing.quantity + cartItem.quantity <= existing.stock) {
            existing.quantity += cartItem.quantity;
        } else {
            alert("No more stock available!");
            return;
        }
    } else {
        cart.push(cartItem);
    }
    localStorage.setItem('cartData', JSON.stringify(cart));
    updateCartCount();
    renderCart();
    document.getElementById("cartPanel").classList.add("active");
}

function toggleCart() {
    document.getElementById("cartPanel").classList.toggle("active");
    renderCart();
}

function renderCart() {
    let cart = JSON.parse(localStorage.getItem('cartData')) || [];
    let cartContainer = document.getElementById("cartItems");
    let cartTotal = 0;
    cartContainer.innerHTML = '';
    if (cart.length === 0) {
        cartContainer.innerHTML = '<p>Your cart is empty.</p>';
    } else {
        cart.forEach((item, index) => {
            let subtotal = item.price * item.quantity;
            cartTotal += subtotal;
            let div = document.createElement("div");
            div.className = "cart-item";
            div.innerHTML = `
                <img src="${item.image}" alt="${item.name}">
                <div class="cart-item-details">
                    <p class="item-name"><strong>${item.name}</strong></p>
                    <p>Color: ${item.color}</p>
                    <p>Size: ${item.size}</p>
                    <p>Qty: ${item.quantity} | <span class="item-price">₦${item.price}</span></p>
                </div>
                <button class="remove-btn" onclick="removeFromCart(${index})">Remove</button>
            `;
            cartContainer.appendChild(div);
        });
    }
    document.getElementById("cartTotal").innerHTML = "<strong>Total:</strong> ₦" + cartTotal.toLocaleString();
    updateCartCount();
}

function removeFromCart(index) {
    let cart = JSON.parse(localStorage.getItem('cartData')) || [];
    cart.splice(index, 1);
    localStorage.setItem('cartData', JSON.stringify(cart));
    renderCart();
}

function updateCartCount() {
    let cart = JSON.parse(localStorage.getItem('cartData')) || [];
    let count = cart.reduce((a, c) => a + c.quantity, 0);
    document.getElementById("cartCount").innerText = count;
}

document.addEventListener("DOMContentLoaded", () => {
    // This line will clear the cart every time the page loads
    localStorage.removeItem('cartData');
    
    updateCartCount();
    renderCart();
    
    const quantityInput = document.getElementById('quantityInput');
    const increaseBtn = document.getElementById('increaseQty');
    const decreaseBtn = document.getElementById('decreaseQty');

    if (increaseBtn && decreaseBtn && quantityInput) {
        increaseBtn.addEventListener('click', () => {
            let currentVal = parseInt(quantityInput.value);
            if (!isNaN(currentVal)) {
                quantityInput.value = currentVal + 1;
            }
        });

        decreaseBtn.addEventListener('click', () => {
            let currentVal = parseInt(quantityInput.value);
            if (!isNaN(currentVal) && currentVal > 1) {
                quantityInput.value = currentVal - 1;
            }
        });
    }
});
</script>
</body>
</html>