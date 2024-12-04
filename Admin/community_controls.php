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
    text-decoration: none;
}

table tr:nth-child(even) {
    background-color: #f9f9f9;
}



a {
    color: #4CAF50;
    text-decoration: none;
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

 
#posts-section .editbtn{
    background-color: #333;
color: white;
padding: 10px 15px;
border: none;
border-radius: 4px;
cursor: pointer;
font-size: 16px;
width: 40%;
margin-bottom: 15px;
text-decoration: none;
margin-right: 20px;
}

#posts-section .editbtn:hover{
    background-color: #555;
color: white;
padding: 10px 15px;
border: none;
border-radius: 4px;
cursor: pointer;
font-size: 16px;
width: 40%;
margin-bottom: 15px;
text-decoration: none;

}

#posts-section .delbtn{
    background-color: #ef4444;
color: white;
padding: 10px 15px;
border: none;
border-radius: 4px;
cursor: pointer;
font-size: 16px;
width: 40%;
margin-bottom: 15px;
text-decoration: none;
}


#posts-section .delbtn:hover{
    background-color: #dc2626;
color: white;
padding: 10px 15px;
border: none;
border-radius: 4px;
cursor: pointer;
font-size: 16px;
width: 40%;
margin-bottom: 15px;
text-decoration: none;
}


#comments-section .delbtn{
    background-color: #ef4444;
color: white;
padding: 10px 15px;
border: none;
border-radius: 4px;
cursor: pointer;
font-size: 16px;
width: 40%;
margin-bottom: 15px;
text-decoration: none;
}

#comments-section .delbtn:hover{
    background-color: #dc2626;
color: white;
padding: 10px 15px;
border: none;
border-radius: 4px;
cursor: pointer;
font-size: 16px;
width: 40%;
margin-bottom: 15px;
text-decoration: none;
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
    <h2>Community Controls</h2>

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
                                <a class="editbtn" href="edit_post.php?post_id=<?php echo $post['id']; ?>">Edit</a>
                                <a class="delbtn" href="delete_post.php?post_id=<?php echo $post['id']; ?>"
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
                                <a class="delbtn" href="delete_comment.php?comment_id=<?php echo $comment['id']; ?>">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </section>
</main>
</body>
</html>