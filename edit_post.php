<?php
include 'db.php';

// Start the session
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); 
    exit();
}

// Get the logged-in user's ID
$user_id = $_SESSION['user_id'];

// Get the post ID from the URL
$post_id = $_GET['post_id'] ?? 0;
$post_id = intval($post_id); // Sanitize input

// Fetch the post details
$sql = "SELECT * FROM posts WHERE id = $post_id AND user_id = $user_id";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    // Post does not exist or user doesn't have permission to edit it
    echo "Post not found or you don't have permission to edit this post.";
    exit();
}

$post = $result->fetch_assoc();

// Handle form submission to update the post
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $category = $_POST['category'];
    $pet_category = $_POST['pet_category'];

    // Update the post in the database
    $stmt = $conn->prepare("UPDATE posts SET title = ?, content = ?, category = ?, pet_category = ? WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ssssii", $title, $content, $category, $pet_category, $post_id, $user_id);

    if ($stmt->execute()) {
        // Redirect to the post details page after update
        header("Location: post_detail.php?post_id=" . $post_id);
        exit();
    } else {
        echo "Error updating post.";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Post - Petiverse</title>
    <style>
       
body {
    font-family: 'Arial', sans-serif;
    background-color: #f9f9f9; 
    color: #333; 
    margin: 0;
    padding: 0;
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
}

h1 {
    font-size: 2.5rem;
    color: #4CAF50; 
    text-align: center;
    margin-bottom: 1rem;
}

/* Form container */
form {
    background: linear-gradient(135deg, #6a9bfa, #a0d7f5); 
    padding: 2rem;
    border-radius: 10px;
    box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
    max-width: 500px;
    width: 100%;
}

/* Labels */
label {
    font-weight: bold;
    color: #fff;
    display: block;
    margin-bottom: 0.5rem;
}

/* Inputs and textarea */
input[type="text"],
textarea {
    width: 100%;
    padding: 0.8rem;
    margin-bottom: 1.5rem;
    border: 1px solid #ddd;
    border-radius: 5px;
    box-sizing: border-box;
    font-size: 1rem;
    background-color: #fff;
}

textarea {
    resize: vertical;
    min-height: 100px;
}

/* Submit button */
button[type="submit"] {
    display: block;
    width: 100%;
    padding: 0.8rem;
    background: #4CAF50; 
    border: none;
    border-radius: 5px;
    color: #fff; 
    font-size: 1rem;
    font-weight: bold;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

button[type="submit"]:hover {
    background: #3a8d41; 
}

/* Responsive design */
@media (max-width: 600px) {
    form {
        padding: 1.5rem;
    }

    h1 {
        font-size: 2rem;
    }
}

    </style>
</head>
<body>
    <h1>Edit Post</h1>
    <form action="" method="POST">
        <label for="title">Title:</label>
        <input type="text" name="title" id="title" value="<?php echo htmlspecialchars($post['title']); ?>" required>
        <br>

        <label for="content">Content:</label>
        <textarea name="content" id="content" required><?php echo htmlspecialchars($post['content']); ?></textarea>
        <br>

        <label for="category">Category:</label>
        <input type="text" name="category" id="category" value="<?php echo htmlspecialchars($post['category']); ?>" required>
        <br>

        <label for="pet_category">Pet Category:</label>
        <input type="text" name="pet_category" id="pet_category" value="<?php echo htmlspecialchars($post['pet_category']); ?>" required>
        <br>

        <button type="submit">Update Post</button>
    </form>
</body>
</html>

<?php $conn->close(); ?>
