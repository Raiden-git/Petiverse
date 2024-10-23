<?php
session_start();

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "petiverse";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle quantity change
if (isset($_POST['product_id']) && isset($_POST['quantity'])) {
    $product_id = $_POST['product_id'];
    $quantity = $_POST['quantity'];

    if ($quantity > 0) {
        $_SESSION['cart'][$product_id] = $quantity; // Update quantity
    } else {
        unset($_SESSION['cart'][$product_id]); // Remove item if quantity is 0
    }
}

// Handle removal of items
if (isset($_POST['remove_item'])) {
    $product_id = $_POST['product_id'];
    unset($_SESSION['cart'][$product_id]); // Remove item from cart
}

// Handle checkout action (optional)
if (isset($_POST['payment_method'])) {
    $payment_method = $_POST['payment_method'];

    // Redirect to the respective payment method page
    if ($payment_method === 'online') {
        header('Location: online_payment.php');
        exit;
    } elseif ($payment_method === 'cod') {
        header('Location: cash_on_delivery.php');
        exit;
    }
}

// Display the cart
$cart_items = [];
$total_price = 0; // Initialize total price
if (isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $product_id => $quantity) {
        $sql = "SELECT id, name, description, price, photo FROM products WHERE id = '$product_id'";
        $result = $conn->query($sql);
        if ($result) {
            $cart_item = $result->fetch_assoc();
            $cart_item['quantity'] = $quantity;
            $cart_items[] = $cart_item;

            // Calculate total price for all items
            $total_price += $cart_item['price'] * $quantity;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pet Shop - Cart</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/cart.css">
    <link rel="stylesheet" href="assets/css/scrollbar.css">

    <script>
        function updatePrice(productId, price) {
            const quantity = document.getElementById(`quantity-${productId}`).value;
            const totalPrice = (price * quantity).toFixed(2);
            document.getElementById(`total-price-${productId}`).innerText = `$${totalPrice}`;
            calculateTotal(); // Recalculate total on quantity change
        }

        function calculateTotal() {
            let total = 0;
            const cartItems = document.querySelectorAll('.cart-item');
            cartItems.forEach(item => {
                const itemTotal = parseFloat(item.querySelector('.item-total').innerText.replace('$', ''));
                total += itemTotal;
            });
            document.getElementById('grand-total').innerText = `$${total.toFixed(2)}`;
        }

        // Automatically submit form on quantity change
        function autoSubmitForm(productId) {
            document.getElementById(`form-${productId}`).submit(); // Submit the form
        }

        // Show modal for payment method selection
        function showPaymentOptions() {
            document.getElementById('paymentModal').classList.remove('hidden');
        }

        // Choose payment method and submit the form
        function choosePaymentMethod(method) {
            document.getElementById('payment_method').value = method;
            document.getElementById('checkoutForm').submit();
        }

        // Close the modal
        function closeModal() {
            document.getElementById('paymentModal').classList.add('hidden');
        }
    </script>

</head>
<body>

<?php include 'Cus-NavBar/navBar.php'; ?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl mb-6">Your Shopping Cart</h1>

    <?php if (!empty($cart_items)): ?>

        <div class="space-y-4">
            <?php foreach ($cart_items as $item): ?>

                <div class="cart-item flex items-center">
                    <!-- Convert binary data to base64 for displaying as an image -->
                    <img src="data:image/jpeg;base64,<?= base64_encode($item['photo']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="w-24 h-24 object-cover rounded-md mr-4">
                    <div>
                        <h5 class="text-lg font-semibold"><?= htmlspecialchars($item['name']) ?></h5>
                        <p class="text-blue-600 font-bold item-total" id="total-price-<?= $item['id'] ?>">
                            $<?= number_format($item['price'] * $item['quantity'], 2) ?>
                        </p>
                    </div>
                    <form method="POST" action="" class="ml-auto flex items-center" id="form-<?= $item['id'] ?>">
                        <input type="hidden" name="product_id" value="<?= htmlspecialchars($item['id']) ?>">
                        <input type="number" id="quantity-<?= $item['id'] ?>" name="quantity" value="<?= $item['quantity'] ?>" min="1" class="border rounded-md p-1 w-16 text-center mr-4" onchange="updatePrice(<?= $item['id'] ?>, <?= $item['price'] ?>); autoSubmitForm(<?= $item['id'] ?>)" required>
                        <button class="bg-red-600 text-white px-4 py-1 rounded-md hover:bg-red-500 transition" name="remove_item">Remove</button>
                    </form>
                </div>

            <?php endforeach; ?>
        </div>

        <div class="total-price mt-6">
            <span class="text-xl font-semibold">Total Price: </span>
            <span id="grand-total" class="text-xl font-semibold text-green-600">$<?= number_format($total_price, 2) ?></span>
        </div>
        
        <!-- Proceed to Checkout Button -->
        <div class="mt-8 text-center">
            <button onclick="showPaymentOptions()" class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-500 transition">Proceed to Checkout</button>
        </div>
        
        <!-- Checkout Form for Submitting Payment Method -->
        <form id="checkoutForm" method="POST" action="" class="hidden">
            <input type="hidden" id="payment_method" name="payment_method" value="">
        </form>

    <?php else: ?>
        <p>Your cart is empty. Start shopping!</p>
    <?php endif; ?>
</div>



<!-- Modal for payment method selection -->
<div id="paymentModal" class="fixed inset-0 flex items-center justify-center modal-bg hidden">
    <div class="modal-container p-8 shadow-lg">
        <!-- Close icon -->
        <span class="close-icon" onclick="closeModal()">❌</span>
        
        <div class="modal-header text-center mb-4">
            <h2 class="text-xl font-semibold">Select Payment Method</h2>
        </div>
        <div class="modal-body flex justify-center space-x-6">
            <button onclick="choosePaymentMethod('online')" class="bg-blue-600 text-white px-6 py-3 rounded-md">Online Payment</button>
            <button onclick="choosePaymentMethod('cod')" class="bg-green-600 text-white px-6 py-3 rounded-md">Cash on Delivery</button>
        </div>
    </div>
</div>

</body>
</html>

<?php
// Close the database connection
$conn->close();
?>
