<?php
include('../db.php');
include('session_check.php');

// Fetch the number of new COD orders from the database
$sql = "SELECT COUNT(*) AS new_orders_count FROM COD_orders WHERE order_status = 'pending'";
$result = $conn->query($sql);
$new_orders_count = 0;

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $new_orders_count = $row['new_orders_count'];
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Petiverse - Admin Panel</title>
    <link rel="stylesheet" href="admin_sidebar.css">
    
    <!-- Font Awesome for icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

    <script>
        function confirmLogout() {
            return confirm("Do you really want to log out?");
        }
    </script>
      <style>
        /* Notification styling */
        .notification {
            display: flex;
            align-items: center;
            background-color: #ff5722; /* Vibrant orange */
            color: white;
            padding: 10px 15px;
            border-radius: 25px;
            font-size: 16px;
            text-decoration: none; /* Remove underline */
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s, box-shadow 0.2s;
            margin-top: 15px;
            width: 550px;
            margin-bottom: 100px;
        }

        .notification:hover {
            transform: scale(1.05);
            box-shadow: 0 6px 10px rgba(0, 0, 0, 0.2);
        }

        .notification i {
            font-size: 20px;
            margin-right: 10px;
        }

        .notification-count {
            background-color: #ffeb3b; /* Bright yellow for count */
            color: black;
            border-radius: 50%;
            font-size: 14px;
            font-weight: bold;
            padding: 5px 10px;
            margin-left: 10px;
            margin-right: 10px;
        }

        /* Button styling */
        .action-button {
            display: inline-flex;
            align-items: center;
            background-color: #4caf50; /* Green */
            color: white;
            padding: 10px 20px;
            font-size: 16px;
            border: none;
            border-radius: 25px;
            text-decoration: none;
            font-weight: bold;
            cursor: pointer;
            margin-top: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: background-color 0.3s, box-shadow 0.3s;
        }

        .action-button:hover {
            background-color: #45a049;
            box-shadow: 0 6px 10px rgba(0, 0, 0, 0.2);
        }

        .action-button i {
            font-size: 20px;
            margin-right: 10px;
        }
    </style>

</head>
<body>
<header>
    <h1>Admin Panel</h1>
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
        <li><a href="logout.php" onclick="return confirmLogout();">Logout</a></li>
    </ul>
</nav>

<main>
    <h2>Welcome, <?php echo $_SESSION['username']; ?></h2>


    
    <!-- Notification for new COD orders -->
    <?php if ($new_orders_count > 0): ?>
        <a href="admin_order_details.php" class="notification">
            <i class="fas fa-truck"></i> <!-- Truck icon to represent COD orders -->
            New Cash on Delivery Order(s) - 
            <span class="notification-count"><?php echo $new_orders_count; ?></span>
            <?php echo ($new_orders_count > 1) ? 'orders' : 'order'; ?> pending.
        </a>
    <?php endif; ?>

    <!-- Button for managing orders -->
    <a href="admin_order_details.php" class="action-button">
        <i class="fas fa-list"></i> Go to Cash On Delivery Orders
    </a>

</main>
</body>
</html>
