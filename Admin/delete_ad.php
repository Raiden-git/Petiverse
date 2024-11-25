<?php
include('../db.php'); // Include the database connection

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if pet_id is provided and not empty
    if (isset($_POST['pet_id']) && !empty($_POST['pet_id'])) {
        $pet_id = intval($_POST['pet_id']); // Convert pet_id to an integer

        // Prepare the DELETE query
        $delete_query = "DELETE FROM lost_and_found_pets WHERE id = ?";
        $stmt = $conn->prepare($delete_query);

        if ($stmt) {
            $stmt->bind_param("i", $pet_id); // Bind the pet_id parameter
            if ($stmt->execute()) {
                $stmt->close();
                header("Location: lost_found_pets.php?status=deleted"); // Redirect after successful deletion
                exit();
            } else {
                echo "Error executing query: " . $stmt->error; // Show error if query execution fails
                exit;
            }
        } else {
            echo "Failed to prepare the query: " . $conn->error; // Show error if query preparation fails
            exit;
        }
    } else {
        echo "Invalid or missing pet ID!"; // Show error if pet_id is invalid or missing
        exit;
    }
} else {
    echo "Invalid request method!"; // Show error for invalid request method
    exit;
}
?>
