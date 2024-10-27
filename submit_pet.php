<?php
// Database connection
$host = "localhost";
$dbname = "petiverse"; // Your existing database name
$username = "root";
$password = "";

// Create connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// If the form is submitted, insert data into the database
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $pet_name = $_POST['pet_name'];
    $pet_type = $_POST['pet_type'];
    $description = $_POST['description'];
    $location = $_POST['location'];
    $status = $_POST['status'];
    $contact_info = $_POST['contact_info'];

    // Insert data into the lost_and_found_pets table
    $sql = "INSERT INTO lost_and_found_pets (pet_name, pet_type, description, location, status, contact_info) 
            VALUES ('$pet_name', '$pet_type', '$description', '$location', '$status', '$contact_info')";

    if ($conn->query($sql) === TRUE) {
        echo "Pet details submitted successfully!";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

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

</head>
<body>
    
<?php include './Cus-NavBar/navBar.php'; ?> <!-- Corrected path to include navigation bar -->
    <h1>Report a Lost or Found Pet</h1>
    
    <form action="submit_pet.php" method="POST">
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

        <button type="submit">Submit</button>
    </form>
</body>
</html>
