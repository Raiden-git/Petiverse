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
    $user_id = $_SESSION['user_id'];  
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

$sql = "SELECT posts.*, COALESCE(users.full_name, users.first_name) AS username, users.picture 
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
    <link rel="stylesheet" href="./assets/css/scrollbar.css">
    <link rel="stylesheet" href="./assets/css/community.css"> 
    <style>
        .post {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  background-color: #ffffff;
  border-radius: 10px;
  padding: 15px;
  position: relative;
  max-width: 100%; 
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    transition: box-shadow 0.3s ease;
    text-decoration: none;
}
.post:hover {
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}


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
          width: auto; 
          transition: background-color 0.3s ease;
        }
        #categoryFilter:hover, #animalFilter:hover {
          background-color: #ffeadb;
        }

        #categoryFilter option, #animalFilter option {
          font-size: 14px;
          color: #333;
        }

        .post-badges-container {
    position: relative; 
    padding: 10px; 
}
        .post-badges {
        position: absolute;
        top: 0; 
        right: 10px; 
        display: flex;
        gap: 10px; /* Adds spacing between badges */
        padding: 5px 0; 
    }

        /* Health, Food, etc. */
    .badge {
    display: inline-block;
    padding: 5px 10px;
    font-size: 12px;
    color: white;
    border-radius: 12px;
    text-align: center;
    background-color: #4caf50; /* Default badge color */
    margin-left: 5px;
    }


    .badge.health { background-color: #a8e6cf; }
    .badge.training { background-color: #a3cde8; }
    .badge.pet-stories { background-color: #FF9AA2; }
    .badge.food { background-color: #DEC584; }
    .badge.other { background-color: #c3aed6; }



    .pet-category-badge {
    padding: 5px 10px;
    font-size: 12px;
    border-radius: 12px;
    background-color: #DA8359; 
    color: #ffffff;
    margin-left: 5px;
    display: inline-block; 
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
    }
    </style>
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
            <input type="text" id="searchBar" class="search-bar" placeholder="Search for topics or categories" oninput="applyFilters()">
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
            <button class="ask-question-btn" onclick="openQuestionModal()">Start a Discussion</button>
        </div>

       <!-- Post List -->
<div class="posts-list">
    <?php
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $fullContent = htmlspecialchars($row['content']);
            $shortContent = substr($fullContent, 0, 160); 
            $isTruncated = strlen($fullContent) > 160; 

            echo '<a href="post_detail.php?post_id=' . $row['id'] . '" class="post">';
            if (!empty($row['image'])) {
                echo '<img src="' . htmlspecialchars($row['image']) . '" alt="Post Image">';
            }
            echo '<div class="post-content">';
            echo '<h4 class="post-username">' . htmlspecialchars($row['username']) . '</h4>';
            echo '<h3>' . htmlspecialchars($row['title']) . '</h3>';
            echo '<div class="post-badges-container">';
            echo '<div class="post-badges">';
            echo '<span class="badge ' . strtolower($row['category']) . '">' . htmlspecialchars($row['category']) . '</span>';
            
            echo '<span class="pet-category-badge">Category: ' . htmlspecialchars($row['pet_category']) . '</span>';
            
            echo '</div>';
            echo '</div>';
            echo '<p class="post-text">' . ($isTruncated ? $shortContent . '...' : $fullContent) . '</p>';

            // Only add the "Read More" button if the content is truncated
            if ($isTruncated) {
                echo '<button class="read-more-btn">Read More</button>';
            }

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
        .then(response => response.text())
        .then(html => {
            document.querySelector('.posts-list').innerHTML = html;
        })
        .catch(error => console.error('Error fetching filtered posts:', error));
}



        // Preserve filter values on page reload
        window.onload = function() {
            const urlParams = new URLSearchParams(window.location.search);
            document.getElementById("categoryFilter").value = urlParams.get('category') || "";
            document.getElementById("animalFilter").value = urlParams.get('animal') || "";
            document.getElementById("searchBar").value = urlParams.get('search') || "";
        };

        document.addEventListener('DOMContentLoaded', () => {
    const readMoreButtons = document.querySelectorAll('.read-more-btn');

    readMoreButtons.forEach(button => {
        button.addEventListener('click', (e) => {
            const postContent = e.target.previousElementSibling;
            const fullContent = postContent.getAttribute('data-full-content');
            const isExpanded = postContent.classList.contains('expanded');

            if (isExpanded) {
                postContent.textContent = fullContent.slice(0, 160) + '...';
                e.target.textContent = 'Read More';
            } else {
                postContent.textContent = fullContent;
                e.target.textContent = 'Read Less';
            }

            postContent.classList.toggle('expanded');
        });
    });
});


    </script>
</body>
</html> 