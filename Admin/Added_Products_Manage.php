<?php

include('../db.php');
include('session_check.php');
// Code for user management goes here (e.g., displaying, deleting users, etc.)

// Handling form submission for editing a product
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_product'])) {
  $product_id = isset($_POST['product_id']) ? $conn->real_escape_string($_POST['product_id']) : '';
  $product_name = isset($_POST['name']) ? $conn->real_escape_string($_POST['name']) : '';
  $product_description = isset($_POST['description']) ? $conn->real_escape_string($_POST['description']) : '';
  $product_price = isset($_POST['price']) ? $conn->real_escape_string($_POST['price']) : '';
  $product_photo = isset($_FILES['photo']['name']) ? $_FILES['photo']['name'] : '';

  // Handle file upload if a new photo is uploaded
  if (!empty($product_photo)) {
      $target_dir = "../uploads/";
      $target_file = $target_dir . basename($product_photo);
      move_uploaded_file($_FILES['photo']['tmp_name'], $target_file);

      // Update product in the database with new photo
      $sql = "UPDATE products SET name='$product_name', description='$product_description', price='$product_price', photo='$product_photo' WHERE id='$product_id'";
  } else {
      // Update product in the database without changing the photo
      $sql = "UPDATE products SET name='$product_name', description='$product_description', price='$product_price' WHERE id='$product_id'";
  }

  if ($conn->query($sql) === TRUE) {
      $_SESSION['message'] = "<div class='alert alert-success fade-in'>Product <strong>$product_name</strong> updated successfully!</div>";
  } else {
      $_SESSION['message'] = "<div class='alert alert-danger fade-in'>Error updating product: " . $conn->error . "</div>";
  }
  header("Location: " . $_SERVER['PHP_SELF']);
  exit();
}

// Fetch all products for editing
$sql = "SELECT id, name, description, price, photo FROM products";
$all_products = $conn->query($sql);

// Close the connection
$conn->close();

?>

<!DOCTYPE html>
<html>
<head>
    <title>Added Products Manage</title>
    <link rel="stylesheet" href="admin_sidebar.css">
    <script src="logout_js.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="../assets/css/product_edite.css">
</head>
<body>
<header>
    <h1>Added Products Manage</h1>
</header>

<nav>
    <ul>
        <li><a href="dashboard.php">Home</a></li>
        <li><a href="user_management.php">User Management</a></li>
        <li><a href="shop_management.php">Shop Management</a></li>
        <li><a href="Added_Products_Manage.php">Added Products Manage</a></li>
        <li><a href="community_controls.php">Community Controls</a></li>
        <li><a href="blog_management.php">Blog Management</a></li>
        <li><a href="lost_found_pets.php">Lost & Found Pets</a></li>
        <li><a href="special_events.php">Special Events</a></li>
        <li><a href="vet_management.php">Vet Management</a></li>
        <li><a href="logout.php" onclick="return confirmLogout();">Logout</a></li>
    </ul>
</nav>

<main>
    <h2>Added Products Manage</h2>
    
</main>


<div class="container mt-3">
        <!-- Display session messages -->
        <?php
        if (isset($_SESSION['message']) && !empty($_SESSION['message'])) {
            echo $_SESSION['message'];
            $_SESSION['message'] = ''; // Clear the message after displaying
        }
        ?>

        <form action="" method="post" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="product" class="form-label">Select Product to Edit</label>
                <select class="form-control" id="product" name="product_id" required>
                    <option value="">Select Product</option>
                    <?php if ($all_products->num_rows > 0): ?>
                        <?php while ($row = $all_products->fetch_assoc()): ?>
                            <option value="<?= htmlspecialchars($row['id']) ?>"><?= htmlspecialchars($row['name']) ?></option>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <option value="">No products found</option>
                    <?php endif; ?>
                </select>
            </div>

            <div class="product-details">
                <div class="mb-3">
                    <label for="name" class="form-label">Product Name</label>
                    <input type="text" class="form-control" name="name" id="name" placeholder="Enter product name" required>
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Product Description</label>
                    <textarea class="form-control" name="description" id="description" placeholder="Enter product description" required></textarea>
                </div>

                <div class="mb-3">
                    <label for="price" class="form-label">Product Price</label>
                    <input type="number" class="form-control" name="price" id="price" placeholder="Enter product price" required>
                </div>

                <div class="mb-3">
                    <label for="photo" class="form-label">Upload New Product Photo (optional)</label>
                    <input type="file" class="form-control" name="photo" id="photo">
                </div>

                <div class="mb-3">
                    <label for="current-photo" class="form-label">Current Product Photo</label>
                    <img id="current-photo" class="product-image" src="" alt="Product Photo">
                </div>
            </div>

            <button type="submit" class="btn btn-primary" name="update_product">Update Product</button>
        </form>
    </div>

    <script>
        // Load product details and photo when a product is selected
        $(document).ready(function() {
            $('#product').change(function() {
                var product_id = $(this).val();
                if (product_id) {
                    $.ajax({
                        type: 'POST',
                        url: 'fetch_product.php', // This file will fetch the product details
                        data: { product_id: product_id },
                        dataType: 'json',
                        success: function(response) {
                            $('#name').val(response.name);
                            $('#description').val(response.description);
                            $('#price').val(response.price);

                            // Update the product image if available
                            if (response.photo) {
                                $('#current-photo').attr('src', '../uploads/' + response.photo);
                            } else {
                                $('#current-photo').attr('src', ''); 
                            }
                        }
                    });
                } else {
                    $('#name').val('');
                    $('#description').val('');
                    $('#price').val('');
                    $('#current-photo').attr('src', ''); // Clear the image if no product is selected
                }
            });
        });
    </script>

</body>
</html>