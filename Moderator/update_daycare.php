<?php
include '../db.php';  // Include the database connection

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $address = $_POST['address'];
    $latitude = $_POST['latitude'];
    $longitude = $_POST['longitude'];

    // Update daycare details in the database
    $sql = "UPDATE daycare_locations SET name='$name', address='$address', latitude='$latitude', longitude='$longitude' WHERE id=$id";
    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('Daycare location updated successfully'); window.location.href='admin_daycare_management.php';</script>";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>
