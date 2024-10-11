<?php
// Database connection
include 'db.php';

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
    $stmt = $conn->prepare("INSERT INTO comments (post_id, content) VALUES (?, ?)");
    $stmt->bind_param("is", $post_id, $comment);
    $stmt->execute();
    $stmt->close();
    
    // Refresh to show the new comment
    header("Location: post_detail.php?post_id=" . $post_id);
    exit();
}

// Fetch comments for the post
$comments_sql = "SELECT * FROM comments WHERE post_id = $post_id ORDER BY created_at DESC";
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
            max-width: 600px;
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .post-container img {
            width: 100%;
            border-radius: 10px;
        }
        .post-detail h2, .post-detail p {
            margin: 10px 0;
        }
        .like-section {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .like-section button {
            background: none;
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
        }
        .comment p {
            margin: 0;
        }
        .comment .like-section {
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

    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Like post AJAX
            $('.like-post').on('click', function(e) {
                e.preventDefault();
                var postId = $(this).data('post-id');

                $.ajax({
                    url: 'like.php',
                    type: 'POST',
                    data: { type: 'post', id: postId },
                    success: function(response) {
                        var likes = parseInt($('#post-likes').text()) + 1;
                        $('#post-likes').text(likes + ' likes');
                    }
                });
            });

            // Like comment AJAX
            $('.like-comment').on('click', function(e) {
                e.preventDefault();
                var commentId = $(this).data('comment-id');

                $.ajax({
                    url: 'like.php',
                    type: 'POST',
                    data: { type: 'comment', id: commentId },
                    success: function(response) {
                        var likes = parseInt($('#comment-likes-' + commentId).text()) + 1;
                        $('#comment-likes-' + commentId).text(likes + ' likes');
                    }
                });
            });
        });
    </script>
</head>
<body>

<div class="page-container">
<!-- <header>
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
    </header> -->

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
