<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the delivery information from the form
    $full_name = $_POST['name'];
    $delivery_address = $_POST['address'];
    $phone_number = $_POST['phone'];
    $postal_code = $_POST['postal_code'];

    // Check if the cart is not empty
    if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
        // Database connection
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "petiverse";
        $conn = new mysqli($servername, $username, $password, $dbname);

        // Check for connection error
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Fetch user id (assuming the user is logged in and their id is stored in the session)
        $user_id = $_SESSION['user_id'];

        // Generate a unique order ID
        $order_id = 'ORD' . strtoupper(uniqid());

        // Loop through cart items to insert each one into the database
        foreach ($_SESSION['cart'] as $product_id => $quantity) {
            // Fetch product details from the database
            $sql = "SELECT id, name, price, photo FROM products WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $product_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result && $item = $result->fetch_assoc()) {
                // Get product details
                $product_name = $item['name'];
                $product_price = $item['price'];
                $product_photo = $item['photo'];

                // Calculate total price
                $total_price = $product_price * $quantity;

                // Prepare the insert query for COD_orders table
                $insert_sql = "INSERT INTO COD_orders (user_id, order_id, full_name, delivery_address, phone_number, postal_code, product_id, product_name, quantity, price, total_price) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $insert_stmt = $conn->prepare($insert_sql);
                $insert_stmt->bind_param("isssssiissd", $user_id, $order_id, $full_name, $delivery_address, $phone_number, $postal_code, $product_id, $product_name, $quantity, $product_price, $total_price);

                // Execute the insert query for each product
                if (!$insert_stmt->execute()) {
                    echo "Error inserting order: " . $conn->error;
                    exit();
                }
            }

            $stmt->close();
        }

        // Close the connection
        $conn->close();

        // Clear the cart after the order is saved
        unset($_SESSION['cart']);

        // Include the success page
        include 'cod_oder_Success.php';
        exit();
    } else {
        echo "<script>alert('Your cart is empty. Please add items before proceeding.'); window.location.href='shop.php';</script>";
    }
} else {
    echo "<script>alert('Invalid request'); window.location.href='shop.php';</script>";
}
?>
