<?php

include('../db.php');
include('session_check.php');
// Code for user management goes here (e.g., displaying, deleting users, etc.)

?>

<!DOCTYPE html>
<html>
<head>
    <title>Petiverse - Lost & Found Pets</title>
    <link rel="stylesheet" href="admin_sidebar.css">
    <script src="logout_js.js"></script>
</head>
<body>
<header>
    <h1>Lost & Found Pets Management</h1>
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
    <h2>Lost & Found Pets</h2>
    <!-- Add functionality for viewing, adding, and managing users here -->
</main>
</body>
</html>