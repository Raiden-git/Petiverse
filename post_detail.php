<?php
// Database connection
include 'db.php';

// Start session to track logged-in user
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Redirect to login page if not logged in
    exit();
}

// Get the logged-in user's ID
$user_id = $_SESSION['user_id'];

// Get post ID from URL
$post_id = $_GET['post_id'] ?? 0;
$post_id = intval($post_id); // Sanitize input

// Fetch post details
$sql = "SELECT * FROM posts WHERE id = $post_id";
$result = $conn->query($sql);
$post = $result->fetch_assoc(); 

// Handle like action (will be moved to AJAX)
if (isset($_POST['like_post'])) {
    $conn->query("UPDATE posts SET likes = likes + 1 WHERE id = $post_id");
}

// Handle comment submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['comment'])) {
    $comment = $_POST['comment'];
    
    // Insert comment into the database
    $stmt = $conn->prepare("INSERT INTO comments (post_id, content, user_id) VALUES (?, ?, ?)");
    $stmt->bind_param("isi", $post_id, $comment, $user_id);
    $stmt->execute();
    $stmt->close();
    
    // Refresh to show the new comment
    header("Location: post_detail.php?post_id=" . $post_id);
    exit();
}

// Fetch comments along with the username of the user who posted the comment
$comments_sql = "
    SELECT comments.*, 
       COALESCE(users.full_name, users.first_name) AS username 
    FROM comments 
    JOIN users ON comments.user_id = users.id 
    WHERE comments.post_id = $post_id 
    ORDER BY comments.created_at DESC
";
$comments_result = $conn->query($comments_sql);

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($post['title']); ?> - Petiverse</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f3f3f3;
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            
        }
        .page-container {
            display: flex;
            flex-direction: column;
            max-width: 800px;
            width: 100%;
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            width: 100%;
            box-sizing: border-box;
        }
        header nav ul {
            display: flex;
            list-style: none;
            margin: 0;
            padding: 0;
        }
        header nav ul li {
            position: relative;
            margin-right: 15px;
        }
        header nav ul li:last-child {
            margin-right: 0;
        }
        nav ul li a {
            color: white;
            text-decoration: none;
            padding: 10px 15px;
            display: block;
        }
        nav ul li a:hover {
            background-color: #555;
            border-radius: 5px;
        }
        .login a {
            color: white;
            padding: 10px 15px;
            text-decoration: none;
            background-color: #f04e45;
            border-radius: 5px;
        }

        .login a:hover {
            background-color: #d63b37;
        }
        .post-container {
            max-width: 100%;
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .post-container img {
            max-width: 100%;         /* Ensure image doesn't overflow */
            height: auto;            /* Keep image proportionate */
            border-radius: 10px;
            margin: 15px 0;
        }
        .post-detail h2, .post-detail p {
            word-wrap: break-word;   /* Break long words if needed */
            overflow-wrap: break-word;
            hyphens: auto;           /* Add hyphen when breaking words */
            max-width: 100%;         /* Ensure they don't exceed the container */
            margin-bottom: 10px;
            font-size: 16px;
            color: #333;
        }
        .post-detail strong {
            font-weight: bold;
        }
        .like-section {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .like-section button {
            background: transparent;
            border: none;
            cursor: pointer;
            font-size: 1.2rem;
        }
        .comment-section {
            margin-top: 20px;
        }
        .comment {
            margin-bottom: 15px;
            padding: 10px;
            background: #f9f9f9;
            border-radius: 8px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.1);
        }
        .comment p {
            word-wrap: break-word;   /* Break long words */
            overflow-wrap: break-word;
            max-width: 100%;
            margin-bottom: 8px;
            font-size: 14px;
        }
        .comment small {
            color: #999;
        }
        .comment .like-section {
            display: flex;
            align-items: center;
            margin-top: 5px;
        }
        form textarea, form button {
            width: 100%;
            padding: 10px;
            margin: 5px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .back-button {
            margin-bottom: 20px;
        }

        .back-button a {
            text-decoration: none;
            color: #f04e45;
            font-weight: bold;
            padding: 8px 12px;
            background-color: #ffffff;
            border: 2px solid #f04e45;
            border-radius: 5px;
            transition: 0.3s;
        }

        .back-button a:hover {
            background-color: #f04e45;
            color: #ffffff;
        }
        /* General overflow prevention for all sections */
        .post-container, .comment-section {
            overflow-x: hidden; /* Ensure no horizontal scroll due to overflow */
        }

    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
         $(document).ready(function() {
    $('.like-post').on('click', function(e) {
        e.preventDefault();
        var postId = $(this).data('post-id');

        $.ajax({
            url: 'like.php',
            type: 'POST',
            data: { type: 'post', id: postId },
            success: function(response) {
                if (response === 'liked') {
                    var likes = parseInt($('#post-likes').text()) + 1;
                    $('#post-likes').text(likes + ' likes');
                    $('.like-post').prop('disabled', true);  // Disable the button
                } else if (response === 'already liked') {
                    alert('You have already liked this post.');
                }
            }
        });
    });

    $('.like-comment').on('click', function(e) {
        e.preventDefault();
        var commentId = $(this).data('comment-id');

        $.ajax({
            url: 'like.php',
            type: 'POST',
            data: { type: 'comment', id: commentId },
            success: function(response) {
                if (response === 'liked') {
                    var likes = parseInt($('#comment-likes-' + commentId).text()) + 1;
                    $('#comment-likes-' + commentId).text(likes + ' likes');
                    $(this).prop('disabled', true);  // Disable the button
                } else if (response === 'already liked') {
                    alert('You have already liked this comment.');
                }
            }
        });
    });
});


    </script>
</head>
<body>

<div class="page-container">

    <div class="back-button">
        <a href="community.php">← Back to Community</a>
    </div>

    <div class="post-container">
        <section class="post-detail">
            <h2><?php echo htmlspecialchars($post['title']); ?></h2>
            <?php if (!empty($post['image'])): ?>
                <img src="<?php echo htmlspecialchars($post['image']); ?>" alt="Post Image">
            <?php endif; ?>
            <p><?php echo htmlspecialchars($post['content']); ?></p>
            <p><strong>Category:</strong> <?php echo htmlspecialchars($post['category']); ?></p>
            <p><strong>Pet Category:</strong> <?php echo htmlspecialchars($post['pet_category']); ?></p>
            <p><small>Posted on <?php echo date("Y-m-d | h:i A", strtotime($post['created_at'])); ?></small></p>
            
            <div class="like-section">
                <button class="like-post" data-post-id="<?php echo $post_id; ?>">❤️</button>
                <span id="post-likes"><?php echo $post['likes']; ?> likes</span>
            </div>
            <hr>
            
            <h3>Comments</h3>
            <div class="comment-section">
                <?php while ($comment = $comments_result->fetch_assoc()): ?>
                    <div class="comment">
                        <p><strong><?php echo htmlspecialchars($comment['username']); ?></strong></p> <!-- Display username -->
                        <p><?php echo htmlspecialchars($comment['content']); ?></p>
                        <small><?php echo date("Y-m-d | h:i A", strtotime($comment['created_at'])); ?></small>
                        <div class="like-section">
                            <button class="like-comment" data-comment-id="<?php echo $comment['id']; ?>">❤️</button>
                            <span id="comment-likes-<?php echo $comment['id']; ?>"><?php echo $comment['likes']; ?> likes</span>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>

            <form action="" method="POST">
                <textarea name="comment" placeholder="Add your comment..." required></textarea>
                <button type="submit">Post Comment</button>
            </form>
        </section>
</div>
</div>
</body>
</html>

<?php $conn->close(); ?>
