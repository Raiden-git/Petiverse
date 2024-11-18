<?php

include('../db.php');
include('session_check.php');
// Code for user management goes here (e.g., displaying, deleting users, etc.)
$sql = "SELECT * FROM vets";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Petiverse - Vet Management</title>
    <link rel="stylesheet" href="admin_sidebar.css">
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDHdLOieN0OIcTyyY6CmJv6gPNx-OX3MwA">
        // JavaScript function to display confirmation
        function confirmLogout() {
            return confirm("Do you really want to log out?");
        }
    </script>
    <style>
        .map {
            width: 300px;
            height: 200px;
        }
    </style>
</head>
<body>
<header>
    <h1>Vet Management</h1>
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
    <!-- Add functionality for viewing, adding, and managing users here -->
    <h2>Registered Vets</h2>

<table border="1">
    <tr>
        <th>Name</th>
        <th>Qualification</th>
        <th>Experience</th>
        <th>Clinic Name</th>
        <th>Clinic Address</th>
        <th>Consultation Fee</th>
        <th>Contact Details</th>
        <th>Services</th>
        <th>Location</th>
    </tr>

    <?php
    $index = 0;
    while ($row = mysqli_fetch_assoc($result)) {
        $index++; // Increment index for each row
        echo "<tr>
            <td>{$row['name']}</td>
            <td>{$row['qualification']}</td>
            <td>{$row['experience']}</td>
            <td>{$row['clinic_name']}</td>
            <td>{$row['clinic_address']}</td>
            <td>{$row['consultation_fee']}</td>
            <td>{$row['contact_details']}</td>
            <td>{$row['services']}</td>
            <td>
                <div id='map-$index' class='map'></div> <!-- Map container for each vet -->
                <script>
                    function initMap() {
                        var location = {lat: {$row['latitude']}, lng: {$row['longitude']}};
                        var map = new google.maps.Map(document.getElementById('map-$index'), {
                            zoom: 12,
                            center: location
                        });
                        var marker = new google.maps.Marker({
                            position: location,
                            map: map
                        });
                    }
                    initMap();
                </script>
            </td>
        </tr>";
    }
    ?>

</table>
</main>
</body>
</html>