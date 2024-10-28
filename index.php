<?php
session_start(); // Start the session to check login status
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Petiverse - Let's care your Furball</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/scrollbar.css">
    <link rel="stylesheet" href="assets/css/popup.css"> <!-- CSS for the popup -->
</head>
<body>
   
    <?php include 'Cus-NavBar/navBar.php'; ?>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-overlay"></div>
        <div class="hero-text">
            <h2>Welcome to Petiverse</h2>
            <p>Your One-Stop for Pet Care and Community</p>
            <div class="cta-buttons">
                <a href="./shop.php" class="cta">Explore Our Shop</a>
                <a href="./vets_map.php" class="cta">Find a Veterinarian</a>
            </div>
        </div>
    </section>

    <!-- Popup Notification for Lost & Found -->
    <div class="popup-container" id="notifications-container">
        <?php
        // Database connection
        $conn = new mysqli('localhost', 'root', '', 'petiverse');
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Fetch the latest 4 lost and found reports
        $sql = "SELECT pet_name, location, date FROM lost_and_found_pets ORDER BY date DESC LIMIT 4";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            // Display each record as a notification item
            while ($row = $result->fetch_assoc()) {
                echo '<div class="notification">';
                echo '<p><strong>Pet Name:</strong> ' . htmlspecialchars($row['pet_name']) . '</p>';
                echo '<p><strong>Location:</strong> ' . htmlspecialchars($row['location']) . '</p>';
                echo '<p><strong>Date:</strong> ' . htmlspecialchars($row['date']) . '</p>';
                echo '<button onclick="closeNotification(this)">✕</button>';
                echo '</div>';
            }
        }
        $conn->close();
        ?>
    </div>
    <button id="close-all-btn" onclick="closeAllNotifications()">Close All</button>

    <section class="about-section">
        <h2>Our Story</h2>
        <p>Founded with a vision to unite pet lovers and caregivers, Petiverse is dedicated to enhancing the lives of pets and their owners. We bring together services, resources, and a supportive community all in one platform, helping you find everything your pet needs with ease and confidence.</p>
    </section>

    <section class="about-section">
        <h2>Our Mission</h2>
        <p>Our mission is simple: to make pet care accessible, trustworthy, and supportive. We aim to provide an all-in-one solution for pet owners, offering a network of veterinarians, a comprehensive pet shop, and a forum for community connection.</p>
    </section>

    <!-- Features Section -->
    <section class="features">
        <div class="feature-card">
            <img src="src/img/shop.jpg" alt="Shop">
            <h3>Shop</h3>
            <p>Food, accessories, and medicines for your pets.</p>
            <a href="./shop.php" class="button">Shop Now</a>
        </div>
        <div class="feature-card">
            <img src="src/img/vet.jpg" alt="Vet">
            <h3>Vet Services</h3>
            <p>Book online or physical appointments with vets.</p>
            <a href="./vets_map.php" class="button">Book Now</a>
        </div>
        <div class="feature-card">
            <img src="src/img/day-care.jpeg" alt="Day Care">
            <h3>Day Care</h3>
            <p>Find reliable day care services for your pets.</p>
            <a href="#" class="button">Find Day Care</a>
        </div>
        <div class="feature-card">
            <img src="src/img/Health-track.jpg" alt="Health Tracker">
            <h3>Health Tracker</h3>
            <p>Monitor your pet's health with our BMI tracker.</p>
            <a href="#" class="button">Track Health</a>
        </div>
    </section>

    <!-- Community and Blog Section -->
    <section class="community-blog">
        <div class="community">
            <h2>Join Our Community</h2>
            <p>Connect with fellow pet lovers, share stories, and tips.</p>
            <a href="#" class="button">Join the Conversation</a>
        </div>
        <div class="blog">
            <h2>Latest from Our Blog</h2>
            <p>Get the latest tips, news, and stories about pets.</p>
            <a href="#" class="button">Read More</a>
        </div>
    </section>

    <!-- Special Events and Lost & Found Section -->
    <section class="events-lost-found">
        <div class="special-events">
            <h2>Special Events</h2>
            <!-- Event details here -->
        </div>
        <div class="lost-found">
            <h2>Lost & Found Pets</h2>
            <!-- Lost & Found details here -->
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="footer-content">
            <p>&copy; 2024 Petiverse. All Rights Reserved.</p>
        </div>
    </footer>

    <!-- JavaScript for notifications -->
    <script>
        // Function to close a single notification
        function closeNotification(button) {
            const notificationElement = button.parentElement;
            notificationElement.remove();
            checkNotifications(); // Check if there are no more notifications after closing one
        }

        // Function to close all notifications
        function closeAllNotifications() {
            const container = document.getElementById("notifications-container");
            container.innerHTML = ""; // Clear all notifications
            document.getElementById("close-all-btn").style.display = "none"; // Hide the Close All button
        }

        // Function to check notifications and hide the button if there are none
        function checkNotifications() {
            const container = document.getElementById("notifications-container");
            if (container.children.length === 0) {
                document.getElementById("close-all-btn").style.display = "none"; // Hide the Close All button
            }
        }

        // Auto-hide the notifications after 8 seconds
        setTimeout(() => {
            closeAllNotifications();
        }, 20000); // Adjust timing as needed
    </script>
</body>
</html>
