<?php
include('db.php');

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $date = trim($_POST['date']);
    $image = null;

    // Check if an image was uploaded
    if ($_FILES['image']['error'] == UPLOAD_ERR_OK) {
        $image = file_get_contents($_FILES['image']['tmp_name']);
    }

    // Validate inputs
    if (empty($title) || empty($description) || empty($date)) {
        $error_message = "All fields are required.";
    } else {
        // Insert the event into the database (pending approval)
        $query = "INSERT INTO special_events (title, description, date, image, approved) VALUES (?, ?, ?, ?, 0)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssss", $title, $description, $date, $image);
        $stmt->execute();

        // Redirect to the special events page
        header("Location: special_events.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Publish New Event</title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>

    <header>
        <h1>Publish New Event</h1>
    </header>

    <main>
        <form action="add_event.php" method="POST" enctype="multipart/form-data">
            <label for="title">Event Title:</label>
            <input type="text" name="title" id="title" required>

            <label for="description">Event Description:</label>
            <textarea name="description" id="description" required></textarea>

            <label for="date">Event Date:</label>
            <input type="date" name="date" id="date" required>

            <label for="image">Event Image (Optional):</label>
            <input type="file" name="image" id="image" accept="image/*">

            <?php if (isset($error_message)) { echo "<p>$error_message</p>"; } ?>

            <button type="submit" class="btn">Submit Event for Review</button>
        </form>
    </main>

</body>
</html>
