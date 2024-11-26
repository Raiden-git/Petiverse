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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4a90e2;
            --secondary-color: #f8f9fa;
            --text-color: #333;
            --border-color: #e0e0e0;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--secondary-color);
            color: var(--text-color);
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .page-header {
            text-align: center;
            color: var(--primary-color);
            margin-bottom: 30px;
            padding: 20px 0;
            background-color: white;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .order-card {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            overflow: hidden;
            transition: transform 0.3s ease;
        }

        .order-card:hover {
            transform: translateY(-5px);
        }

        .order-header {
            background-color: var(--primary-color);
            color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .order-details {
            padding: 20px;
            background-color: var(--secondary-color);
        }

        .order-details p {
            margin-bottom: 10px;
        }

        .product-list {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            padding: 20px;
            background-color: white;
        }

        .product-item {
            flex: 1;
            min-width: 250px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            transition: box-shadow 0.3s ease;
        }

        .product-item:hover {
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .product-item img {
            max-width: 200px;
            max-height: 200px;
            object-fit: contain;
            margin-top: 10px;
            border-radius: 8px;
        }

        .order-status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 5px;
            font-weight: bold;
        }

        .status-pending {
            background-color: #ffc107;
            color: #212529;
        }

        .status-completed {
            background-color: #28a745;
            color: white;
        }

        .status-cancelled {
            background-color: #dc3545;
            color: white;
        }

        .no-orders {
            text-align: center;
            color: var(--primary-color);
            padding: 50px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
<?php include 'Cus-NavBar/navBar.php'; ?>

<div class="container">
    <div class="page-header">
        <h1>My Orders</h1>
    </div>

    <?php if ($result->num_rows > 0): ?>
        <?php
        // Group orders by order_id
        $orders = [];
        while ($row = $result->fetch_assoc()) {
            $orders[$row['order_code']][] = $row;
        }

        foreach ($orders as $order_code => $products):
            $order_details = $products[0]; // Use the first product to get order-level details

            // Determine status color class
            $status_class = 'status-pending';
            if (strtolower($order_details['order_status']) === 'completed') {
                $status_class = 'status-completed';
            } elseif (strtolower($order_details['order_status']) === 'cancelled') {
                $status_class = 'status-cancelled';
            }
        ?>
            <div class="order-card">
                <div class="order-header">
                    <h2>Order #<?= htmlspecialchars($order_code) ?> (<?= htmlspecialchars($order_details['payment_type']) ?>)</h2>
                    <span class="order-status <?= $status_class ?>">
                        <?= htmlspecialchars($order_details['order_status']) ?>
                    </span>
                </div>
                <div class="order-details">
                    <div class="order-info">
                        <p><strong>Full Name:</strong> <?= htmlspecialchars($order_details['full_name']) ?></p>
                        <p><strong>Delivery Address:</strong> <?= htmlspecialchars($order_details['delivery_address']) ?></p>
                        <p><strong>Phone Number:</strong> <?= htmlspecialchars($order_details['phone_number']) ?></p>
                        <p><strong>Postal Code:</strong> <?= htmlspecialchars($order_details['postal_code']) ?></p>
                        <p><strong>Total Price:</strong> LKR. <?= number_format($order_details['total_price'], 2) ?></p>
                        <p><strong>Status Message:</strong> <?= htmlspecialchars($order_details['order_status_message']) ?></p>
                    </div>
                </div>
                <div class="product-list">
                    <?php foreach ($products as $product): ?>
                        <div class="product-item">
                            <h3><?= htmlspecialchars($product['product_name']) ?></h3>
                            <p><?= htmlspecialchars($product['product_description']) ?></p>
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
        <p class="no-orders">No orders found. Start shopping and place your first order!</p>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>

</body>
</html>

<?php
// Close the database connection
$conn->close();
?>