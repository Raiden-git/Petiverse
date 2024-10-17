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
    <title>Online Payment - Pet Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/scrollbar.css">
    <style>
        /* General Styles */
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f7fafc;
            color: #2d3748;
        }
        
        /* Container styling */
        .container {
            max-width: 900px;
            margin: auto;
        }

        /* Title and Subtitles */
        h1 {
            font-size: 2.5rem;
            font-weight: 700;
            color: #2d3748;
            text-align: center;
            margin-bottom: 2rem;
        }

        h2 {
            font-size: 1.75rem;
            font-weight: 600;
            color: #4a5568;
            margin-bottom: 1rem;
        }

        /* Flexbox for order summary and form */
        .form-section-container {
            display: flex;
            justify-content: space-between;
            gap: 3rem;
        }

        /* Order Summary */
        .order-summary {
            background-color: #fff;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            width: 50%; /* Make it 50% width to fit next to form */
            transition: transform 0.3s ease; /* Add smooth transition on hover */
        }

        .order-summary:hover {
            transform: scale(1.02); /* Slightly scale up on hover for effect */
        }

        .order-summary h2 {
            font-size: 1.5rem; /* Adjust title size */
            font-weight: bold; /* Make title bold */
            margin-bottom: 1rem; /* Space below the title */
            color: #2d3748; /* Title color */
        }

        .order-summary ul {
            padding: 0;
            list-style: none;
            margin: 0;
        }

        .order-summary li {
            display: flex;
            justify-content: space-between;
            padding: 1rem 0; /* Add more padding for better spacing */
            border-bottom: 1px solid #e2e8f0; /* Border below each item */
        }

        .order-summary li:last-child {
            border-bottom: none; /* Remove border for the last item */
        }

        .order-summary .item-name {
            font-weight: 600;
            color: #2c5282; /* Darker color for item name */
        }

        .order-summary .item-quantity {
            color: #718096; /* Lighter color for quantity */
            margin-left: 0.5rem; /* Space between item name and quantity */
        }

        .order-summary .item-price, .order-summary .item-total {
            color: #38a169; /* Green color for prices */
            font-weight: bold; /* Bold for prices */
        }

        /* Total Amount Styling */
        .total-amount {
            font-size: 1.5rem; /* Larger font size for emphasis */
            font-weight: bold; /* Bold for emphasis */
            margin-top: 1.5rem; /* Space above total amount */
            text-align: right; /* Align total amount to the right */
            color: #2c5282; /* Darker color for better visibility */
            background-color: #edf2f7; /* Light background for contrast */
            border: 2px solid #38a169; /* Green border for a pop of color */
            border-radius: 8px; /* Rounded corners */
            padding: 1rem; /* Add padding for better spacing */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Subtle shadow for depth */
            transition: background-color 0.3s ease; /* Smooth transition on hover */
        }

        .total-amount:hover {
            background-color: #e2f7e2; /* Change background on hover */
        }

        /* Form Styles */
        .form-section {
            background-color: #fff;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            width: 50%; 
        }

        label {
            font-size: 0.875rem;
            font-weight: 500;
            color: #4a5568;
            margin-bottom: 0.5rem;
            display: block;
        }

        input {
            width: 100%;
            padding: 0.75rem;
            font-size: 1rem;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            background-color: #edf2f7;
            transition: border-color 0.2s ease;
            margin-bottom: 1rem; /* Space between fields */
        }

        input:focus {
            border-color: #63b3ed;
            outline: none;
        }

        /* Payment Method Icons */
        .payment-icons {
            display: flex;
            justify-content: space-between;
            margin: 1rem 0; /* Space around icons */
        }

        .payment-icon {
            width: 48px; /* Adjust width for icons */
            height: auto;
        }

        /* Button Styles */
        .pay-button {
            background-color: #38a169;
            color: #fff;
            padding: 0.75rem 2rem;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            text-align: center;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }

        .pay-button:hover {
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
    <h1>Online Payment</h1>
    
    <div class="form-section-container">

        <!-- Order Summary (Left Side) -->
        <div class="order-summary">
            <h2>Order Summary</h2>
            <?php if (!empty($cart_items)): ?>
                <ul>
                    <?php foreach ($cart_items as $item): ?>
                        <li class="flex items-center">
                            <!-- Display product photo -->
                            <img src="../uploads/<?= htmlspecialchars($item['photo']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="w-16 h-16 object-cover rounded-md mr-4">
                            
                            <!-- Product details -->
                            <div class="flex-grow">
                                <span class="item-name"><?= htmlspecialchars($item['name']) ?></span><br>
                                <span class="item-quantity">x<?= $item['quantity'] ?></span>
                                <span class="item-price">$<?= number_format($item['price'], 2) ?></span>
                                <span class="item-total">= $<?= number_format($item['price'] * $item['quantity'], 2) ?></span>
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
            <div class="payment-icons">
                <img src="./src/img/visa.png" alt="Visa" class="payment-icon">
                <img src="./src/img/mastercard.png" alt="MasterCard" class="payment-icon">
                <img src="./src/img/paypal.png" alt="PayPal" class="payment-icon">
            </div>

            <form action="process_payment.php" method="POST">
                <h2 class="text-lg font-semibold mb-4">Payment Details</h2>
                
                <div class="mb-4">
                    <label for="card-name">Cardholder's Name</label>
                    <input type="text" id="card-name" name="card_name" placeholder="Name" required>
                </div>
                <div class="mb-4">
                    <label for="card-number">Card Number</label>
                    <input type="text" id="card-number" name="card_number" placeholder="Card Number" required>
                </div>
                <div class="flex gap-4 mb-4">
                    <div class="flex-grow">
                        <label for="expiry-date">Expiry Date (MM/YY)</label>
                        <input type="text" id="expiry-date" name="expiry_date" placeholder="MM/YY" required>
                    </div>
                    <div class="flex-grow">
                        <label for="cvv">CVV</label>
                        <input type="text" id="cvv" name="cvv" placeholder="***" required>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="billing-address">Billing Address</label>
                    <input type="text" id="billing-address" name="billing_address" placeholder="Address" required>
                </div>
                <div class="flex gap-4 mb-4">
                    <div class="flex-grow">
                        <label for="city">City</label>
                        <input type="text" id="city" name="city" required>
                    </div>
                    <div class="flex-grow">
                        <label for="zip">Zip Code</label>
                        <input type="text" id="zip" name="zip" required>
                    </div>
                </div>

                <div class="text-center">
                    <button type="submit" class="pay-button">Pay Now</button>
                </div>
            </form>
        </div>
    </div>
</div>

</body>
</html>
