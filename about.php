<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>About - Lexybeeclosets</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap');

    :root {
      --primary: #f0c040;
      --primary-hover: #e0ac30;
      --bg: #f5f7fa;
      --text: #333;
      --muted: #666;
      --card-bg: #fff;
      --card-shadow: rgba(0, 0, 0, 0.06);
      --header-bg: #1e2a38;
      --footer-bg: #1e2a38;
      --font-family: "Inter", "Segoe UI", sans-serif;
      --radius: 10px;
      --transition: 0.3s ease;
    }

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
    }

    a {
      text-decoration: none;
      color: white;
    }

    header {
      width: 100%;
      background: var(--header-bg);
      color: white;
      padding: 25px 20px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 20px;
      flex-wrap: wrap;
    }

    .logo {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .logo img {
      width: 40px;
      height: 40px;
    }

    nav a {
      color: #fff;
      margin-left: 15px;
      text-decoration: none;
      font-weight: bold;
    }

    .about-container {
      max-width: 1000px;
      margin: 40px auto;
      padding: 20px;
      background-color: #fff;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
      border-radius: 8px;
    }

    .section {
      margin-bottom: 40px;
      text-align: center;
    }

    .section h3 {
      color: #444;
      border-bottom: 2px solid #ddd;
      padding-bottom: 10px;
      margin-bottom: 20px;
    }

    .founders-img {
      display: flex;
      justify-content: center;
      gap: 20px;
      flex-wrap: wrap;
      margin-bottom: 15px;
    }

    .founders-img img {
      width: 200px;
      height: 200px;
      object-fit: cover;
      border-radius: 50%;
    }

    .section img {
      width: 210px;
      height: 280px;
      object-fit: cover;
      border-radius: 50%;
      margin-bottom: 15px;
    }

    .section p {
      line-height: 1.6;
      margin: 5px 0;
    }

    footer {
      background: var(--header-bg);
      color: #eee;
      padding: 40px 30px;
      font-size: 14px;
    }

    footer h4 {
      color: var(--primary);
    }

    .footer-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 30px;
    }

    .footer-note {
      margin-top: 20px;
      font-style: italic;
      color: #ccc;
    }

    .footer-bottom {
      text-align: center;
      margin-top: 30px;
      font-size: 12px;
      color: #aaa;
    }

    footer i {
      margin-right: 8px;
      color: var(--primary);
    }

    .footer-grid ul li {
      margin-bottom: 10px;
    }

    .footer-grid a {
      color: var(--primary);
    }

    .footer-grid a:hover {
      text-decoration: underline;
    }

    @media (max-width: 768px) {
      header, .footer-grid {
        flex-direction: column;
        align-items: center;
      }

      nav {
        margin-top: 10px;
      }

      .about-container {
        margin: 20px;
      }

      .founders-img {
        flex-direction: column;
        align-items: center;
      }
    }
  </style>
</head>
<body>
<body>

<header>
  <div class="logo">
    <img src="logoimg.jpg" alt="Lexxybee Logo" />
    <h2>Lexybeeclosets</h2>
  </div>
  <nav>
    <a href="login.php">Login</a>
    <a href="signup.php">Signup</a>
    <a href="about.php">About</a>
  </nav>
</header>

<div class="about-container">

  <div class="section">
    <h3><i class="fas fa-users"></i> Meet the Founders</h3>
    <div class="founders-img">
      <img src="the couple.jpg" alt="Adedulu Bolanle Damilola & Wife" />
    </div>
    <p><strong>Names:</strong> Bolanle & Olalekan Adedulu </p>
    <p><strong>Roles:</strong> Founder & Co-supporter of Lexybee Closets</p>
    <p><strong>Vision:</strong> To empower fashion-forward individuals with affordable, stylish, and quality clothing while maintaining exceptional customer service.</p>
    <p><strong>Contact:</strong> info@lexybeeclosets.com | +23407033581634</p>
  </div>

  <div class="section">
    <h3><i class="fas fa-code"></i> Developer</h3>
    <img src="Samson.png" alt="Developer" />
    <p><strong>Name:</strong> Adebayo Ololade Samson</p>
    <p><strong>Role:</strong> Web Developer</p>
    <p><strong>Skills:</strong> HTML, CSS, JavaScript, PHP, Responsive Design</p>
    <p><strong>Contact:</strong> sa9362673@gmail.com | Adebayo Samson</p>
  </div>

  <div class="section">
    <h3><i class="fas fa-store"></i> About LexyBeeClosets</h3>
    <p>LexyBeeClosets is both an online and physical store located in Agric, Ikorodu, Lagos State. We offer a wide range of products including footwear, nightwear, underwear, household appliances, bags, smartwatches, clothing, and much more.</p>
    <p>All our products are available at wholesale prices, and we deliver nationwide as well as internationally. Both dropshippers and retailers are welcome.</p>
    <p><strong>Contact us today — with LexyBeeClosets, satisfaction is guaranteed!</strong></p>
  </div>

</div>


<footer>
  <!-- Same footer as before -->
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
    <li>
  <a href="https://www.facebook.com/share/16dJPSAcNC/" target="_blank" style="color: #eee;">
    <i class="fab fa-facebook"></i> info@lexybeeclosets.com
  </a>
</li>
<li>
  <i class="fab fa-telegram-plane"></i>
  <a href="https://t.me/+1pFD0r4g2k9hNjQ0" target="_blank" style="color: #eee;">
    Telegram
  </a>
</li>

        <li><i class="fas fa-store"></i> Lexybee Closets</li>
        <li>
  <i class="fab fa-whatsapp"></i>
  <a href="https://chat.whatsapp.com/LY8miQLuLtE7EQyZ92bc3e?mode=ems_copy_t" target="_blank" style="color: #eee;">
    Join our WhatsApp Group
  </a>
</li>

        <li><i class="fas fa-clock"></i> Opening: Mondays - Fridays, 9am - 6pm</li>
      </ul>
    </div>
  </div>
  <div class="footer-bottom">
    © Lexybee Closets. All Rights Reserved. Powered By 3Core Technology Limited
  </div>
</footer>

</body>


</html>
