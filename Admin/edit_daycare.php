<?php
include '../db.php'; 

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Fetch daycare details based on the ID
    $sql = "SELECT * FROM daycare_locations WHERE id = $id";
    $result = mysqli_query($conn, $sql);
    $daycare = mysqli_fetch_assoc($result);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="admin_sidebar.css">
    <title>Edit Daycare Location</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f7f7f7;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 800px;
            margin: 40px auto;
            background-color: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            color: #333;
            font-size: 2.5em;
            margin-bottom: 20px;
        }

        label {
            font-size: 1.2em;
            color: #555;
            margin-bottom: 10px;
            display: block;
        }

        input[type="text"] {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 1em;
            box-sizing: border-box;
        }

        #map {
            height: 400px;
            width: 100%;
            margin-bottom: 20px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        button {
            display: block;
            width: 100%;
            padding: 15px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 1.2em;
            cursor: pointer;
            margin-top: 20px;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #218838;
        }
    </style>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDHdLOieN0OIcTyyY6CmJv6gPNx-OX3MwA"></script>
    <script>
        function initMap() {
            var location = {lat: <?php echo $daycare['latitude']; ?>, lng: <?php echo $daycare['longitude']; ?>};
            var map = new google.maps.Map(document.getElementById('map'), {
                zoom: 15,
                center: location
            });

            var marker = new google.maps.Marker({
                position: location,
                map: map,
                draggable: true
            });

            // Update hidden fields when marker is dragged
            google.maps.event.addListener(marker, 'dragend', function(event) {
                document.getElementById('latitude').value = event.latLng.lat();
                document.getElementById('longitude').value = event.latLng.lng();
            });
        }
    </script>
</head>
<body onload="initMap()">

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
        <li><a href="admin_daycare_management.php">Daycare Management</a></li>
        <li><a href="lost_found_pets.php">Lost & Found Pets</a></li>
        <li><a href="special_events.php">Special Events</a></li>
        <li><a href="vet_management.php">Vet Management</a></li>
        <li><a href="moderator_management.php">Moderator Management</a></li>
        <li><a href="petselling.php">Pet selling</a><li>        
        <li><a href="view_feedback.php">Feedbacks</a></li>
        <li><a href="logout.php" onclick="return confirmLogout();">Logout</a></li>
    </ul>
</nav>

<main>

    <h1>Edit Pet Daycare Location</h1>

    <!-- Display map with draggable marker -->
    <div id="map"></div>

    <!-- Form to update daycare location -->
    <form method="post" action="update_daycare.php">
        <input type="hidden" name="id" value="<?php echo $daycare['id']; ?>">

        <label for="name">Daycare Name:</label><br>
        <input type="text" id="name" name="name" value="<?php echo $daycare['name']; ?>" required><br><br>

        <label for="address">Daycare Address:</label><br>
        <input type="text" id="address" name="address" value="<?php echo $daycare['address']; ?>" required><br><br>

        <!-- Hidden fields to store latitude and longitude -->
        <input type="hidden" id="latitude" name="latitude" value="<?php echo $daycare['latitude']; ?>">
        <input type="hidden" id="longitude" name="longitude" value="<?php echo $daycare['longitude']; ?>">

        <button type="submit">Update Daycare Location</button>
    </form>
</main>
</body>
</html>
