<?php
include './db.php';
session_start();

// Initialize variables
$categories = [];
$selected_category = 'All';
$selected_subcategory = '';
$subcategories = [];
$products = [];
$search_query = '';

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
} else {
    $selected_category = 'Food'; // Default to "Food" category
}

// Fetch subcategories for the selected category
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


// Get selected subcategory and search query
if (isset($_GET['subcategory'])) {
    $selected_subcategory = $conn->real_escape_string($_GET['subcategory']);
}
if (isset($_GET['search_query'])) {
    $search_query = $conn->real_escape_string($_GET['search_query']);
}

// Process form submission (add to cart)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $product_id = $conn->real_escape_string($_POST['product_id']);
    $quantity = 1;

    $_SESSION['cart'][$product_id] = isset($_SESSION['cart'][$product_id]) ? $_SESSION['cart'][$product_id] + $quantity : $quantity;

    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Fetch products based on category, subcategory, or search query
$limit = 9;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$sql = "SELECT id, name, description, price, photo FROM products WHERE 1";
if ($selected_category && $selected_category !== 'All') {
    $sql .= " AND category = '$selected_category'";
    if ($selected_subcategory) {
        $sql .= " AND subcategory = '$selected_subcategory'";
    }
}
if ($search_query) {
    $sql .= " AND (name LIKE '%$search_query%' OR description LIKE '%$search_query%')";
}
$sql .= " LIMIT $limit OFFSET $offset";

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

$total_cart_items = isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pet Shop - Product Display</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/scrollbar.css">
    <script src="https://unpkg.com/boxicons@2.1.4/dist/boxicons.js"></script>
    <link rel="stylesheet" href="./assets/css/shop.css">
</head>
<body class="bg-light font-poppins">

<?php include 'Cus-NavBar/navBar.php'; ?>

<!-- Fixed Cart Icon Button at the Bottom of the Page -->
<a href="cart.php" class="cart-icon-button">
    <box-icon name="cart" type="solid" size="lg"></box-icon>
    <?php if ($total_cart_items > 0): ?>
        <span class="cart-badge"><?= $total_cart_items ?></span>
    <?php endif; ?>
</a>


<!-- Search Bar -->
<div class="mb-6 container">
    <form method="GET" action="" class="d-flex">
        <input type="text" name="search_query" placeholder="Search for products..." 
               value="<?= htmlspecialchars($search_query) ?>" class="form-control me-2">
        <button type="submit" class="btn btn-primary">Search</button>
    </form>
</div>

<div class="container d-flex">
    
    <!-- Sidebar for Subcategories -->
    <aside class="subcategory">
    <div class="shop-intro">
                <h2>Your One-Stop Shop for All Pet Needs at Petivers</h2>
                <p>Discover a captivating range of products designed to intertwine with your pet's needs.</p>
            </div>

        <h2 class="h5">Subcategories</h2>
        <?php if ($selected_category === 'All'): ?>

        <?php elseif (!empty($subcategories)): ?>
            <ul class="list-unstyled">
                <?php foreach ($subcategories as $subcategory): ?>
                    <li class="mb-2">
                        <a href="?category=<?= urlencode($selected_category) ?>&subcategory=<?= urlencode($subcategory) ?>" class="">
                            <?= htmlspecialchars($subcategory) ?>
                            
                        </a>
                        
                    </li>
                <?php endforeach; ?>
            </ul>

        <?php else: ?>
            <p>No subcategories available.</p>
        <?php endif; ?>
    </aside>

    <!-- Main Product Section -->
    <div class="col-9 p-3">
        <!-- Category Navbar -->
        <div class="main-category-nav mb-4">
            <ul class="nav nav-pills">
                <?php foreach ($categories as $category): ?>
                    <li class="nav-item">
                        <a href="?category=<?= urlencode($category) ?>" class="nav-link <?= $category == $selected_category ? 'active' : '' ?>">
                            <?= htmlspecialchars($category) ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <!-- Product Cards -->
        <div class="row row-cols-1 row-cols-md-3 g-4">
            <?php if (!empty($products)): ?>
                <?php foreach ($products as $product): ?>
                    <div class="col">
                        <div class="card h-100">
                            <img src="data:image/jpeg;base64,<?= base64_encode($product['photo']) ?>" class="card-img-top" alt="<?= htmlspecialchars($product['name']) ?>">
                            <div class="card-body text-center">
                                <h5 class="card-title"><?= htmlspecialchars($product['name']) ?></h5>
                                <p class="card-text text-primary fw-bold">LKR <?= number_format($product['price'], 2) ?></p>
                                <p class="card-text"><?= htmlspecialchars($product['description']) ?></p>
                                <form method="POST" action="">
                                    <input type="hidden" name="product_id" value="<?= htmlspecialchars($product['id']) ?>">
                                    <button type="button" class="btn btn-primary" onclick="showPaymentModal()">Buy Now</button>
                                    <button class="btn btn-secondary" name="add_to_cart">Add to Cart</button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No products available.</p>
            <?php endif; ?>
        </div>

        <!-- Pagination -->
        <div class="mt-4 d-flex justify-content-center">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="?page=<?= $i ?>" class="btn <?= ($i == $page) ? 'btn-primary' : 'btn-outline-secondary' ?> mx-1"><?= $i ?></a>
            <?php endfor; ?>
        </div>
    </div>
</div>

<!-- Footer -->
<?php include 'footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function showPaymentModal() {
        var modal = new bootstrap.Modal(document.getElementById('paymentModal'));
        modal.show();
    }
</script>

</body>
</html>
