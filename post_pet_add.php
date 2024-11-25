<?php
session_start();
include './db.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $pet_name = $conn->real_escape_string($_POST['pet_name']);
    $pet_type = $conn->real_escape_string($_POST['pet_type']);
    $pet_breed = $conn->real_escape_string($_POST['pet_breed']);
    $age = (int)$_POST['age'];
    $price = (float)$_POST['price'];
    $description = $conn->real_escape_string($_POST['description']);
    $contact_number = $conn->real_escape_string($_POST['contact_number']);

    if (isset($_FILES['image1']) && isset($_FILES['image2'])) {
        if ($_FILES['image1']['error'] === UPLOAD_ERR_OK && $_FILES['image2']['error'] === UPLOAD_ERR_OK) {
            $image1 = file_get_contents($_FILES['image1']['tmp_name']);
            $image2 = file_get_contents($_FILES['image2']['tmp_name']);
            $image1_size = $_FILES['image1']['size'];
            $image2_size = $_FILES['image2']['size'];

            $stmt = $conn->prepare(
                "INSERT INTO pet_selling_ads 
                (user_id, pet_name, pet_type, pet_breed, age, price, description, contact_number, image1, image1_size, image2, image2_size, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')"
            );

            if ($stmt) {
                $stmt->bind_param(
                    "issssdsssisi",
                    $user_id,
                    $pet_name,
                    $pet_type,
                    $pet_breed,
                    $age,
                    $price,
                    $description,
                    $contact_number,
                    $image1,
                    $image1_size,
                    $image2,
                    $image2_size
                );

                if ($stmt->execute()) {
                    $message = "Ad posted successfully! It is now under review.";
                } else {
                    $message = "Error while posting the ad: " . $stmt->error;
                }

                $stmt->close();
            } else {
                $message = "Error preparing the statement: " . $conn->error;
            }
        } else {
            $message = "Error with file uploads. Please try again.";
        }
    } else {
        $message = "Both images are required.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post Pet Ad</title>
    <link rel="stylesheet" href="assets/css/post_pet_add.css">
</head>
<body>
<?php include 'Cus-NavBar/navBar.php'; ?>
    <header>
        <h1>Post a Pet Ad</h1>
    </header>

    <main>
        <div class="form-container">
            <?php if (!empty($message)): ?>
                <div class="message"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>

            <form action="post_pet_add.php" method="POST" enctype="multipart/form-data">
                <label for="pet_name">Pet Name</label>
                <input type="text" id="pet_name" name="pet_name" required>

                <label for="pet_type">Pet Type</label>
                <input type="text" id="pet_type" name="pet_type" required>

                <label for="pet_breed">Pet Breed</label>
                <input type="text" id="pet_breed" name="pet_breed" required>

                <label for="age">Age</label>
                <input type="number" id="age" name="age" min="0" required>

                <label for="price">Price</label>
                <input type="number" id="price" name="price" step="0.01" min="0" required>

                <label for="contact_number">Contact Number</label>
                <input type="text" id="contact_number" name="contact_number" required>

                <label for="description">Description</label>
                <textarea id="description" name="description" rows="5" required></textarea>

                <label for="image1">Image 1</label>
                <input type="file" id="image1" name="image1" accept="image/*" required>

                <label for="image2">Image 2</label>
                <input type="file" id="image2" name="image2" accept="image/*" required>

                <button type="submit">Post Ad</button>
            </form>
        </div>
    </main>
</body>
</html>
