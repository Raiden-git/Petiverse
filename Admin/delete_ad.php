<?php
include('../db.php'); 

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if pet_id is provided and not empty
    if (isset($_POST['pet_id']) && !empty($_POST['pet_id'])) {
        $pet_id = intval($_POST['pet_id']); 

        // Prepare the DELETE query
        $delete_query = "DELETE FROM lost_and_found_pets WHERE id = ?";
        $stmt = $conn->prepare($delete_query);

        if ($stmt) {
            $stmt->bind_param("i", $pet_id); 
            if ($stmt->execute()) {
                $stmt->close();
                header("Location: lost_found_pets.php?status=deleted");
                exit();
            } else {
                echo "Error executing query: " . $stmt->error;
                exit;
            }
        } else {
            echo "Failed to prepare the query: " . $conn->error; 
            exit;
        }
    } else {
        echo "Invalid or missing pet ID!"; 
        exit;
    }
} else {
    echo "Invalid request method!"; 
    exit;
}
?>
