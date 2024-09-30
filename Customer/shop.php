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
    
    if ($selected_subcategory) {
        // Fetch products for the selected subcategory with pagination
        $limit = 9; // Products per page
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $offset = ($page - 1) * $limit;

        $sql = "SELECT * FROM products WHERE subcategory = '$selected_subcategory' LIMIT $limit OFFSET $offset";
        $result = $conn->query($sql);
        
        if ($result === false) {
            die("Error fetching products: " . $conn->error);
        } else {
            while ($row = $result->fetch_assoc()) {
                $products[] = $row;
            }
        }

        // Count total products for pagination
        $sql_count = "SELECT COUNT(*) as total FROM products WHERE subcategory = '$selected_subcategory'";
        $result_count = $conn->query($sql_count);
        $total_products = $result_count->fetch_assoc()['total'];
        $total_pages = ceil($total_products / $limit);
    }
}

// Handle add to cart
if (isset($_POST['add_to_cart'])) {
    $product_id = $conn->real_escape_string($_POST['product_id']);
    $quantity = 1; // Fixed quantity for the cart

    // Start session and save product and quantity in session
    session_start();
    $_SESSION['cart'][$product_id] = isset($_SESSION['cart'][$product_id]) ? $_SESSION['cart'][$product_id] + $quantity : $quantity; 
}

// Display the cart
$cart_items = [];
if (isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $product_id => $quantity) {
        $sql = "SELECT * FROM products WHERE id = '$product_id'";
        $result = $conn->query($sql);
        if ($result) {
            $cart_item = $result->fetch_assoc();
            $cart_item['quantity'] = $quantity;
            $cart_items[] = $cart_item;
        }
    }
}

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
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f7fa;
            color: #333;
        }
        h1, h2 {
            color: #2b2b2b;
            margin-bottom: 20px;
            font-weight: 600;
            text-align: center;
        }
        .product-card {
            padding: 50px;
            transition: transform 0.3s, box-shadow 0.3s;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2);
        }
        .button-primary {
            background-color: #1d4ed8;
            color: #fff;
        }
        .button-secondary {
            background-color: #6b7280;
            color: #fff;
        }
    </style>
</head>
<body>

<nav class="bg-white shadow-md">
    <div class="container mx-auto px-4 py-4">
        <div class="flex justify-between items-center">
            <a class="text-2xl font-bold text-blue-600" href="#">Petiverse</a>
            <div class="flex space-x-4">
                <a class="text-gray-600 hover:text-blue-600" href="shop.php">Shop</a>
                <a class="text-gray-600 hover:text-blue-600" href="cart.php">Cart (<?= $total_cart_items ?>)</a>
            </div>
        </div>
    </div>
</nav>

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

        <!-- Product Cards -->
        <?php if ($selected_subcategory): ?>
            <h2 class="text-2xl mb-4">Products in <?= htmlspecialchars($selected_subcategory) ?></h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <?php if (!empty($products)): ?>
                    <?php foreach ($products as $product): ?>
                        <div class="product-card p-4">
                            <img src="../uploads/<?= htmlspecialchars($product['photo']) ?>" class="w-full h-48 object-cover rounded-t-lg" alt="<?= htmlspecialchars($product['name']) ?>">
                            <div class="p-4 text-center">
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
                    <p>No products available in this category.</p>
                <?php endif; ?>
            </div>

 
        <?php endif; ?>
    <?php endif; ?>
</div>



</body>
</html>

<?php
// Close database connection
$conn->close();
?>
