<?php include('../db.php'); ?>

<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $qualification = $_POST['qualification'];
    $experience = $_POST['experience'];
    $clinic_name = $_POST['clinic_name'];
    $clinic_address = $_POST['clinic_address'];
    $latitude = $_POST['latitude'];
    $longitude = $_POST['longitude'];
    $consultation_fee = $_POST['consultation_fee'];
    $contact_details = $_POST['contact_details'];
    $services = $_POST['services'];

    // Database connection
    $conn = new mysqli('localhost', 'root', '', 'db');

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Insert into the vets table with is_approved = FALSE
    $sql = "INSERT INTO vets (name, qualification, experience, clinic_name, clinic_address, latitude, longitude, consultation_fee, contact_details, services, is_approved)
            VALUES ('$name', '$qualification', '$experience', '$clinic_name', '$clinic_address', '$latitude', '$longitude', '$consultation_fee', '$contact_details', '$services', 0)";

    if ($conn->query($sql) === TRUE) {
        echo "Vet registered successfully! Awaiting approval.";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

    $conn->close();
}
?>
