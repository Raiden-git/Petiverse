<?php

include('../db.php');

?>


<!DOCTYPE html>
<html>
<head>
    <title>Petiverse - COD Oder Details</title>
    <link rel="stylesheet" href="admin_sidebar.css">
    <script src="logout_js.js"></script>
    <style>
      
        /* General styles */
body {
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 0;
    background-color: #f9f9f9;
    color: #333;
}

h3 {
    color: #444;
    margin-bottom: 10px;
}

p {
    margin: 5px 0;
    line-height: 1.6;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin: 10px 0;
    background-color: #fff;
}

thead th {
    background-color: #007BFF;
    color: white;
    padding: 10px;
    text-align: left;
    border: 1px solid #ddd;
}

tbody td {
    padding: 10px;
    text-align: left;
    border: 1px solid #ddd;
}

tbody tr:nth-child(even) {
    background-color: #f2f2f2;
}

/* Order container */
div {
    margin: 20px auto;
    padding: 20px;
    border-radius: 10px;
    background-color: #ffffff;
    box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
    max-width: 900px;
}

/* Buttons */
button {
    padding: 10px 15px;
    font-size: 14px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    margin: 5px;
}

button[type="submit"]:hover {
    opacity: 0.9;
}

button[name="update_status"][value="confirmed"] {
    background-color: #28a745;
    color: #ffffff;
}

button[name="update_status"][value="cancelled"] {
    background-color: #dc3545;
    color: #ffffff;
}

/* Flash messages */
div[style*="background-color: #d4edda"], 
div[style*="background-color: #f8d7da"] {
    width: 90%;
    margin: 20px auto;
    padding: 15px;
    border-radius: 5px;
    font-weight: bold;
    text-align: center;
}

/* Images */
img {
    max-width: 100px;
    max-height: 100px;
    border-radius: 5px;
    object-fit: cover;
}

/* Strong styling */
strong {
    color: #555;
}



/* Responsive design */
@media (max-width: 768px) {
    div {
        padding: 15px;
        margin: 10px;
    }

    table {
        font-size: 12px;
    }

    button {
        font-size: 12px;
        padding: 8px;
    }
}




    </style>

</head>
<body>
<header>
    <h1>Cash On delivery Oder Management</h1>
</header>

<nav>
    <ul>
        <li><a href="dashboard.php">Home</a></li>
        <li><a href="user_management.php">User Management</a></li>
        <li><a href="shop_management.php">Shop Management</a></li>
        <li><a href="community_controls.php">Community Controls</a></li>
        <li><a href="blog_management.php">Blog Management</a></li>
        <li><a href="admin_daycare_management.php">Daycare Management</a></li>
        <li><a href="lost_found_pets.php">Lost & Found Pets</a></li>
        <li><a href="special_events.php">Special Events</a></li>
        <li><a href="vet_management.php">Vet Management</a></li>
        <li><a href="moderator_management.php">Moderator Management</a></li>
        <li><a href="petselling.php">Pet selling</a><li>        
        <li><a href="view_feedback.php">Feedbacks</a></li>
        <li><a href="logout.php" onclick="return confirmLogout();">Logout</a></li>
    </ul>
</nav>

<main>

<a href="./confirmed_orders.php" class="confirm"><button>Confirm orders</button></a>
<a href="./cancelled_orders.php"><button>cancelled orders</button></a>

    <h2>Oder Details</h2>


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

// Check if admin submitted an update for order status
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $order_code = $_POST['order_code']; 
    $new_status = $_POST['status'];

    // Prepare the status message based on the new status
    $status_message = ($new_status === 'confirmed') 
        ? "Order confirmed, your order is on the way." 
        : "Your order has been cancelled.";

    // Update the order status and message in the database
    $update_sql = "UPDATE COD_orders SET order_status = ?, order_status_message = ? WHERE order_id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("sss", $new_status, $status_message, $order_code);

    // Execute the update
    if ($stmt->execute()) {
        $_SESSION['message'] = "Order #$order_code has been " . ($new_status === 'confirmed' ? 'confirmed' : 'cancelled') . ".";
        $_SESSION['message_type'] = 'success';
    } else {
        $_SESSION['message'] = "Failed to update the order #$order_code. Please try again.";
        $_SESSION['message_type'] = 'error';
    }

    $stmt->close();

    // Redirect to the same page to display the message and updated list
    header("Location: admin_order_details.php");
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

// SQL query to fetch only pending orders (not confirmed or cancelled)
$sql = "SELECT 
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
        WHERE COD_orders.order_status NOT IN ('confirmed', 'cancelled')  
        GROUP BY COD_orders.order_id, COD_orders.product_id
        ORDER BY COD_orders.order_id";

$result = $conn->query($sql);

// Check if we have results
if ($result->num_rows > 0) {
    $current_order_code = null;

    while ($row = $result->fetch_assoc()) {
        // Start a new container for a new order
        if ($current_order_code !== $row['order_code']) {
            // Close the previous container
            if ($current_order_code !== null) {
                echo "</tbody></table>";
                echo "<p><strong>Total Amount:</strong> $" . number_format($current_order_total, 2) . "</p>";
                echo "<form method='POST'>
                        <input type='hidden' name='order_code' value='" . $current_order_code . "'>
                        <input type='hidden' name='status' value='confirmed'>
                        <button type='submit' name='update_status' style='background: green; color: white; padding: 10px; border: none;'>Confirm All</button>
                      </form>";
                echo "<form method='POST' style='margin-top: 5px;' >
                        <input type='hidden' name='order_code' value='" . $current_order_code . "'>
                        <input type='hidden' name='status' value='cancelled'>
                        <button type='submit' name='update_status' style='background: red; color: white; padding: 10px; border: none;'>Cancel All</button>
                      </form>";
                echo "</div>";
            }

            // Start a new order container
            $current_order_total = 0;
            echo "<div style='border: 1px solid #ccc; margin: 10px; padding: 10px;'>";
            echo "<h3>Order Code: " . $row['order_code'] . "</h3>";
            echo "<p><strong>Full Name:</strong> " . $row['full_name'] . "</p>";
            echo "<p><strong>Delivery Address:</strong> " . $row['delivery_address'] . "</p>";
            echo "<p><strong>Phone Number:</strong> " . $row['phone_number'] . "</p>";
            echo "<p><strong>Postal Code:</strong> " . $row['postal_code'] . "</p>";
            echo "<p><strong>Status:</strong> " . ucfirst($row['order_status']) . "</p>";
            echo "<p><strong>Status Message:</strong> " . $row['order_status_message'] . "</p>";

            // Start table for products
            echo "<h4>Products:</h4>";
            echo "<table border='1' style='width: 100%;'>
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
            echo "<td><img src='data:image/jpeg;base64," . base64_encode($row['product_photo']) . "' alt='" . htmlspecialchars($row['product_name']) . "' style='width: 100px; height: 100px;'></td>";
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
    echo "<form method='POST'>
            <input type='hidden' name='order_code' value='" . $current_order_code . "'>
            <input type='hidden' name='status' value='confirmed'>
            <button type='submit' name='update_status' style='background: green; color: white; padding: 10px; border: none;'>Confirm All</button>
          </form>";
    echo "<form method='POST' style='margin-top: 5px;' >
            <input type='hidden' name='order_code' value='" . $current_order_code . "'>
            <input type='hidden' name='status' value='cancelled'>
            <button type='submit' name='update_status' style='background: red; color: white; padding: 10px; border: none;'>Cancel All</button>
          </form>";
    echo "</div>";
} else {
    echo "No orders found.";
}

// Close the database connection
$conn->close();
?>


   
</main>
</body>
</html>
































