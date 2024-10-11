<?php
// Database connection
include 'db.php';

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $category = $_POST['category'];
    $pet_category = $_POST['pet_category'];

    // Handle file upload
    $image = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $image = 'uploads/' . basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], $image);
    }

    // Insert the post into the database
    $stmt = $conn->prepare("INSERT INTO posts (title, content, image, category, pet_category) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $title, $content, $image, $category, $pet_category);
    $stmt->execute();
    $stmt->close();

    // Redirect to refresh the page
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Fetch posts from the database
$sql = "SELECT * FROM posts ORDER BY created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Petiverse - Let's care your Furball</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
        .community-section{
            padding: 20px;
        }
        .filter-bar{
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            background-color: #f5f5f5;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        .search-bar, .animal-btn, .ask-question-btn{
            padding: 10px;
            margin: 0 10px;
        }

        .dropdown-filter{
            background-color: #ececec;
            border: none;
            padding: 10px 20px;
            border-radius: 10px;
            font-size: 14px;
            cursor: pointer;
        }
        .search-bar{
            flex-grow: 2;
            margin: 0 15px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 50px;
            font-size: 14px;
        }
        .animal-btn, .ask-question-btn{
            background-color: #ececec;
            border: none;
            padding: 10px 20px;
            border-radius: 10px;
            font-size: 14px;
            cursor: pointer;
        }
        .ask-question-btn{
            background-color: #fff6d1;
            border: 1px solid #e0e0e0;
            font-weight: bold;
        }
        #questionModal {
            position: fixed;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            display: none;
            justify-content: center;
            align-items: center;
    
        }

        .modal-content {
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            width: 400px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.3);
        }

        .modal-content form input, 
        .modal-content form textarea, 
        .modal-content form select {
            width: 100%;
            margin-bottom: 10px;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .modal-content form button {
            padding: 10px;
            border: none;
            border-radius: 5px;
            background-color: #28a745;
            color: #fff;
            cursor: pointer;
        }

        .modal-content form button[type="button"] {
            background-color: #dc3545;
        }

        /* Post List Styling */
        .posts-list {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .post {
            display: flex;
            align-items: flex-start;
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            padding: 15px;
            border-left: 5px solid #dcdcdc;
            position: relative;
        }

        .post img {
            width: 60px;
            height: 60px;
            border-radius: 10px;
            margin-right: 15px;
        }

        .post h3 {
            margin: 0;
            font-size: 16px;
            color: #333;
        }

        .post p {
            font-size: 14px;
            color: #666;
            margin: 5px 0;
        }

        .category {
            position: absolute;
            top: 10px;
            right: 15px;
            padding: 5px 10px;
            font-size: 12px;
            border-radius: 12px;
            color: #ffffff;
        }

       .post-time {
            font-size: 12px;
            color: #999;
            margin-top: 10px;
        }

        /* Category colors */
        .category[data-category="dog"] {
            background-color: #8bc34a;
        }

        .category[data-category="cat"] {
            background-color: #ffa000;
        }

        .category[data-category="ginipig"] {
            background-color: #9c27b0;
        }

        .category[data-category="other"] {
            background-color: #757575;
        }

        /* Health, Food, etc. */
        .badge {
            display: inline-block;
            padding: 3px 8px;
            font-size: 10px;
            color: white;
            border-radius: 12px;
            background-color: #4caf50; /* Default badge color */
        }

        .badge.health { background-color: #4caf50; }
        .badge.food { background-color: #ffc107; }
        .badge.other { background-color: #757575; }


    </style>
</head>
<body>
   
    <header>
        <div class="logo">
            <h1>Petiverse</h1>
        </div>
        <nav>
            <ul>
                <li><a href="#">Shop</a></li>
                <li><a href="#">Vet Services</a></li>
                <li><a href="#">Day Care</a></li>
                <li><a href="community.html">Community</a></li>
                <li><a href="#">Blog</a></li>
                <li><a href="#">Special Events</a></li>
                <li><a href="#">Contact Us</a></li>
                <li><a href="#">Pet Selling</a></li>
            </ul>
        </nav>
        <div class="login">
            <a href="profile.php">User Profile</a>
        </div>
    </header>

    <!-- Community Section -->
    <section class="community-section">
        <div class="filter-bar">
            <button class="dropdown-filter">Dropdown Filter</button>
            <input type="text" class="search-bar" placeholder="Search here">
            <button class="animal-btn">Animal</button>
            <button class="ask-question-btn" onclick="openQuestionModal()">Ask a Question</button>
        </div>

        <!-- Post List -->
        <div class="posts-list">
    <?php
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            echo '<a href="post_detail.php?post_id=' . $row['id'] . '" class="post">'; // Add link with post_id
            if (!empty($row['image'])) {
                echo '<img src="' . htmlspecialchars($row['image']) . '" alt="Post Image">';
            }
            echo '<div>';
            echo '<h3>' . htmlspecialchars($row['title']) . '</h3>';
            echo '<span class="badge ' . strtolower($row['category']) . '">' . htmlspecialchars($row['category']) . '</span>';
            echo '<span class="badge ' . strtolower($row['pet_category']) . '">' . htmlspecialchars($row['pet_category']) . '</span>';
            echo '<p>' . htmlspecialchars($row['content']) . '</p>';
            echo '<span class="post-time">' . date("Y-m-d | h:i A", strtotime($row['created_at'])) . '</span>';
            echo '</div>';
            echo '</a>'; // Close link
        }
    } else {
        echo "<p>No posts available.</p>";
    }
    $conn->close();
    ?>
        </div>


    </section>

    <!-- Ask a Question Modal -->
    <div id="questionModal">
        <div class="modal-content">
            <h3>Create a Post</h3>
            <form action="" method="POST" enctype="multipart/form-data">
                <input type="text" name="title" placeholder="Post Title" required>
                <textarea name="content" placeholder="Write your post here..." required></textarea>
                <input type="file" name="image" accept="image/*">
                <select name="category">
                    <option value="health">Health</option>
                    <option value="training">Training</option>
                    <option value="pet-stories">Pet Stories</option>
                    <option value="food">Food</option>
                    <option value="other">Other</option>
                </select>
                <select name="pet_category">
                    <option value="dog">Dog</option>
                    <option value="cat">Cat</option>
                    <option value="bird">Bird</option>
                    <option value="rabbit">Rabbit</option>
                    <option value="hamster">Hamster</option>
                    <option value="guinea-pig">Guinea Pig</option>
                    <option value="fish">Fish</option>
                    <option value="reptile">Reptile</option>
                    <option value="horse">Horse</option>
                    <option value="ferret">Ferret</option>
                    <option value="tarantula">Tarantula</option>
                    <option value="frog">Frog</option>
                    <option value="tortoise">Tortoise</option>
                    <option value="chicken">Chicken</option>
                    <option value="duck">Duck</option>
                    <option value="goat">Goat</option>
                    <option value="pig">Pig</option>
                    <option value="insect">Insect</option>
                    <option value="other">Other</option>
                </select>
                <button type="submit">Post</button>
                <button type="button" onclick="closeQuestionModal()">Cancel</button>
            </form>
        </div>
    </div>

    <script>
        function openQuestionModal() {
            document.getElementById('questionModal').style.display = 'flex';
        }

        function closeQuestionModal() {
            document.getElementById('questionModal').style.display = 'none';
        }
    </script>
</body>
</html>