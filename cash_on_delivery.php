<?php
session_start();
$total_price = 0;
$cart_items = [];

// Fetch cart items from session and calculate total price
if (isset($_SESSION['cart'])) {
    // Database connection
    $conn = new mysqli("localhost", "root", "", "petiverse");

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    foreach ($_SESSION['cart'] as $product_id => $quantity) {
        $sql = "SELECT * FROM products WHERE id = '$product_id'";
        $result = $conn->query($sql);
        if ($result) {
            $item = $result->fetch_assoc();
            $item['quantity'] = $quantity;
            $cart_items[] = $item;
            $total_price += $item['price'] * $quantity;
        }
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cash on Delivery - Pet Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/scrollbar.css">
    <style>
        /* General Styles */
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f9fafb;
            color: #2d3748;
            line-height: 1.6;
        }

        /* Container styling */
        .container {
            max-width: 900px;
            margin: auto;
        }

        h1 {
            font-size: 2.5rem;
            font-weight: 700;
            color: #2d3748;
            text-align: center;
            margin-bottom: 2rem;
            position: relative;
        }

        h1::after {
            content: '';
            display: block;
            width: 50px;
            height: 4px;
            background-color: #38a169;
            margin: 0.5rem auto 0;
            border-radius: 2px;
        }

        /* Flexbox for order summary and form */
        .form-section-container {
            display: flex;
            justify-content: space-between;
            gap: 2rem;
        }

        /* Order Summary */
        .order-summary {
            background-color: #fff;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            width: 45%;
        }

        .order-summary h2 {
            font-size: 1.75rem;
            font-weight: 600;
            color: #4a5568;
            margin-bottom: 1.5rem;
        }

        .order-summary ul {
            padding: 0;
            list-style: none;
        }

        .order-summary li {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.75rem 0;
            border-bottom: 1px solid #e2e8f0;
        }

        .order-summary li:last-child {
            border-bottom: none;
        }

        /* Product details inside the Order Summary */
        .order-summary img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
            margin-right: 1rem;
        }

        .order-summary .item-details {
            display: flex;
            align-items: center;
        }

        .order-summary .item-name {
            font-weight: 600;
        }

        .order-summary .item-quantity {
            color: #718096;
            margin-left: 0.5rem;
        }

        .order-summary .item-total {
            font-weight: 600;
            color: #2d3748;
        }

        /* Total Amount */
        .total-amount {
            font-size: 1.5rem;
            font-weight: bold;
            text-align: right;
            margin-top: 1.5rem;
            color: #2c5282;
            background-color: #edf2f7;
            border: 2px solid #38a169;
            border-radius: 8px;
            padding: 1rem;
            transition: background-color 0.3s ease;
        }

        .total-amount:hover {
            background-color: #e2f7e2;
        }

        /* Form Section */
        .form-section {
            background-color: #fff;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            width: 55%;
        }

        .form-section h2 {
            font-size: 1.75rem;
            font-weight: 600;
            color: #4a5568;
            margin-bottom: 1.5rem;
        }

        .form-section label {
            font-size: 0.875rem;
            font-weight: 500;
            color: #4a5568;
            margin-bottom: 0.5rem;
            display: block;
        }

        .form-section input, 
        .form-section textarea {
            width: 100%;
            padding: 0.75rem;
            font-size: 1rem;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            background-color: #f7fafc;
            transition: border-color 0.2s ease;
        }

        .form-section input:focus, 
        .form-section textarea:focus {
            border-color: #63b3ed;
            outline: none;
        }

        /* Button Styles */
        .confirm-button {
            background-color: #38a169;
            color: #fff;
            padding: 0.75rem 2rem;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            text-align: center;
            margin-top: 1rem;
            transition: background-color 0.3s ease, transform 0.2s ease;
            display: inline-block;
            width: 100%;
        }

        .confirm-button:hover {
            background-color: #2f855a;
            transform: scale(1.05);
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .form-section-container {
                flex-direction: column;
            }

            .form-section, .order-summary {
                width: 100%;
            }
        }
    </style>
</head>
<body>

<?php include 'Cus-NavBar/navBar.php'; ?>

<div class="container mx-auto py-16 px-4">
    <h1>Cash on Delivery</h1>

    <div class="form-section-container">

        <!-- Order Summary (Left Side) -->
        <div class="order-summary">
            <h2>Order Summary</h2>
            <?php if (!empty($cart_items)): ?>
                <ul>
                    <?php foreach ($cart_items as $item): ?>
                        <li class="flex items-center">
                            <!-- Display product photo -->
                            <div class="item-details">
                                <img src="../uploads/<?= htmlspecialchars($item['photo']) ?>" alt="<?= htmlspecialchars($item['name']) ?>">
                                <div>
                                    <span class="item-name"><?= htmlspecialchars($item['name']) ?></span>
                                    <span class="item-quantity">x<?= $item['quantity'] ?></span>
                                </div>
                            </div>
                            <div>
                                <span class="item-total">$<?= number_format($item['price'] * $item['quantity'], 2) ?></span>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <div class="total-amount">Total Amount: $<?= number_format($total_price, 2) ?></div>
            <?php else: ?>
                <p>Your cart is empty.</p>
            <?php endif; ?>
        </div>

        <!-- Form Section (Right Side) -->
        <div class="form-section">
            <h2>Delivery Information</h2>
            <form action="confirm_order.php" method="POST">
                <div class="mb-4">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" required>
                </div>
                <div class="mb-4">
                    <label for="address">Delivery Address</label>
                    <textarea id="address" name="address" rows="3" required></textarea>
                </div>
                <div class="mb-4">
                    <label for="phone">Phone Number</label>
                    <input type="text" id="phone" name="phone" required>
                </div>
                <div class="mb-4">
                    <label for="postal_code">Postal Code</label>
                    <input type="text" id="postal_code" name="postal_code" required>
                </div>
                <button type="submit" class="confirm-button">Confirm Order</button>
            </form>
        </div>
    </div>
</div>

</body>
</html>
