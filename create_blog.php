<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    $user_name = $_SESSION['username'];
    $title = $_POST['title'];
    $content = $_POST['content'];
    $category = $_POST['category'];
    $photoData = null;

    // Check if a file is uploaded and handle the photo data
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $photoData = file_get_contents($_FILES['photo']['tmp_name']);
    }

    // Prepare the SQL statement
    $stmt = $conn->prepare("INSERT INTO user_blogs (user_id, user_name, title, content, category, photo) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssb", $user_id, $user_name, $title, $content, $category, $null);

    // Send the photo data as long data
    $stmt->send_long_data(5, $photoData);

    if ($stmt->execute()) {
        echo "<p>Blog post created successfully!</p>";
        header("Location: index.php");
        exit();
    } else {
        echo "<p>Error: " . $stmt->error . "</p>";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Blog Post - Petiverse</title>
</head>
<body>
    <h2>Create a New Blog Post</h2>
    <form action="create_blog.php" method="POST" enctype="multipart/form-data">
        <label>Title:</label>
        <input type="text" name="title" required>

        <label>Content:</label>
        <textarea name="content" required></textarea>

        <label>Category:</label>
        <select name="category" required>
            <option value="Health">Health</option>
            <option value="Entertainment">Entertainment</option>
            <option value="Events">Events</option>
            <option value="Wisdom">Wisdom</option>
            <option value="Experience">Experience</option>
        </select>

        <label>Photo:</label>
        <input type="file" name="photo" required>

        <input type="submit" value="Create Blog Post">
    </form>
</body>
</html>
