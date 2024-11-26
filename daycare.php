<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nearest Pet Daycare Locations</title>
    <style>
        #map {
            height: 100vh;
            width: 100%;
        }
    </style>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDHdLOieN0OIcTyyY6CmJv6gPNx-OX3MwA&callback=initMap" async defer></script>
</head>
<body>
    <h1>Pet Daycare Locations</h1>
    <div id="map"></div>

    <script>
        function initMap() {
            // Create a map centered on a default location
            const defaultLocation = { lat: 6.9271, lng: 79.8612 };  // Example: Colombo, Sri Lanka
            const map = new google.maps.Map(document.getElementById('map'), {
                zoom: 12,
                center: defaultLocation
            });

            // Fetch daycare locations from the server
            fetch('get_daycares.php')
                .then(response => response.json())
                .then(data => {
                    data.forEach(daycare => {
                        const marker = new google.maps.Marker({
                            position: {
                                lat: parseFloat(daycare.latitude),
                                lng: parseFloat(daycare.longitude)
                            },
                            map: map,
                            title: daycare.name
                        });

                        const infoWindow = new google.maps.InfoWindow({
                            content: `<h3>${daycare.name}</h3><p>${daycare.address}</p>`
                        });

                        marker.addListener('click', function() {
                            infoWindow.open(map, marker);
                        });
                    });
                })
                .catch(error => console.log('Error fetching daycares:', error));
        }
    </script>
</body>
</html>
