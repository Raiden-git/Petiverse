<?php
include './db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if the user is not logged in
    header('Location: login.php');
    exit();
}

$userId = $_SESSION['user_id']; // Get the logged-in user's ID

// Handle delete request
if (isset($_POST['delete'])) {
    $postId = $_POST['post_id'];
    $deleteSql = "DELETE FROM lost_and_found_pets WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($deleteSql);
    $stmt->bind_param('ii', $postId, $userId);
    if ($stmt->execute()) {
        $message = "Post deleted successfully!";
    } else {
        $message = "Failed to delete the post.";
    }
}

// Fetch user's posts
$sql = "SELECT id, pet_name, pet_type, description, location, status, date, image FROM lost_and_found_pets WHERE user_id = ? ORDER BY date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Posts - Lost and Found</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="./assets/css/lost_found.css">
    <style>
        .myposts-section {
            padding: 20px;
            max-width: 800px;
            margin: auto;
        }
        .mypost-card {
            border: 1px solid #ccc;
            border-radius: 8px;
            margin-bottom: 20px;
            padding: 10px;
        }
        .mypost-card img {
            width: 100%;
            height: auto;
            border-radius: 8px;
        }
        .mypost-card button {
            margin: 5px;
        }
        <style>
    .myposts-section {
        padding: 20px;
        max-width: 1000px;
        margin: auto;
    }

    /* Grid view for posts */
    .myposts-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 20px;
    }

    .mypost-card {
        border: 1px solid #ccc;
        border-radius: 8px;
        padding: 15px;
        background-color: #f9f9f9;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    }

    .mypost-card:hover {
        transform: scale(1.02);
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
    }

    <style>
    .mypost-card img {
        width: 100%; /* Ensure the image fits within the container's width */
        max-height: 200px; /* Limit the height for consistency */
        object-fit: contain; /* Show the full image without cropping */
        border-radius: 8px; /* Keep rounded corners for a clean look */
        background-color: #f0f0f0; /* Optional: Add a light background color for empty space */
        margin-bottom: 10px;
    }


    .mypost-card h3 {
        font-size: 1.2rem;
        margin: 10px 0;
        color: #333;
    }

    .mypost-card p {
        font-size: 0.9rem;
        margin: 5px 0;
        color: #555;
    }

    /* Style for Edit and Delete buttons */
    .mypost-card button {
        padding: 10px 20px;
        border: none;
        border-radius: 5px;
        font-size: 0.9rem;
        cursor: pointer;
        transition: background-color 0.3s ease, transform 0.2s ease;
    }

    .mypost-card button:hover {
        transform: translateY(-2px);
    }

    .mypost-card button:active {
        transform: scale(0.95);
    }

    /* Specific styles for Edit button */
    .mypost-card button[type="submit"]:nth-of-type(1) {
        background-color: #4caf50;
        color: white;
    }

    .mypost-card button[type="submit"]:nth-of-type(1):hover {
        background-color: #45a049;
    }

    /* Specific styles for Delete button */
    .mypost-card button[name="delete"] {
        background-color: #f44336;
        color: white;
    }

    .mypost-card button[name="delete"]:hover {
        background-color: #d32f2f;
    }

    /* Footer styling */
    footer {
        text-align: center;
        padding: 20px;
        background-color: #333;
        color: white;
        margin-top: 20px;
    }
</style>

    </style>
</head>
<body>
<?php include './Cus-NavBar/navBar.php'; ?> <!-- Include navigation bar -->

<section class="myposts-section">
    <h2>My Posts</h2>
    <?php if (isset($message)): ?>
        <p><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <?php if ($result->num_rows > 0): ?>
        <?php while ($post = $result->fetch_assoc()): ?>
            <div class="mypost-card">
                <?php if (!empty($post['image'])): ?>
                    <img src="data:image/jpeg;base64,<?php echo base64_encode($post['image']); ?>" alt="<?php echo htmlspecialchars($post['pet_name']); ?>">
                <?php else: ?>
                    <img src="assets/img/placeholder.jpg" alt="No image available">
                <?php endif; ?>
                <h3><?php echo htmlspecialchars($post['pet_name']); ?></h3>
                <p><strong>Type:</strong> <?php echo htmlspecialchars($post['pet_type']); ?></p>
                <p><strong>Status:</strong> <?php echo htmlspecialchars($post['status']); ?></p>
                <p><strong>Location:</strong> <?php echo htmlspecialchars($post['location']); ?></p>
                <p><strong>Date:</strong> <?php echo htmlspecialchars($post['date']); ?></p>
                <p><strong>Description:</strong> <?php echo nl2br(htmlspecialchars($post['description'])); ?></p>
                <form method="GET" action="lost_found_edit_post.php" style="display: inline-block;">
                 <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                <button type="submit">Edit</button>
            </form>

                <form method="POST" style="display: inline-block;">
                    <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                    <button type="submit" name="delete" onclick="return confirm('Are you sure you want to delete this post?')">Delete</button>
                </form>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>You have no posts.</p>
    <?php endif; ?>
</section>

<footer>
    <div class="footer-content">
        <p>&copy; 2024 Petiverse. All Rights Reserved.</p>
    </div>
</footer>
</body>
</html>
