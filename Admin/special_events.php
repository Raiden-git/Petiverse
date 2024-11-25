<?php
include('../db.php');
include('session_check.php');

// Handle the event submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['title']) && isset($_POST['description']) && isset($_POST['date'])) {
        $title = $_POST['title'];
        $description = $_POST['description'];
        $date = $_POST['date'];
        
        // Handling the image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
            $image_data = file_get_contents($_FILES['image']['tmp_name']);
        } else {
            $image_data = null;
        }

        // Insert event into the database
        $insert_query = "INSERT INTO special_events (title, description, date, image) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($insert_query);

        if ($stmt) {
            $stmt->bind_param("sssb", $title, $description, $date, $image_data);
            if ($stmt->execute()) {
                echo "<script>alert('Event added successfully!');</script>";
            } else {
                echo "<script>alert('Error adding event: " . $stmt->error . "');</script>";
            }
        }
    }
}

// Handle event deletion
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $delete_query = "DELETE FROM special_events WHERE id = ?";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param("i", $delete_id);
    if ($stmt->execute()) {
        echo "<script>alert('Event deleted successfully!');</script>";
    } else {
        echo "<script>alert('Error deleting event: " . $stmt->error . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Special Event Management</title>
    <link rel="stylesheet" href="admin_sidebar.css">
    <style>
        body {
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 0;
    background-color: #f8f9fa;
}

header {
    background-color: #343a40;
    color: white;
    padding: 1em;
    text-align: center;
}

main {
    padding: 2em;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 1em;
}

table, th, td {
    border: 1px solid #dee2e6;
}

th, td {
    padding: 0.75em;
    text-align: left;
}
</style>
</head>
<body>
<header>
    <h1>Special Event Management</h1>
</header>

<nav>
    <ul>
        <li><a href="dashboard.php">Home</a></li>
        <li><a href="user_management.php">User Management</a></li>
        <li><a href="shop_management.php">Shop Management</a></li>
        <li><a href="community_controls.php">Community Controls</a></li>
        <li><a href="blog_management.php">Blog Management</a></li>
        <li><a href="lost_found_pets.php">Lost & Found Pets</a></li>
        <li><a href="special_events.php">Special Events</a></li>
        <li><a href="vet_management.php">Vet Management</a></li>
        <li><a href="moderator_management.php">Moderator Management</a></li>
        <li><a href="petselling.php">Pet selling</a><li>
        <li><a href="view_feedback.php">Feedbacks</a></li>
        <li><a href="logout.php" onclick="return confirmLogout();">Logout</a></li>
    </ul>
</nav>

<main>
<<<<<<< HEAD
    <h2>Add Special Event</h2>
    <form method="POST" enctype="multipart/form-data">
        <label for="title">Event Title:</label>
        <input type="text" id="title" name="title" required><br><br>

        <label for="description">Event Description:</label>
        <textarea id="description" name="description" required></textarea><br><br>

        <label for="date">Event Date:</label>
        <input type="date" id="date" name="date" required><br><br>

        <label for="image">Event Image:</label>
        <input type="file" id="image" name="image"><br><br>

        <button type="submit">Add Event</button>
    </form>

    <h2>Manage Events</h2>
    <table>
        <thead>
            <tr>
                <th>Title</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $query = "SELECT * FROM special_events ORDER BY date DESC";
            $stmt = $conn->prepare($query);
            $stmt->execute();
            $result = $stmt->get_result();

            while ($event = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($event['title']) . "</td>";
                echo "<td>" . htmlspecialchars($event['date']) . "</td>";
                echo "<td>
                        <a href='edit_special_event.php?id=" . $event['id'] . "'>Edit</a> | 
                        <a href='special_events.php?delete_id=" . $event['id'] . "' onclick='return confirm(\"Are you sure?\")'>Delete</a>
                      </td>";
                echo "</tr>";
            }
            ?>
        </tbody>
    </table>
=======
    <h2>Special Event Management</h2>
   
>>>>>>> 8e174373cfe696749201fd38eb04ef54c15e6dfb
</main>
</body>
</html>
