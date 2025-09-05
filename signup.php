<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 📦 Load database connection
require_once 'database.php'; // Adjust path if needed
$connection = $conn;
// ✅ Ensure connection is valid
if (!isset($connection) || !($connection instanceof mysqli)) {
    die("❌ Database connection failed.");
}

$signup_error = '';
$signup_success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 🧼 Sanitize input
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $mobile_number = trim($_POST['mobile_number'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // ✅ Validate input
    if ($full_name && $email && $mobile_number && $password) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // 🔍 Check if full name exists
        $nameCheck = $connection->prepare("SELECT id FROM users WHERE full_name = ?");
        $nameCheck->bind_param("s", $full_name);
        $nameCheck->execute();
        $nameCheck->store_result();

        if ($nameCheck->num_rows > 0) {
            $signup_error = "Full name already exists.";
        } else {
            // 🔍 Check if email exists
            $emailCheck = $connection->prepare("SELECT id FROM users WHERE email = ?");
            $emailCheck->bind_param("s", $email);
            $emailCheck->execute();
            $emailCheck->store_result();

            if ($emailCheck->num_rows > 0) {
                $signup_error = "An account with this email already exists.";
            } else {
                // 📝 Insert new user
                $insert = $connection->prepare("INSERT INTO users (full_name, email, mobile_number, password) VALUES (?, ?, ?, ?)");
                $insert->bind_param("ssss", $full_name, $email, $mobile_number, $hashed_password);

                if ($insert->execute()) {
                    echo "<script>alert('Account created successfully'); window.location.href='login.php';</script>";
                    exit;
                } else {
                    $signup_error = "Something went wrong. Please try again.";
                }
                $insert->close();
            }
            $emailCheck->close();
        }
        $nameCheck->close();
    } else {
        $signup_error = "Please fill in all fields.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Signup - Lexxybee</title>
  <link rel="stylesheet" href="display.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    @keyframes fadeInUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
    @keyframes pulse { 0% { transform: scale(1); } 50% { transform: scale(1.05); } 100% { transform: scale(1); } }
    body { min-height: 100vh; background: #f4f6f8; display: flex; flex-direction: column; justify-content: center; }
    .login-container { max-width: 500px; min-height: 500px; margin: 60px auto; background: #fff; padding: 50px 40px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); animation: fadeInUp 0.6s ease-out; display: flex; flex-direction: column; justify-content: center; }
    .login-container h2 { text-align: center; margin-bottom: 25px; font-size: 24px; }
    .login-container input { width: 100%; padding: 12px; margin-top: 12px; border-radius: 6px; border: 1px solid #ccc; font-size: 16px; }
    .login-container button { width: 100%; padding: 12px; margin-top: 25px; background: #f0c040; color: white; border: none; border-radius: 6px; font-weight: bold; font-size: 16px; cursor: pointer; transition: background 0.3s ease; animation: pulse 1.5s infinite; }
    .login-container button:hover { background: #d9a82d; }
    .login-container .error { color: red; margin-top: 10px; text-align: center; }
    .login-container .success { color: green; margin-top: 10px; text-align: center; }

    /* Hamburger Menu CSS */
    header nav { display: flex; gap: 15px; }
    .hamburger { display: none; background: none; border: none; font-size: 24px; cursor: pointer; color: #f0c040; }
    @media (max-width:768px) {
      header nav { display: none; flex-direction: column; background:#1e2a38; position:absolute; right:20px; top:70px; padding:15px; border-radius:8px; }
      header nav.show { display: flex; }
      .hamburger { display: block; }
    }
  </style>
</head>
<body>

<header>
  <div class="logo">
    <img src="logoimg.jpg" alt="Lexxybee Logo">
    <h2>LexybeeClosets</h2>
  </div>
  <button class="hamburger" onclick="toggleMenu()">
    <i class="fas fa-bars"></i>
  </button>
  <nav>
    <a href="login.php">Login</a>
    <a href="signup.php">Signup</a>
    <a href="about.php">About</a>
  </nav>
</header>

<div class="login-container">
  <h2>Create Your Account</h2>
  <form method="POST" action="">
    <input type="text" name="full_name" placeholder="Full Name" required>
    <input type="email" name="email" placeholder="Email Address" required>
    <input type="text" name="mobile_number" placeholder="Mobile Number" required
           pattern="^\+?\d{10,15}$"
           title="Enter a valid mobile number (10-15 digits, optional + for country code)">
    <input type="password" name="password" placeholder="Create Password" 
           title="Password must be at least 8 characters long and include uppercase, lowercase, number, and special character.">

    <button type="submit">Sign Up</button>
    <?php if ($signup_error): ?>
      <div class="error"><?php echo htmlspecialchars($signup_error); ?></div>
    <?php elseif ($signup_success): ?>
      <div class="success"><?php echo htmlspecialchars($signup_success); ?></div>
    <?php endif; ?>
    <div style="text-align: center; margin-top: 15px;">
      <span>Already have an account?</span>
      <a href="login.php" style="color: #007bff; text-decoration: none; font-weight: bold;">Login here</a>
    </div>
  </form>
</div>

<?php include 'footer.php'; ?>

<script>
function toggleMenu() {
  const nav = document.querySelector('header nav');
  nav.classList.toggle('show');
}
</script>

</body>
</html>
