<?php
include '../db.php'; 

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Delete daycare location from the database
    $sql = "DELETE FROM daycare_locations WHERE id = $id";
    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('Daycare location deleted successfully'); window.location.href='admin_daycare_management.php';</script>";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>
