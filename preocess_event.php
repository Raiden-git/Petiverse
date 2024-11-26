<?php
// Database connection
include('db.php');
// Validate and sanitize inputs
$title = $conn->real_escape_string($_POST['title']);
$date = $conn->real_escape_string($_POST['date']);
$description = $conn->real_escape_string($_POST['description']);

// Image handling
$image_data = null;
if (!empty($_FILES['image']['tmp_name'])) {
    $image_tmp = $_FILES['image']['tmp_name'];
    $image_data = file_get_contents($image_tmp);
    $image_data = $conn->real_escape_string($image_data);
}

// Prepare SQL statement
$sql = "INSERT INTO special_events (title, date, description, image) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);

// Bind parameters
if ($image_data) {
    $stmt->bind_param("ssss", $title, $date, $description, $image_data);
} else {
    $sql = "INSERT INTO special_events (title, date, description) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $title, $date, $description);
}

// Execute and check for success
if ($stmt->execute()) {
    // Redirect with success message
    header("Location: index.php?success=1");
    exit();
} else {
    // Redirect with error message
    header("Location: create_event.php?error=" . urlencode($stmt->error));
    exit();
}

$stmt->close();
$conn->close();
?>