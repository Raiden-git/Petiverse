<?php
include('../db.php');
include('session_check.php');

// Handle blog approval, rejection, or deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['blog_id'])) {
    $blog_id = $_POST['blog_id'];

    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'approve') {
            $status = 'approved';
        } elseif ($_POST['action'] === 'reject') {
            $status = 'rejected';
        } elseif ($_POST['action'] === 'delete') {
            $stmt = $conn->prepare("DELETE FROM user_blogs WHERE id = ?");
            $stmt->bind_param("i", $blog_id);
            $stmt->execute();
            header("Location: blog_management.php"); 
            exit();
        }

        if (isset($status)) {
            $stmt = $conn->prepare("UPDATE user_blogs SET status = ? WHERE id = ?");
            $stmt->bind_param("si", $status, $blog_id);
            $stmt->execute();
        }
    } elseif (isset($_POST['edit'])) {
        // This block is for updating blog details
        $title = $_POST['title'];
        $content = $_POST['content'];
        $category = $_POST['category'];
        $photo = null;

        // Handle photo upload
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] == UPLOAD_ERR_OK) {
            $photo = file_get_contents($_FILES['photo']['tmp_name']);
        }

        $stmt = $conn->prepare("UPDATE user_blogs SET title = ?, content = ?, category = ?, photo = ? WHERE id = ?");
        $stmt->bind_param("sssii", $title, $content, $category, $photo, $blog_id);
        $stmt->execute();
    }
}

// Retrieve all pending blog posts
$stmt_pending = $conn->prepare("SELECT * FROM user_blogs WHERE status = 'pending' ORDER BY created_at DESC");
$stmt_pending->execute();
$result_pending = $stmt_pending->get_result();

// Retrieve all approved blog posts
$stmt_approved = $conn->prepare("SELECT DISTINCT category FROM user_blogs WHERE status = 'approved'");
$stmt_approved->execute();
$categories_result = $stmt_approved->get_result();

// Handle category filtering
$selected_category = 'all';
if (isset($_GET['category'])) {
    $selected_category = $_GET['category'];
}

// Query approved blog posts based on selected category
if ($selected_category === 'all') {
    $stmt_approved_posts = $conn->prepare("SELECT * FROM user_blogs WHERE status = 'approved' ORDER BY created_at DESC");
} else {
    $stmt_approved_posts = $conn->prepare("SELECT * FROM user_blogs WHERE status = 'approved' AND category = ? ORDER BY created_at DESC");
    $stmt_approved_posts->bind_param("s", $selected_category);
}

$stmt_approved_posts->execute();
$result_approved_posts = $stmt_approved_posts->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Petiverse - Blog Management</title>
    <link rel="stylesheet" href="admin_sidebar.css">
    <link rel="stylesheet" href="../assets/css/blog_management.css">
    <script src="logout_js.js"></script>
    <style>
        .createblog{
            background-color: #333;
color: white;
padding: 10px 15px;
border: none;
border-radius: 4px;
cursor: pointer;
font-size: 16px;
width: 15%;
margin-bottom: 15px;
text-decoration: none;
display: block;
        }

.createblog:hover{
    background-color: #555;
color: white;
padding: 10px 15px;
border: none;
border-radius: 4px;
cursor: pointer;
font-size: 16px;
width: 15%;
margin-bottom: 15px;
text-decoration: none;
display: block;
}


form .approvebtn{
background-color: #333;
color: white;
padding: 10px 15px;
border: none;
border-radius: 4px;
cursor: pointer;
font-size: 16px;
width: 100%;
margin-bottom: 15px;
text-decoration: none;
display: block;
}

form .approvebtn:hover{
background-color: #555;
color: white;
padding: 10px 15px;
border: none;
border-radius: 4px;
cursor: pointer;
font-size: 16px;
width: 100%;
margin-bottom: 15px;
text-decoration: none;
display: block;
}


form .rejectbtn{
    background-color: #ef4444;
color: white;
padding: 10px 15px;
border: none;
border-radius: 4px;
cursor: pointer;
font-size: 16px;
width: 100%;
margin-bottom: 15px;
text-decoration: none;
display: block;
}


form .rejectbtn:hover{
    background-color: #dc2626;
color: white;
padding: 10px 15px;
border: none;
border-radius: 4px;
cursor: pointer;
font-size: 16px;
width: 100%;
margin-bottom: 15px;
text-decoration: none;
display: block;
}

form .edit-button{
    background-color: #333;
color: white;
padding: 10px 15px;
border: none;
border-radius: 4px;
cursor: pointer;
font-size: 16px;
width: 100%;
margin-bottom: 15px;
text-decoration: none;
display: block;
}


form .edit-button:hover{
    background-color: #555;
color: white;
padding: 10px 15px;
border: none;
border-radius: 4px;
cursor: pointer;
font-size: 16px;
width: 100%;
margin-bottom: 15px;
text-decoration: none;
display: block;
}


form .rejected-button{
    background-color: #ef4444;
color: white;
padding: 10px 15px;
border: none;
border-radius: 4px;
cursor: pointer;
font-size: 16px;
width: 100%;
margin-bottom: 15px;
text-decoration: none;
display: block;
}

form .rejected-button:hover{
    background-color: #dc2626;
color: white;
padding: 10px 15px;
border: none;
border-radius: 4px;
cursor: pointer;
font-size: 16px;
width: 100%;
margin-bottom: 15px;
text-decoration: none;
display: block;
}

    </style>
</head>
<body>
<header>
    <h1>Blog Management</h1>
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

<a href="../Admin/admin_add_blog.php" ><button class="createblog">Create Blogs</button></a>




    <div class="section">
        <h2>Pending Blog Posts for Approval</h2>
        <div class="blogs-container">
            <?php if ($result_pending->num_rows > 0): ?>
                <?php while ($row = $result_pending->fetch_assoc()): ?>
                    <div class="blog-post">
                        <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                        <p><strong>Username:</strong> <?php echo htmlspecialchars($row['user_name']); ?></p>
                        <p><strong>Content:</strong> <?php echo htmlspecialchars($row['content']); ?></p>
                        <p><strong>Category:</strong> <?php echo htmlspecialchars($row['category']); ?></p>
                        <p><strong>Date:</strong> <?php echo htmlspecialchars(date('Y-m-d H:i:s', strtotime($row['created_at']))); ?></p>
                        <?php if (!empty($row['photo'])): ?>
                            <p><strong>Photo:</strong></p>
                            <img src="data:image/jpeg;base64,<?php echo base64_encode($row['photo']); ?>" alt="Blog Photo">
                        <?php endif; ?>
                        <form method="POST" action="" style="display: inline;">
                            <input type="hidden" name="blog_id" value="<?php echo $row['id']; ?>">
                            <button class="approvebtn" type="submit" name="action" value="approve">Approve</button>
                            <button class="rejectbtn" type="submit" name="action" value="reject" class="rejected-button">Reject</button>
                        </form>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No pending blog posts.</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="section">
        <h2>Approved Blog Posts</h2>

        <!-- Category Filter -->
        <div class="filter">
            <form method="GET" action="">
                <label for="category">Filter by Category:</label>
                <select name="category" id="category" onchange="this.form.submit()">
                    <option value="all" <?php echo $selected_category === 'all' ? 'selected' : ''; ?>>All Categories</option>
                    <?php while ($cat = $categories_result->fetch_assoc()): ?>
                        <option value="<?php echo $cat['category']; ?>" <?php echo $selected_category === $cat['category'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat['category']); ?></option>
                    <?php endwhile; ?>
                </select>
            </form>
        </div>

        <div class="blogs-container">
            <?php if ($result_approved_posts->num_rows > 0): ?>
                <?php while ($row = $result_approved_posts->fetch_assoc()): ?>
                    <div class="blog-post">
                        <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                        <?php if (!empty($row['photo'])): ?>
                            <p><strong>Photo:</strong></p>
                            <img src="data:image/jpeg;base64,<?php echo base64_encode($row['photo']); ?>" alt="Blog Photo">
                        <?php endif; ?>
                        <p><strong>Username:</strong> <?php echo htmlspecialchars($row['user_name']); ?></p>
                        <p><strong>Content:</strong> <?php echo htmlspecialchars($row['content']); ?></p>
                        <p><strong>Category:</strong> <?php echo htmlspecialchars($row['category']); ?></p>
                        <p><strong>Date:</strong> <?php echo htmlspecialchars(date('Y-m-d H:i:s', strtotime($row['created_at']))); ?></p>

                        <form method="POST" action="">
                            <input type="hidden" name="blog_id" value="<?php echo $row['id']; ?>">
                            <button type="button" class="edit-button" onclick="toggleEditForm(<?php echo $row['id']; ?>)">Edit</button>
                            <button type="submit" name="action" value="delete" class="rejected-button">Delete</button>
                        </form>
                        <div class="edit-form" id="edit-form-<?php echo $row['id']; ?>">
                            <h4>Edit Blog Post</h4>
                            <form method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="blog_id" value="<?php echo $row['id']; ?>">
                                <label for="title">Title:</label>
                                <input type="text" name="title" value="<?php echo htmlspecialchars($row['title']); ?>" required>
                                <label for="content">Content:</label>
                                <textarea name="content" required><?php echo htmlspecialchars($row['content']); ?></textarea>
                                <label for="category">Category:</label>
                                <select name="category" required>
                                    <option value="Health" <?php echo $row['category'] === 'Health' ? 'selected' : ''; ?>>Health</option>
                                    <option value="Entertainment" <?php echo $row['category'] === 'Entertainment' ? 'selected' : ''; ?>>Entertainment</option>
                                    <option value="Events" <?php echo $row['category'] === 'Events' ? 'selected' : ''; ?>>Events</option>
                                    <option value="Wisdom" <?php echo $row['category'] === 'Wisdom' ? 'selected' : ''; ?>>Wisdom</option>
                                    <option value="Experience" <?php echo $row['category'] === 'Experience' ? 'selected' : ''; ?>>Experience</option>
                                </select>
                                <label for="photo">Upload New Photo:</label>
                                <input type="file" name="photo">
                                <button type="submit" name="edit">Update Blog Post</button>
                            </form>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No approved blog posts.</p>
            <?php endif; ?>
        </div>
    </div>
</main>

<script>
    function toggleEditForm(blogId) {
        const form = document.getElementById('edit-form-' + blogId);
        form.style.display = form.style.display === "none" || form.style.display === "" ? "block" : "none";
    }
</script>

</body>
</html>

<?php
$stmt_pending->close();
$stmt_approved->close();
$stmt_approved_posts->close();
$conn->close();
?>
