<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if not logged in
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Database connection
$conn = new mysqli('localhost', 'root', '', 'petiverse');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error_message = '';

// Handle form submission to save pet details
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $pet_name = $conn->real_escape_string($_POST['petName']);
    $birthday = $conn->real_escape_string($_POST['birthday']);
    $gender = $conn->real_escape_string($_POST['gender']);
    $breed = $conn->real_escape_string($_POST['breed']);
    $weight = $_POST['weight'];
    $height = $_POST['height'];
    $bmi = $_POST['bmi']; // Get BMI value from the form submission

    if (empty($pet_name) || empty($birthday) || empty($gender) || empty($breed) || empty($weight) || empty($height)) {
        $error_message = "All fields are required.";
    } else {
        // Insert pet details and BMI into the health_tracker table
        $stmt = $conn->prepare("INSERT INTO health_tracker (user_id, pet_name, birthday, gender, breed, weight, height, bmi) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

        $stmt->bind_param("issssddi", $user_id, $pet_name, $birthday, $gender, $breed, $weight, $height, $bmi);

        if (!$stmt->execute()) {
            $error_message = "Error adding pet details.";
        }
        $stmt->close();
    }
}

$conn->close();
?>