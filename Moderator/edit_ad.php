<?php
include('../db.php'); // Include the database connection

// Check if pet_id is provided in the URL
if (isset($_GET['pet_id']) && is_numeric($_GET['pet_id'])) {
    $pet_id = intval($_GET['pet_id']); // Convert to an integer for security

    // Fetch the pet details from the database
    $query = "SELECT * FROM lost_and_found_pets WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $pet_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $pet = $result->fetch_assoc();

    if (!$pet) {
        echo "<p>Pet not found! Please check the ID.</p>";
        exit;
    }

    // Handle form submission for editing the ad
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Get the updated data from the form
        $pet_name = trim($_POST['pet_name']);
        $pet_type = trim($_POST['pet_type']);
        $description = trim($_POST['description']);
        $location = trim($_POST['location']);

        // Validate input fields
        if (empty($pet_name) || empty($pet_type) || empty($description) || empty($location)) {
            echo "<p>All fields are required. Please fill them in and try again.</p>";
        } else {
            // Prepare the update query
            $update_query = "UPDATE lost_and_found_pets SET pet_name = ?, pet_type = ?, description = ?, location = ? WHERE id = ?";
            $stmt = $conn->prepare($update_query);
            $stmt->bind_param("ssssi", $pet_name, $pet_type, $description, $location, $pet_id);
            $stmt->execute();

            // Redirect back to the pet management page after update
            header("Location: lost_found_pets.php");
            exit();
        }
    }
} else {
    echo "<p>Invalid request! Missing or invalid pet ID.</p>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Pet Ad</title>
    <link rel="stylesheet" href="../assets/css/admin.css"> <!-- Adjusted path for the CSS file -->

    <style>
        /* General Reset */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: Arial, sans-serif;
}

/* Body Styling */
body {
    margin: 20px;
    background-color: #f4f4f4;
    color: #333;
    line-height: 1.6;
}

/* Header Styling */
header {
    text-align: center;
    margin-bottom: 20px;
}

header h1 {
    color: #333;
    font-size: 2rem;
    font-weight: 600;
}

/* Main Form Container */
main {
    max-width: 800px;
    margin: 0 auto;
    padding: 20px;
    background-color: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

/* Form Styling */
form {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

/* Label Styling */
label {
    font-weight: bold;
    color: #555;
}

/* Input Fields */
input[type="text"], textarea {
    padding: 12px;
    font-size: 1rem;
    border: 1px solid #ccc;
    border-radius: 5px;
    background-color: #f9f9f9;
    transition: border-color 0.3s ease;
}

input[type="text"]:focus, textarea:focus {
    border-color: #007bff;
    outline: none;
}

/* Textarea Styling */
textarea {
    resize: vertical;
    min-height: 100px;
}

/* Button Styling */
button {
    padding: 12px 20px;
    background-color: #007bff;
    color: white;
    border: none;
    border-radius: 5px;
    font-size: 1rem;
    font-weight: bold;
    cursor: pointer;
    transition: background-color 0.3s ease, transform 0.2s ease;
    text-align: center;
}

button:hover {
    background-color: #0056b3;
    transform: scale(1.05);
}

button:active {
    background-color: #004085;
}

/* Optional: Add a subtle shadow to the form inputs and button for a clean look */
input[type="text"], textarea, button {
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
}

/* Responsive Design */
@media (max-width: 768px) {
    body {
        margin: 10px;
    }

    main {
        padding: 15px;
    }

    h1 {
        font-size: 1.8rem;
    }

    button {
        padding: 10px 15px;
    }

    input[type="text"], textarea {
        font-size: 0.9rem;
    }
}

        </style>
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
