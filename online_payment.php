<?php
// Load Stripe PHP SDK
require 'vendor/autoload.php';
require 'db.php'; 
session_start();

\Stripe\Stripe::setApiKey('sk_test_51QNWQKG2zxFLmtj9w1HsGLgAkVByklUMkMC59EYOk9A2XNaL5azhcTTlFT2LE5oJMkYPxOysXU4cdJidanITC70n00S49ksdJ4'); // Replace with your Stripe Secret Key

if (isset($_POST['full_name'], $_POST['delivery_address'], $_POST['phone_number'], $_POST['total_price']) && isset($_SESSION['cart'])) {
    // Retrieve and sanitize user details
    $user_id = $_SESSION['user_id'] ?? null; 
    $full_name = htmlspecialchars($_POST['full_name']);
    $delivery_address = htmlspecialchars($_POST['delivery_address']);
    $phone_number = htmlspecialchars($_POST['phone_number']);
    $postal_code = htmlspecialchars($_POST['postal_code'] ?? '');
    $total_price = $_POST['total_price'];
    $cart_items = $_SESSION['cart'];
    $order_id = strtoupper(uniqid('ORDER_')); 

    // Check if the user is logged in
    if (!$user_id) {
        header('Location: login.php');
        exit;
    }

    // Insert cart details into the database
    foreach ($cart_items as $product_id => $quantity) {
        $query = "SELECT id, name, price FROM products WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $product_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            $product_name = $row['name'];
            $price = $row['price'];
            $total_item_price = $price * $quantity;

            $insert_query = "INSERT INTO online_payment_orders (
                user_id, order_id, full_name, delivery_address, phone_number, postal_code,
                product_id, product_name, quantity, price, total_price, created_at, updated_at
            ) VALUES (
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW()
            )";

            $insert_stmt = $conn->prepare($insert_query);
            $insert_stmt->bind_param(
                'isssssisidd',
                $user_id, $order_id, $full_name, $delivery_address, $phone_number, $postal_code,
                $product_id, $product_name, $quantity, $price, $total_item_price
            );
            $insert_stmt->execute();
            $insert_stmt->close();
        }
        $stmt->close();
    }

    // Store user details and total price in the session
    $_SESSION['full_name'] = $full_name;
    $_SESSION['delivery_address'] = $delivery_address;
    $_SESSION['phone_number'] = $phone_number;
    $_SESSION['postal_code'] = $postal_code;
    $_SESSION['total_price'] = $total_price;

    // Redirect to Stripe Checkout
    try {
        $currency = 'LKR'; // Currency
        $session = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => $currency,
                    'product_data' => [
                        'name' => 'Cart Purchase',
                    ],
                    'unit_amount' => $total_price * 100,
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => 'http://localhost/petiverse/online_payment_success.php?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => 'http://localhost/cart.php',
        ]);

        header("HTTP/1.1 303 See Other");
        header("Location: " . $session->url);
        exit;
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
        exit;
    }
} else {
    echo "Invalid request.";
    exit;
}
?>
