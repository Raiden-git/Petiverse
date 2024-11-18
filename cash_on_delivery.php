<?php
session_start();
$total_price = 0;
$cart_items = [];

// Check if the cart is not empty
if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    // Database connection
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "petiverse";

    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check for connection error
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Fetch cart items and calculate total price
    foreach ($_SESSION['cart'] as $product_id => $quantity) {
        $sql = "SELECT id, name, description, price, photo FROM products WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $item = $result->fetch_assoc()) {
            $item['quantity'] = $quantity;
            $cart_items[] = $item; // Add item to cart items array
            $total_price += $item['price'] * $quantity; // Calculate total price
        }
    }

    $stmt->close();
    $conn->close();
} else {
    echo "<script>alert('Your cart is empty. Please add items before proceeding.'); window.location.href='shop.php';</script>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cash on Delivery - Pet Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="./assets/css/cash_on_delivery.css">
</head>
<body>

<?php include 'Cus-NavBar/navBar.php'; ?>

<div class="container">
    <h1 class="text-3xl font-semibold mb-6">Cash on Delivery</h1>

    <div class="flex flex-col md:flex-row gap-4">
        <div class="order-summary flex-1">
            <h2 class="text-2xl font-semibold">Order Summary</h2>
            <?php if (!empty($cart_items)): ?>
                <ul class="space-y-4">
                    <?php foreach ($cart_items as $item): ?>
                        <li class="flex items-center justify-between">
                            <!-- Display product photo -->
                            <div class="item-details flex items-center">
                                <?php if ($item['photo']): ?>
                                    <!-- Display the product photo if it exists -->
                                    <img src="data:image/jpeg;base64,<?= base64_encode($item['photo']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="product-photo w-16 h-16 object-cover">
                                <?php else: ?>
                                    <!-- Display a default image if no photo exists -->
                                    <img src="default-image.jpg" alt="<?= htmlspecialchars($item['name']) ?>" class="product-photo w-16 h-16 object-cover">
                                <?php endif; ?>
                                <div class="ml-4">
                                    <span class="item-name font-semibold"><?= htmlspecialchars($item['name']) ?></span>
                                    <span class="item-quantity text-gray-500">x<?= htmlspecialchars($item['quantity']) ?></span>
                                </div>
                            </div>
                            <div class="ml-auto">
                                <span class="item-total text-lg font-semibold">LKR <?= number_format($item['price'] * $item['quantity'], 2) ?></span>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <div class="total-amount mt-4 text-xl font-bold">Total Amount: LKR <?= number_format($total_price, 2) ?></div>
            <?php else: ?>
                <p>Your cart is empty.</p>
            <?php endif; ?>
        </div>

        <div class="form-section flex-1">
            <h2 class="text-2xl font-semibold">Delivery Information</h2>
            <form action="confirm_order.php" method="POST">
                <div class="mb-4">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" required class="w-full p-2 border rounded">
                </div>
                <div class="mb-4">
                    <label for="address">Delivery Address</label>
                    <textarea id="address" name="address" rows="3" required class="w-full p-2 border rounded"></textarea>
                </div>
                <div class="mb-4">
                    <label for="phone">Phone Number</label>
                    <input type="text" id="phone" name="phone" required class="w-full p-2 border rounded">
                </div>
                <div class="mb-4">
                    <label for="postal_code">Postal Code</label>
                    <input type="text" id="postal_code" name="postal_code" required class="w-full p-2 border rounded">
                </div>
                <button type="submit" class="confirm-button bg-blue-500 text-white p-2 rounded mt-4">Confirm Order</button>
            </form>
        </div>
    </div>
</div>

<!-- Footer -->
<?php include 'footer.php'; ?>

</body>
</html>
