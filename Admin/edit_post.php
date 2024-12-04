

<?php
include('../db.php');
include('session_check.php');

// Get the post ID from the URL
$post_id = $_GET['post_id'] ?? 0;
$post_id = intval($post_id); 

// Fetch the post details
$sql = "SELECT * FROM posts WHERE id = $post_id";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    echo "Post not found.";
    exit();
}

$post = $result->fetch_assoc();

// Handle form submission for updating the post
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $category = $_POST['category'];
    $pet_category = $_POST['pet_category'];

    // Update the post in the database
    $stmt = $conn->prepare("UPDATE posts SET title = ?, content = ?, category = ?, pet_category = ? WHERE id = ?");
    $stmt->bind_param("ssssi", $title, $content, $category, $pet_category, $post_id);

    if ($stmt->execute()) {
        header("Location: community_controls.php#posts-section");
        exit();
    } else {
        echo "Error updating post.";
    }

    $stmt->close();
}
?>


<!DOCTYPE html>
<html>
<head>
    <title>Petiverse - Edit Community post</title>
    <link rel="stylesheet" href="admin_sidebar.css">
    <script src="logout_js.js"></script>
    <style>

                /* General Reset */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Arial', sans-serif;
}



/* Container for the Edit Post Form */
.edit-post-container {
    background: #fff;
    border-radius: 12px;
    padding: 30px 40px;
    box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
    width: 600px;
    max-width: 90%;
    border: 2px solid #e0e0e0;
    margin-left: 300px;
}

/* Form Header */
.edit-post-container h1 {
    font-size: 3rem;
    color: black;
    margin-bottom: 20px;
    text-align: center;
}

/* Form Labels */
.edit-post-container label {
    font-size: 1rem;
    color: #555;
    margin-bottom: 8px;
    display: block;
}

/* Input Fields */
.edit-post-container input[type="text"],
.edit-post-container textarea {
    width: 100%;
    padding: 12px 15px;
    font-size: 1rem;
    border: 1px solid #ccc;
    border-radius: 8px;
    outline: none;
    margin-bottom: 20px;
    transition: border-color 0.3s ease, box-shadow 0.3s ease;
    background: #f9f9f9;
}

.edit-post-container input[type="text"]:focus,
.edit-post-container textarea:focus {
    border-color: #4CAF50;
    box-shadow: 0 0 8px rgba(76, 175, 80, 0.2);
}

/* Textarea Specific Styling */
.edit-post-container textarea {
    resize: none;
    height: 120px;
}
/* General Select Styling */
.edit-post-container select {
    width: 100%;
    padding: 12px 15px;
    font-size: 1rem;
    color: #555;
    border: 1px solid #ccc;
    border-radius: 8px;
    outline: none;
    margin-bottom: 20px;
    background: #f9f9f9;
    appearance: none; 
    cursor: pointer;
    transition: border-color 0.3s ease, box-shadow 0.3s ease;
}

/* Focus State for Select */
.edit-post-container select:focus {
    border-color: #4CAF50;
    box-shadow: 0 0 8px rgba(76, 175, 80, 0.2);
}

/* Optional Styling for Select Dropdown Arrow */
.edit-post-container select::-ms-expand {
    display: none; 
}

/* Optional Styling for the Options */
.edit-post-container option {
    font-size: 1rem;
    color: #333;
    background-color: #fff;
    padding: 10px;
}


/* Submit Button */
.edit-post-container button {
    width: 100%;
    padding: 12px 20px;
    font-size: 1rem;
    color: #fff;
    background-color: #333;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: background-color 0.3s ease, transform 0.2s ease;
}

.edit-post-container button:hover {
    background-color: #555;
    transform: scale(1.02);
}

.edit-post-container button:active {
    transform: scale(0.98);
}

/* Responsive Design */
@media (max-width: 600px) {
    .edit-post-container {
        padding: 20px;
    }

    .edit-post-container h1 {
        font-size: 1.5rem;
    }
}


    </style>
</head>
<body>
<header>
    <h1 class="text-center mt-4">Community Management</h1>
</header>

<nav>
    <ul>
        <li><a href="dashboard.php">Home</a></li>
        <li><a href="user_management.php">User Management</a></li>
        <li><a href="shop_management.php">Shop Management</a></li>
        <li><a href="community_controls.php">Community Controls</a></li>
        <li><a href="blog_management.php">Blog Management</a></li>
        <li><a href="admin_daycare_management.php">Daycare Management</a></li>
        <li><a href="lost_found_pets.php">Lost & Found Pets</a></li>
        <li><a href="special_events.php">Special Events</a></li>
        <li><a href="vet_management.php">Vet Management</a></li>
        <li><a href="moderator_management.php">Moderator Management</a></li>
        <li><a href="petselling.php">Pet selling</a><li>        
        <li><a href="view_feedback.php">Feedbacks</a></li>
        <li><a href="logout.php" onclick="return confirmLogout();">Logout</a></li>
    </ul>
</nav>

<main>

<div class="edit-post-container">
    <h1>Edit Post</h1>
    <form action="" method="POST">
        <label for="title">Title:</label>
        <input type="text" name="title" id="title" value="<?php echo htmlspecialchars($post['title']); ?>" required>
        <br>
        <label for="content">Content:</label>
        <textarea name="content" id="content" required><?php echo htmlspecialchars($post['content']); ?></textarea>
        <br>
        <label for="category">Category:</label>
        <select name="category" id="category" required>
            <option value="health" <?php echo ($post['category'] === 'health') ? 'selected' : ''; ?>>Health</option>
            <option value="training" <?php echo ($post['category'] === 'training') ? 'selected' : ''; ?>>Training</option>
            <option value="pet-stories" <?php echo ($post['category'] === 'pet-stories') ? 'selected' : ''; ?>>Pet Stories</option>
            <option value="food" <?php echo ($post['category'] === 'food') ? 'selected' : ''; ?>>Food</option>
            <option value="other" <?php echo ($post['category'] === 'other') ? 'selected' : ''; ?>>Other</option>
        </select>

        <br>
        <label for="pet_category">Pet Category:</label>
        <select name="pet_category" id="pet_category" required>
            <option value="dog" <?php echo ($post['pet_category'] === 'dog') ? 'selected' : ''; ?>>Dog</option>
            <option value="cat" <?php echo ($post['pet_category'] === 'cat') ? 'selected' : ''; ?>>Cat</option>
            <option value="bird" <?php echo ($post['pet_category'] === 'bird') ? 'selected' : ''; ?>>Bird</option>
            <option value="rabbit" <?php echo ($post['pet_category'] === 'rabbit') ? 'selected' : ''; ?>>Rabbit</option>
            <option value="hamster" <?php echo ($post['pet_category'] === 'hamster') ? 'selected' : ''; ?>>Hamster</option>
            <option value="guinea pig" <?php echo ($post['pet_category'] === 'guinea pig') ? 'selected' : ''; ?>>Guinea Pig</option>
            <option value="fish" <?php echo ($post['pet_category'] === 'fish') ? 'selected' : ''; ?>>Fish</option>
            <option value="reptile" <?php echo ($post['pet_category'] === 'reptile') ? 'selected' : ''; ?>>Reptile</option>
            <option value="horse" <?php echo ($post['pet_category'] === 'horse') ? 'selected' : ''; ?>>Horse</option>
            <option value="ferret" <?php echo ($post['pet_category'] === 'ferret') ? 'selected' : ''; ?>>Ferret</option>
            <option value="tarantla" <?php echo ($post['pet_category'] === 'tarantla') ? 'selected' : ''; ?>>Tarantula</option>
            <option value="frog" <?php echo ($post['pet_category'] === 'frog') ? 'selected' : ''; ?>>Frog</option>
            <option value="tortoise" <?php echo ($post['pet_category'] === 'tortoise') ? 'selected' : ''; ?>>Tortoise</option>
            <option value="chicken" <?php echo ($post['pet_category'] === 'chicken') ? 'selected' : ''; ?>>Chicken</option>
            <option value="duck" <?php echo ($post['pet_category'] === 'duck') ? 'selected' : ''; ?>>Duck</option>
            <option value="goat" <?php echo ($post['pet_category'] === 'goat') ? 'selected' : ''; ?>>Goat</option>
            <option value="pig" <?php echo ($post['pet_category'] === 'pig') ? 'selected' : ''; ?>>Pig</option>
            <option value="insect" <?php echo ($post['pet_category'] === 'insect') ? 'selected' : ''; ?>>Insect</option>
            <option value="other" <?php echo ($post['pet_category'] === 'other') ? 'selected' : ''; ?>>Other</option>
        </select>
        <br>
        <button type="submit">Update Post</button>
    </form>
</div>

</main>
</body>
</html>