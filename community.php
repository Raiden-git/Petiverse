<?php
// Database connection
include 'db.php';

session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

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
    <link rel="stylesheet" href="assets/css/scrollbar.css">
    <link rel="stylesheet" href="./assets/css/community.css">

</head>
<body>
   
    <?php include 'Cus-NavBar/navBar.php'; ?>

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