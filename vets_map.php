<?php
include 'db.php';

session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch vet data including more details
$sql = "SELECT id, name, qualification, experience, clinic_name, consultation_fee, latitude, longitude FROM vets";
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

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        tr.expandable {
            cursor: pointer;
        }
        tr.details-row {
            display: none;
        }
        .appointment-btn {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            text-align: center;
            text-decoration: none;
            border-radius: 5px;
            display: inline-block;
        }
        .appointment-btn:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>

<?php include 'Cus-NavBar/navBar.php'; ?>

<br><br>
<h1>Expert Veterinary Services at Your Fingertips – Your Pet’s Health, Our Priority</h1>

<br>

<h2>All Registered Vets</h2>
<div id="map"></div>

<!-- Table of vets -->
<h3>List of Registered Vets</h3>
<table>
    <thead>
        <tr>
            <th>Name</th>
            <th>Qualification</th>
            <th>Experience</th>
            <th>Clinic Name</th>
            <th>Consultation Fee</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($vets as $vet): ?>
        <tr class="expandable" data-vet-id="<?= $vet['id'] ?>">
            <td><?= htmlspecialchars($vet['name']) ?></td>
            <td><?= htmlspecialchars($vet['qualification']) ?></td>
            <td><?= htmlspecialchars($vet['experience']) ?> years</td>
            <td><?= htmlspecialchars($vet['clinic_name']) ?></td>
            <td>Rs.<?= htmlspecialchars($vet['consultation_fee']) ?></td>
        </tr>
        <tr class="details-row" id="details-<?= $vet['id'] ?>">
            <td colspan="5">
                <strong>Services:</strong> <?= htmlspecialchars($vet['services']) ?><br>
                <strong>More Info:</strong> This is where additional details about the vet can go.<br><br>
                <!-- Make an Appointment Button -->
                <a href="book_appointment.php?vet_id=<?= $vet['id'] ?>" class="appointment-btn">Make an Appointment</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

   <!-- Footer -->
   <?php include 'footer.php'; ?>


<script>
    var vets = <?php echo json_encode($vets); ?>;
    
    function initMap() {
        // Default center coordinates
        var defaultCenter = { lat: -34.397, lng: 150.644 }; 
        var mapOptions = {
            zoom: 13,
            center: defaultCenter
        };

        var map = new google.maps.Map(document.getElementById('map'), mapOptions);

        // Custom icon for user's location
        var userIcon = {
            url: "https://maps.google.com/mapfiles/ms/icons/blue-dot.png",
            scaledSize: new google.maps.Size(40, 40)
        };

        // Get user's current location
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
                var userLocation = {
                    lat: position.coords.latitude,
                    lng: position.coords.longitude
                };
                map.setCenter(userLocation);
                new google.maps.Marker({
                    position: userLocation,
                    map: map,
                    title: "Your Location",
                    icon: userIcon
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
            handleLocationError(false, defaultCenter, map);
        }

        // Add markers and info windows for vets
        vets.forEach(function(vet) {
            var marker = new google.maps.Marker({
                position: { lat: parseFloat(vet.latitude), lng: parseFloat(vet.longitude) },
                map: map,
                title: vet.name
            });

            // Create content for info window
            var infoWindowContent = `
                <div>
                    <h3>${vet.name}</h3>
                    <p><strong>Qualification:</strong> ${vet.qualification}</p>
                    <p><strong>Experience:</strong> ${vet.experience} years</p>
                    <p><strong>Clinic:</strong> ${vet.clinic_name}</p>
                    <p><strong>Fee:</strong> Rs.${vet.consultation_fee}</p>
                    <a href="book_appointment.php?vet_id=${vet.id}" class="btn">Make an Appointment</a>
                </div>
            `;

            var infoWindow = new google.maps.InfoWindow({
                content: infoWindowContent
            });

            // Add click event to marker to show info window
            marker.addListener('click', function() {
                infoWindow.open(map, marker);
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


    //Vet Table
     // Expand/Collapse row logic
    document.addEventListener('DOMContentLoaded', function() {
        var rows = document.querySelectorAll('tr.expandable');
        var expandedRow = null;

        rows.forEach(function(row) {
            row.addEventListener('click', function() {
                var vetId = row.getAttribute('data-vet-id');
                var detailsRow = document.getElementById('details-' + vetId);

                // Collapse the previously expanded row, if any
                if (expandedRow && expandedRow !== detailsRow) {
                    expandedRow.style.display = 'none';
                }

                // Toggle the clicked row's details
                if (detailsRow.style.display === 'table-row') {
                    detailsRow.style.display = 'none';
                } else {
                    detailsRow.style.display = 'table-row';
                    expandedRow = detailsRow;  // Keep track of the expanded row
                }
            });
        });
    });


</script>

</body>
</html>
