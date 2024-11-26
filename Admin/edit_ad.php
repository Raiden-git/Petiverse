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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2ecc71;
            --background-color: #f0f4f8;
            --text-color: #2c3e50;
            --card-background: #ffffff;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--background-color);
            color: var(--text-color);
            line-height: 1.6;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            background-color: var(--card-background);
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            padding: 40px;
            width: 100%;
            max-width: 600px;
            animation: fadeIn 0.5s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .header h1 {
            color: var(--primary-color);
            font-size: 2.5rem;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
        }

        .header h1 i {
            color: var(--secondary-color);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--text-color);
            font-weight: 600;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
        }

        textarea.form-control {
            resize: vertical;
            min-height: 150px;
        }

        .btn {
            width: 100%;
            padding: 15px;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn:hover {
            background-color: #2980b9;
            transform: translateY(-3px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .btn:active {
            transform: translateY(0);
        }

        .navigation {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .navigation a {
            color: var(--primary-color);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: color 0.3s ease;
        }

        .navigation a:hover {
            color: var(--secondary-color);
        }

        @media (max-width: 600px) {
            .container {
                padding: 20px;
            }

            .header h1 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="navigation">
            <a href="dashboard.php">
                <i class="fas fa-home"></i> Home
            </a>
            <a href="lost_found_pets.php">
                <i class="fas fa-arrow-left"></i> Back to lost and found pets
            </a>
        </div>

        <div class="header">
            <h1>
                <i class="fas fa-edit"></i>
                Edit Pet Ad
            </h1>
        </div>

        <form method="POST">
            <div class="form-group">
                <label for="pet_name">Pet Name</label>
                <input type="text" id="pet_name" name="pet_name" class="form-control" value="<?= htmlspecialchars($pet['pet_name']) ?>" required>
            </div>

            <div class="form-group">
                <label for="pet_type">Pet Type</label>
                <input type="text" id="pet_type" name="pet_type" class="form-control" value="<?= htmlspecialchars($pet['pet_type']) ?>" required>
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" class="form-control" required><?= htmlspecialchars($pet['description']) ?></textarea>
            </div>

            <div class="form-group">
                <label for="location">Location</label>
                <input type="text" id="location" name="location" class="form-control" value="<?= htmlspecialchars($pet['location']) ?>" required>
            </div>

            <button type="submit" class="btn">
                <i class="fas fa-save"></i>
                Save Changes
            </button>
        </form>
    </div>
</body>
</html>
