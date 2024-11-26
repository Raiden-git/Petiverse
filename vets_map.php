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
    <title>Veterinary Services</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="./assets/css/scrollbar.css">
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDHdLOieN0OIcTyyY6CmJv6gPNx-OX3MwA&callback=initMap"   
    async defer></script>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f4f4;
            color: #333;
            margin: 0;
            padding: 0;
        }

        h1, h2 {
            color: #2c3e50;
        }

        .herotxt {
            font-size: 3.5em;
            font-weight: 700;
            margin-bottom: 10px;
            color: #fff;
        }

        .content-section {
            max-width: 1100px;
            margin: 0 auto;
            padding: 20px;
        }

        /* Hero Section Styling */
        .hero-section {
            position: relative;
            height: 80vh;
            background: url('assets/img/vet_hero_bg.jpg') no-repeat center center/cover;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: #fff;
        }

        .hero-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5); 
            z-index: 1;
        }

        .hero-content {
            z-index: 2;
            position: relative;
            left: 25vw;
        }

        .hero-content h1 {
            font-size: 3.5em;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .hero-content p {
            font-size: 1.3em;
            margin-bottom: 20px;
        }

        .hero-content .btn {
            background-color: #3498db;
            color: #fff;
            padding: 15px 30px;
            font-size: 1.1em;
            border-radius: 50px;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }

        .hero-content .btn:hover {
            background-color: #2980b9;
        }

        /* Map styling */
        #map {
            height: 500px;
            width: 100%;
            margin-bottom: 30px;
            border-radius: 8px;
        }

        /* Section Styling */
        .section {
            margin-bottom: 50px;
            padding: 40px;
            background-color: #ecf0f1;
            border-radius: 10px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
        }

        .section h2 {
            font-size: 1.8em;
        }

        .description {
            font-size: 1.1em;
            color: #34495e;
        }

        .btn {
            display: inline-block;
            background-color: #2980b9;
            color: white;
            padding: 10px 20px;
            font-size: 1.1em;
            border-radius: 5px;
            text-align: center;
            margin: 20px auto;
            text-decoration: none;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .btn:hover {
            background-color: #3498db;
        }

        /* Images Section */
        .image-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 40px;
        }

        .image-section img {
            width: 45%;
            border-radius: 10px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
        }

        .image-left {
            float: left;
        }

        .image-right {
            float: right;
        }


        .flex-container {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px; 
    margin-bottom: 50px;
}

.image-container {
    flex: 1;
}

.vetpet-image {
    max-width: 400px;
    height: 500px;
    width: 500px;
    box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
    margin: 0;
    padding: 0;
    object-fit: cover;
}

.content-container {
    flex: 2;
    padding: 20px;
}

.content-container h2 {
    font-size: 1.8em;
    margin-bottom: 10px;
}

.content-container .description {
    font-size: 1.1em;
    color: #34495e;
    line-height: 1.6;
}

ul.description {
    list-style: none;
    padding-left: 0;
}

ul.description li {
    margin-bottom: 10px;
}

.flex-container-reverse {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px; 
    margin-bottom: 50px;
}

.image-container {
    flex: 1;
}

.vet-image {
    max-width: 400px;
    height: 500px;
    width: 500px;
    border-radius: 10px;
    object-fit: cover;
    box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
}

.btn {
    display: inline-block;
    background-color: #2980b9;
    color: white;
    padding: 10px 20px;
    font-size: 1.1em;
    border-radius: 5px;
    text-align: center;
    margin-top: 20px;
    text-decoration: none;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.btn:hover {
    background-color: #3498db;
}

@media (max-width: 768px) {
    .flex-container {
        flex-direction: column;
    }

    .image-container {
        margin-bottom: 20px;
    }

    .content-container {
        padding: 0;
    }

    .flex-container-reverse {
        flex-direction: column;
    }

}

    </style>
</head>
<body>

<?php include 'Cus-NavBar/navBar.php'; ?>

<!-- Hero Section -->
<div class="hero-section">
    <div class="hero-overlay"></div>
    <div class="hero-content">
        <h1 class="herotxt">Top-Notch Veterinary Care</h1>
        <p>Providing high-quality veterinary services for your beloved pets.</p>
        <a href="vet_listing.php" class="btn">Find a Veterinarian</a>
    </div>
</div>

</br>

<div class="section flex-container">
    <!--Image -->
    <div class="image-container">
        <img src="assets/img/vet_image.avif" alt="Veterinarian with Pet" class="vetpet-image">
    </div>

    <!--Content -->
    <div class="content-container">
        <h2>Why Choose Our Veterinarians?</h2>
        <p class="description">
            We provide the highest quality veterinary services for your pets, ensuring their health and well-being. Our veterinarians are certified experts offering a range of services from routine check-ups to specialist care.
        </p>
        <ul class="description">
            <li><strong>Comprehensive Exams:</strong> Regular check-ups for overall health.</li>
            <li><strong>Vaccinations:</strong> Keep your pets safe with essential vaccines.</li>
            <li><strong>Online Consultations:</strong> Get advice from the comfort of your home.</li>
            <li><strong>In-Clinic Visits:</strong> Professional care at our state-of-the-art clinics.</li>
        </ul>
    </div>
</div>

<div class="content-section">

    <!-- Why Choose Our Veterinarians Section -->
    

    <!-- Map Section -->
    <h1>Find a Veterinarian Near You</h1>
    <div id="map"></div>
</div>
 


<!-- View All Veterinarians -->
<div class="section flex-container">
    <!--Image -->
    <div class="image-container">
        <img src="assets/img/all_vets.jpg" alt="Veterinarian with Pet" class="vetpet-image">
    </div>

    <!--Content -->
    <div class="content-container">
        <h2>Expert Veterinary Services at Your Fingertips</h2>
        <p class="description">
        Our network of certified and experienced veterinarians is dedicated to providing top-quality care for your pets. From routine check-ups to specialized treatments, trust our experts for your pet's health and well-being.
        </p>
        <a href="vet_listing.php" class="btn">View All Veterinarians</a>
    </div>
</div>




<div class="section flex-container-reverse">
    <!-- Left side: Content -->
    <div class="content-container">
        <h2>Become a Veterinarian</h2>
        <p class="description">
            Are you a certified vet looking to offer your services to a wider community? Join our platform and connect with pet owners across the country. We provide all the tools you need to manage appointments, consultations, and offer your expertise to those who need it most.
        </p>
        <a href="vet_register_form.php" class="btn">Join as a Veterinarian</a>
    </div>

    <!-- Right side: Image -->
    <div class="image-container">
        <img src="assets/img/become_vet.jpg" alt="Veterinarian Joining" class="vet-image">
    </div>
</div>







<!-- Footer -->
<?php include 'footer.php'; ?>

<script>
    var vets = <?php echo json_encode($vets); ?>;
    
    function initMap() {
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
            });
        }

        // Add markers for vets
        vets.forEach(function(vet) {
            var marker = new google.maps.Marker({
                position: { lat: parseFloat(vet.latitude), lng: parseFloat(vet.longitude) },
                map: map,
                title: vet.name + " - " + vet.clinic_name
            });

            // Create content for info window
            var infoWindowContent = `
                <div>
                    <h3>${vet.name}</h3>
                    <p><strong>Qualification:</strong> ${vet.qualification}</p>
                    <p><strong>Experience:</strong> ${vet.experience} years</p>
                    <p><strong>Clinic:</strong> ${vet.clinic_name}</p>
                    <p><strong>Fee:</strong> Rs.${vet.consultation_fee}</p>
                    <a href="vet_profile.php?id=${vet.id}" class="btn">Make an Appointment</a>
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
</script>

</body>
</html>
