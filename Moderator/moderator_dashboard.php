
<?php
include('../db.php');
include('session_check.php');

/* // Fetch the number of new COD orders from the database
$sql = "SELECT COUNT(*) AS new_orders_count FROM COD_orders WHERE order_status = 'pending'";
$result = $conn->query($sql);
$new_orders_count = 0;

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $new_orders_count = $row['new_orders_count'];
}
 */
// Fetch the number of new Online Payment orders from the database
$sql_online_payment = "SELECT COUNT(*) AS new_online_payment_count FROM online_payment_orders WHERE order_status = 'pending'";
$result_online_payment = $conn->query($sql_online_payment);
$new_online_payment_count = 0;

if ($result_online_payment->num_rows > 0) {
    $row_online_payment = $result_online_payment->fetch_assoc();
    $new_online_payment_count = $row_online_payment['new_online_payment_count'];
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Petiverse - Admin Panel</title>
    <link rel="stylesheet" href="./moderator_sidebar.css">
    
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
            margin-top: 50px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: background-color 0.3s, box-shadow 0.3s;
            margin-right: 40px;
            margin-left: 150px;
            margin-bottom: 70px;
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
    <li><a href="moderator_dashboard.php">Home</a></li>
        <li><a href="Moderator_shop_management.php">Shop Management</a></li>
        <li><a href="community_controls.php">Community Controls</a></li>
        <li><a href="blog_management.php">Blog Management</a></li>
        <li><a href="lost_found_pets.php">Lost & Found Pets</a></li>
        <li><a href="special_events.php">Special Events</a></li>
        <li><a href="vet_management.php">Vet Management</a></li>
        <li><a href="petselling.php">Pet selling</a><li>
        <li><a href="logout.php" onclick="return confirmLogout();">Logout</a></li>
    </ul>
</nav>

<main>
   

    <!-- Button for COD managing orders -->
    <a href="admin_order_details.php" class="action-button">
        <i class="fas fa-truck"></i> Go to Cash On Delivery Orders
    </a>

    <!-- Button for managing Online orders -->
    <a href="online_payment_orders.php" class="action-button">
        <i class="fas fa-credit-card"></i> Online Payment Orders
    </a>

    <h2>Order Notification</h2>

    <!-- Notification for new COD orders -->
    <?php if ($new_orders_count > 0): ?>
        <a href="admin_order_details.php" class="notification">
            <i class="fas fa-truck"></i> <!-- Truck icon to represent COD orders -->
            New Cash on Delivery Order(s) - 
            <span class="notification-count"><?php echo $new_orders_count; ?></span>
            <?php echo ($new_orders_count > 1) ? 'orders' : 'order'; ?> pending.
        </a>
    <?php endif; ?>

    <!-- Notification for new Online Payment orders -->
    <?php if ($new_online_payment_count > 0): ?>
        <a href="online_payment_orders.php" class="notification">
            <i class="fas fa-credit-card"></i> <!-- Credit card icon for online payment -->
            New Card Payment Order(s) - 
            <span class="notification-count"><?php echo $new_online_payment_count; ?></span>
            <?php echo ($new_online_payment_count > 1) ? 'orders' : 'order'; ?> pending.
        </a>
    <?php endif; ?>

</main>
</body>
</html>
