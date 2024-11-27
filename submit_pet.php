<?php

session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit(); 
}

// Database connection
$host = "localhost";
$dbname = "petiverse"; 
$username = "root";
$password = "";

// Create connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Variable to control popup display
$showPopup = false;

// If the form is submitted, insert data into the database
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $pet_name = $_POST['pet_name'];
    $pet_type = $_POST['pet_type'];
    $description = $_POST['description'];
    $location = $_POST['location'];
    $status = $_POST['status'];
    $contact_info = $_POST['contact_info'];

    // Handle image upload
    $image = null; 
    if (isset($_FILES['pet_image']) && $_FILES['pet_image']['error'] == 0) {
        $image = file_get_contents($_FILES['pet_image']['tmp_name']);
    }

    $sql = "INSERT INTO lost_and_found_pets (pet_name, pet_type, description, location, status, contact_info, image, approved, user_id) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    // Check if the statement was prepared correctly
    if ($stmt === false) {
        die("Error preparing statement: " . $conn->error);
    }

    // Ensure the image data is bound correctly
    $approved = 0; 
    $user_id = $_SESSION['user_id']; 

    // Bind parameters (image must use `addslashes` for proper binary handling)
    $stmt->bind_param(
        "ssssssbii", 
        $pet_name, 
        $pet_type, 
        $description, 
        $location, 
        $status, 
        $contact_info, 
        $image, 
        $approved, 
        $user_id
    );

    // Send image data using `send_long_data` to handle large binary files
    if ($image !== null) {
        $stmt->send_long_data(6, $image); 
    }

    // Execute the query
    if ($stmt->execute()) {
        $showPopup = true; 
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Lost or Found Pet</title>
    <link rel="stylesheet" href="./assets/css/submit_pet.css">
    <style>
        /* Popup styling */
        .popup {
            display: none; 
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.6);
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }
        .popup-content {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            width: 300px;
        }
        .popup-content h2 {
            margin: 0 0 15px;
        }
        .popup-content button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            cursor: pointer;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <?php include './Cus-NavBar/navBar.php'; ?> 
    <h1>Report a Lost or Found Pet</h1>

    <form action="submit_pet.php" method="POST" enctype="multipart/form-data">
        <label for="pet-name">Pet Name:</label>
        <input type="text" id="pet-name" name="pet_name" required><br><br>

        <label for="pet-type">Pet Type:</label>
        <select id="pet-type" name="pet_type" required>
            <option value="dog">Dog</option>
            <option value="cat">Cat</option>
            <option value="bird">Bird</option>
            <option value="other">Other</option>
        </select><br><br>

        <label for="description">Description:</label>
        <textarea id="description" name="description" required></textarea><br><br>

        <label for="location">Location Last Seen/Found:</label>
        <input type="text" id="location" name="location" required><br><br>

        <label for="status">Status:</label>
        <select id="status" name="status" required>
            <option value="lost">Lost</option>
            <option value="found">Found</option>
        </select><br><br>

        <label for="contact-info">Contact Information (Email/Phone):</label>
        <input type="text" id="contact-info" name="contact_info" required><br><br>

        <label for="pet-image">Upload Pet Image:</label>
        <input type="file" id="pet-image" name="pet_image" accept="image/*" required><br><br>

        <button type="submit">Submit</button>
    </form>

    <!-- Success Popup -->
    <div class="popup" id="successPopup">
        <div class="popup-content">
            <h2>Pet details submitted successfully!</h2>
            <button onclick="closePopup()">OK</button>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script>
        // JavaScript to handle popup
        function closePopup() {
            document.getElementById('successPopup').style.display = 'none';
        }

        // Show popup if form submission was successful
        <?php if ($showPopup): ?>
            document.getElementById('successPopup').style.display = 'flex';
        <?php endif; ?>
    </script>
</body>
</html>
