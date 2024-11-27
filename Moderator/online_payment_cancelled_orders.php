<!DOCTYPE html>
<html>
<head>
    <title>Petiverse - online payment cancelled orders</title>
    <link rel="stylesheet" href="./moderator_sidebar.css">
    
    <!-- Font Awesome for icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

    <script>
        function confirmLogout() {
            return confirm("Do you really want to log out?");
        }
    </script>

<style>

/* General Page Styling */
body {
 font-family: Arial, sans-serif;
 margin: 0;
 padding: 0;
 background-color: #f9f9f9;
 color: #333;
}

h2 {
 text-align: center;
 color: #444;
 margin-top: 20px;
}

h3 {
 color: #555;
}

/* Message Styles */
.message {
 width: 90%;
 margin: 20px auto;
 padding: 15px;
 border-radius: 5px;
 text-align: center;
 font-weight: bold;
}

.message.success {
 background-color: #d4edda;
 color: #155724;
 border: 1px solid #c3e6cb;
}

.message.error {
 background-color: #f8d7da;
 color: #721c24;
 border: 1px solid #f5c6cb;
}

/* Order Container */
.order-container {
 background-color: #fff;
 margin: 20px auto;
 padding: 20px;
 border: 1px solid #ddd;
 border-radius: 8px;
 box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
 max-width: 800px;
}

.order-container h3 {
 margin-bottom: 10px;
 color: #333;
}

.order-container p {
 margin: 5px 0;
 line-height: 1.5;
}

/* Table Styling */
.product-table {
 width: 100%;
 border-collapse: collapse;
 margin-top: 10px;
}

.product-table th,
.product-table td {
 padding: 10px;
 text-align: left;
 border: 1px solid #ddd;
}

.product-table th {
 background-color: #f2f2f2;
 color: #333;
 font-weight: bold;
}

.product-table td img {
 display: block;
 width: 100px;
 height: auto;
 border-radius: 4px;
}

/* Buttons */
.order-actions {
 margin-top: 15px;
 text-align: right;
}

button {
 padding: 10px 15px;
 font-size: 14px;
 border: none;
 border-radius: 5px;
 cursor: pointer;
}

button.delete-btn {
 background-color: #ff4d4d;
 color: white;
 transition: background-color 0.3s;
}

button.delete-btn:hover {
 background-color: #e60000;
}

/* Responsive Design */
@media (max-width: 768px) {
 .order-container {
     padding: 15px;
 }

 .product-table th,
 .product-table td {
     font-size: 12px;
 }

 button {
     font-size: 12px;
     padding: 8px 10px;
 }
}

   
 </style>
</head>
<body>
<header>
    <h1>Cancelled Online Payment Orders </h1>
</header>

<nav>
    <ul>
    <li><a href="moderator_dashboard.php">Home</a></li>
        <li><a href="Moderator_shop_management.php">Shop Management</a></li>
        <li><a href="community_controls.php">Community Controls</a></li>
        <li><a href="blog_management.php">Blog Management</a></li>
        <li><a href="admin_daycare_management.php">Daycare Management</a></li>
        <li><a href="lost_found_pets.php">Lost & Found Pets</a></li>
        <li><a href="special_events.php">Special Events</a></li>
        <li><a href="vet_management.php">Vet Management</a></li>
        <li><a href="petselling.php">Pet selling</a><li>
        <li><a href="view_feedback.php">Feedbacks</a></li>
        <li><a href="logout.php" onclick="return confirmLogout();">Logout</a></li>
    </ul>
</nav>

<main>
<?php
// Start a session to store flash messages
session_start();

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

// Handle order deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_order'])) {
    $order_code = $_POST['order_code']; // Order code received from the form

    // Delete the order from the database
    $delete_sql = "DELETE FROM online_payment_orders WHERE order_id = ?";
    $stmt = $conn->prepare($delete_sql);
    $stmt->bind_param("s", $order_code);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Order #$order_code has been successfully deleted.";
        $_SESSION['message_type'] = 'success';
    } else {
        $_SESSION['message'] = "Failed to delete the order #$order_code. Please try again.";
        $_SESSION['message_type'] = 'error';
    }

    $stmt->close();

    // Redirect to the same page to display the message
    header("Location: online_payment_cancelled_orders.php");
    exit;
}

// Display any session messages and then clear them
if (isset($_SESSION['message'])) {
    $message_type = $_SESSION['message_type'];
    $message = $_SESSION['message'];
    echo "<div class='message " . ($message_type === 'success' ? 'success' : 'error') . "'>$message</div>";
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
}

// SQL query to fetch cancelled online payment orders
$cancelled_sql = "SELECT 
                    online_payment_orders.order_id AS order_code,
                    online_payment_orders.full_name,
                    online_payment_orders.delivery_address,
                    online_payment_orders.phone_number,
                    online_payment_orders.postal_code,
                    online_payment_orders.order_status,
                    online_payment_orders.order_status_message,
                    SUM(online_payment_orders.quantity * products.price) AS total_amount,
                    products.name AS product_name,
                    products.description AS product_description,
                    products.photo AS product_photo,
                    online_payment_orders.quantity AS product_quantity,
                    products.price AS product_price
                FROM online_payment_orders
                INNER JOIN products ON online_payment_orders.product_id = products.id
                WHERE online_payment_orders.order_status = 'cancelled'
                GROUP BY online_payment_orders.order_id, online_payment_orders.product_id
                ORDER BY online_payment_orders.order_id";

// Execute the query
$cancelled_result = $conn->query($cancelled_sql);

// Function to display cancelled orders
function display_orders($result, $status) {
    $current_order_code = null;
    $current_order_total = 0;

    echo "<h2>" . ucfirst($status) . " Orders</h2>";

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Start a new container for a new order
            if ($current_order_code !== $row['order_code']) {
                // Close the previous container
                if ($current_order_code !== null) {
                    echo "</tbody></table>";
                    echo "<p><strong>Total Amount:</strong> LKR ." . number_format($current_order_total, 2) . "</p>";
                    echo "<div class='order-actions'>
                            <form method='POST'>
                                <input type='hidden' name='order_code' value='" . $current_order_code . "'>
                                <button type='submit' name='delete_order' class='delete-btn'>Delete Order</button>
                            </form>
                          </div>";
                    echo "</div>";
                }

                // Start a new order container
                $current_order_total = 0;
                echo "<div class='order-container'>";
                echo "<h3>Order Code: " . $row['order_code'] . "</h3>";
                echo "<p><strong>Full Name:</strong> " . $row['full_name'] . "</p>";
                echo "<p><strong>Delivery Address:</strong> " . $row['delivery_address'] . "</p>";
                echo "<p><strong>Phone Number:</strong> " . $row['phone_number'] . "</p>";
                echo "<p><strong>Postal Code:</strong> " . $row['postal_code'] . "</p>";
                echo "<p><strong>Status:</strong> " . ucfirst($row['order_status']) . "</p>";
                echo "<p><strong>Status Message:</strong> " . $row['order_status_message'] . "</p>";

                // Start table for products
                echo "<h4>Products:</h4>";
                echo "<table class='product-table'>
                        <thead>
                            <tr>
                                <th>Product Name</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Photo</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>";
            }

            // Add product details to the current order's container
            $subtotal = $row['product_quantity'] * $row['product_price'];
            $current_order_total += $subtotal;

            echo "<tr>
                    <td>" . $row['product_name'] . "</td>
                    <td>LKR ." . number_format($row['product_price'], 2) . "</td>
                    <td>" . $row['product_quantity'] . "</td>";

            if ($row['product_photo']) {
                echo "<td><img src='data:image/jpeg;base64," . base64_encode($row['product_photo']) . "' alt='" . htmlspecialchars($row['product_name']) . "' class='product-photo'></td>";
            } else {
                echo "<td>No Image</td>";
            }

            echo "<td>$" . number_format($subtotal, 2) . "</td>";
            echo "</tr>";

            // Update current order code
            $current_order_code = $row['order_code'];
        }

        // Close the last container
        echo "</tbody></table>";
        echo "<p><strong>Total Amount:</strong> LKR ." . number_format($current_order_total, 2) . "</p>";
        echo "<div class='order-actions'>
                <form method='POST'>
                    <input type='hidden' name='order_code' value='" . $current_order_code . "'>
                    <button type='submit' name='delete_order' class='delete-btn'>Delete Order</button>
                </form>
              </div>";
        echo "</div>";
    } else {
        echo "<p>No " . $status . " orders found.</p>";
    }
}

// Display cancelled orders
display_orders($cancelled_result, 'cancelled');

// Close the database connection
$conn->close();
?>

</main>
</body>
</html>
