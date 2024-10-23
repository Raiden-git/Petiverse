<?php
include './db.php';

// Initialize variables
$categories = [];
$selected_category = 'All'; // Default to "All" category
$selected_subcategory = '';
$subcategories = [];
$products = [];
$search_query = ''; // Search query variable

// Fetch main categories
$sql = "SELECT DISTINCT main_category FROM subcategories";
$result = $conn->query($sql);

if ($result === false) {
    die("Error fetching main categories: " . $conn->error);
} else {
    $categories[] = 'All'; // Add "All" option for displaying all products
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row['main_category'];
    }
}

// Get selected main category
if (isset($_GET['category'])) {
    $selected_category = $conn->real_escape_string($_GET['category']);
}

// Fetch subcategories for the selected main category (except when "All" is selected)
if ($selected_category && $selected_category !== 'All') {
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

// Get search query
if (isset($_GET['search_query'])) {
    $search_query = $conn->real_escape_string($_GET['search_query']);
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

// Fetch products based on selected category, subcategory, or search query
$limit = 9; // Products per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Construct the SQL query for products
$sql = "SELECT id, name, description, price, photo FROM products WHERE 1"; // Include the photo field

// If a category is selected but not "All", filter by the selected category
if ($selected_category && $selected_category !== 'All') {
    $sql .= " AND category = '$selected_category'"; // Filter by selected category

    // Further filter by subcategory if selected
    if ($selected_subcategory) {
        $sql .= " AND subcategory = '$selected_subcategory'";
    }
}

// Add search functionality: If search query is present, search by product name or description
if ($search_query) {
    $sql .= " AND (name LIKE '%$search_query%' OR description LIKE '%$search_query%')";
}

// Add the limit for pagination
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
if ($selected_category && $selected_category !== 'All') {
    $sql_count .= " AND category = '$selected_category'";
}
if ($selected_subcategory) {
    $sql_count .= " AND subcategory = '$selected_subcategory'";
}
if ($search_query) {
    $sql_count .= " AND (name LIKE '%$search_query%' OR description LIKE '%$search_query%')";
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
    <link rel="stylesheet" href="./assets/css/shop.css">
    <link rel="stylesheet" href="./assets/css/scrollbar.css">
</head>
<body>

<?php include 'Cus-NavBar/navBar.php'; ?>

<!-- Search Bar -->
<div class="search-bar-container">
    <form method="GET" action="">
        <input type="text" name="search_query" placeholder="Search for products..." value="<?= htmlspecialchars($search_query) ?>" class="search-bar">
        <button type="submit" class="search-button">Search</button>
    </form>
</div>

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
    <?php if ($selected_category && $selected_category !== 'All'): ?>
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
    <div class="grid md:grid-cols-4 gap-4">
        <?php if (!empty($products)): ?>
            <?php foreach ($products as $product): ?>
                <div class="product-card p-4">
                    <!-- Convert binary data to base64 for displaying as an image -->
                    <img src="data:image/jpeg;base64,<?= base64_encode($product['photo']) ?>" class="w-full h-48 object-cover rounded-t-lg" alt="<?= htmlspecialchars($product['name']) ?>">
                    <div class="p-3 text-center">
                        <h5 class="text-lg font-semibold"><?= htmlspecialchars($product['name']) ?></h5>
                        <p class="text-xl text-blue-600 font-bold">$<?= number_format($product['price'], 2) ?></p>
                        <p class="text-gray-600 mb-4"><?= htmlspecialchars($product['description']) ?></p>
                        <form method="POST" action="">
                            <input type="hidden" name="product_id" value="<?= htmlspecialchars($product['id']) ?>">
                            <button type="button" class="button-primary rounded-full px-4 py-2 hover:bg-blue-500 transition" onclick="showPaymentModal()">Buy Now</button>
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

<!-- Fixed Cart Icon -->
<div class="cart-icon-container">
    <a href="cart.php">
        <box-icon name='cart' size="40px"></box-icon>
        <span class="cart-count"><?= $total_cart_items ?></span>
    </a>
</div>

<!-- Payment Modal -->
<div id="paymentModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center hidden">
    <div class="bg-white rounded-lg shadow-lg p-6 max-w-md w-full">
        <h3 class="text-xl font-semibold mb-4">Choose Payment Method</h3>
        <p class="mb-6">Please select a payment method for your purchase.</p>
        <div class="flex justify-between">
            <button class="bg-green-500 text-white rounded px-4 py-2 hover:bg-green-600 transition" onclick="handlePayment('cod')">Cash on Delivery</button>
            <button class="bg-blue-500 text-white rounded px-4 py-2 hover:bg-blue-600 transition" onclick="handlePayment('online')">Online Payment</button>
        </div>
        <button class="mt-4 text-red-500" onclick="closePaymentModal()">Cancel</button>
    </div>
</div>

<script>
    function showPaymentModal() {
        document.getElementById('paymentModal').classList.remove('hidden');
    }

    function closePaymentModal() {
        document.getElementById('paymentModal').classList.add('hidden');
    }

    function handlePayment(method) {
        if (method === 'cod') {
            // Redirect to cash_on_delivery.php
            window.location.href = 'cash_on_delivery.php'; // Redirect to COD page
        } else if (method === 'online') {
            alert('You selected Online Payment. Redirecting to payment gateway...');
            // Here you can redirect to the payment gateway or further implement online payment logic
        }
        closePaymentModal(); // Close the modal after selection
    }
</script>

</body>
</html>
