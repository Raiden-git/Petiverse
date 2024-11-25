<?php
include('../db.php'); // Include the database connection

// Check if pet_id is provided in the URL
if (isset($_GET['pet_id'])) {
    $pet_id = $_GET['pet_id'];

    // Fetch the pet details from the database
    $query = "SELECT * FROM lost_and_found_pets WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $pet_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $pet = $result->fetch_assoc();

    if (!$pet) {
        echo "Pet not found!";
        exit;
    }

    // Handle form submission for editing the ad
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Get the updated data from the form
        $pet_name = $_POST['pet_name'];
        $pet_type = $_POST['pet_type'];
        $description = $_POST['description'];
        $location = $_POST['location'];

        // Prepare the update query
        $update_query = "UPDATE lost_and_found_pets SET pet_name = ?, pet_type = ?, description = ?, location = ? WHERE id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("ssssi", $pet_name, $pet_type, $description, $location, $pet_id);
        $stmt->execute();
        
        // Redirect back to the pet management page after update
        header("Location: lost_found_pets.php");
        exit();
    }
} else {
    echo "Invalid request!";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Pet Ad</title>
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body>
    <header>
        <h1>Edit Pet Ad</h1>
    </header>

    <main>
        <form method="POST">
            <label for="pet_name">Pet Name:</label>
            <input type="text" id="pet_name" name="pet_name" value="<?= htmlspecialchars($pet['pet_name']) ?>" required>

            <label for="pet_type">Pet Type:</label>
            <input type="text" id="pet_type" name="pet_type" value="<?= htmlspecialchars($pet['pet_type']) ?>" required>

            <label for="description">Description:</label>
            <textarea id="description" name="description" required><?= htmlspecialchars($pet['description']) ?></textarea>

            <label for="location">Location:</label>
            <input type="text" id="location" name="location" value="<?= htmlspecialchars($pet['location']) ?>" required>

            <button type="submit" class="btn">Save Changes</button>
        </form>
    </main>
</body>
</html>
