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

// SQL query to combine COD and Online Payment orders
$sql = "
    SELECT 
        'COD' AS payment_type,
        cod_orders.id AS order_id,
        cod_orders.order_id AS order_code,
        cod_orders.full_name,
        cod_orders.delivery_address,
        cod_orders.phone_number,
        cod_orders.postal_code,
        cod_orders.quantity,
        cod_orders.price,
        cod_orders.total_price,
        cod_orders.order_status,
        cod_orders.order_status_message,
        products.name AS product_name,
        products.description AS product_description,
        products.photo AS product_photo
    FROM cod_orders
    INNER JOIN products ON cod_orders.product_id = products.id
    WHERE cod_orders.user_id = ?
    
    UNION ALL
    
    SELECT 
        'Online' AS payment_type,
        online_payment_orders.id AS order_id,
        online_payment_orders.order_id AS order_code,
        online_payment_orders.full_name,
        online_payment_orders.delivery_address,
        online_payment_orders.phone_number,
        online_payment_orders.postal_code,
        online_payment_orders.quantity,
        online_payment_orders.price,
        online_payment_orders.total_price,
        online_payment_orders.order_status,
        online_payment_orders.order_status_message,
        products.name AS product_name,
        products.description AS product_description,
        products.photo AS product_photo
    FROM online_payment_orders
    INNER JOIN products ON online_payment_orders.product_id = products.id
    WHERE online_payment_orders.user_id = ?
    ORDER BY order_code DESC";  // Orders sorted by order code

// Prepare and execute the query
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $user_id, $user_id);
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
    <link rel="stylesheet" href="styles.css"> <!-- Link to the external CSS -->
    <style>
        /* General Reset */
        body, h1, h2, p {
    margin: 0;
    padding: 0;
    font-family: Arial, sans-serif;
    color: black;
}

/* Body Styling */
body {
    background-color: #f4f4f9;
    margin: 0;
    padding: 0;
    line-height: 1.6;
}


/* Container */
.container {
    width: 80%;
    max-width: 1200px;
    margin: 20px auto;
    padding: 20px;
    background-color: #fff;
    border-radius: 10px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

/* Header */
h1 {
    margin-bottom:20px ;
    font-size: 3em;
    margin-bottom: 20px;
    color: orange;
    text-align: center;
}

/* Order Card */
.order-card {
    margin-bottom: 20px;
    padding: 20px;
    border: 1px solid #ddd;
    border-radius: 10px;
    background-color: #f9f9fc;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
}

.order-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
}

/* Order Details */
.order-details {
    margin-bottom: 15px;
  
}

.order-details p {
    margin-bottom: 8px;
    font-size: 1em;
    line-height: 1.4;
}

.order-details p strong {
    color: brown;
}

/* Product List */
.product-list {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 15px;
}

.product-item {
    padding: 15px;
    border: 1px solid #ddd;
    border-radius: 8px;
    background-color: #fff;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    transition: transform 0.2s ease-in-out;
    width: 400px;
    
}

.product-item:hover {
    transform: translateY(-3px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}

.product-item p {
    margin-bottom: 8px;
    font-size: 0.95em;
    line-height: 1.4;
}

.product-item p strong {
    color: #555;
}

.product-item img {
    display: block;
    max-width: 60%;
    height: auto;
    margin-top: 10px;
    border-radius: 8px;
}

/* No Orders Message */
.no-orders {
    font-size: 1.2em;
    color: #555;
    text-align: center;
    margin: 20px 0;
}

/* Buttons */
button {
    display: inline-block;
    padding: 10px 20px;
    font-size: 1em;
    color: #fff;
    background-color: #007bff;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s ease-in-out;
}

button:hover {
    background-color: #0056b3;
}

/* Responsive Design */
@media (max-width: 768px) {
    .order-card {
        padding: 15px;
    }

    .product-list {
        grid-template-columns: 1fr;
    }

    h1 {
        font-size: 2em;
    }
}

    </style>
</head>
<body>
<?php include 'Cus-NavBar/navBar.php'; ?>


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
                <h2>Order #<?= htmlspecialchars($order_code) ?> (<?= htmlspecialchars($order_details['payment_type']) ?>)</h2>
                <div class="order-details">
                    <p><strong>Full Name:</strong> <?= htmlspecialchars($order_details['full_name']) ?></p>
                    <p><strong>Delivery Address:</strong> <?= htmlspecialchars($order_details['delivery_address']) ?></p>
                    <p><strong>Phone Number:</strong> <?= htmlspecialchars($order_details['phone_number']) ?></p>
                    <p><strong>Postal Code:</strong> <?= htmlspecialchars($order_details['postal_code']) ?></p>
                    <p><strong>Total Price:</strong> LKR. <?= number_format($order_details['total_price'], 2) ?></p>
                    <p><strong>Status:</strong> <?= htmlspecialchars($order_details['order_status']) ?></p>
                    <p><strong>Status Message:</strong> <?= htmlspecialchars($order_details['order_status_message']) ?></p>
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




