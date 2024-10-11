<?php
include 'db.php';

session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}


// Fetch vets data from the database
$sql = "SELECT name, latitude, longitude FROM vets";
$result = mysqli_query($conn, $sql);
$vets = [];

while ($row = mysqli_fetch_assoc($result)) {
    $vets[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vets Map</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDHdLOieN0OIcTyyY6CmJv6gPNx-OX3MwA&callback=initMap" async defer></script>
    <style>
        #map {
            height: 500px;
            width: 100%;
        }
    </style>
</head>
<body>

<?php include 'Cus-NavBar/navBar.php'; ?>

<h2>All Registered Vets</h2>
<div id="map"></div>

<script>
    var vets = <?php echo json_encode($vets); ?>;
    
    function initMap() {
        // Default center coordinates if geolocation fails
        var defaultCenter = { lat: -34.397, lng: 150.644 }; 
        var mapOptions = {
            zoom: 13,
            center: defaultCenter
        };

        var map = new google.maps.Map(document.getElementById('map'), mapOptions);

        // Custom icon for the user's location
        var userIcon = {
            url: "https://maps.google.com/mapfiles/ms/icons/blue-dot.png", // URL to a custom icon (blue dot)
            scaledSize: new google.maps.Size(40, 40) // Optionally scale the icon size
        };

        // Try to get the user's current location
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
                var userLocation = {
                    lat: position.coords.latitude,
                    lng: position.coords.longitude
                };

                // Center the map on the user's location
                map.setCenter(userLocation);

                // Add a marker for the user's location with the custom icon
                new google.maps.Marker({
                    position: userLocation,
                    map: map,
                    title: "Your Location",
                    icon: userIcon // Use the custom icon here
                });
            }, function(error) {
                console.log("Geolocation error: " + error.message);
                handleLocationError(true, defaultCenter, map);
            }, {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 0
            });
        } else {
            // Browser doesn't support Geolocation
            handleLocationError(false, defaultCenter, map);
        }

        // Add markers for vets
        vets.forEach(function(vet) {
            var marker = new google.maps.Marker({
                position: { lat: parseFloat(vet.latitude), lng: parseFloat(vet.longitude) },
                map: map,
                title: vet.name
            });
        });
    }

    function handleLocationError(browserHasGeolocation, pos, map) {
        var infoWindow = new google.maps.InfoWindow({
            position: pos,
            map: map,
            content: browserHasGeolocation
                ? "Error: The Geolocation service failed."
                : "Error: Your browser doesn't support geolocation."
        });
        map.setCenter(pos);
    }
</script>

</body>
</html>
