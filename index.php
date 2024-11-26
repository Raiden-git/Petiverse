<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Petiverse - Let's care your Furball</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/scrollbar.css">
    <link rel="stylesheet" href="assets/css/popup.css"> 
    <style>

.special-events {
    padding: 20px;
    background-color: #f8f9fa;
}

.special-events h2 {
    text-align: center;
    margin-bottom: 20px;
}

.event-cards {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    justify-content: center;
}

.event-card {
    background: #ffffff;
    border: 1px solid #ddd;
    border-radius: 8px;
    width: 300px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    text-align: center;
    padding: 15px;
}

.event-image {
    width: 100%;
    height: 200px;
    object-fit: cover;
    border-bottom: 1px solid #ddd;
}

.event-card h3 {
    font-size: 20px;
    color: #333;
    margin: 15px 0;
}

.event-card p {
    font-size: 16px;
    color: #555;
    margin: 10px 0;
}


        /* Container for the contact and feedback section */
        .footer-container {
    background-color: #2C3E50;
    color: #fff;
    display: flex;
    justify-content: space-between;
    padding: 40px 20px;
    border-top: 4px solid #fda085;
    text-align: left;
    box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.2);
}

.footer-box {
    width: 30%;
    padding: 20px;
    margin-left: 20px;
    margin-right: 20px;
}

.footer-box h3 {
    font-size: 1.6em;
    color: #fda085;
    margin-bottom: 15px;
}

.footer-box p,
.footer-links li,
.newsletter-form input {
    font-size: 1em;
    line-height: 1.6;
    color: #ddd;
}

.footer-links {
    list-style: none;
    padding: 0;
}

.footer-links li {
    margin-bottom: 10px;
}

.footer-links a {
    text-decoration: none;
    color: #fda085;
}

.footer-links a:hover {
    color: #fff;
}

.social-links a {
    color: #fda085;
    font-size: 1.6em;
    margin-right: 15px;
}

.social-links a:hover {
    color: #fff;
}

.newsletter-form {
    display: flex;
    flex-direction: column;
}

.newsletter-form input {
    padding: 10px;
    border-radius: 5px;
    border: none;
    margin-bottom: 10px;
    font-size: 1em;
}

.newsletter-form button {
    background-color: #fda085;
    color: #2C3E50;
    padding: 10px;
    border: none;
    border-radius: 5px;
    font-size: 1em;
    cursor: pointer;
    transition: 0.3s;
}

.newsletter-form button:hover {
    background-color: #fff;
    color: #fda085;
}

.feedback-box {
    background-color: #fff;
    padding: 30px;
    border: 2px solid #ddd;
    border-radius: 10px;
    box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
    max-width: 600px;
    margin: 0 auto;
}

.feedback-box h2 {
    font-size: 1.8em;
    color: #6A097D;
    margin-bottom: 15px;
}

.feedback-box p {
    font-size: 1em;
    color: #555;
    line-height: 1.6;
    margin-bottom: 25px;
}

.feedback-box label {
    font-size: 1.1em;
    margin-bottom: 10px;
    color: #333;
}

.feedback-box input,
.feedback-box textarea {
    width: 95%;
    padding: 12px;
    margin-bottom: 20px;
    border: 1px solid #ddd;
    border-radius: 8px;
    font-size: 1em;
}

.feedback-box button {
    background-color: #6A097D;
    color: white;
    padding: 12px 20px;
    border: none;
    border-radius: 5px;
    font-size: 1.2em;
    cursor: pointer;
}

.feedback-box button:hover {
    background-color: #fda085;
}


/* Additional CSS for notification images */
.notification img {
            width: 80px;
            height: 80px; 
            object-fit: cover;
            margin-right: 10px; 
            border-radius: 5px;
        }
        .notification {
            display: flex;
            align-items: center; 
            margin-bottom: 10px; 
        }
        .status {
            font-weight: bold;
            margin-left: 10px; 
            color: #d9534f; 
        }
        .status.found {
            color: #5cb85c;
        }



/* Responsive Design */
@media (max-width: 768px) {
    .footer-container {
        flex-direction: column;
        text-align: center;
    }

    .footer-box {
        width: 100%;
        margin-bottom: 20px;
    }
}


    </style>
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

    // Fetch the latest 4 lost and found reports including the image and status
    $sql = "SELECT pet_name, location, date, image, status FROM lost_and_found_pets ORDER BY date DESC LIMIT 4";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // Display each record as a notification item
        while ($row = $result->fetch_assoc()) {
            // Convert the image BLOB to a base64 string
            $base64_image = base64_encode($row['image']);
            $image_src = 'data:image/jpeg;base64,' . $base64_image; 

            // Set the status class based on the pet status
            $status_class = ($row['status'] === 'found') ? 'found' : '';
            $status_text = ucfirst(htmlspecialchars($row['status'])); 

            // Render each notification with 'display: none' initially
            echo '<div class="notification" style="display:none;">';
            echo '<img src="' . htmlspecialchars($image_src) . '" alt="' . htmlspecialchars($row['pet_name']) . '" class="pet-image" />';
            echo '<div>'; // Create a wrapper for text
            echo '<p><strong>Pet Name:</strong> ' . htmlspecialchars($row['pet_name']) . ' <span class="status ' . $status_class . '">' . $status_text . '</span></p>';
            echo '<p><strong>Location:</strong> ' . htmlspecialchars($row['location']) . '</p>';
            echo '<p><strong>Date:</strong> ' . htmlspecialchars($row['date']) . '</p>';
            echo '</div>'; 
            echo '<button onclick="closeNotification(this)">✕</button>';
            echo '</div>';
        }
    }
    $conn->close();
    ?>
</div>
<button id="close-all-btn" onclick="closeAllNotifications()">Close All</button>



    <!-- Our Story Section -->
    <section class="about-section" style="position:relative; padding: 60px 20px; background-color: #f0f8ff;">
        <!-- Adding a Blob SVG Shape -->
        <!-- <svg style="position: absolute; top: 50px;  z-index: 3;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200" width="400" height="400">
            <path fill="#FFD700" d="M45.5,-62.7C60.7,-51.4,73.8,-38.2,76.6,-22.7C79.4,-7.2,71.8,10.5,61.2,22.7C50.5,34.8,37,41.4,23.4,46.6C9.8,51.7,-3.9,55.5,-16.7,52.7C-29.5,49.8,-41.4,40.3,-47.5,28C-53.7,15.7,-53.9,0.6,-51.8,-13.6C-49.8,-27.8,-45.5,-41.1,-36.5,-53C-27.6,-64.9,-13.8,-75.4,1.2,-76.7C16.2,-78.1,32.4,-70,45.5,-62.7Z" transform="translate(100 100)" />
        </svg> -->

        <div class="about-content" style="position: relative; z-index: 2;">
            <h2 style="font-size: 2.5em; text-align: center; color: #6A097D;">Our Story</h2>
            <p style="font-size: 1.2em; line-height: 1.8; color: #333; max-width: 800px; margin: 0 auto; text-align: center;">
                Founded with a vision to unite pet lovers and caregivers, Petiverse is dedicated to enhancing the lives of pets and their owners. We bring together services, resources, and a supportive community all in one platform, helping you find everything your pet needs with ease and confidence.
            </p>
        </div>
    </section>

    <!-- Features Section -->
    <h2>Our Services</h2>
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
            <a href="./health_tracker.php" class="button">Track Health</a>
        </div>
    </section>



    <!-- Our Mission Section -->
    <section class="mission-section" style="background: linear-gradient(135deg, #f6d365 0%, #fda085 100%); padding-top: 60px; padding-bottom: 90px; ">
        <div class="mission-content" style="text-align: center;">
            <h2 style="font-size: 2.5em; color: #fff; text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);">Our Mission</h2>
            <p style="font-size: 1.2em; line-height: 1.8; color: #fff; max-width: 800px; margin: 0 auto; text-align: center;">
                Our mission is simple: to make pet care accessible, trustworthy, and supportive. We aim to provide an all-in-one solution for pet owners, offering a network of veterinarians, a comprehensive pet shop, and a forum for community connection.
            </p>
        </div>
        <!-- Adding a wave SVG shape from Hero Patterns -->
        <svg style="position: absolute; top: 560px; bottom: 0; left: 0; z-index: 1;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320" width="100%" height="auto">
            <path fill="#f6d365" fill-opacity="1" d="M0,192L48,176C96,160,192,128,288,128C384,128,480,160,576,170.7C672,181,768,171,864,149.3C960,128,1056,96,1152,106.7C1248,117,1344,171,1392,197.3L1440,224V320H1392C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320H0Z"></path>
        </svg>
    </section>

    
    <!-- Community and Blog Section -->
    <section class="community-blog">
        <div class="community">
            <h2>Join Our Community</h2>
            <p>Connect with fellow pet lovers, share stories, and tips.</p>
            <a href="community.php" class="button">Join the Conversation</a>
        </div>
        <div class="blog">
            <h2>Latest from Our Blog</h2>
            <p>Get the latest tips, news, and stories about pets.</p>
            <a href="blog.php" class="button">Read More</a>
        </div>
    </section>

   <!-- Special Events Section -->
<section class="special-events">
    <h2>Special Events</h2>
    <div class="event-cards">
    <?php
    include('db.php');

    /// Fetch the latest 4 approved events ordered by date
    $query = "SELECT * FROM special_events WHERE approved = 1 ORDER BY date DESC LIMIT 4";
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

    // Close the prepared statement
    $stmt->close();
    ?>
    </div>

    <!-- Button to redirect to special_events.php -->
    <a href="special_events.php" class="btn">See All Events</a>
</section>






    <section class="about-section feedback-form">
    <div class="feedback-box">
        <h2>Feedback Forum</h2>
        <p><i class="fas fa-comments"></i> Your feedback helps us grow! Please share your experience or any suggestions.</p>
        <form action="submit_feedback.php" method="POST">
            <label for="name">Name:</label>
            <input type="text" id="name" name="name" required>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>

            <label for="message">Feedback:</label>
            <textarea id="message" name="message" rows="5" required></textarea>

            <button type="submit"><i class="fas fa-paper-plane"></i> Send Feedback</button>
        </form>
        <br>
    </div>
</section>

<br>

    <footer class="footer-container">

    

    

    <div class="footer-box">
        <h3>Quick Links</h3>
        <ul class="footer-links">
            <li><a href="shop.php">Shop</a></li>
            <li><a href="vets_map.php">Find a Vet</a></li>
            <li><a href="community.php">Community</a></li>
            <li><a href="blog.php">Blog</a></li>
        </ul>
    </div>

    <div class="footer-box">
        <h3>Contact Us</h3>
        <p>Email: <a href="mailto:support@petiverse.com">support@petiverse.com</a></p>
        <p>Phone: +94 (71) 123-4567</p>
        <div class="social-links">
            <a href="https://facebook.com/petiverse" target="_blank"><i class="fab fa-facebook"></i></a>
            <a href="https://twitter.com/petiverse" target="_blank"><i class="fab fa-twitter"></i></a>
            <a href="https://instagram.com/petiverse" target="_blank"><i class="fab fa-instagram"></i></a>
        </div>

        </div>

    <div class="footer-box">
        <h3>About Petiverse</h3>
        <p>Petiverse brings you the best in pet care, connecting you with veterinarians, pet products, and a thriving community of pet lovers.</p>
    </div>
    
    
    </footer>

    <!-- Footer -->
    <?php include 'footer.php'; ?>

    <!-- JavaScript for notifications -->
    <script>
        // Function to close a single notification
        function closeNotification(button) {
            const notificationElement = button.parentElement;
            notificationElement.remove();
            checkNotifications(); 
        }

        // Function to close all notifications
        function closeAllNotifications() {
            const container = document.getElementById("notifications-container");
            container.innerHTML = ""; 
            document.getElementById("close-all-btn").style.display = "none"; 
        }

        // Function to check notifications and hide the button if there are none
        function checkNotifications() {
            const container = document.getElementById("notifications-container");
            if (container.children.length === 0) {
                document.getElementById("close-all-btn").style.display = "none"; 
            }
        }

        // Auto-hide the notifications after 20 seconds
        setTimeout(() => {
            closeAllNotifications();
        }, 20000); 

        // Function to show notifications and the "Close All" button with a delay
        document.addEventListener("DOMContentLoaded", function() {
            setTimeout(function() {
                // Show all notifications
                const notifications = document.querySelectorAll('.notification');
                notifications.forEach(notification => {
                    notification.style.display = 'flex'; // Show notification after 2 seconds
                });

                // Show the "Close All" button after 2 seconds
                const closeAllBtn = document.getElementById('close-all-btn');
                closeAllBtn.style.display = 'block'; 
            }, 2000); // 2000ms delay
        });

        // Function to close individual notification
        function closeNotification(button) {
            const notification = button.parentElement;
            notification.style.display = 'none';
        }

        // Function to close all notifications
        function closeAllNotifications() {
            const notifications = document.querySelectorAll('.notification');
            notifications.forEach(notification => {
                notification.style.display = 'none';
            });

            // Optionally, you can hide the "Close All" button after closing all notifications
            const closeAllBtn = document.getElementById('close-all-btn');
            closeAllBtn.style.display = 'none';
        }
    </script>


    
</body>
</html>
