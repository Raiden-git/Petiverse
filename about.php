<?php
    session_start();
    
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us | Petiverse</title>
    <link rel="stylesheet" href="assets/css/scrollbar.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"> <!-- Font Awesome CDN -->
    <style>
        /* General Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Roboto', sans-serif;
        }

        body {
            background-color: #FCFAEE; 
            color: #333;
        }

        /* Center Wrapper */
        .center-wrapper {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        /* Main Container */
        .about-container {
            max-width: 1200px;
            width: 100%;
            background-color: #ffffff; 
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        /* Header */
        .about-header {
            text-align: center;
            margin-bottom: 20px;
        }

        .about-header h1 {
            font-size: 2.5rem;
            color: #DA8359; 
        }

        .about-header p {
            font-size: 1.2rem;
            color: #666;
        }

        /* Section Titles */
        .about-section h2 {
            font-size: 1.8rem;
            color: #DA8359; 
            margin-bottom: 10px;
        }

        .about-section {
            margin-bottom: 20px;
        }

        /* Paragraphs */
        .about-section p {
            font-size: 1rem;
            color: #333;
            line-height: 1.6;
            margin-bottom: 15px;
        }

        /* Team Section */
        .team-section {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }

        .team-member {
            text-align: center;
            padding: 10px;
            background-color: #fcfaee;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .team-member img {
            width: 200px;
            height: 200px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 10px;
            border: 3px solid #DA8359;
        }

        .team-member h3 {
            font-size: 1.1rem;
            color: #333;
            margin-bottom: 5px;
        }

        .team-member p {
            font-size: 1.1rem;
            color: #555;
        }

        /* Contact and Feedback Container */
        .contact-feedback-container {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            margin-top: 30px;
        }

        /* Contact Details */
        .contact-details {
            flex: 1;
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .contact-details p {
            font-size: 1rem;
            color: #333;
            margin: 10px 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .contact-details a {
            color: #DA8359; 
            text-decoration: none;
        }

        /* Social Media Links */
        .social-media-links {
            list-style: none;
            display: flex;
            gap: 15px;
            margin-top: 10px;
        }

        .social-media-links li a {
            font-size: 1.1rem;
            color: #DA8359; 
            text-decoration: none;
            transition: color 0.3s;
        }

        .social-media-links li a:hover {
            color: #A5B68D; 
        }

        /* Feedback Form */
        .feedback-form {
            flex: 1;
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .feedback-form label {
            font-size: 1rem;
            display: block;
            margin-top: 10px;
            margin-bottom: 5px;
        }

        .feedback-form input[type="text"],
        .feedback-form input[type="email"],
        .feedback-form textarea {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }

        .feedback-form input:focus,
        .feedback-form textarea:focus {
            border-color: #DA8359; 
            outline: none;
        }

        .feedback-form button {
            display: flex;
            align-items: center;
            gap: 5px;
            margin-top: 15px;
            padding: 10px 15px;
            background-color: #DA8359; 
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
            transition: background-color 0.3s;
        }

        .feedback-form button:hover {
            background-color: #A5B68D; 
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .about-container {
                padding: 20px;
            }

            .about-header h1 {
                font-size: 2rem;
            }

            .about-section h2 {
                font-size: 1.6rem;
            }

            .contact-feedback-container {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>

<?php include 'Cus-NavBar/navBar.php'; ?>

<div class="center-wrapper">
    <div class="about-container">
        <div class="about-header">
            <h1>About Us</h1>
            <p>Your Partner in Pet Care and Community</p>
        </div>

        <section class="about-section organization">
            <h2>About Our Organization</h2>
            <p>At Petiverse, we believe pets are family. With a commitment to quality, convenience, and compassion, we continuously strive to improve our services to support pet owners and their beloved companions. Our team is dedicated to providing a safe and trusted platform where you can access pet care essentials and advice from knowledgeable professionals.</p>
        </section>

        <section class="about-section team">
            <h2>Meet the Team</h2>
            <div class="team-section">
                
                <div class="team-member">
                    <img src="./assets/img/osura.jpg" alt="Osura's Picture">
                    <h3>Project Manager | UI/UX Designer</h3>
                    <p>Osura Chandula</p>
                </div>

                <div class="team-member">
                    <img src="./assets/img/prashid.png" alt="Prashid's Picture">
                    <h3>Full Stack Developer</h3>
                    <p>Prashid Dilshan</p>
                </div>

                <div class="team-member">
                    <img src="./assets/img/himaz.jpeg" alt="Himaz's Picture">
                    <h3>Backend Developer</h3>
                    <p>Mohomad Himaz</p>
                </div>
                <div class="team-member">
                    <img src="./assets/img/malmi.jpg" alt="Malmi's Picture">
                    <h3>Frontend Developer</h3>
                    <p>Malmi Wimalaweera</p>
                </div>
            </div>
        </section>

        <div class="contact-feedback-container">
            <section class="about-section contact-details">
                <h2>Contact Us</h2>
                <p>We'd love to hear from you! Reach out with any questions, suggestions, or feedback.</p>
                <p><i class="fas fa-envelope"></i> Email: <a href="mailto:support@petiverse.com">support@petiverse.com</a></p>
                <p><i class="fas fa-phone"></i> Phone: +1 (555) 123-4567</p>
                <p><i class="fas fa-share-alt"></i> Follow us on social media:</p>
                <ul class="social-media-links">
                    <li><a href="https://facebook.com/petiverse" target="_blank"><i class="fab fa-facebook"></i> Facebook</a></li>
                    <li><a href="https://twitter.com/petiverse" target="_blank"><i class="fab fa-twitter"></i> Twitter</a></li>
                    <li><a href="https://instagram.com/petiverse" target="_blank"><i class="fab fa-instagram"></i> Instagram</a></li>
                </ul>
            </section>

            <section class="about-section feedback-form">
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
            </section>
        </div>
    </div>
</div>

<!-- Footer -->
<?php include 'footer.php'; ?>
</body>
</html>
