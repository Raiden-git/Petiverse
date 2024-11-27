<?php
include '../db.php'; 

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $address = $_POST['address'];
    $latitude = $_POST['latitude'];
    $longitude = $_POST['longitude'];

    // Insert the data into the daycare_locations table
    $sql = "INSERT INTO daycare_locations (name, address, latitude, longitude) VALUES ('$name', '$address', '$latitude', '$longitude')";
    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('Daycare location saved successfully!');</script>";
        header("Location: admin_daycare.php");
        exit();
    } else {
        echo "<script>alert('Error: " . mysqli_error($conn) . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./moderator_sidebar.css">
    <title>Add Pet Daycare Location</title>
    <style>
body {
            font-family: Arial, sans-serif;
            background-color: #f7f7f7;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 600px;
            margin: 50px auto;
            background-color: #fff;
            padding: 30px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
        }

        h1 {
            color: #333;
            text-align: center;
            font-size: 2em;
            margin-bottom: 20px;
        }

        label {
            font-size: 1.1em;
            color: #555;
        }

        input[type="text"] {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1em;
        }

        #map {
            height: 300px;
            width: 100%;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        button {
            width: 100%;
            padding: 15px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 1.2em;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #218838;
        }

        .hidden {
            display: none;
        }
    </style>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDHdLOieN0OIcTyyY6CmJv6gPNx-OX3MwA&callback=initMap&libraries=places" async defer></script>
</head>
<body>

<header>
    <h1>Add New Daycare</h1>
</header>

<nav>
    <ul>
    <li><a href="moderator_dashboard.php">Home</a></li>
        <li><a href="Moderator_shop_management.php">Shop Management</a></li>
        <li><a href="community_controls.php">Community Controls</a></li>
        <li><a href="blog_management.php">Blog Management</a></li>
        <li><a href="admin_daycare_management.php">Daycare Management</a></li>
        <li><a href="lost_found_pets.php">Lost & Found Pets</a></li>
        <li><a href="special_events.php">Special Events</a></li>
        <li><a href="vet_management.php">Vet Management</a></li>
        <li><a href="petselling.php">Pet selling</a><li>
        <li><a href="view_feedback.php">Feedbacks</a></li>
        <li><a href="logout.php" onclick="return confirmLogout();">Logout</a></li>
    </ul>
</nav>

<main>
    
    <h1>Add Pet Daycare Location</h1>

    <form id="daycareForm" method="post" action="admin_daycare.php">
        <label for="name">Daycare Name:</label><br>
        <input type="text" id="name" name="name" required><br><br>

        <label for="address">Daycare Address:</label><br>
        <input type="text" id="address" name="address" required><br><br>


        <input type="hidden" id="latitude" name="latitude" readonly>

        <input type="hidden" id="longitude" name="longitude" readonly>

        <div id="map"></div><br>

        <button type="submit">Save Daycare Location</button>
    </form>

    <script>
        let map, marker;

        function initMap() {
            // Set the initial map center (can be any default location)
            const initialLocation = { lat: 6.9271, lng: 79.8612 };  
            map = new google.maps.Map(document.getElementById("map"), {
                zoom: 13,
                center: initialLocation
            });

            marker = new google.maps.Marker({
                position: initialLocation,
                map: map,
                draggable: true
            });

            // Update latitude and longitude fields when marker is moved
            google.maps.event.addListener(marker, 'dragend', function(event) {
                document.getElementById('latitude').value = event.latLng.lat();
                document.getElementById('longitude').value = event.latLng.lng();
            });

            // Allow the admin to click on the map to place the marker
            google.maps.event.addListener(map, 'click', function(event) {
                marker.setPosition(event.latLng);
                document.getElementById('latitude').value = event.latLng.lat();
                document.getElementById('longitude').value = event.latLng.lng();
            });
        }
    </script>
</main>
</body>
</html>
