
<!DOCTYPE html>
<html>
<head>
    <title>Petiverse - COD Comfirmed Orders </title>
    <link rel="stylesheet" href="./moderator_sidebar.css">
    
    <!-- Font Awesome for icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

    <script>
        function confirmLogout() {
            return confirm("Do you really want to log out?");
        }
    </script>
      <style>

    .order-heading {
        text-align: center;
        color: #333;
    }
    .order-container {
        border: 1px solid #ccc;
        background-color: #fff;
        margin: 10px auto;
        padding: 15px;
        border-radius: 5px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        max-width: 800px;
    }
    .product-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
    }
    .product-table th, .product-table td {
        border: 1px solid #ddd;
        padding: 8px;
        text-align: left;
    }
    .product-photo {
        width: 80px;
        height: 80px;
        object-fit: cover;
        border-radius: 5px;
    }
    .delete-form {
        text-align: right;
        margin-top: 10px;
    }
    .delete-button {
        background-color: #dc3545;
        color: white;
        padding: 10px 15px;
        border: none;
        border-radius: 3px;
        cursor: pointer;
        font-size: 14px;
    }
    .delete-button:hover {
        background-color: #c82333;
    }
      
    </style>

</head>
<body>
<header>
    <h1>COD Comfirmed Oders </h1>
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

session_start();


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

// Check if admin submitted a delete request for an order
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_order'])) {
    $order_code = $_POST['order_code']; 

    // Delete the order from the database (admin-side only)
    $delete_sql = "DELETE FROM COD_orders WHERE order_id = ?";
    $stmt = $conn->prepare($delete_sql);
    $stmt->bind_param("s", $order_code);

    // Execute the delete
    if ($stmt->execute()) {
        $_SESSION['message'] = "Order #$order_code has been deleted successfully.";
        $_SESSION['message_type'] = 'success';
    } else {
        $_SESSION['message'] = "Failed to delete order #$order_code. Please try again.";
        $_SESSION['message_type'] = 'error';
    }

    $stmt->close();

    // Redirect to the same page to display the message
    header("Location: confirmed_orders.php");
    exit;
}

// Display any session messages and then clear them
if (isset($_SESSION['message'])) {
    $message_type = $_SESSION['message_type'];
    $message = $_SESSION['message'];
    echo "<div style='margin: 10px; padding: 10px; background-color: " . ($message_type === 'success' ? '#d4edda' : '#f8d7da') . "; color: " . ($message_type === 'success' ? '#155724' : '#721c24') . "; border: 1px solid " . ($message_type === 'success' ? '#c3e6cb' : '#f5c6cb') . "; border-radius: 5px;'>$message</div>";
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
}

// SQL query to fetch confirmed orders
$confirmed_sql = "SELECT 
                    COD_orders.order_id AS order_code,
                    COD_orders.full_name,
                    COD_orders.delivery_address,
                    COD_orders.phone_number,
                    COD_orders.postal_code,
                    COD_orders.order_status,
                    COD_orders.order_status_message,
                    SUM(COD_orders.quantity * products.price) AS total_amount,
                    products.name AS product_name,
                    products.description AS product_description,
                    products.photo AS product_photo,
                    COD_orders.quantity AS product_quantity,
                    products.price AS product_price
                FROM COD_orders
                INNER JOIN products ON COD_orders.product_id = products.id
                WHERE COD_orders.order_status = 'confirmed'
                GROUP BY COD_orders.order_id, COD_orders.product_id
                ORDER BY COD_orders.order_id";

// Execute the query
$confirmed_result = $conn->query($confirmed_sql);

// Function to display confirmed orders
function display_orders($result, $status) {
    $current_order_code = null;
    $current_order_total = 0;

    echo "<h2 class='order-heading'>" . ucfirst($status) . " Orders</h2>";

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Start a new container for a new order
            if ($current_order_code !== $row['order_code']) {
                // Close the previous container
                if ($current_order_code !== null) {
                    echo "</tbody></table>";
                    echo "<p><strong>Total Amount:</strong> LKR ." . number_format($current_order_total, 2) . "</p>";
                    echo "<form method='POST' class='delete-form'>
                            <input type='hidden' name='order_code' value='" . $current_order_code . "'>
                            <button type='submit' name='delete_order' class='delete-button'>Delete Order</button>
                          </form>";
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

            echo "<td>LKR ." . number_format($subtotal, 2) . "</td>";
            echo "</tr>";

            // Update current order code
            $current_order_code = $row['order_code'];
        }

        // Close the last container
        echo "</tbody></table>";
        echo "<p><strong>Total Amount:</strong> LKR ." . number_format($current_order_total, 2) . "</p>";
        echo "<form method='POST' class='delete-form'>
                <input type='hidden' name='order_code' value='" . $current_order_code . "'>
                <button type='submit' name='delete_order' class='delete-button'>Delete Order</button>
              </form>";
        echo "</div>";
    } else {
        echo "<p>No confirmed orders found.</p>";
    }
}

// Display confirmed orders
display_orders($confirmed_result, 'confirmed');

// Close the database connection
$conn->close();
?>

</main>
</body>
</html>











































