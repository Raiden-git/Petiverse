<?php
include('../db.php');
include('session_check.php');

// Check if vet ID is provided
if (!isset($_GET['vet_id'])) {
    die("No vet ID provided");
}

$vet_id = $_GET['vet_id'];

// Fetch vet details
$sql = "SELECT * FROM vets WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $vet_id);
$stmt->execute();
$result = $stmt->get_result();
$vet = $result->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Prepare update statement
    $updateSql = "UPDATE vets SET 
        name = ?, 
        qualification = ?, 
        specialization = ?, 
        experience = ?, 
        clinic_name = ?, 
        clinic_address = ?, 
        latitude = ?, 
        longitude = ?, 
        consultation_fee = ?, 
        contact_details = ?, 
        services = ?, 
        license_number = ?, 
        email = ?
    WHERE id = ?";
    
    $stmt = $conn->prepare($updateSql);
    $stmt->bind_param(
        "sssissddsdsssi", 
        $_POST['name'],
        $_POST['qualification'],
        $_POST['specialization'],
        $_POST['experience'],
        $_POST['clinic_name'],
        $_POST['clinic_address'],
        $_POST['selected_latitude'],
        $_POST['selected_longitude'],
        $_POST['consultation_fee'],
        $_POST['contact_details'],
        $_POST['services'],
        $_POST['license_number'],
        $_POST['email'],
        $vet_id
    );
    
    if ($stmt->execute()) {
        header("Location: vet_management.php");
        exit();
    } else {
        $error = "Error updating vet details: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Vet Details</title>
    <link rel="stylesheet" href="./moderator_sidebar.css">
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDHdLOieN0OIcTyyY6CmJv6gPNx-OX3MwA&libraries=places"></script>
    <style>
        .form-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
            margin-left: 550px;
            margin-top: 50px;
        }
        .form-group {
            margin-bottom: 15px;
            width: 700px;
          
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group input, 
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
        }
        .error {
            color: red;
            margin-bottom: 15px;
        }
        #map {
            height: 400px;
            width: 100%;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
<header>
    <h1>Edit Vet Details</h1>
</header>

<nav>
    <ul>
        <li><a href="./moderator_dashboard.php">Home</a></li>
        <li><a href="vet_management.php">Back to Vet Management</a></li>
    </ul>
</nav>

<main class="form-container">
    <?php if (isset($error)): ?>
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <form method="post" id="vetForm">
        <div class="form-group">
            <label>Name</label>
            <input type="text" name="name" value="<?php echo htmlspecialchars($vet['name']); ?>" required>
        </div>
        
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($vet['email']); ?>" required>
        </div>
        
        <div class="form-group">
            <label>Qualification</label>
            <input type="text" name="qualification" value="<?php echo htmlspecialchars($vet['qualification']); ?>" required>
        </div>
        
        <div class="form-group">
            <label>Specialization</label>
            <input type="text" name="specialization" value="<?php echo htmlspecialchars($vet['specialization'] ?? ''); ?>">
        </div>
        
        <div class="form-group">
            <label>Experience (Years)</label>
            <input type="number" name="experience" value="<?php echo htmlspecialchars($vet['experience']); ?>" required>
        </div>
        
        <div class="form-group">
            <label>Clinic Name</label>
            <input type="text" name="clinic_name" value="<?php echo htmlspecialchars($vet['clinic_name']); ?>" required>
        </div>
        
        <div class="form-group">
            <label>Clinic Address</label>
            <input type="text" name="clinic_address" id="clinic_address" value="<?php echo htmlspecialchars($vet['clinic_address']); ?>" required>
        </div>
        
        <div class="form-group">
            <label>Select Clinic Location on Map</label>
            <div id="map"></div>
        </div>
        
        <!-- Hidden inputs to store selected latitude and longitude -->
        <input type="hidden" name="selected_latitude" id="selected_latitude" 
               value="<?php echo htmlspecialchars($vet['latitude'] ?? ''); ?>">
        <input type="hidden" name="selected_longitude" id="selected_longitude" 
               value="<?php echo htmlspecialchars($vet['longitude'] ?? ''); ?>">
        
        <div class="form-group">
            <label>Consultation Fee</label>
            <input type="number" step="0.01" name="consultation_fee" value="<?php echo htmlspecialchars($vet['consultation_fee']); ?>" required>
        </div>
        
        <div class="form-group">
            <label>Contact Details</label>
            <input type="text" name="contact_details" value="<?php echo htmlspecialchars($vet['contact_details']); ?>" required>
        </div>
        
        <div class="form-group">
            <label>Services</label>
            <select name="services" required>
                <option value="online" <?php echo ($vet['services'] == 'online' ? 'selected' : ''); ?>>Online</option>
                <option value="physical" <?php echo ($vet['services'] == 'physical' ? 'selected' : ''); ?>>Physical</option>
                <option value="both" <?php echo ($vet['services'] == 'both' ? 'selected' : ''); ?>>Both</option>
            </select>
        </div>
        
        <div class="form-group">
            <label>License Number</label>
            <input type="text" name="license_number" value="<?php echo htmlspecialchars($vet['license_number']); ?>" required>
        </div>
        
        <div class="form-group">
            <input type="submit" value="Update Vet Details">
        </div>
    </form>
</main>

<script>
let map, marker;

function initMap() {
    // Initial coordinates (can be set to the existing vet's location or a default location)
    const initialLat = <?php echo $vet['latitude'] ? floatval($vet['latitude']) : 0; ?>;
    const initialLng = <?php echo $vet['longitude'] ? floatval($vet['longitude']) : 0; ?>;

    // Create map
    map = new google.maps.Map(document.getElementById('map'), {
        center: { lat: initialLat || 0, lng: initialLng || 0 },
        zoom: initialLat && initialLng ? 15 : 2
    });

    // Create autocomplete for address
    const addressInput = document.getElementById('clinic_address');
    const autocomplete = new google.maps.places.Autocomplete(addressInput);
    
    // Add marker if initial coordinates exist
    if (initialLat && initialLng) {
        marker = new google.maps.Marker({
            position: { lat: initialLat, lng: initialLng },
            map: map,
            draggable: true
        });
        
        // Update hidden inputs with initial coordinates
        document.getElementById('selected_latitude').value = initialLat;
        document.getElementById('selected_longitude').value = initialLng;
    }

    // Listener for autocomplete place selection
    autocomplete.addListener('place_changed', function() {
        const place = autocomplete.getPlace();
        
        if (!place.geometry) {
            console.log('No details available for input: ' + addressInput.value);
            return;
        }

        // Clear existing marker
        if (marker) {
            marker.setMap(null);
        }

        // Create new marker
        marker = new google.maps.Marker({
            map: map,
            position: place.geometry.location,
            draggable: true
        });

        // Center map on the selected location
        map.setCenter(place.geometry.location);
        map.setZoom(15);

        // Update hidden inputs
        document.getElementById('selected_latitude').value = place.geometry.location.lat();
        document.getElementById('selected_longitude').value = place.geometry.location.lng();

        // Add listener for marker drag
        marker.addListener('dragend', function() {
            const newPosition = marker.getPosition();
            document.getElementById('selected_latitude').value = newPosition.lat();
            document.getElementById('selected_longitude').value = newPosition.lng();
        });
    });

    // Allow map click to place/move marker
    map.addListener('click', function(event) {
        // Remove existing marker if present
        if (marker) {
            marker.setMap(null);
        }

        // Create new marker
        marker = new google.maps.Marker({
            position: event.latLng,
            map: map,
            draggable: true
        });

        // Update hidden inputs
        document.getElementById('selected_latitude').value = event.latLng.lat();
        document.getElementById('selected_longitude').value = event.latLng.lng();

        // Add listener for marker drag
        marker.addListener('dragend', function() {
            const newPosition = marker.getPosition();
            document.getElementById('selected_latitude').value = newPosition.lat();
            document.getElementById('selected_longitude').value = newPosition.lng();
        });
    });
}

// Initialize map when page loads
window.onload = initMap;
</script>
</body>
</html>