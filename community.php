<?php
// Database connection
include 'db.php';

session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
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
    $user_id = $_SESSION['user_id'];  // Assuming this is set when the user logs in

    $stmt = $conn->prepare("INSERT INTO posts (title, content, image, category, pet_category, user_id) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssi", $title, $content, $image, $category, $pet_category, $user_id);
    $stmt->execute();
    $stmt->close();  

    // Redirect to refresh the page
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}
$categoryFilter = isset($_GET['category']) ? $_GET['category'] : '';
$animalFilter = isset($_GET['animal']) ? $_GET['animal'] : '';
$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';

$sql = "SELECT posts.*, COALESCE(users.full_name, users.first_name) AS username 
        FROM posts
        JOIN users ON posts.user_id = users.id
        ORDER BY posts.created_at DESC";

// Fetch posts from the database

$result = $conn->query($sql);

// Check if the query was successful
if (!$result) {
    die("Query failed: " . $conn->error);
}

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
            <select id="categoryFilter" onchange="applyFilters()">
                <option value="">All Categories</option>
                <option value="health">Health</option>
                <option value="training">Training</option>
                <option value="pet-stories">Pet Stories</option>
                <option value="food">Food</option>
                <option value="other">Other</option>
            </select>
            <input type="text" id="searchBar" class="search-bar" placeholder="Search here" oninput="applyFilters()">
            <select id="animalFilter" onchange="applyFilters()">
                <option value="">All Animals</option>
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
            <button class="ask-question-btn" onclick="openQuestionModal()">Ask a Question</button>
        </div>

       <!-- Post List -->
       <div class="posts-list">
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo '<a href="post_detail.php?post_id=' . $row['id'] . '" class="post">';
                    if (!empty($row['image'])) {
                        echo '<img src="' . htmlspecialchars($row['image']) . '" alt="Post Image">';
                    }
                    echo '<div class="post-content">';
                    echo '<h4 class="post-username">' . htmlspecialchars($row['username']) . '</h4>';
                    echo '<h3>' . htmlspecialchars($row['title']) . '</h3>';
                    echo '<div style="display: flex; align-items: center;">';
                    echo '<span class="badge ' . strtolower($row['category']) . '">' . htmlspecialchars($row['category']) . '</span>';
                    echo '<span class="pet-category-badge">Category: ' . htmlspecialchars($row['pet_category']) . '</span>';
                    echo '</div>';
                    echo '<p>' . htmlspecialchars($row['content']) . '</p>';
                    echo '<span class="post-time">' . date("Y-m-d | h:i A", strtotime($row['created_at'])) . '</span>';
                    echo '</div>';
                    echo '</a>';
                }
            } else {
                echo "<p>No posts available.</p>";
            }
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

        function applyFilters() {
    const selectedCategory = document.getElementById("categoryFilter").value;
    const selectedAnimal = document.getElementById("animalFilter").value;
    const searchTerm = document.getElementById("searchBar").value;

    fetch(`fetch_posts.php?category=${selectedCategory}&animal=${selectedAnimal}&search=${encodeURIComponent(searchTerm)}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.text();
        })
        .then(html => {
            document.querySelector('.posts-list').innerHTML = html;
        })
        .catch(error => {
            console.error('Error fetching filtered posts:', error);
            document.querySelector('.posts-list').innerHTML = "<p>Failed to load posts. Please try again later.</p>";
        });
}



        // Preserve filter values on page reload
        window.onload = function() {
            const urlParams = new URLSearchParams(window.location.search);
            document.getElementById("categoryFilter").value = urlParams.get('category') || "";
            document.getElementById("animalFilter").value = urlParams.get('animal') || "";
            document.getElementById("searchBar").value = urlParams.get('search') || "";
        };

        document.addEventListener("DOMContentLoaded", function() {
            const posts = document.querySelectorAll(".post");

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add("fade-in");
                        observer.unobserve(entry.target); 
                    }
                });
            }, {
                threshold: 0.1 
            });

            posts.forEach(post => observer.observe(post));
        });
    </script>

    <style>
        #categoryFilter, #animalFilter{
          background-color: #ececec !important;
          border: none !important;
          padding: 10px 20px !important;
          border-radius: 10px !important;
          font-size: 14px !important;
          cursor: pointer;
          -webkit-appearance: none; /* For Safari and Chrome */
          -moz-appearance: none; /* For Firefox */
          appearance: none; /* Standard */
          color: #333 !important;
          width: auto; /* Ensure it doesn't shrink */
          transition: background-color 0.3s ease;
        }
        #categoryFilter:hover, #animalFilter:hover {
          background-color: #ffeadb;
        }

        #categoryFilter option, #animalFilter option {
          font-size: 14px;
          color: #333;
        }
    </style>
</body>
</html>