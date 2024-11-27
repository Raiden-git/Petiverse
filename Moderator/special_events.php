<?php
include('../db.php');
include('session_check.php');

// Handle event submission (for new event creation)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['title']) && isset($_POST['description']) && isset($_POST['date'])) {
        $title = $_POST['title'];
        $description = $_POST['description'];
        $date = $_POST['date'];

        // Handle image upload
        $image_data = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
            $image_data = file_get_contents($_FILES['image']['tmp_name']); 
        }

        // Insert into the database
        $insert_query = "INSERT INTO special_events (title, description, date, image) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($insert_query);

        if ($stmt) {
            $stmt->bind_param("sssb", $title, $description, $date, $image_data); 
            $stmt->send_long_data(3, $image_data); 
            if ($stmt->execute()) {
                echo "Event added successfully!";
            } else {
                echo "Error adding event: " . $stmt->error;
            }
        } else {
            echo "Error preparing statement: " . $conn->error;
        }
    }
}

// Handle event approval
if (isset($_GET['approve_id'])) {
    $approve_id = intval($_GET['approve_id']);
    $query = "UPDATE special_events SET approved = 1 WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $approve_id);
    if ($stmt->execute()) {
        echo "<script>alert('Event approved successfully!'); window.location.href='special_events.php';</script>";
    } else {
        echo "<script>alert('Error approving event: " . $stmt->error . "');</script>";
    }
}

// Handle event rejection
if (isset($_GET['reject_id'])) {
    $reject_id = intval($_GET['reject_id']);
    $query = "UPDATE special_events SET approved = 0 WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $reject_id);
    if ($stmt->execute()) {
        echo "<script>alert('Event rejected successfully!'); window.location.href='special_events.php';</script>";
    } else {
        echo "<script>alert('Error rejecting event: " . $stmt->error . "');</script>";
    }
}

// Handle event deletion
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    // Retrieve the image path to delete the file
    $query = "SELECT image FROM special_events WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $event = $result->fetch_assoc();
    if ($event && file_exists($event['image'])) {
        unlink($event['image']); 
    }

    $delete_query = "DELETE FROM special_events WHERE id = ?";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param("i", $delete_id);
    if ($stmt->execute()) {
        echo "<script>alert('Event deleted successfully!'); window.location.href='special_events.php';</script>";
    } else {
        echo "<script>alert('Error deleting event: " . $stmt->error . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Special Event Management</title>
    <link rel="stylesheet" href="./moderator_sidebar.css">
    <style>
/* General body styling */
body {
    font-family: 'Arial', sans-serif;
    margin: 0;
    padding: 0;
    background-color: #f4f6f8;
    color: #333;
}

/* Header styling */
header {
    background-color: #007bff;
    color: white;
    text-align: center;
    padding: 1.5em 0;
    font-size: 1.5rem;
    box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
}


/* Main content styling */
main {
    padding: 2em;
    max-width: 1000px;
    margin: 0 auto;
    background-color: white;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    margin-top: 60px;
    margin-left: 520px;
}

/* Form styling */
form {
    margin-bottom: 60px;
}

form label {
    display: block;
    font-weight: bold;
    margin-bottom: 0.5em;
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
}

form button {
    background-color: #007bff;
    color: white;
    border: none;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

form button:hover {
    background-color: #0056b3;
}

/* Table styling */
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 1em;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

table th,
table td {
    padding: 1em;
    text-align: left;
    border: 1px solid #dee2e6;
}

table th {
    background-color: #f8f9fa;
    font-weight: bold;
}

table td {
    vertical-align: middle;
}

.image-preview {
    max-width: 100px;
    border-radius: 4px;
}

/* Action buttons */
.actions a {
    display: inline-block;
    padding: 0.5em 0.75em;
    margin: 0 0.2em;
    border-radius: 4px;
    text-decoration: none;
    color: white;
    font-size: 0.9rem;
    transition: background-color 0.3s ease;
}

.actions a:hover {
    opacity: 0.8;
}

.actions a:nth-child(1) {
    background-color: #17a2b8;
}

.actions a:nth-child(2) {
    background-color: #dc3545;
}

.actions a:nth-child(3) {
    background-color: #28a745;
}

.actions a:nth-child(4) {
    background-color: #ffc107;
}

/* Responsive design */
@media (max-width: 768px) {
    nav ul {
        flex-direction: column;
        align-items: center;
    }

    form input,
    form textarea,
    form button {
        font-size: 0.9rem;
    }

    table th,
    table td {
        padding: 0.5em;
        font-size: 0.9rem;
    }

    .actions a {
        font-size: 0.8rem;
        padding: 0.3em 0.5em;
    }
}

</style>
  
</head>
<body>
<header>
    <h1>Special Event Management</h1>
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
                <th>Image</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Query to select events (approved or pending) ordered by date
            $query = "SELECT * FROM special_events ORDER BY date DESC";
            $stmt = $conn->prepare($query);
            $stmt->execute();
            $result = $stmt->get_result();

            while ($event = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($event['title']) . "</td>";
                echo "<td>" . htmlspecialchars($event['date']) . "</td>";
                echo "<td>";
                if ($event['image']) {
                    echo "<img src='data:image/jpeg;base64," . base64_encode($event['image']) . "' class='image-preview' alt='Event Image'>";
                } else {
                    echo "No Image";
                }
                echo "</td>";
                echo "<td class='actions'>
                        <a href='edit_special_event.php?id=" . $event['id'] . "'>Edit</a> |
                        <a href='special_events.php?delete_id=" . $event['id'] . "' onclick='return confirm(\"Are you sure?\");'>Delete</a> |
                        <a href='special_events.php?approve_id=" . $event['id'] . "'>Approve</a> |
                        <a href='special_events.php?reject_id=" . $event['id'] . "'>Reject</a>
                      </td>";
                echo "</tr>";
            }
            ?>
        </tbody>
    </table>
</main>
</body>
</html>
