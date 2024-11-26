<?php
session_start();
include('db.php'); // Ensure db.php includes your database connection details

include ('Cus-NavBar/navBar.php');

// Check if the user is logged in by verifying the session
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page or show an error
    header("Location: login.php"); // Adjust this to your login page
    exit(); // Stop further script execution
}

$user_id = $_SESSION['user_id']; // Assuming user_id is stored in session after login

// Fetch user details to check premium status
$sql = "SELECT is_premium FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

$is_premium = $user['is_premium'];

// Fetch the number of pets the user has added
$sql = "SELECT COUNT(*) as pet_count FROM pets WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$pet_count = $result->fetch_assoc()['pet_count'];

// Initialize variables for form editing
$editing_pet = false;
$pet_id = '';
$pet_name = '';
$pet_type = '';
$note = '';

// Handle editing a pet (when clicking "Edit")
if (isset($_GET['edit_id'])) {
    $pet_id = $_GET['edit_id'];

    // Fetch the pet details for editing
    $sql = "SELECT * FROM pets WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $pet_id, $user_id);
    $stmt->execute();
    $pet = $stmt->get_result()->fetch_assoc();

    if ($pet) {
        $pet_name = $pet['pet_name'];
        $pet_type = $pet['pet_type'];
        $note = $pet['note'];
        $editing_pet = true;
    } else {
        $error_message = "Pet not found.";
    }
}

// Handle form submission for adding or editing a pet
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pet_name = $_POST['pet_name'];
    $pet_type = $_POST['pet_type'];
    $note = $_POST['note'];

    if (isset($_POST['pet_id']) && $_POST['pet_id'] != '') {
        // Update existing pet
        $pet_id = $_POST['pet_id'];
        $sql = "UPDATE pets SET pet_name = ?, pet_type = ?, note = ? WHERE id = ? AND user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssii", $pet_name, $pet_type, $note, $pet_id, $user_id);

        if ($stmt->execute()) {
            $success_message = "Pet updated successfully!";
        } else {
            $error_message = "Error updating pet.";
        }
    } else {
        // Add new pet (only if non-premium user doesn't exceed 3 pets)
        if (!$is_premium && $pet_count >= 3) {
            $error_message = "You can only add up to 3 pets. Please upgrade to premium for unlimited pets.";
        } else {
            // Insert new pet
            $sql = "INSERT INTO pets (user_id, pet_name, pet_type, note) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("isss", $user_id, $pet_name, $pet_type, $note);

            if ($stmt->execute()) {
                $success_message = "Pet added successfully!";
            } else {
                $error_message = "Error adding pet.";
            }
        }
    }
}

// Handle deleting a pet
if (isset($_GET['delete_id'])) {
    $pet_id = $_GET['delete_id'];

    $sql = "DELETE FROM pets WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $pet_id, $user_id);

    if ($stmt->execute()) {
        $success_message = "Pet deleted successfully!";
    } else {
        $error_message = "Error deleting pet.";
    }
}

// Fetch pets of the logged-in user
$sql = "SELECT * FROM pets WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$pets = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Pets</title>
    <style>

        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

        form {
            margin: 20px;
        }
        label {
            display: block;
            margin: 10px 0 5px;
        }
        input, textarea {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
        }
        button {
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }
        button:hover {
            background-color: #45a049;
        }
        .pet-list {
            margin: 15px 0;
            padding: 10px;
            border: 1px solid #ccc;
        }
        .message {
            color: green;
            margin-bottom: 10px;
        }
        .error {
            color: red;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>

<h1>My Pets</h1>

<!-- Display success or error messages -->
<?php if (isset($success_message)): ?>
    <p class="message"><?= htmlspecialchars($success_message); ?></p>
<?php endif; ?>
<?php if (isset($error_message)): ?>
    <p class="error"><?= htmlspecialchars($error_message); ?></p>
<?php endif; ?>

<!-- Add/Edit Pet Form -->
<form action="my-pets.php" method="POST">
    <input type="hidden" name="pet_id" value="<?= htmlspecialchars($pet_id); ?>">

    <label for="pet_name">Pet Name:</label>
    <input type="text" id="pet_name" name="pet_name" value="<?= htmlspecialchars($pet_name); ?>" required>

    <label for="pet_type">Pet Type:</label>
    <input type="text" id="pet_type" name="pet_type" value="<?= htmlspecialchars($pet_type); ?>" required>

    <label for="note">Note:</label>
    <textarea id="note" name="note"><?= htmlspecialchars($note); ?></textarea>

    <button type="submit"><?= $editing_pet ? 'Update Pet' : 'Add Pet'; ?></button>
</form>

<!-- Display user's pets -->
<h2>Your Pets</h2>
<?php while ($pet = $pets->fetch_assoc()): ?>
    <div class="pet-list">
        <h3><?= htmlspecialchars($pet['pet_name']); ?> (<?= htmlspecialchars($pet['pet_type']); ?>)</h3>
        <p>Note: <?= htmlspecialchars($pet['note']); ?></p>
        <a href="my-pets.php?edit_id=<?= $pet['id']; ?>">Edit</a> | 
        <a href="my-pets.php?delete_id=<?= $pet['id']; ?>" onclick="return confirm('Are you sure you want to delete this pet?')">Delete</a>
    </div>
<?php endwhile; ?>

</body>
</html>
