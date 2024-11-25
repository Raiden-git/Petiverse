<?php
include('../db.php');
include('session_check.php');

// Fetch event details if an ID is provided
if (isset($_GET['id'])) {
    $event_id = intval($_GET['id']);
    $query = "SELECT * FROM special_events WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $event_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $event = $result->fetch_assoc();

    if (!$event) {
        echo "<script>alert('Event not found!'); window.location='special_events.php';</script>";
        exit;
    }
}

// Handle form submission for updating the event
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['title']) && isset($_POST['description']) && isset($_POST['date'])) {
        $title = $_POST['title'];
        $description = $_POST['description'];
        $date = $_POST['date'];

        if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
            $image_data = file_get_contents($_FILES['image']['tmp_name']);
        } else {
            $image_data = $event['image']; // Retain old image if none is uploaded
        }

        $update_query = "UPDATE special_events SET title = ?, description = ?, date = ?, image = ? WHERE id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("sssbi", $title, $description, $date, $image_data, $event_id);

        if ($stmt->execute()) {
            echo "<script>alert('Event updated successfully!'); window.location='special_events.php';</script>";
        } else {
            echo "<script>alert('Error updating event: " . $stmt->error . "');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Special Event</title>
</head>
<body>
<header>
    <h1>Edit Special Event</h1>
</header>

<main>
    <form method="POST" enctype="multipart/form-data">
        <label for="title">Event Title:</label>
        <input type="text" id="title" name="title" value="<?= htmlspecialchars($event['title']) ?>" required><br><br>

        <label for="description">Event Description:</label>
        <textarea id="description" name="description" required><?= htmlspecialchars($event['description']) ?></textarea><br><br>

        <label for="date">Event Date:</label>
        <input type="date" id="date" name="date" value="<?= htmlspecialchars($event['date']) ?>" required><br><br>

        <label for="image">Event Image:</label>
        <input type="file" id="image" name="image"><br><br>

        <button type="submit">Update Event</button>
    </form>
</main>
</body>
</html>
