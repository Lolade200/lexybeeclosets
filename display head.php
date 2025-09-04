
<?php

$full_name = $_SESSION['full_name'] ?? null;
?>
<header>
  <div class="logo">
    <img src="bee.png" alt="Lexxybee Logo">
     <h2>LexybeClosets</h2>
  </div>
<form id="searchForm" class="search-bar">
  <input type="text" id="searchInput" name="search" placeholder="Search by name, description, or category...">
  <button type="submit"><i class="fas fa-search"></i></button>
</form>


  <button class="hamburger" onclick="toggleMenu()">
    <i class="fas fa-bars"></i>
  </button>
  <nav>
      <?php if ($full_name): ?>
      <span style="color: white; font-weight: bold;">Welcome, <?php echo htmlspecialchars($full_name); ?>!</span>
    <?php endif; ?>
    <a href="product_display.php">Home</a>
    <a href="logout.php">Logout</a>

<div class="cart-icon-wrapper">
  <i class="fas fa-shopping-cart" id="cart-icon"></i>
  <span class="cart-dot" id="cart-count">0</span>
</div>


  </nav>
</header>