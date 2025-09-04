<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 📊 DB connection
$connection = new mysqli("localhost", "root", "", "bbbb");
if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}
$connection->set_charset('utf8mb4');

$login_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login_input = trim($_POST['login_input'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($login_input !== '' && $password !== '') {
        // 🔍 Look up user by email or mobile number
        $stmt = $connection->prepare("
            SELECT id, full_name, password, role 
            FROM users 
            WHERE email = ? OR mobile_number = ?
        ");
        if (!$stmt) {
            die("Prepare failed: " . $connection->error);
        }

        $stmt->bind_param("ss", $login_input, $login_input);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 1) {
            $stmt->bind_result($user_id, $full_name, $stored_password, $role);
            $stmt->fetch();

            $login_ok = false;

            // ✅ Verify password
            if (password_verify($password, $stored_password)) {
                $login_ok = true;

                // 🔄 Rehash if needed
                if (password_needs_rehash($stored_password, PASSWORD_DEFAULT)) {
                    $new_hash = password_hash($password, PASSWORD_DEFAULT);
                    $upd = $connection->prepare("UPDATE users SET password = ? WHERE id = ?");
                    if ($upd) {
                        $upd->bind_param("si", $new_hash, $user_id);
                        $upd->execute();
                        $upd->close();
                    }
                }
            }

            // 🧠 Optional fallback for legacy plaintext passwords
            elseif (hash_equals($stored_password, $password)) {
                $login_ok = true;
                $new_hash = password_hash($password, PASSWORD_DEFAULT);
                $upd = $connection->prepare("UPDATE users SET password = ? WHERE id = ?");
                if ($upd) {
                    $upd->bind_param("si", $new_hash, $user_id);
                    $upd->execute();
                    $upd->close();
                }
            }

            if ($login_ok) {
                // 🧑‍💼 Store session
                $_SESSION['user_id'] = $user_id;
                $_SESSION['full_name'] = $full_name;
                $_SESSION['role'] = $role;

                // 🚪 Redirect based on role
                if (strtolower($role) === 'admin') {
                    header("Location: dashboard.php");
                } else {
                    header("Location: product_display.php");
                }
                exit;
            } else {
                $login_error = "Invalid password.";
            }
        } else {
            $login_error = "No account found with that email or mobile number.";
        }

        $stmt->close();
    } else {
        $login_error = "Please enter both email/mobile number and password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login - Lexxybee</title>
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
    header nav { display: flex; gap: 15px; }
    .hamburger { display: none; background: none; border: none; font-size: 24px; cursor: pointer; color: #f0c040; }
    @media (max-width:768px) { header nav { display: none; flex-direction: column; background:#1e2a38; position:absolute; right:20px; top:70px; padding:15px; border-radius:8px; } header nav.show { display: flex; } .hamburger { display: block; } }
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
  <h2>Login to LexybeeCloset</h2>
  <form method="POST" action="login.php" autocomplete="off" novalidate>
    <input type="text" name="login_input" placeholder="Email or Mobile Number" required />
    <input type="password" name="password" placeholder="Password" required />
    <button type="submit">Login</button>
    <?php if (!empty($login_error)): ?>
      <div class="error"><?= htmlspecialchars($login_error) ?></div>
    <?php endif; ?>
    <div style="text-align: center; margin-top: 15px;">
      <span>Don't have an account?</span>
      <a href="signup.php" style="color: #007bff; text-decoration: none; font-weight: bold;">Sign up here</a>
    </div>
  </form>
</div>

<?php include 'footer.php'
?>
 
<script>
function toggleMenu() {
  const nav = document.querySelector('header nav');
  nav.classList.toggle('show');
}
</script>

</body>
</html>
