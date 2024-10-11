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

// Handle quantity change and item removal
if (isset($_POST['update_cart'])) {
    $product_id = $_POST['product_id'];
    $quantity = $_POST['quantity'];

    if ($quantity > 0) {
        $_SESSION['cart'][$product_id] = $quantity; // Update quantity
    } else {
        unset($_SESSION['cart'][$product_id]); // Remove item if quantity is 0
    }
}

if (isset($_POST['remove_item'])) {
    $product_id = $_POST['product_id'];
    unset($_SESSION['cart'][$product_id]); // Remove item
}

// Display the cart
$cart_items = [];
$total_price = 0; // Initialize total price
if (isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $product_id => $quantity) {
        $sql = "SELECT * FROM products WHERE id = '$product_id'";
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
    </script>
</head>
<body>

<header>
    <div class="logo">
        <a href="../index.html"><h1>Petiverse</h1></a>
    </div>
    <nav>
        <ul>
            <li><a href="../index.php">Home</a></li>
            <li><a href="../shop.php">Shop</a></li>
            <li><a href="#">Vet Services</a></li>
            <li><a href="#">Day Care</a></li>
            <li><a href="#">Community</a></li>
            <li><a href="#">Blog</a></li>
            <li><a href="#">Special Events</a></li> 
            <li><a href="#">Contact Us</a></li>
            <li><a href="#">Pet Selling</a></li>
        </ul>
    </nav>
</header>

<nav class="bg-white shadow-md">
    <div class="container mx-auto px-4 py-4">
        <div class="flex justify-between items-center">
            <a class="text-2xl font-bold text-blue-600" href="#">Petiverse</a>
            <div class="flex space-x-4">
                <a class="text-gray-600 hover:text-blue-600" href="shop.php">Shop</a>
                <a class="text-gray-600 hover:text-blue-600" href="cart.php">Cart</a>
            </div>
        </div>
    </div>
</nav>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl mb-6">Your Shopping Cart</h1>

    <?php if (!empty($cart_items)): ?>
        <div class="space-y-4">
            <?php foreach ($cart_items as $item): ?>
                <div class="cart-item">
                    <img src="../uploads/<?= htmlspecialchars($item['photo']) ?>" alt="<?= htmlspecialchars($item['name']) ?>">
                    <div>
                        <h5 class="text-lg font-semibold"><?= htmlspecialchars($item['name']) ?></h5>
                        <p class="text-blue-600 font-bold item-total" id="total-price-<?= $item['id'] ?>">
                            $<?= number_format($item['price'] * $item['quantity'], 2) ?>
                        </p>
                    </div>
                    <form method="POST" action="">
                        <input type="hidden" name="product_id" value="<?= htmlspecialchars($item['id']) ?>">
                        <input type="number" id="quantity-<?= $item['id'] ?>" name="quantity" value="<?= $item['quantity'] ?>" min="1" class="border rounded-md p-1 w-20" onchange="updatePrice(<?= $item['id'] ?>, <?= $item['price'] ?>)" required>
              
                        <button class="bg-red-600 text-white px-4 py-1 rounded-md hover:bg-red-500 transition" name="remove_item">Remove</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="total-price">
            <span>Total Price: </span>
            <span id="grand-total">$<?= number_format($total_price, 2) ?></span>
        </div>
        <div class="mt-4 text-center">
            <a href="checkout.php" class="bg-green-600 text-white px-6 py-2 rounded-md hover:bg-green-500 transition">Checkout</a>
        </div>
    <?php else: ?>
        <p>Your cart is empty. Start shopping!</p>
    <?php endif; ?>
</div>

</body>
</html>

<?php
$conn->close();
?>
