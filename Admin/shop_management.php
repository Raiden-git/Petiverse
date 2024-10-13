<?php
include('../db.php');
include('session_check.php');

// Initialize message variable
if (!isset($_SESSION['message'])) {
    $_SESSION['message'] = '';
}


// Handling form submission for adding a subcategory
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_subcategory'])) {
    $main_category = isset($_POST['main_category']) ? $conn->real_escape_string($_POST['main_category']) : '';
    $sub_category = isset($_POST['sub_category']) ? $conn->real_escape_string($_POST['sub_category']) : '';

    $sql = "INSERT INTO subcategories (main_category, sub_category) VALUES ('$main_category', '$sub_category')";

    if ($conn->query($sql) === TRUE) {
        $_SESSION['message'] = "New subcategory <strong>$sub_category</strong> added successfully!";
    } else {
        $_SESSION['message'] = "Error: " . $conn->error . "</div>";
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}





// Handling deletion of a subcategory
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_subcategory'])) {
    $subcategory_id = isset($_POST['subcategory_id']) ? $conn->real_escape_string($_POST['subcategory_id']) : '';

    $sql = "DELETE FROM subcategories WHERE id = '$subcategory_id'";
    if ($conn->query($sql) === TRUE) {
        $_SESSION['message'] = "Subcategory deleted successfully!";
    } else {
        $_SESSION['message'] = "Error deleting subcategory: " . $conn->error;
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

    $target_dir = "../uploads/";
    $target_file = $target_dir . basename($product_photo);
    move_uploaded_file($_FILES['photo']['tmp_name'], $target_file);

    $sql = "INSERT INTO products (category, subcategory, name, description, price, photo) VALUES ('$product_category', '$product_subcategory', '$product_name', '$product_description', '$product_price', '$product_photo')";

    if ($conn->query($sql) === TRUE) {
        $_SESSION['message'] = "New product <strong>$product_name</strong> added successfully!";
    } else {
        $_SESSION['message'] = "Error: " . $conn->error . "</div>";
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Handling form submission for editing a product
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_product'])) {
    $product_id = isset($_POST['product_id']) ? $conn->real_escape_string($_POST['product_id']) : '';
    $product_category = isset($_POST['category']) ? $conn->real_escape_string($_POST['category']) : '';
    $product_subcategory = isset($_POST['subcategory']) ? $conn->real_escape_string($_POST['subcategory']) : '';
    $product_name = isset($_POST['name']) ? $conn->real_escape_string($_POST['name']) : '';
    $product_description = isset($_POST['description']) ? $conn->real_escape_string($_POST['description']) : '';
    $product_price = isset($_POST['price']) ? $conn->real_escape_string($_POST['price']) : '';
    $product_photo = isset($_FILES['photo']['name']) ? $_FILES['photo']['name'] : '';

    // Check if a new photo is uploaded
    if (!empty($product_photo)) {
        // Handle file upload
        $target_dir = "../uploads/";
        $target_file = $target_dir . basename($product_photo);
        move_uploaded_file($_FILES['photo']['tmp_name'], $target_file);

        // Update product details with the new photo
        $sql = "UPDATE products SET category = '$product_category', subcategory = '$product_subcategory', name = '$product_name', description = '$product_description', price = '$product_price', photo = '$product_photo' WHERE id = '$product_id'";
    } else {
        // Update product details without changing the photo
        $sql = "UPDATE products SET category = '$product_category', subcategory = '$product_subcategory', name = '$product_name', description = '$product_description', price = '$product_price' WHERE id = '$product_id'";
    }

    if ($conn->query($sql) === TRUE) {
        $_SESSION['message'] = "Product updated successfully!";
    } else {
        $_SESSION['message'] = "Error updating product: " . $conn->error . "</div>";
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Handling deletion of a product
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_product'])) {
    $product_id = isset($_POST['product_id']) ? $conn->real_escape_string($_POST['product_id']) : '';

    $sql = "SELECT photo FROM products WHERE id = '$product_id'";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
        $photo = $product['photo'];

        $photo_path = "./uploads/" . $photo;
        if (file_exists($photo_path)) {
            unlink($photo_path);
        }

        $sql = "DELETE FROM products WHERE id = '$product_id'";
        if ($conn->query($sql) === TRUE) {
            $_SESSION['message'] = "Product deleted successfully!";
        } else {
            $_SESSION['message'] = "Error deleting product: " . $conn->error . "</div>";
        }
    } else {
        $_SESSION['message'] = "Product not found.";
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

$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop Management</title>
    <link rel="stylesheet" href="admin_sidebar.css">
    <script src="logout_js.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

</head>
<body>
<header>
    <h1>Shop Management</h1>
</header>

<nav>
    <ul>
        <li><a href="dashboard.php">Home</a></li>
        <li><a href="user_management.php">User Management</a></li>
        <li><a href="shop_management.php">Shop Management</a></li>
        <li><a href="community_controls.php">Community Controls</a></li>
        <li><a href="blog_management.php">Blog Management</a></li>
        <li><a href="lost_found_pets.php">Lost & Found Pets</a></li>
        <li><a href="special_events.php">Special Events</a></li>
        <li><a href="vet_management.php">Vet Management</a></li>
        <li><a href="logout.php" onclick="return confirmLogout();">Logout</a></li>
    </ul>
</nav>

<main>  
<main class="container mt-5">

<h2>Add New Subcategory</h2>
<!-- Display session messages -->
<?php if (isset($_SESSION['message']) && !empty($_SESSION['message'])): ?>
    <div class="alert alert-success">
<?= $_SESSION['message'] ?>
</div>

    <?php $_SESSION['message'] = ''; // Clear the message after displaying ?>
<?php endif; ?>

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
        <select class="form-control" id="category" name="category" required onchange="loadSubcategories(this.value)">
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
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editProductModal" 
                            onclick="editProduct('<?= $row['id'] ?>', '<?= htmlspecialchars($row['category']) ?>', '<?= htmlspecialchars($row['subcategory']) ?>', '<?= htmlspecialchars($row['name']) ?>', '<?= htmlspecialchars($row['description']) ?>', '<?= htmlspecialchars($row['price']) ?>', '<?= htmlspecialchars($row['photo']) ?>')">
                            Edit
                        </button>
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
</main>

<!-- Edit Product Modal -->
<div class="modal fade" id="editProductModal" tabindex="-1" aria-labelledby="editProductModalLabel" aria-hidden="true">
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="editProductModalLabel">Edit Product</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <form id="editProductForm" action="" method="post" enctype="multipart/form-data">
                <input type="hidden" name="product_id" id="edit_product_id">
                
                <div class="mb-3">
                    <label for="edit_category" class="form-label">Category</label>
                    <select class="form-control" id="edit_category" name="category" required>
                        <option value="Food">Food</option>
                        <option value="Accessories">Accessories</option>
                        <option value="Medicines">Medicines</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="edit_subcategory" class="form-label">Subcategory</label>
                    <select class="form-control" id="edit_subcategory" name="subcategory" required>
                        <option value="">Select Subcategory</option>
                        <!-- Subcategories will be populated dynamically -->
                    </select>
                </div>

                <div class="mb-3">
                    <label for="edit_name" class="form-label">Product Name</label>
                    <input type="text" class="form-control" name="name" id="edit_name" required>
                </div>
                <div class="mb-3">
                    <label for="edit_description" class="form-label">Product Description</label>
                    <textarea class="form-control" name="description" id="edit_description" required></textarea>
                </div>
                <div class="mb-3">
                    <label for="edit_price" class="form-label">Product Price</label>
                    <input type="number" class="form-control" name="price" id="edit_price" required>
                </div>
                <div class="mb-3">
                    <label for="edit_photo" class="form-label">Product Photo</label>
                    <input type="file" class="form-control" name="photo" id="edit_photo">
                </div>
                <button type="submit" class="btn btn-primary" name="edit_product">Update Product</button>
            </form>
        </div>
    </div>
</div>
</div>

<script>
function loadSubcategories(category, selectedSubcategory = "") {
    $.ajax({
        type: 'POST',
        url: '<?= $_SERVER['PHP_SELF'] ?>', // Adjust the path as needed
        data: { main_category: category },
        success: function(response) {
            var subcategories = JSON.parse(response);
            $('#subcategory, #edit_subcategory').empty();
            $('#subcategory, #edit_subcategory').append('<option value="">Select Subcategory</option>');
            $.each(subcategories, function(index, sub_category) {
                var isSelected = (sub_category === selectedSubcategory) ? 'selected' : '';
                $('#subcategory, #edit_subcategory').append('<option value="' + sub_category + '" ' + isSelected + '>' + sub_category + '</option>');
            });
        },
        error: function() {
            console.error('Error loading subcategories');
        }
    });
}

function editProduct(id, category, subcategory, name, description, price, photo) {
    $('#edit_product_id').val(id);
    $('#edit_name').val(name);
    $('#edit_description').val(description);
    $('#edit_price').val(price);
    
    // Set selected category
    $('#edit_category').val(category);
    
    // Load subcategories based on the selected category
    loadSubcategories(category, subcategory);
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>






   
</main>



</body>
</html>
