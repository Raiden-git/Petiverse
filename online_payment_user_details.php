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
            $cart_items[] = $item;
            $total_price += $item['price'] * $quantity;
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
    <title>Online Payment - Pet Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body>

<?php include 'Cus-NavBar/navBar.php'; ?>

<div class="container">
    <h1 class="text-3xl font-semibold mb-6">Online Payment</h1>

    <div class="flex flex-col md:flex-row gap-4">
        <div class="order-summary flex-1">
            <h2 class="text-2xl font-semibold">Order Summary</h2>
            <?php if (!empty($cart_items)): ?>
                <ul class="space-y-4">
                    <?php foreach ($cart_items as $item): ?>
                        <li class="flex items-center justify-between">
                            <div class="item-details flex items-center">
                                <?php if ($item['photo']): ?>
                                    <img src="data:image/jpeg;base64,<?= base64_encode($item['photo']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="product-photo w-16 h-16 object-cover">
                                <?php else: ?>
                                    <img src="default-image.jpg" alt="<?= htmlspecialchars($item['name']) ?>" class="product-photo w-16 h-16 object-cover">
                                <?php endif; ?>
                                <div class="ml-4">
                                    <span class="item-name font-semibold"><?= htmlspecialchars($item['name']) ?></span>
                                    <span class="item-quantity text-gray-500">x<?= htmlspecialchars($item['quantity']) ?></span>
                                </div>
                            </div>
                            <div class="ml-auto">
                                <span class="item-total text-lg font-semibold">LKR.<?= number_format($item['price'] * $item['quantity'], 2) ?></span>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <div class="total-amount mt-4 text-xl font-bold">Total Amount: LKR.<?= number_format($total_price, 2) ?></div>
            <?php else: ?>
                <p>Your cart is empty.</p>
            <?php endif; ?>
        </div>

        <div class="form-section flex-1">
            <h2 class="text-2xl font-semibold">Payment Information</h2>
            <form action="online_payment.php" method="POST">
    <div class="mb-4">
        <label for="full_name">Full Name</label>
        <input type="text" id="full_name" name="full_name" required class="w-full p-2 border rounded">
    </div>
    <div class="mb-4">
        <label for="phone_number">Phone Number</label>
        <input type="text" id="phone_number" name="phone_number" required class="w-full p-2 border rounded">
    </div>
    <div class="mb-4">
        <label for="delivery_address">Delivery Address</label>
        <textarea id="delivery_address" name="delivery_address" rows="3" required class="w-full p-2 border rounded"></textarea>
    </div>
    <div class="mb-4">
        <label for="postal_code">Postal Code (Optional)</label>
        <input type="text" id="postal_code" name="postal_code" class="w-full p-2 border rounded">
    </div>
    <input type="hidden" name="total_price" value="<?= htmlspecialchars($total_price) ?>">
    <button type="submit" class="confirm-button bg-green-500 text-white p-2 rounded mt-4">Proceed to Payment</button>
</form>


        </div>
    </div>
</div>

<?php include 'footer.php'; ?>

</body>
</html>
