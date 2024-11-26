<?php
// Database connection
include 'db.php';

// Fetch all daycare locations
$sql = "SELECT * FROM daycare_locations";
$result = mysqli_query($conn, $sql);

$daycareLocations = [];

if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $daycareLocations[] = $row;
    }
}

echo json_encode($daycareLocations);
?>
