<?php include('db.php'); ?>

<?php
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $lat = $_GET['lat'];
    $lng = $_GET['lng'];
    $radius = 10; // 10 km search radius

    // Database connection
    $conn = new mysqli('localhost', 'root', '', 'db');

    $sql = "SELECT id, name, clinic_name, latitude, longitude, 
            (6371 * acos(cos(radians($lat)) * cos(radians(latitude)) * cos(radians(longitude) - radians($lng)) + sin(radians($lat)) * sin(radians(latitude)))) AS distance 
            FROM vets 
            WHERE is_approved = 1
            HAVING distance < $radius 
            ORDER BY distance";

    $result = $conn->query($sql);

    $vets = [];
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $vets[] = $row;
        }
    }
    echo json_encode($vets);

    $conn->close();
}
?>
