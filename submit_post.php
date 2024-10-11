<?php
session_start();
include 'db.php';

// Remove the login check; the code will proceed regardless of user session status

// Get user input
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null; // Optionally, handle user_id as null if session isn't set
$post_title = $_POST['post_title'];
$post_content = $_POST['post_content'];
$category = $_POST['category'];

// Validate inputs
if (empty($post_title) || empty($post_content) || empty($category)) {
    echo "Please fill in all fields.";
    exit();
}

// Handle image upload
$image = null; // Default value for image

if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
    $target_dir = "C:\xampp\htdocs\Petiverse1\Petiverse\uploads"; // Directory where files will be uploaded
    $target_file = $target_dir . basename($_FILES["image"]["name"]);
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Check if image file is a real image or fake image
    $check = getimagesize($_FILES["image"]["tmp_name"]);
    if ($check !== false) {
        // Move the uploaded file to the "uploads" directory
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $image = $target_file; // Store the file path in the $image variable
        } else {
            echo "Sorry, there was an error uploading your file.";
            exit();
        }
    } else {
        echo "File is not an image.";
        exit();
    }
}

// Insert new post into the database, handle case when user_id is not set
$sql = "INSERT INTO posts (user_id, post_title, post_content, category, image) VALUES (?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);

if ($stmt) {
    $stmt->bind_param("issss", $user_id, $post_title, $post_content, $category, $image);
    
    if ($stmt->execute()) {
        header("Location: community.php"); // Redirect to community after successful post
        exit();
    } else {
        echo "Error: " . $conn->error;
    }

    $stmt->close();
} else {
    echo "Error preparing statement: " . $conn->error;
}

$conn->close();
?>
