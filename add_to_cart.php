<?php
session_start();

// Initialize the cart if it doesn't exist
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Check if a product ID is provided
if (isset($_GET['id'])) {
    $product_id = $_GET['id'];

    // Add product to cart (you can customize the cart structure)
    $_SESSION['cart'][$product_id] = ($_SESSION['cart'][$product_id] ?? 0) + 1;
    header("Location: customer.php");
    exit();
}
?>
