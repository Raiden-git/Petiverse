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


    <section class="about-section">
            <h2>Our Story</h2>
            <p>Founded with a vision to unite pet lovers and caregivers, Petiverse is dedicated to enhancing the lives of pets and their owners. We bring together services, resources, and a supportive community all in one platform, helping you find everything your pet needs with ease and confidence.</p>
        </section>

        <section class="about-section">
            <h2>Our Mission</h2>
            <p>Our mission is simple: to make pet care accessible, trustworthy, and supportive. We aim to provide an all-in-one solution for pet owners, offering a network of veterinarians, a comprehensive pet shop, and a forum for community connection.</p>
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
            <a href="#" class="button">Track Health</a>
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
        <h3>About Petiverse</h3>
        <p>Petiverse brings you the best in pet care, connecting you with veterinarians, pet products, and a thriving community of pet lovers.</p>
    </div>
    <div class="footer-box">
        <h3>Quick Links</h3>
        <ul class="footer-links">
            <li><a href="shop.php">Shop</a></li>
            <li><a href="vets_map.php">Find a Vet</a></li>
            <li><a href="#">Community</a></li>
            <li><a href="#">Blog</a></li>
        </ul>
    </div>
    <div class="footer-box">
        <h3>Contact Us</h3>
        <p>Email: <a href="mailto:support@petiverse.com">support@petiverse.com</a></p>
        <p>Phone: +1 (555) 123-4567</p>
        <div class="social-links">
            <a href="https://facebook.com/petiverse" target="_blank"><i class="fab fa-facebook"></i></a>
            <a href="https://twitter.com/petiverse" target="_blank"><i class="fab fa-twitter"></i></a>
            <a href="https://instagram.com/petiverse" target="_blank"><i class="fab fa-instagram"></i></a>
        </div>
    </footer>
</body>
</html>
