
<header>
  <div class="logo">
    <img src="logoimg.jpg" alt="Lexxybee Logo">
    <h2>LEXYBEE</h2>
  </div>
<form class="search-bar" method="GET" action="">
  <input type="text" name="search" placeholder="Search products..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
  <button type="submit"><i class="fas fa-search"></i></button>
</form>
  <button class="hamburger" onclick="toggleMenu()">
    <i class="fas fa-bars"></i>
  </button>

  <nav>
    <a href="#">Login</a>
    <a href="#">Signup</a>
    <a href="#">About</a>
    <span class="cart-icon-wrapper">
 <div class="cart-icon-wrapper" style="position: relative;">
  <i class="fas fa-shopping-cart" style="font-size: 24px;"></i>
  <span class="cart-dot" style="
      display: none;
      position: absolute;
      top: -5px;
      right: -5px;
      width: 12px;
      height: 12px;
      background-color: red;
      border-radius: 50%;
  "></span>
</div>

</span>

  </nav>
</header>