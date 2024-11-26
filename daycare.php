<?php
    session_start();

    include('db.php');
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit();
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nearby Pet Daycare Locations</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDHdLOieN0OIcTyyY6CmJv6gPNx-OX3MwA&callback=initMap" async defer></script>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            display: flex;
            align-items: center;
            background-color: #ffffff;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            padding: 15px 20px;
        }
        .header-icon {
            width: 60px;
            margin-right: 15px;
        }
        .header h1 {
            margin: 0;
            color: #333;
            font-size: 24px;
        }
        #map {
            height: 70vh;
            width: 100%;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-top: 20px;
        }
        .description {
            background-color: #ffffff;
            border-radius: 10px;
            padding: 20px;
            margin-top: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            line-height: 1.6;
            color: #666;
        }
    </style>
</head>
<body>
    <?php include 'Cus-NavBar/navBar.php'; ?>
    <div class="container">
        <div class="header">
            <svg class="header-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64">
                <path fill="#FFA07A" d="M32 10c-11 0-20 9-20 20 0 15 20 34 20 34s20-19 20-34c0-11-9-20-20-20zm0 27a7 7 0 1 1 0-14 7 7 0 0 1 0 14z"/>
                <circle cx="32" cy="27" r="5" fill="#FF6347"/>
                <path fill="#FF6347" d="M22 42a10 10 0 0 0 20 0v-5H22z"/>
            </svg>
            <h1>Pet Daycare Locator</h1>
        </div>

        <div id="map"></div>

        <div class="description">
            <h2>Find the Perfect Care for Your Furry Friend</h2>
            <p>Our interactive map helps you discover the best pet daycare locations near you. Each marker represents a trusted daycare center where your pets can play, rest, and receive professional care while you're away.</p>
            <p>Simply click on a marker to see the daycare's name and address. We're here to help you find a safe and loving environment for your beloved companions!</p>
        </div>
    </div>

    <script>
        function initMap() {
            // Create a map centered on a default location
            const defaultLocation = { lat: 6.9271, lng: 79.8612 };  // Example: Colombo, Sri Lanka
            const map = new google.maps.Map(document.getElementById('map'), {
                zoom: 12,
                center: defaultLocation,
                styles: [
                    {
                        featureType: "poi.business",
                        stylers: [{ visibility: "off" }]
                    },
                    {
                        featureType: "transit",
                        stylers: [{ visibility: "off" }]
                    }
                ]
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
                            icon: {
                                url: 'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="%23FF6347" stroke="%23FF6347" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>',
                                scaledSize: new google.maps.Size(40, 40)
                            },
                            title: daycare.name
                        });

                        const infoWindow = new google.maps.InfoWindow({
                            content: `
                                <div style="font-family: 'Poppins', sans-serif; padding: 10px;">
                                    <h3 style="margin: 0; color: #333;">${daycare.name}</h3>
                                    <p style="margin: 5px 0; color: #666;">${daycare.address}</p>
                                </div>
                            `
                        });

                        marker.addListener('click', function() {
                            infoWindow.open(map, marker);
                        });
                    });
                })
                .catch(error => console.log('Error fetching daycares:', error));
        }
    </script>
    <?php include 'footer.php'; ?>
</body>
</html>