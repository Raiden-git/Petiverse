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

// Initialize variables
$categories = [];
$selected_category = '';
$selected_subcategory = '';
$subcategories = [];
$products = [];

// Fetch main categories
$sql = "SELECT DISTINCT main_category FROM subcategories";
$result = $conn->query($sql);

if ($result === false) {
    die("Error fetching main categories: " . $conn->error);
} else {
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row['main_category'];
    }
}

// Get selected main category
if (isset($_GET['category'])) {
    $selected_category = $conn->real_escape_string($_GET['category']);

    // Fetch subcategories for the selected main category
    $sql = "SELECT sub_category FROM subcategories WHERE main_category = '$selected_category'";
    $result = $conn->query($sql);
    
    if ($result === false) {
        die("Error fetching subcategories: " . $conn->error);
    } else {
        while ($row = $result->fetch_assoc()) {
            $subcategories[] = $row['sub_category'];
        }
    }
}

// Get selected subcategory
if (isset($_GET['subcategory'])) {
    $selected_subcategory = $conn->real_escape_string($_GET['subcategory']);
}

// Process form submission (add to cart)
session_start(); // Start session for cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $product_id = $conn->real_escape_string($_POST['product_id']);
    $quantity = 1; // Fixed quantity for the cart

    // Save product and quantity in session
    $_SESSION['cart'][$product_id] = isset($_SESSION['cart'][$product_id]) ? $_SESSION['cart'][$product_id] + $quantity : $quantity;

    // Redirect to prevent form resubmission on refresh
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Fetch products based on selected category and subcategory
$limit = 9; // Products per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Construct the SQL query for products
$sql = "SELECT * FROM products WHERE 1"; // Start with a condition that always returns true

if ($selected_category) {
    $sql .= " AND category = '$selected_category'"; // Filter by selected category
}

if ($selected_subcategory) {
    $sql .= " AND subcategory = '$selected_subcategory'"; // Further filter by subcategory if selected
}

$sql .= " LIMIT $limit OFFSET $offset"; // Add pagination

$result = $conn->query($sql);

if ($result === false) {
    die("Error fetching products: " . $conn->error);
} else {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}

// Count total products for pagination
$sql_count = "SELECT COUNT(*) as total FROM products WHERE 1";
if ($selected_category) {
    $sql_count .= " AND category = '$selected_category'";
}
if ($selected_subcategory) {
    $sql_count .= " AND subcategory = '$selected_subcategory'";
}
$result_count = $conn->query($sql_count);
$total_products = $result_count->fetch_assoc()['total'];
$total_pages = ceil($total_products / $limit);

// Count total items in cart for display
$total_cart_items = isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pet Shop - Product Display</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/boxicons@2.1.4/dist/boxicons.js"></script>
    <link rel="stylesheet" href="../assets/css/shop.css">
</head>
<body>

<header>
    <div class="logo">
        <a href="../index.html"><h1>Petiverse</h1></a>
    </div>
    <nav>
        <ul>
        <li><a href="../index.html">Home</a></li>
            <li><a href="../Customer/shop.php">Shop</a></li>
            <li><a href="#">Vet Services</a></li>
            <li><a href="#">Day Care</a></li>
            <li><a href="#">Community</a></li>
            <li><a href="#">Blog</a></li>
            <li><a href="#">Special Events</a></li>
            <li><a href="#">Contact Us</a></li>
            <li><a href="#">Pet Selling</a></li>
        </ul>
    </nav>
</header>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl mb-6">Pet Shop Categories</h1>
    
    <!-- Category Navbar -->
    <ul class="flex justify-center mb-6">
        <?php if (!empty($categories)): ?>
            <?php foreach ($categories as $category): ?>
                <li class="mx-2">
                    <a class="bg-blue-600 text-white rounded-full px-4 py-2 hover:bg-blue-500 transition" 
                       href="?category=<?= urlencode($category) ?>"><?= htmlspecialchars($category) ?></a>
                </li>
            <?php endforeach; ?>
        <?php else: ?>
            <li><p>No categories available</p></li>
        <?php endif; ?>
    </ul>

    <!-- Subcategories Navbar -->
    <?php if ($selected_category): ?>
        <h2 class="text-2xl mb-4"><?= htmlspecialchars($selected_category) ?> Subcategories</h2>
        <ul class="flex justify-center mb-6">
            <?php if (!empty($subcategories)): ?>
                <?php foreach ($subcategories as $subcategory): ?>
                    <li class="mx-2">
                        <a class="bg-gray-200 text-gray-700 rounded-full px-3 py-1 hover:bg-gray-300 transition" 
                           href="?category=<?= urlencode($selected_category) ?>&subcategory=<?= urlencode($subcategory) ?>"><?= htmlspecialchars($subcategory) ?></a>
                    </li>
                <?php endforeach; ?>
            <?php else: ?>
                <li><p>No subcategories available</p></li>
            <?php endif; ?>
        </ul>
    <?php endif; ?>

<!-- Product Cards -->
<div class="container mx-auto px-2"> <!-- Reduced padding -->
    <div class="grid md:grid-cols-4 gap-4"> <!-- Reduced gap -->
        <?php if (!empty($products)): ?>
            <?php foreach ($products as $product): ?>
                <div class="product-card p-4">
                    <img src="../uploads/<?= htmlspecialchars($product['photo']) ?>" class="w-full h-48 object-cover rounded-t-lg" alt="<?= htmlspecialchars($product['name']) ?>">
                    <div class="p-3 text-center">
                        <h5 class="text-lg font-semibold"><?= htmlspecialchars($product['name']) ?></h5>
                        <p class="text-xl text-blue-600 font-bold">$<?= number_format($product['price'], 2) ?></p>
                        <p class="text-gray-600 mb-4"><?= htmlspecialchars($product['description']) ?></p>
                        <form method="POST" action="">
                            <input type="hidden" name="product_id" value="<?= htmlspecialchars($product['id']) ?>">
                            <button class="button-primary rounded-full px-4 py-2 hover:bg-blue-500 transition">Buy Now</button>
                            <button class="button-secondary rounded-full px-4 py-2 hover:bg-gray-500 transition ml-2" name="add_to_cart">Add to Cart</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No products available.</p>
        <?php endif; ?>
    </div>
</div>

    
</div>

<!-- Fixed Cart Icon -->
<div class="cart-icon-container">
    <a href="cart.php">
        <box-icon name='cart' size="40px"></box-icon>
        <?php if ($total_cart_items > 0): ?>
            <span class="cart-count"><?= $total_cart_items ?></span>
        <?php endif; ?>
    </a>
</div>

</body>
</html>

<?php
// Close database connection
$conn->close();
?>
