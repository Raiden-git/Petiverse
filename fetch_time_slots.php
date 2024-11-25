<?php
require 'db.php';

if (isset($_GET['vet_id']) && isset($_GET['date'])) {
    $vet_id = intval($_GET['vet_id']);
    $date = $_GET['date'];

    // Fetch the vet's available slots for the selected date
    $query = "SELECT start_time, end_time FROM vet_availability 
              WHERE vet_id = ? AND day_of_week = DAYOFWEEK(?) - 1 AND is_available = 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('is', $vet_id, $date);
    $stmt->execute();
    $result = $stmt->get_result();

    $slots = [];
    while ($row = $result->fetch_assoc()) {
        $slots[] = $row;
    }

    echo json_encode(['slots' => $slots]);
}
?>
