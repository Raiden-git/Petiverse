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

// Start session to manage messages
session_start();

// Initialize message variable
if (!isset($_SESSION['message'])) {
    $_SESSION['message'] = '';
}

// Handling form submission for adding a subcategory
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_subcategory'])) {
    $main_category = isset($_POST['main_category']) ? $conn->real_escape_string($_POST['main_category']) : '';
    $sub_category = isset($_POST['sub_category']) ? $conn->real_escape_string($_POST['sub_category']) : '';

    // Insert new subcategory into the database
    $sql = "INSERT INTO subcategories (main_category, sub_category) VALUES ('$main_category', '$sub_category')";

    if ($conn->query($sql) === TRUE) {
        $_SESSION['message'] = "<div class='alert alert-success fade-in'>New subcategory <strong>$sub_category</strong> added successfully!</div>";
    } else {
        $_SESSION['message'] = "<div class='alert alert-danger fade-in'>Error: " . $conn->error . "</div>";
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Handling form submission for adding a product
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_product'])) {
    $product_category = isset($_POST['category']) ? $conn->real_escape_string($_POST['category']) : '';
    $product_subcategory = isset($_POST['subcategory']) ? $conn->real_escape_string($_POST['subcategory']) : '';
    $product_name = isset($_POST['name']) ? $conn->real_escape_string($_POST['name']) : '';
    $product_description = isset($_POST['description']) ? $conn->real_escape_string($_POST['description']) : '';
    $product_price = isset($_POST['price']) ? $conn->real_escape_string($_POST['price']) : '';
    $product_photo = isset($_FILES['photo']['name']) ? $_FILES['photo']['name'] : '';

    // Handle file upload
    $target_dir = "../uploads/";
    $target_file = $target_dir . basename($product_photo);
    move_uploaded_file($_FILES['photo']['tmp_name'], $target_file);

    // Insert new product into the database
    $sql = "INSERT INTO products (category, subcategory, name, description, price, photo) VALUES ('$product_category', '$product_subcategory', '$product_name', '$product_description', '$product_price', '$product_photo')";

    if ($conn->query($sql) === TRUE) {
        $_SESSION['message'] = "<div class='alert alert-success fade-in'>New product <strong>$product_name</strong> added successfully!</div>";
    } else {
        $_SESSION['message'] = "<div class='alert alert-danger fade-in'>Error: " . $conn->error . "</div>";
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Handling deletion of a subcategory
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_subcategory'])) {
    $subcategory_id = isset($_POST['subcategory_id']) ? $conn->real_escape_string($_POST['subcategory_id']) : '';

    // Delete subcategory from the database
    $sql = "DELETE FROM subcategories WHERE id = '$subcategory_id'";

    if ($conn->query($sql) === TRUE) {
        $_SESSION['message'] = "<div class='alert alert-success fade-in'>Subcategory deleted successfully!</div>";
    } else {
        $_SESSION['message'] = "<div class='alert alert-danger fade-in'>Error deleting subcategory: " . $conn->error . "</div>";
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Handling deletion of a product
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_product'])) {
    $product_id = isset($_POST['product_id']) ? $conn->real_escape_string($_POST['product_id']) : '';

    // Fetch the product photo name
    $sql = "SELECT photo FROM products WHERE id = '$product_id'";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
        $photo = $product['photo'];

        // Delete the product photo from the server
        $photo_path = "./uploads/" . $photo;
        if (file_exists($photo_path)) {
            unlink($photo_path);
        }

        // Delete product from the database
        $sql = "DELETE FROM products WHERE id = '$product_id'";
        if ($conn->query($sql) === TRUE) {
            $_SESSION['message'] = "<div class='alert alert-success fade-in'>Product deleted successfully!</div>";
        } else {
            $_SESSION['message'] = "<div class='alert alert-danger fade-in'>Error deleting product: " . $conn->error . "</div>";
        }
    } else {
        $_SESSION['message'] = "<div class='alert alert-danger fade-in'>Product not found.</div>";
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Fetch subcategories based on the main category
if (isset($_POST['main_category'])) {
    $main_category = $conn->real_escape_string($_POST['main_category']);

    $sql = "SELECT sub_category FROM subcategories WHERE main_category = '$main_category'";
    $result = $conn->query($sql);

    $subcategories = [];
    while ($row = $result->fetch_assoc()) {
        $subcategories[] = $row['sub_category'];
    }

    echo json_encode($subcategories);
    exit;
}

// Fetch all subcategories to display for deletion
$sql = "SELECT id, main_category, sub_category FROM subcategories";
$all_subcategories = $conn->query($sql);

// Fetch all products to display
$sql = "SELECT id, category, subcategory, name, description, price, photo FROM products";
$all_products = $conn->query($sql);

// Close the connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add/Delete Subcategory and Product</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .fade-in {
            animation: fadeIn 1.5s ease;
        }

        @keyframes fadeIn {
            0% { opacity: 0; }
            100% { opacity: 1; }
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <!-- Display session messages -->
        <?php
        if (isset($_SESSION['message']) && !empty($_SESSION['message'])) {
            echo $_SESSION['message'];
            $_SESSION['message'] = ''; // Clear the message after displaying
        }
        ?>

        <h2>Add New Subcategory</h2>
        <form action="" method="post">
            <div class="mb-3">
                <label for="main_category" class="form-label">Main Category</label>
                <select class="form-control" id="main_category" name="main_category" required>
                    <option value="Food">Food</option>
                    <option value="Accessories">Accessories</option>
                    <option value="Medicines">Medicines</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="sub_category" class="form-label">Subcategory Name</label>
                <input type="text" class="form-control" name="sub_category" placeholder="Enter subcategory name" required>
            </div>

            <button type="submit" class="btn btn-primary" name="add_subcategory">Add Subcategory</button>
        </form>

        <h2 class="mt-5">Existing Subcategories</h2>
        <!-- Display existing subcategories with delete option -->
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Main Category</th>
                    <th>Subcategory</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($all_subcategories->num_rows > 0): ?>
                    <?php while ($row = $all_subcategories->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['main_category']) ?></td>
                            <td><?= htmlspecialchars($row['sub_category']) ?></td>
                            <td>
                                <form action="" method="post" class="d-inline">
                                    <input type="hidden" name="subcategory_id" value="<?= htmlspecialchars($row['id']) ?>">
                                    <button type="submit" class="btn btn-danger" name="delete_subcategory">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3">No subcategories found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <h2 class="mt-5">Add New Product</h2>
        <form action="" method="post" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="category" class="form-label">Category</label>
                <select class="form-control" id="category" name="category" required>
                    <option value="Food">Food</option>
                    <option value="Accessories">Accessories</option>
                    <option value="Medicines">Medicines</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="subcategory" class="form-label">Subcategory</label>
                <select class="form-control" id="subcategory" name="subcategory" required>
                    <option value="">Select Subcategory</option>
                    <!-- Subcategories will be dynamically loaded using JavaScript -->
                </select>
            </div>

            <div class="mb-3">
                <label for="name" class="form-label">Product Name</label>
                <input type="text" class="form-control" name="name" placeholder="Enter product name" required>
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">Product Description</label>
                <textarea class="form-control" name="description" placeholder="Enter product description" required></textarea>
            </div>

            <div class="mb-3">
                <label for="price" class="form-label">Product Price</label>
                <input type="number" class="form-control" name="price" placeholder="Enter product price" required>
            </div>

            <div class="mb-3">
                <label for="photo" class="form-label">Product Photo</label>
                <input type="file" class="form-control" name="photo" required>
            </div>

            <button type="submit" class="btn btn-primary" name="add_product">Add Product</button>
        </form>

        <h2 class="mt-5">Existing Products</h2>
        <!-- Display existing products with delete option -->
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Category</th>
                    <th>Subcategory</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Price</th>
                    <th>Photo</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($all_products->num_rows > 0): ?>
                    <?php while ($row = $all_products->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['category']) ?></td>
                            <td><?= htmlspecialchars($row['subcategory']) ?></td>
                            <td><?= htmlspecialchars($row['name']) ?></td>
                            <td><?= htmlspecialchars($row['description']) ?></td>
                            <td>$<?= htmlspecialchars($row['price']) ?></td>
                            <td><img src="../uploads/<?= htmlspecialchars($row['photo']) ?>" alt="Product Photo" width="50"></td>
                            <td>
                                <form action="" method="post" class="d-inline">
                                    <input type="hidden" name="product_id" value="<?= htmlspecialchars($row['id']) ?>">
                                    <button type="submit" class="btn btn-danger" name="delete_product">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7">No products found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <script>
        // Dynamically load subcategories based on the selected category
        $(document).ready(function() {
            $('#category').change(function() {
                var main_category = $(this).val();
                $.ajax({
                    type: 'POST',
                    url: '<?= $_SERVER['PHP_SELF'] ?>',
                    data: {main_category: main_category},
                    success: function(response) {
                        var subcategories = JSON.parse(response);
                        $('#subcategory').empty();
                        $('#subcategory').append('<option value="">Select Subcategory</option>');
                        $.each(subcategories, function(index, sub_category) {
                            $('#subcategory').append('<option value="' + sub_category + '">' + sub_category + '</option>');
                        });
                    }
                });
            });
        });
    </script>
</body>
</html>
