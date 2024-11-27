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
    <link rel="stylesheet" href="./moderator_sidebar.css">
</head>
<body>
<header>
    <h1>Edit Special Event</h1>
   
    <style>
        /* Main content styling */
main {
    padding: 2em;
    max-width: 600px;
    margin: 2em auto;
    background-color: #ffffff;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    margin-left: 640px;
}

/* Form styling */
form {
    margin-bottom: 1.5em;
    width: 500px;
    
}

form label {
    display: block;
    font-weight: bold;
    margin-bottom: 0.5em;
    color: #333;
}

form input,
form textarea,
form button {
    width: 100%;
    padding: 0.75em;
    margin-bottom: 1em;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    font-size: 1rem;
    box-sizing: border-box;
}

form textarea {
    resize: vertical;
    min-height: 100px;
}

form input[type="file"] {
    padding: 0.4em;
}

form button {
    background-color: #007bff;
    color: white;
    border: none;
    font-size: 1rem;
    cursor: pointer;
    transition: background-color 0.3s ease;
    padding: 0.8em;
}

form button:hover {
    background-color: #0056b3;
}

/* Input focus and hover effects */
form input:focus,
form textarea:focus,
form button:focus {
    outline: none;
    border-color: #007bff;
    box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
}

/* Responsive design */
@media (max-width: 768px) {
    main {
        padding: 1.5em;
    }

    form input,
    form textarea,
    form button {
        font-size: 0.9rem;
    }
}

    </style>
</header>
<nav>
    <ul>
    <li><a href="moderator_dashboard.php">Home</a></li>
        <li><a href="Moderator_shop_management.php">Shop Management</a></li>
        <li><a href="community_controls.php">Community Controls</a></li>
        <li><a href="blog_management.php">Blog Management</a></li>
        <li><a href="admin_daycare_management.php">Daycare Management</a></li>
        <li><a href="lost_found_pets.php">Lost & Found Pets</a></li>
        <li><a href="special_events.php">Special Events</a></li>
        <li><a href="vet_management.php">Vet Management</a></li>
        <li><a href="petselling.php">Pet selling</a><li>
        <li><a href="view_feedback.php">Feedbacks</a></li>
        <li><a href="logout.php" onclick="return confirmLogout();">Logout</a></li>
    </ul>
</nav>



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
