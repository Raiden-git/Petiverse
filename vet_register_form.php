<?php 
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Veterinarian Registration</title>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDHdLOieN0OIcTyyY6CmJv6gPNx-OX3MwA&callback=initMap" async defer></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="./assets/css/scrollbar.css">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f4f7f6;
            margin: 0;
            padding: 0;
            color: #333;
            line-height: 1.6;
        }
        .container {
            max-width: 700px;
            margin: 20px auto;
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        h2 {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 30px;
            border-bottom: 3px solid #3498db;
            padding-bottom: 10px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            color: #34495e;
            font-weight: bold;
        }
        input, select {
            width: 100%;
            padding: 10px;
            border: 1px solid #bdc3c7;
            border-radius: 5px;
            transition: border-color 0.3s ease;
        }
        input:focus, select:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 5px rgba(52, 152, 219, 0.5);
        }
        .document-upload {
            border: 2px dashed #bdc3c7;
            padding: 20px;
            text-align: center;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .document-info {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 0.9em;
        }
        .required-label {
            color: #e74c3c;
            font-size: 0.8em;
            margin-left: 5px;
        }
        #map {
            height: 400px;
            width: 100%;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 2px solid #bdc3c7;
        }
        button {
            width: 100%;
            padding: 12px;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        button:hover {
            background-color: #2980b9;
        }
        .map-label {
            text-align: center;
            color: #7f8c8d;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <?php include 'Cus-NavBar/navBar.php'; ?>

    <div class="container">
        <h2>Veterinarian Registration</h2>
        
        <div class="document-info">
            <h4>Required Verification Document</h4>
            <p>Please submit one of the following documents:</p>
            <ul>
                <li>Veterinary Medical License</li>
                <li>State/Provincial Registration Certificate</li>
                <li>Professional Association Membership</li>
            </ul>
        </div>

        <form action="register_vet.php" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="name">Name:</label>
                <input type="text" id="name" name="name" required>
            </div>

            <div class="form-group">
                <label for="qualification">Qualification:</label>
                <input type="text" id="qualification" name="qualification" required>
            </div>

            <div class="form-group">
                <label for="specialization">Specialization:</label>
                <input type="text" id="specialization" name="specialization">
            </div>

            <div class="form-group">
                <label for="experience">Experience (years):</label>
                <input type="number" id="experience" name="experience" required>
            </div>

            <div class="form-group">
                <label for="license_number">License/Registration Number: <span class="required-label">*</span></label>
                <input type="text" id="license_number" name="license_number" required>
            </div>

            <div class="form-group">
                <label for="vet_document">Verification Document: <span class="required-label">*</span></label>
                <div class="document-upload">
                    <input type="file" id="vet_document" name="vet_document" accept=".pdf,.jpg,.jpeg,.png" required>
                    <p>Upload your verification document (PDF, JPG, PNG)</p>
                </div>
            </div>

            <div class="form-group">
                <label for="clinic_name">Clinic Name:</label>
                <input type="text" id="clinic_name" name="clinic_name" required>
            </div>

            <div class="form-group">
                <label for="clinic_address">Clinic Address:</label>
                <input type="text" id="clinic_address" name="clinic_address" required>
            </div>

            <div class="form-group">
                <label for="consultation_fee">Consultation Fee:</label>
                <input type="text" id="consultation_fee" name="consultation_fee" required>
            </div>

            <div class="form-group">
                <label for="contact_details">Contact Details:</label>
                <input type="text" id="contact_details" name="contact_details" required>
            </div>

            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirm Password:</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>

            <div class="form-group">
                <label for="profile_picture">Profile Picture:</label>
                <input type="file" id="profile_picture" name="profile_picture" accept="image/*" required>
            </div>

            <div class="form-group">
                <label for="services">Services:</label>
                <select id="services" name="services">
                    <option value="online">Online</option>
                    <option value="physical">Physical</option>
                    <option value="both">Both</option>
                </select>
            </div>

            <p class="map-label">Choose the Location of Your Veterinary Hospital</p>
            <div id="map"></div>

            <input type="hidden" id="latitude" name="latitude">
            <input type="hidden" id="longitude" name="longitude">

            <button type="submit">Register</button>
        </form>
        
    </div>
    <?php include 'footer.php'; ?>
    <script>
        document.querySelector('form').addEventListener('submit', function(event) {
            var password = document.getElementById('password').value;
            var confirmPassword = document.getElementById('confirm_password').value;
            
            if (password !== confirmPassword) {
                alert("Passwords do not match.");
                event.preventDefault();
            }
        });


        function initMap() {
            var defaultCenter = { lat: -34.397, lng: 150.644 };
            var mapOptions = {
                zoom: 12,
                center: defaultCenter
            };

            var map = new google.maps.Map(document.getElementById('map'), mapOptions);

            var marker = new google.maps.Marker({
                position: defaultCenter,
                map: map,
                draggable: true
            });

            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    var userLocation = {
                        lat: position.coords.latitude,
                        lng: position.coords.longitude
                    };
                    map.setCenter(userLocation);
                    marker.setPosition(userLocation);
                    document.getElementById('latitude').value = userLocation.lat;
                    document.getElementById('longitude').value = userLocation.lng;
                });
            }

            google.maps.event.addListener(marker, 'position_changed', function() {
                var lat = marker.getPosition().lat();
                var lng = marker.getPosition().lng();
                document.getElementById('latitude').value = lat;
                document.getElementById('longitude').value = lng;
            });
        }
    </script>
</body>
</html>