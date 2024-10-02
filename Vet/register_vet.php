<?php
include '../db.php'; // Your database connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
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

    $sql = "INSERT INTO vets (name, qualification, experience, clinic_name, clinic_address, latitude, longitude, consultation_fee, contact_details, services) 
            VALUES ('$name', '$qualification', '$experience', '$clinic_name', '$clinic_address', '$latitude', '$longitude', '$consultation_fee', '$contact_details', '$services')";

    if (mysqli_query($conn, $sql)) {
        echo "Vet registered successfully!";
    } else {
        echo "Error: " . $sql . "<br>" . mysqli_error($conn);
    }

    mysqli_close($conn);
}
?>
