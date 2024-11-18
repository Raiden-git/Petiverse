<?php

include('../db.php');
include('session_check.php');


// Fetch posts
$post_result = $conn->query("SELECT * FROM posts ORDER BY created_at DESC");

// Fetch comments
$comment_result = $conn->query("SELECT * FROM comments ORDER BY created_at DESC");

// Fetch users
$user_result = $conn->query("SELECT * FROM users ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Petiverse - Community</title>
    <link rel="stylesheet" href="admin_sidebar.css">
    <script src="logout_js.js"></script>
    <style>
        table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 2rem;
}

table th, table td {
    border: 1px solid #ddd;
    padding: 0.75rem;
    text-align: left;
}

table th {
    background-color: #f4f4f9;
    color: #333;
}

table tr:nth-child(even) {
    background-color: #f9f9f9;
}

table tr:hover {
    background-color: #f1f1f1;
}

a {
    color: #4CAF50;
    text-decoration: none;
}

a:hover {
    text-decoration: underline;
}
/* Dropdown Menu Styles */
nav ul li .dropdown-menu {
    display: none;
    flex-direction: column;
    background-color: #444;
    position: relative;
    margin-top: 5px;
    border-radius: 5px;
    overflow: hidden;
}

nav ul li .dropdown-menu a {
    font-size: 1rem;
    padding: 10px 15px;
    background-color: #444;
    color: white;
}

nav ul li .dropdown-menu a:hover {
    background-color: #555;
}

/* Show Dropdown on Hover or Click */
nav ul li:hover .dropdown-menu {
    display: flex;
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
        <li class="dropdown">
            <a href="community_controls.php" class="dropdown-toggle">Community Controls</a>
            <ul class="dropdown-menu">
                <li><a href="#posts-section">Manage Posts</a></li>
                <li><a href="#comments-section">Manage Comments</a></li>
            </ul>
        </li>
        <li><a href="blog_management.php">Blog Management</a></li>
        <li><a href="lost_found_pets.php">Lost & Found Pets</a></li>
        <li><a href="special_events.php">Special Events</a></li>
        <li><a href="vet_management.php">Vet Management</a></li>
        <li><a href="moderator_management.php">Moderator Management</a></li>
        <li><a href="logout.php" onclick="return confirmLogout();">Logout</a></li>
    </ul>
</nav>

<main>
    <h2>Community Controls</h2>
    <!-- Add functionality for viewing, adding, and managing users here -->
    

    <section id="posts-section">
            <h3>Manage Posts</h3>
            <table>
                <thead>
                    <tr>
                        <th>Post ID</th>
                        <th>Title</th>
                        <th>Author</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($post = $post_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $post['id']; ?></td>
                            <td><?php echo htmlspecialchars($post['title']); ?></td>
                            <td><?php echo htmlspecialchars($post['user_id']); ?></td>
                            <td>
                                <a href="edit_post.php?post_id=<?php echo $post['id']; ?>">Edit</a>
                                <a href="delete_post.php?post_id=<?php echo $post['id']; ?>"
                                   onclick="return confirm('Are you sure you want to delete this post?');">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </section>

        <section id="comments-section">
            <h3>Manage Comments</h3>
            <table>
                <thead>
                    <tr>
                        <th>Comment ID</th>
                        <th>Content</th>
                        <th>Post ID</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($comment = $comment_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $comment['id']; ?></td>
                            <td><?php echo htmlspecialchars($comment['content']); ?></td>
                            <td><?php echo $comment['post_id']; ?></td>
                            <td>
                                <a href="delete_comment.php?comment_id=<?php echo $comment['id']; ?>">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </section>
</main>
</body>
</html>