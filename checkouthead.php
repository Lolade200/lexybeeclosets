<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'database.php';
// Connect to database
//$connection = new mysqli("localhost", "root", "", "bbbb");
//if ($connection->connect_error) {
//    die("Connection failed: " . $connection->connect_error);
//}

// Use session name or default to "Guest"
$full_name = !empty($_SESSION['full_name']) ? $_SESSION['full_name'] : 'Guest';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_address      = trim($_POST['user_address'] ?? '');
    $delivery_location = trim($_POST['delivery_location'] ?? '');
    $rawProducts       = $_POST['products'] ?? '';
    $phone_number      = trim($_POST['phone_number'] ?? '');

    if (!$user_address || !$delivery_location || !$rawProducts || !$phone_number) {
        echo "<script>alert('Please fill in all required fields.'); history.back();</script>";
        exit;
    }

    // Handle receipt upload
    $receipt_path = '';
    if (!empty($_FILES['receipt_image']['name']) && $_FILES['receipt_image']['error'] === UPLOAD_ERR_OK) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $filename     = preg_replace("/[^a-zA-Z0-9\._-]/", "_", basename($_FILES["receipt_image"]["name"]));
        $receipt_path = $target_dir . time() . "_" . $filename;

        if (!move_uploaded_file($_FILES["receipt_image"]["tmp_name"], $receipt_path)) {
            echo "<script>alert('Failed to upload receipt. Please try again.'); history.back();</script>";
            exit;
        }
    } else {
        echo "<script>alert('Please upload your receipt image.'); history.back();</script>";
        exit;
    }

    $order_id        = 'LB-' . rand(10000, 99999);
    $orderedProducts = json_decode($rawProducts, true);

    if (json_last_error() !== JSON_ERROR_NONE || !is_array($orderedProducts)) {
        echo "<script>alert('Invalid product data.'); history.back();</script>";
        exit;
    }

    $finalProducts = [];
    $total_price   = 0;

    foreach ($orderedProducts as $item) {
        $productId = intval($item['productId'] ?? 0);
        $name      = $item['name'] ?? '';
        $color     = strtolower(trim($item['color'] ?? ''));
        $size      = strtolower(trim($item['size'] ?? ''));
        $quantity  = intval($item['quantity'] ?? 1);
        $image     = $item['image'] ?? '';

        $price = 0;
        $priceQuery = $connection->prepare("
            SELECT price FROM product_variants 
            WHERE product_id = ? AND LOWER(size) = ? AND LOWER(color) = ? 
            LIMIT 1
        ");
        if ($priceQuery) {
            $priceQuery->bind_param("iss", $productId, $size, $color);
            $priceQuery->execute();
            $result = $priceQuery->get_result();
            if ($result && $result->num_rows > 0) {
                $price = (float)$result->fetch_assoc()['price'];
            } else {
                $fallback = $connection->prepare("SELECT price FROM product_variants WHERE product_id = ? LIMIT 1");
                if ($fallback) {
                    $fallback->bind_param("i", $productId);
                    $fallback->execute();
                    $fallbackResult = $fallback->get_result();
                    if ($fallbackResult && $fallbackResult->num_rows > 0) {
                        $price = (float)$fallbackResult->fetch_assoc()['price'];
                    }
                    $fallback->close();
                }
            }
            $priceQuery->close();
        }

        $itemTotal    = $price * $quantity;
        $total_price += $itemTotal;

        $finalProducts[] = compact('productId', 'name', 'color', 'size', 'quantity', 'price', 'image');
    }

    $productsJson = json_encode($finalProducts, JSON_UNESCAPED_UNICODE);

    $connection->begin_transaction();

    // Insert order
    $stmt = $connection->prepare("
        INSERT INTO orders 
        (order_id, full_name, phone_number, user_address, delivery_location, total_price, products, receipt_image, status, notified, stock_updated) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Received', 0, 0)
    ");
    $stmt->bind_param(
        "ssssisss",
        $order_id,
        $full_name,
        $phone_number,
        $user_address,
        $delivery_location,
        $total_price,
        $productsJson,
        $receipt_path
    );

    if (!$stmt->execute()) {
        echo "MySQL error: " . $stmt->error;
        $connection->rollback();
        exit;
    }

    // ✅ Reduce stock for each item
    foreach ($finalProducts as $product) {
        $updateStock = $connection->prepare("
            UPDATE product_variants 
            SET stock = stock - ? 
            WHERE product_id = ? AND LOWER(size) = ? AND LOWER(color) = ? AND stock >= ?
        ");
        if ($updateStock) {
            $qty = $product['quantity'];
            $updateStock->bind_param("iissi", $qty, $product['productId'], $product['size'], $product['color'], $qty);
            $updateStock->execute();
            $updateStock->close();
        }
    }

    $connection->commit();
    echo "<script>alert('Order placed successfully! Your Order ID is $order_id'); window.location.href='dashboard.php';</script>";
    $stmt->close();
    exit;
}

// Fetch user's past orders
$orders = [];
$stmt = $connection->prepare("
    SELECT products, order_id, created_at 
    FROM orders 
    WHERE full_name = ? 
    ORDER BY created_at DESC
");
$stmt->bind_param("s", $full_name);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $row['products'] = json_decode($row['products'], true);
    $orders[] = $row;
}
$stmt->close();
$connection->close();
?>
