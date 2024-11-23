<?php
// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // If user is not logged in, redirect to login page
    header("Location: login.php");
    exit();
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "petiverse";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the logged-in user's ID from session
$user_id = $_SESSION['user_id'];

// SQL query to get the order details for the logged-in user along with product details
$sql = "SELECT 
            COD_orders.id AS order_id,
            COD_orders.order_id AS order_code,
            COD_orders.full_name,
            COD_orders.delivery_address,
            COD_orders.phone_number,
            COD_orders.postal_code,
            COD_orders.quantity,
            COD_orders.price,
            COD_orders.total_price,
            products.name AS product_name,
            products.description AS product_description,
            products.photo AS product_photo
        FROM COD_orders
        INNER JOIN products ON COD_orders.product_id = products.id
        WHERE COD_orders.user_id = ?";  

// Prepare and execute the query
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);  
$stmt->execute();
$result = $stmt->get_result();

// Close the statement
$stmt->close();

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - Pet Shop</title>
    <link rel="stylesheet" href="./assets/css/my_oders.css">
</head>
<body>
<?php include 'Cus-NavBar/navBar.php'; ?>

<div class="container">
    <h1>My Orders</h1>

    <?php if ($result->num_rows > 0): ?>
        <?php
        // Group orders by order_id
        $orders = [];
        while ($row = $result->fetch_assoc()) {
            $orders[$row['order_code']][] = $row;
        }

        foreach ($orders as $order_code => $products):
            $order_details = $products[0]; // Use the first product to get order-level details
        ?>
            <div class="order-card">
                <h2>Order #<?= htmlspecialchars($order_code) ?></h2>
                <div class="order-details">
                    <p><strong>Full Name:</strong> <?= htmlspecialchars($order_details['full_name']) ?></p>
                    <p><strong>Delivery Address:</strong> <?= htmlspecialchars($order_details['delivery_address']) ?></p>
                    <p><strong>Phone Number:</strong> <?= htmlspecialchars($order_details['phone_number']) ?></p>
                    <p><strong>Postal Code:</strong> <?= htmlspecialchars($order_details['postal_code']) ?></p>
                    <p><strong>Total Price:</strong> LKR. <?= number_format($order_details['total_price'], 2) ?></p>
                </div>
                <div class="product-list">
                    <?php foreach ($products as $product): ?>
                        <div class="product-item">
                            <p><strong>Product Name:</strong> <?= htmlspecialchars($product['product_name']) ?></p>
                            <p><strong>Description:</strong> <?= htmlspecialchars($product['product_description']) ?></p>
                            <p><strong>Quantity:</strong> <?= htmlspecialchars($product['quantity']) ?></p>
                            <p><strong>Price:</strong> LKR. <?= number_format($product['price'], 2) ?></p>
                            <?php if ($product['product_photo']): ?>
                                <img src="data:image/jpeg;base64,<?= base64_encode($product['product_photo']) ?>" alt="<?= htmlspecialchars($product['product_name']) ?>">
                            <?php else: ?>
                                <p>No Image Available</p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p class="no-orders">No orders found.</p>
    <?php endif; ?>

</div>

</body>
</html>

<?php
// Close the database connection
$conn->close();
?>
