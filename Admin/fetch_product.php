<?php
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

// Fetch product details
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['product_id'])) {
    $product_id = $conn->real_escape_string($_POST['product_id']);
    $sql = "SELECT name, description, price, photo FROM products WHERE id='$product_id'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // Fetch the product details
        $product = $result->fetch_assoc();
        echo json_encode($product); // Return the product details as JSON
    } else {
        echo json_encode(array("error" => "Product not found."));
    }
}

// Close the connection
$conn->close();
?>
