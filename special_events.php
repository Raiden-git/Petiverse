<?php
include('db.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Special Events</title>
    <link rel="stylesheet" href="assets/css/styles.css"> <!-- Your CSS file -->
</head>
<body>

    <header>
        <h1>Special Events</h1>
    </header>

    <main>
        <div class="event-cards">
        <?php
        // Fetch all special events ordered by date
        $query = "SELECT * FROM special_events WHERE approved = 1 ORDER BY date DESC";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($event = $result->fetch_assoc()) {
            echo "<div class='event-card'>";
            
            if (!empty($event['image'])) {
                // Properly encode binary image data as Base64
                $image_data = base64_encode($event['image']);
                $image_src = "data:image/jpeg;base64,{$image_data}";
                echo "<img src='{$image_src}' alt='Event Image' class='event-image'>";
            } else {
                echo "<img src='placeholder.jpg' alt='No Image Available' class='event-image'>";
            }

            echo "<h3>" . htmlspecialchars($event['title']) . "</h3>";
            echo "<p>" . htmlspecialchars($event['description']) . "</p>";
            echo "<p><strong>Date:</strong> " . htmlspecialchars($event['date']) . "</p>";
            echo "</div>";
        }

        $stmt->close();
        ?>
        </div>

        <!-- Button to allow users to add a new event -->
        <a href="add_event.php" class="btn">Publish New Event</a>
    </main>

</body>
</html>
