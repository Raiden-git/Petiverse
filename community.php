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

    <!-- <link rel="stylesheet" href="./assets/css/community.css">  -->
    <style>
       /* Reset and Base Styles */
/* Modern List View Styles */


body {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
    background-color: #f0f2f5;
    line-height: 1.6;
    color: #1c1e21;
}

.community-section {
    max-width: 1000px;
    margin: 0 auto;
    padding: 20px 10px;
}

/* Filter Bar Styling */
.filter-bar {
    display: flex;
    background-color: white;
    padding: 12px 15px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    margin-bottom: 15px;
    gap: 10px;
    position: sticky;
    top: 0;
    z-index: 100;
}

.filter-bar select, 
.filter-bar input, 
.ask-question-btn {
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 0.9em;
}

.search-bar {
    flex-grow: 1;
}

.ask-question-btn {
    background-color: #1877f2;
    color: white;
    border: none;
    cursor: pointer;
    font-weight: 600;
    transition: background-color 0.2s;
}

.ask-question-btn:hover {
    background-color: #166fe5;
}

/* Posts List Styling - List View */
.posts-list {
    display: flex;
    flex-direction: column;
}

.post {
    display: flex;
    background-color: white;
    border-radius: 8px;
    margin-bottom: 10px;
    text-decoration: none;
    color: inherit;
    transition: background-color 0.2s;
    overflow: hidden;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
}

.post:hover {
    background-color: #f5f5f5;
}

.post img {
    width: 120px;
    height: 120px;
    object-fit: cover;
    flex-shrink: 0;
}

.post-content {
    flex-grow: 1;
    padding: 15px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.post-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.post-username {
    font-weight: 600;
    color: #1c1e21;
    font-size: 0.95em;
}

.post-time {
    color: #65676b;
    font-size: 0.8em;
}

.post-content h3 {
    font-size: 1.1em;
    margin-bottom: 8px;
    color: #1c1e21;
    line-height: 1.4;
}

.post-badges {
    display: flex;
    gap: 8px;
    margin-bottom: 10px;
}

.badge {
    padding: 4px 8px;
    border-radius: 16px;
    font-size: 0.75em;
    font-weight: 500;
    text-transform: capitalize;
}

.badge.health { 
    background-color: #e7f3fe; 
    color: #1976d2; 
}
.badge.training { 
    background-color: #e8f5e9; 
    color: #2e7d32; 
}
.badge.pet-stories { 
    background-color: #fff3e0; 
    color: #ef6c00; 
}
.badge.food { 
    background-color: #fbe9e7; 
    color: #d84315; 
}
.badge.other { 
    background-color: #f3e5f5; 
    color: #6a1b9a; 
}

.pet-category-badge {
    background-color: #f1f8e9;
    color: #2e7d32;
    padding: 4px 8px;
    border-radius: 16px;
    font-size: 0.75em;
}

.post-text {
    color: #65676b;
    font-size: 0.95em;
    margin-bottom: 10px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;
}

.read-more-btn {
    background: none;
    color: #1877f2;
    border: none;
    padding: 0;
    font-size: 0.9em;
    cursor: pointer;
    align-self: flex-start;
}

/* Question Modal Styling */
#questionModal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
    display: none;
    justify-content: center;
    align-items: center;
    z-index: 1000;
}

.modal-content {
    background-color: white;
    border-radius: 8px;
    width: 500px;
    max-width: 95%;
    padding: 20px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.modal-content form {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.modal-content input, 
.modal-content textarea, 
.modal-content select {
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 0.95em;
}

.modal-content textarea {
    min-height: 120px;
    resize: vertical;
}

.modal-content button {
    padding: 10px;
    border: none;
    border-radius: 6px;
    font-weight: 600;
    cursor: pointer;
    transition: opacity 0.2s;
}

.modal-content button[type="submit"] {
    background-color: #1877f2;
    color: white;
}

.modal-content button[type="button"] {
    background-color: #f5f5f5;
    color: #1c1e21;
}

/* Responsive Adjustments */
@media screen and (max-width: 768px) {
    .community-section {
        padding: 10px;
    }

    .filter-bar {
        flex-direction: column;
        gap: 10px;
    }

    .post {
        flex-direction: column;
    }

    .post img {
        width: 100%;
        height: 200px;
    }
}

/* No Image Variation */
.post.no-image .post-content {
    width: 100%;
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
        <input type="text" id="searchBar" class="search-bar" placeholder="Search topics..." oninput="applyFilters()">
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
        <button class="ask-question-btn" onclick="openQuestionModal()">
            <i class="plus-icon">+</i> Start Discussion
        </button>
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
                echo '<h4 class="post-username">@' . htmlspecialchars($row['username']) . '</h4>';
                echo '<h3>' . htmlspecialchars($row['title']) . '</h3>';
                
                echo '<div class="post-badges-container">';
                echo '<div class="post-badges">';
                echo '<span class="badge ' . strtolower($row['category']) . '">' . htmlspecialchars($row['category']) . '</span>';
                echo '<span class="pet-category-badge">' . htmlspecialchars($row['pet_category']) . '</span>';
                echo '</div>';
                echo '</div>';
                
                echo '<p class="post-text" data-full-content="' . $fullContent . '">' . 
                     ($isTruncated ? $shortContent . '...' : $fullContent) . '</p>';

                if ($isTruncated) {
                    echo '<button class="read-more-btn">Read More</button>';
                }

                echo '<span class="post-time">' . date("M d, Y | h:i A", strtotime($row['created_at'])) . '</span>';
                echo '</div>';
                echo '</a>';
            }
        } else {
            echo "<p class='no-posts'>No posts available. Be the first to start a discussion!</p>";
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
    
    <?php include 'footer.php'; ?>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
    const categoryFilter = document.getElementById("categoryFilter");
    const animalFilter = document.getElementById("animalFilter");
    const searchBar = document.getElementById("searchBar");
    const postsList = document.querySelector('.posts-list');

    function applyFilters() {
        const selectedCategory = categoryFilter.value.toLowerCase();
        const selectedAnimal = animalFilter.value.toLowerCase();
        const searchTerm = searchBar.value.toLowerCase();

        const posts = document.querySelectorAll('.post');
        let visiblePostCount = 0;

        posts.forEach(post => {
            const categoryBadge = post.querySelector('.badge');
            const petCategoryBadge = post.querySelector('.pet-category-badge');
            const postTitle = post.querySelector('h3').textContent.toLowerCase();
            const postContent = post.querySelector('.post-text').textContent.toLowerCase();

            const categoryMatch = !selectedCategory || 
                categoryBadge.textContent.toLowerCase() === selectedCategory;
            
            const animalMatch = !selectedAnimal || 
                petCategoryBadge.textContent.toLowerCase().includes(selectedAnimal);
            
            const searchMatch = !searchTerm || 
                postTitle.includes(searchTerm) || 
                postContent.includes(searchTerm);

            if (categoryMatch && animalMatch && searchMatch) {
                post.style.display = 'flex';
                visiblePostCount++;
            } else {
                post.style.display = 'none';
            }
        });

        // Manage "No posts" message
        let noPostsMessage = document.getElementById('no-posts-message');
        if (visiblePostCount === 0) {
            if (!noPostsMessage) {
                noPostsMessage = document.createElement('div');
                noPostsMessage.id = 'no-posts-message';
                noPostsMessage.style.textAlign = 'center';
                noPostsMessage.style.padding = '20px';
                noPostsMessage.style.backgroundColor = 'white';
                noPostsMessage.style.borderRadius = '8px';
                noPostsMessage.innerHTML = `
                    <p style="color: #65676b;">No posts match your current filters.</p>
                    <p style="color: #1877f2; margin-top: 10px; cursor: pointer;" onclick="resetFilters()">Reset Filters</p>
                `;
                postsList.appendChild(noPostsMessage);
            }
        } else if (noPostsMessage) {
            noPostsMessage.remove();
        }
    }

    // Attach event listeners
    categoryFilter.addEventListener('change', applyFilters);
    animalFilter.addEventListener('change', applyFilters);
    searchBar.addEventListener('input', applyFilters);

    // Read More/Less functionality
    const readMoreButtons = document.querySelectorAll('.read-more-btn');
    readMoreButtons.forEach(button => {
        button.addEventListener('click', (e) => {
            const postText = e.target.previousElementSibling;
            const fullText = postText.getAttribute('data-full-text') || postText.textContent;
            
            if (postText.classList.contains('expanded')) {
                // Collapse
                postText.textContent = fullText.slice(0, 160) + '...';
                postText.classList.remove('expanded');
                e.target.textContent = 'Read More';
            } else {
                // Expand
                postText.textContent = fullText;
                postText.classList.add('expanded');
                e.target.textContent = 'Read Less';
            }
        });
    });
});

// Modal functionality with smooth transitions
function openQuestionModal() {
    const modal = document.getElementById('questionModal');
    modal.style.display = 'flex';
    setTimeout(() => {
        modal.style.opacity = '1';
    }, 10);
}

function closeQuestionModal() {
    const modal = document.getElementById('questionModal');
    modal.style.opacity = '0';
    setTimeout(() => {
        modal.style.display = 'none';
    }, 300);
}

// Helper function to reset filters
function resetFilters() {
    document.getElementById("categoryFilter").value = "";
    document.getElementById("animalFilter").value = "";
    document.getElementById("searchBar").value = "";
    
    // Trigger filter application
    const event = new Event('change');
    document.getElementById("categoryFilter").dispatchEvent(event);
}


    </script>

</body>
</html> 