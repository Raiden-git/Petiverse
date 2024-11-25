<?php
// Start the session (if not already started)
session_start();


// Database connection
include('../db.php');

// Check if vet is logged in
if (!isset($_SESSION['vet_id'])) {
    header("Location: index.php");
    exit();
}

// Get vet ID (you should get this from session after login)
$vet_id = isset($_SESSION['vet_id']) ? $_SESSION['vet_id'] : 0; // Replace with actual session variable

// Initialize default values
$vet = array(
    'name' => '',
    'email' => '',
    'qualification' => '',
    'specialization' => '',
    'experience' => '',
    'clinic_name' => '',
    'clinic_address' => '',
    'latitude' => '',
    'longitude' => '',
    'consultation_fee' => '',
    'contact_details' => '',
    'profile_picture' => '',
    'services' => '',
    'license_number' => '',
    'available_from' => '',
    'available_to' => ''
);

// Fetch current vet data
if ($vet_id > 0) {
    $sql = "SELECT * FROM vets WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $vet_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        $vet = $result->fetch_assoc();
    } else {
        echo '<div class="alert alert-error">Vet profile not found.</div>';
    }
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && $vet_id > 0) {
    try {
        // Prepare data
        $name = $_POST['name'];
        $email = $_POST['email'];
        $qualification = $_POST['qualification'];
        $specialization = $_POST['specialization'];
        $experience = $_POST['experience'];
        $clinic_name = $_POST['clinic_name'];
        $clinic_address = $_POST['clinic_address'];
        $latitude = $_POST['latitude'] ?: null;
        $longitude = $_POST['longitude'] ?: null;
        $consultation_fee = $_POST['consultation_fee'];
        $contact_details = $_POST['contact_details'];
        $services = $_POST['services'];
        $license_number = $_POST['license_number'];
        $available_from = $_POST['available_from'];
        $available_to = $_POST['available_to'];

        // Handle profile picture upload
        $profile_picture = null;
        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['size'] > 0) {
            // Validate file type
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            if (!in_array($_FILES['profile_picture']['type'], $allowed_types)) {
                throw new Exception('Invalid file type. Only JPG, PNG and GIF are allowed.');
            }
            
            // Validate file size (e.g., max 5MB)
            if ($_FILES['profile_picture']['size'] > 5 * 1024 * 1024) {
                throw new Exception('File too large. Maximum size is 5MB.');
            }

            $profile_picture = file_get_contents($_FILES['profile_picture']['tmp_name']);
        }

        // Prepare the SQL query
        $update_fields = array();
        $types = "";
        $params = array();

        // Build dynamic update query
        if ($profile_picture !== null) {
            $update_fields[] = "profile_picture = ?";
            $types .= "s";
            $params[] = $profile_picture;
        }

        // Add other fields
        $fields = array(
            'name' => 's',
            'email' => 's',
            'qualification' => 's',
            'specialization' => 's',
            'experience' => 'i',
            'clinic_name' => 's',
            'clinic_address' => 's',
            'latitude' => 'd',
            'longitude' => 'd',
            'consultation_fee' => 'd',
            'contact_details' => 's',
            'services' => 's',
            'license_number' => 's',
            'available_from' => 's',
            'available_to' => 's'
        );

        foreach ($fields as $field => $type) {
            if (isset($_POST[$field]) && $_POST[$field] !== '') {
                $update_fields[] = "$field = ?";
                $types .= $type;
                $params[] = $_POST[$field];
            }
        }

        // Add vet_id to params
        $types .= "i";
        $params[] = $vet_id;

        // Create update query
        $update_sql = "UPDATE vets SET " . implode(", ", $update_fields) . " WHERE id = ?";
        
        $stmt = $conn->prepare($update_sql);
        
        // Create reference array for bind_param
        $refs = array();
        $refs[] = &$types;
        foreach($params as $key => $value) {
            $refs[] = &$params[$key];
        }
        
        call_user_func_array(array($stmt, 'bind_param'), $refs);

        if ($stmt->execute()) {
            echo '<div class="alert alert-success">Profile updated successfully!</div>';
            // Refresh the page to show updated data
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } else {
            throw new Exception($stmt->error);
        }
    } catch (Exception $e) {
        echo '<div class="alert alert-error">Error updating profile: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Vet Profile</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            background-color: #f5f5f5;
            padding: 0px;
        }

        .container {
            max-width: 800px;
            margin: 20px auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
        }

        .form-section {
            margin-bottom: 30px;
        }

        .form-section h2 {
            color: #444;
            margin-bottom: 15px;
            padding-bottom: 5px;
            border-bottom: 2px solid #eee;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-row {
            display: flex;
            gap: 20px;
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #555;
            font-weight: bold;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }

        .form-group textarea {
            height: 100px;
            resize: vertical;
        }

        .form-group.col-2 {
            flex: 1;
        }

        .profile-pic-preview {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            margin: 10px 0;
            border: 2px solid #ddd;
        }

        #map {
            width: 100%;
            height: 300px;
            margin: 15px 0;
            border-radius: 4px;
            border: 1px solid #ddd;
        }

        .submit-btn {
            background-color: #4CAF50;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
            font-weight: bold;
        }

        .submit-btn:hover {
            background-color: #45a049;
        }

        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }

        .alert-success {
            background-color: #dff0d8;
            border: 1px solid #d6e9c6;
            color: #3c763d;
        }

        .alert-error {
            background-color: #f2dede;
            border: 1px solid #ebccd1;
            color: #a94442;
        }

        @media (max-width: 768px) {
            .form-row {
                flex-direction: column;
                gap: 10px;
            }
            
            .container {
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <?php include 'nav.php'; ?>
    <div class="container">
        <h1>Update Profile</h1>
        
        <form method="POST" enctype="multipart/form-data">
            <!-- Personal Information Section -->
            <div class="form-section">
                <h2>Personal Information</h2>
                
                <div class="form-row">
                    <div class="form-group col-2">
                        <label for="name">Full Name</label>
                        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($vet['name']); ?>" required>
                    </div>
                    <div class="form-group col-2">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($vet['email']); ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="experience">Years of Experience</label>
                    <input type="number" id="experience" name="experience" value="<?php echo htmlspecialchars($vet['experience']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="profile_picture">Profile Picture</label>
                    <?php if($vet['profile_picture']): ?>
                        <img src="data:image/jpeg;base64,<?php echo base64_encode($vet['profile_picture']); ?>" class="profile-pic-preview" alt="Current profile picture">
                    <?php endif; ?>
                    <input type="file" id="profile_picture" name="profile_picture" accept="image/*">
                </div>
            </div>

            <!-- Clinic Information Section -->
            <div class="form-section">
                <h2>Clinic Information</h2>
                
                <div class="form-group">
                    <label for="clinic_name">Clinic Name</label>
                    <input type="text" id="clinic_name" name="clinic_name" value="<?php echo htmlspecialchars($vet['clinic_name']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="clinic_address">Clinic Address</label>
                    <textarea id="clinic_address" name="clinic_address" required><?php echo htmlspecialchars($vet['clinic_address']); ?></textarea>
                </div>

                <div class="form-group">
                    <label>Clinic Location</label>
                    <div id="map"></div>
                    <input type="hidden" id="latitude" name="latitude" value="<?php echo htmlspecialchars($vet['latitude']); ?>">
                    <input type="hidden" id="longitude" name="longitude" value="<?php echo htmlspecialchars($vet['longitude']); ?>">
                </div>

                <div class="form-group">
                    <label for="contact_details">Contact Number</label>
                    <input type="text" id="contact_details" name="contact_details" value="<?php echo htmlspecialchars($vet['contact_details']); ?>" required>
                </div>
            </div>

            <button type="submit" class="submit-btn">Update Profile</button>
        </form>
    </div>

    <script>
        let map;
        let marker;

        function initMap() {
            // Default location (you can set this to the vet's current location)
            const defaultLat = <?php echo $vet['latitude'] ?: 0; ?>;
            const defaultLng = <?php echo $vet['longitude'] ?: 0; ?>;
            
            const defaultLocation = { lat: defaultLat, lng: defaultLng };

            map = new google.maps.Map(document.getElementById("map"), {
                zoom: 15,
                center: defaultLocation,
            });

            // Create marker
            marker = new google.maps.Marker({
                position: defaultLocation,
                map: map,
                draggable: true
            });

            // Update coordinates when marker is dragged
            google.maps.event.addListener(marker, 'dragend', function(event) {
                document.getElementById("latitude").value = event.latLng.lat();
                document.getElementById("longitude").value = event.latLng.lng();
                
                // Reverse geocoding to update address
                const geocoder = new google.maps.Geocoder();
                geocoder.geocode({ location: event.latLng }, (results, status) => {
                    if (status === "OK" && results[0]) {
                        document.getElementById("clinic_address").value = results[0].formatted_address;
                    }
                });
            });

            // Add click event to map
            google.maps.event.addListener(map, 'click', function(event) {
                marker.setPosition(event.latLng);
                document.getElementById("latitude").value = event.latLng.lat();
                document.getElementById("longitude").value = event.latLng.lng();
                
                // Reverse geocoding to update address
                const geocoder = new google.maps.Geocoder();
                geocoder.geocode({ location: event.latLng }, (results, status) => {
                    if (status === "OK" && results[0]) {
                        document.getElementById("clinic_address").value = results[0].formatted_address;
                    }
                });
            });

            // Add search box functionality
            const input = document.getElementById("clinic_address");
            const searchBox = new google.maps.places.SearchBox(input);

            // Bias the SearchBox results towards current map's viewport
            map.addListener("bounds_changed", () => {
                searchBox.setBounds(map.getBounds());
            });

            // Listen for the event fired when the user selects a prediction
            searchBox.addListener("places_changed", () => {
                const places = searchBox.getPlaces();

                if (places.length == 0) {
                    return;
                }

                const place = places[0];
                if (!place.geometry || !place.geometry.location) {
                    return;
                }

                // Update marker and map
                marker.setPosition(place.geometry.location);
                map.setCenter(place.geometry.location);
                
                // Update form fields
                document.getElementById("latitude").value = place.geometry.location.lat();
                document.getElementById("longitude").value = place.geometry.location.lng();
            });
        }
    </script>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDHdLOieN0OIcTyyY6CmJv6gPNx-OX3MwA&libraries=places&callback=initMap" async defer></script>
</body>
</html>