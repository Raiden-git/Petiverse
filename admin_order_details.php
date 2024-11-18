<?php
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

// SQL query to get order details along with product details
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
        INNER JOIN products ON COD_orders.product_id = products.id";

// Execute the query
$result = $conn->query($sql);

// Check if we have any results
if ($result->num_rows > 0) {
    echo "<table border='1'>
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Order Code</th>
                    <th>Full Name</th>
                    <th>Delivery Address</th>
                    <th>Phone Number</th>
                    <th>Postal Code</th>
                    <th>Product Name</th>
                    <th>Product Description</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Total Price</th>
                    <th>Product Photo</th>
                </tr>
            </thead>
            <tbody>";

    // Output data for each row
    while ($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>" . $row['order_id'] . "</td>
                <td>" . $row['order_code'] . "</td>
                <td>" . $row['full_name'] . "</td>
                <td>" . $row['delivery_address'] . "</td>
                <td>" . $row['phone_number'] . "</td>
                <td>" . $row['postal_code'] . "</td>
                <td>" . $row['product_name'] . "</td>
                <td>" . $row['product_description'] . "</td>
                <td>" . $row['quantity'] . "</td>
                <td>$" . number_format($row['price'], 2) . "</td>
                <td>$" . number_format($row['total_price'], 2) . "</td>";
                
        // Display the product photo
        if ($row['product_photo']) {
            echo "<td><img src='data:image/jpeg;base64," . base64_encode($row['product_photo']) . "' alt='" . htmlspecialchars($row['product_name']) . "' style='width: 100px; height: 100px;'></td>";
        } else {
            echo "<td>No Image</td>";
        }

        echo "</tr>";
    }

    echo "</tbody></table>";
} else {
    echo "No orders found.";
}

// Close the database connection
$conn->close();
?>
