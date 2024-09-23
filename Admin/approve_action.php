<?php
if (isset($_GET['id']) && isset($_GET['action'])) {
    $vet_id = $_GET['id'];
    $action = $_GET['action'];

    // Database connection
    $conn = new mysqli('localhost', 'root', '', 'db');

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    if ($action == 'approve') {
        $sql = "UPDATE vets SET is_approved = 1 WHERE id = $vet_id";
        if ($conn->query($sql) === TRUE) {
            echo "Vet approved successfully!";
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    } elseif ($action == 'reject') {
        $sql = "DELETE FROM vets WHERE id = $vet_id";
        if ($conn->query($sql) === TRUE) {
            echo "Vet rejected and removed from the system.";
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    }

    $conn->close();
} else {
    echo "Invalid request.";
}
?>
