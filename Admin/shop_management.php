<?php
include('../db.php');
include('session_check.php');

// Initialize session message if not set
if (!isset($_SESSION['message'])) {
    $_SESSION['message'] = '';
}



// Handling form submission for adding a subcategory
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_subcategory'])) {
    $main_category = $conn->real_escape_string($_POST['main_category']);
    $sub_category = $conn->real_escape_string($_POST['sub_category']);

    // Prepared statement to prevent SQL injection
    $stmt = $conn->prepare("INSERT INTO subcategories (main_category, sub_category) VALUES (?, ?)");
    $stmt->bind_param("ss", $main_category, $sub_category);

    if ($stmt->execute()) {
        $_SESSION['message'] = "New subcategory <strong>$sub_category</strong> added successfully!";
    } else {
        $_SESSION['message'] = "Error: " . $stmt->error;
    }
    $stmt->close();

    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}




// Delete Subcategory
if (isset($_POST['delete_subcategory'])) {
    $subcategory_id = $_POST['subcategory_id'];

    $sql = "DELETE FROM subcategories WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $subcategory_id);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Subcategory deleted successfully!";
    } else {
        $_SESSION['message'] = "Error deleting subcategory!";
    }
    $stmt->close();
}


// Add New Product
if (isset($_POST['add_product'])) {
    $category = $_POST['category'];
    $subcategory = $_POST['subcategory'];
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];

    // Handle photo upload
    $photo = !empty($_FILES['photo']['name']) ? file_get_contents($_FILES['photo']['tmp_name']) : null;

    $sql = "INSERT INTO products (category, subcategory, name, description, price, photo) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssssds', $category, $subcategory, $name, $description, $price, $photo);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Product added successfully!";
        
        // Redirect to avoid re-submission on page refresh
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        $_SESSION['message'] = "Error adding product!";
    }
    $stmt->close();
}




// Delete Product
if (isset($_POST['delete_product'])) {
    $product_id = $_POST['product_id'];

    $sql = "DELETE FROM products WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $product_id);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Product deleted successfully!";
    } else {
        $_SESSION['message'] = "Error deleting product!";
    }
    $stmt->close();
}




// Edit Product
if (isset($_POST['edit_product'])) {
    $product_id = $_POST['product_id'];
    $category = $_POST['category'];
    $subcategory = $_POST['subcategory'];
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];

    // Handle photo update
    if (!empty($_FILES['photo']['name'])) {
        // Move uploaded photo to a specific folder
        $photo = file_get_contents($_FILES['photo']['tmp_name']);
        $sql = "UPDATE products SET category = ?, subcategory = ?, name = ?, description = ?, price = ?, photo = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        
        // Corrected bind_param types
        $stmt->bind_param('ssssssi', $category, $subcategory, $name, $description, $price, $photo, $product_id);
    } else {
        $sql = "UPDATE products SET category = ?, subcategory = ?, name = ?, description = ?, price = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        
        // Corrected bind_param types
        $stmt->bind_param('sssssi', $category, $subcategory, $name, $description, $price, $product_id);
    }

    if ($stmt->execute()) {
        $_SESSION['message'] = "Product updated successfully!";
    } else {
        $_SESSION['message'] = "Error updating product!";
    }
    $stmt->close();
}








// Fetch Subcategories Dynamically
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['main_category'])) {
    $main_category = $_POST['main_category'];

    $sql = "SELECT sub_category FROM subcategories WHERE main_category = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $main_category);
    $stmt->execute();
    $result = $stmt->get_result();

    $subcategories = [];
    while ($row = $result->fetch_assoc()) {
        $subcategories[] = $row['sub_category'];
    }

    echo json_encode($subcategories);
    exit;
}

// Fetch Existing Subcategories
$sql = "SELECT * FROM subcategories ORDER BY main_category";
$all_subcategories = $conn->query($sql);

// Fetch Existing Products
$sql = "SELECT * FROM products ORDER BY category";
$all_products = $conn->query($sql);

$conn->close();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Petiverse - Shop Management</title>
    <link rel="stylesheet" href="admin_sidebar.css">
    <script src="logout_js.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

</head>

<body>


    <!-- Header -->
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
        <li><a href="moderator_management.php">Moderator Management</a></li>
        <li><a href="petselling.php">Pet selling</a><li>
        <li><a href="logout.php" onclick="return confirmLogout();">Logout</a></li>
    </ul>
</nav>

<main>
    <h2>Shop Management</h2>


     <!-- Main Content -->
     <main class="container mt-5">
        <!-- Add New Subcategory -->
        <h2>Add New Subcategory</h2>
        <?php if (isset($_SESSION['message']) && !empty($_SESSION['message'])) : ?>
            <div class="alert alert-success"><?= $_SESSION['message'] ?></div>
            <?php $_SESSION['message'] = ''; ?>
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

        <!-- List of Existing Subcategories -->
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
                <?php if ($all_subcategories->num_rows > 0) : ?>
                    <?php while ($row = $all_subcategories->fetch_assoc()) : ?>
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
                <?php else : ?>
                    <tr>
                        <td colspan="3">No subcategories found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

       

        <form method="POST" enctype="multipart/form-data" action="shop_management.php">
    <div class="mb-3">
        <label for="category" class="form-label">Category</label>
        <select id="category" name="category" class="form-control" required>
            <option value="">Select Category</option>
            <option value="Food">Food</option>
            <option value="Accessories">Accessories</option>
            <option value="Medicines">Medicines</option>
            <!-- Add other categories if needed -->
        </select>
    </div>

    <div class="mb-3">
        <label for="subcategory" class="form-label">Subcategory</label>
        <select id="subcategory" name="subcategory" class="form-control" required>
            <option value="">Select Subcategory</option>
            <!-- Subcategories will be dynamically loaded here -->
        </select>
    </div>

    <div class="mb-3">
        <label for="name" class="form-label">Product Name</label>
        <input type="text" class="form-control" id="name" name="name" required>
    </div>

    <div class="mb-3">
        <label for="description" class="form-label">Product Description</label>
        <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
    </div>

    <div class="mb-3">
        <label for="price" class="form-label">Product Price</label>
        <input type="number" class="form-control" id="price" name="price" step="0.01" required>
    </div>

    <div class="mb-3">
        <label for="photo" class="form-label">Product Photo</label>
        <input type="file" class="form-control" id="photo" name="photo" accept="image/*">
    </div>

    <button type="submit" name="add_product" class="btn btn-primary">Add Product</button>
</form>



<!-- List of Existing Products -->
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
        <?php if ($all_products->num_rows > 0) : ?>
            <?php while ($row = $all_products->fetch_assoc()) : ?>
                <tr>
                    <td><?= htmlspecialchars($row['category']) ?></td>
                    <td><?= htmlspecialchars($row['subcategory']) ?></td>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td><?= htmlspecialchars($row['description']) ?></td>
                    <td>LKR <?= htmlspecialchars($row['price']) ?></td>
                    <td>
                        <?php if ($row['photo']) : ?>
                            <img src="data:image/jpeg;base64,<?= base64_encode($row['photo']) ?>" alt="Product Photo" width="50">
                        <?php endif; ?>
                    </td>
                    <td>
                        <button class="btn btn-warning" onclick="editProduct(<?= htmlspecialchars($row['id']) ?>, '<?= htmlspecialchars($row['category']) ?>', '<?= htmlspecialchars($row['subcategory']) ?>', '<?= htmlspecialchars($row['name']) ?>', '<?= htmlspecialchars($row['description']) ?>', <?= htmlspecialchars($row['price']) ?>, '<?= base64_encode($row['photo']) ?>')">Edit</button>
                        <form action="" method="post" class="d-inline">
                            <input type="hidden" name="product_id" value="<?= htmlspecialchars($row['id']) ?>">
                            <button type="submit" class="btn btn-danger" name="delete_product" onclick="return confirm('Are you sure you want to delete this product?');">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else : ?>
            <tr>
                <td colspan="7">No products found</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>


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




    <!-- JavaScript for dynamic loading -->
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



        $(document).ready(function () {
    // When the category dropdown changes, load the subcategories
    $('#category').on('change', function () {
        var mainCategory = $(this).val();
        
        if (mainCategory !== "") {
            $.ajax({
                url: 'shop_management.php', // Replace this with the correct PHP file handling the subcategory fetch
                type: 'POST',
                data: { main_category: mainCategory },
                success: function (response) {
                    var subcategories = JSON.parse(response);
                    var subcategorySelect = $('#subcategory');
                    subcategorySelect.empty(); // Clear the previous subcategory options
                    
                    subcategorySelect.append('<option value="">Select Subcategory</option>');
                    $.each(subcategories, function (index, subcategory) {
                        subcategorySelect.append('<option value="' + subcategory + '">' + subcategory + '</option>');
                    });
                },
                error: function () {
                    alert("Error loading subcategories.");
                }
            });
        }
    });
});









// Function to load subcategories based on the selected main category
function loadSubcategories(mainCategory, selectedSubcategory = null) {
    $.ajax({
        url: 'shop_management.php', // URL to fetch subcategories
        type: 'POST',
        data: { main_category: mainCategory }, // Send the selected main category
        success: function(response) {
            let subcategories = JSON.parse(response);
            let subcategorySelect = $('#edit_subcategory');
            
            subcategorySelect.empty(); // Clear existing subcategories
            subcategorySelect.append('<option value="">Select Subcategory</option>');
            
            // Append fetched subcategories
            $.each(subcategories, function(index, subcategory) {
                let isSelected = selectedSubcategory === subcategory ? 'selected' : '';
                subcategorySelect.append(`<option value="${subcategory}" ${isSelected}>${subcategory}</option>`);
            });
        }
    });
}



// Trigger subcategory loading when category changes
$('#edit_category').on('change', function() {
    let selectedCategory = $(this).val();
    loadSubcategories(selectedCategory);
});

// Function to populate the modal when editing a product
function editProduct(id, category, subcategory, name, description, price, photo) {
    $('#edit_product_id').val(id);
    $('#edit_name').val(name);
    $('#edit_description').val(description);
    $('#edit_price').val(price);
    
    // Set the main category
    $('#edit_category').val(category);

    // Load the subcategories for the main category and select the current subcategory
    loadSubcategories(category, subcategory);

    // Show the modal
    $('#editProductModal').modal('show');
}


   
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
