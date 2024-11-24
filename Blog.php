<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require 'db.php';

// Handle blog deletion
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_blog_id'])) {
    $delete_blog_id = $_POST['delete_blog_id'];
    $delete_stmt = $conn->prepare("DELETE FROM user_blogs WHERE id = ? AND user_id = ?");
    $delete_stmt->bind_param("ii", $delete_blog_id, $_SESSION['user_id']);
    $delete_stmt->execute();
    $delete_stmt->close();
}

// Retrieve category and search term from URL or default to 'All Blogs' and empty string
$category = $_GET['category'] ?? 'all';
$search_term = $_GET['search'] ?? '';

// Prepare SQL based on category selection and search term
if ($category === 'all') {
    $query = "SELECT * FROM user_blogs WHERE status = 'approved'";
    $params = [];
    $types = '';
} else {
    $query = "SELECT * FROM user_blogs WHERE status = 'approved' AND category = ?";
    $params = [$category];
    $types = 's';
}

if ($search_term) {
    $query .= " AND (title LIKE ? OR content LIKE ?)";
    $params[] = '%' . $search_term . '%';
    $params[] = '%' . $search_term . '%';
    $types .= 'ss';
}

$query .= " ORDER BY created_at DESC";
$stmt = $conn->prepare($query);

if ($types) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

// Retrieve user's own blogs for the "My Blogs" section
$user_id = $_SESSION['user_id'];
$user_blogs_stmt = $conn->prepare("SELECT * FROM user_blogs WHERE user_id = ? ORDER BY created_at DESC");
$user_blogs_stmt->bind_param("i", $user_id);
$user_blogs_stmt->execute();
$user_blogs_result = $user_blogs_stmt->get_result();

// Handle blog creation
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['create_blog'])) {
    $user_name = $_SESSION['username'];
    $title = $_POST['title'];
    $content = $_POST['content'];
    $category = $_POST['category'];
    $photoData = null;

    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $photoData = file_get_contents($_FILES['photo']['tmp_name']);
    }

    $stmt = $conn->prepare("INSERT INTO user_blogs (user_id, user_name, title, content, category, photo) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssb", $user_id, $user_name, $title, $content, $category, $null);
    $stmt->send_long_data(5, $photoData);

    if ($stmt->execute()) {
        echo "<p>Blog post created successfully!</p>";
        header("Location: blog.php");
        exit();
    } else {
        echo "<p>Error: " . $stmt->error . "</p>";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog - Petiverse</title>
    <link rel="stylesheet" href="./assets/css/blog.css">
</head>
<body>

<?php include 'Cus-NavBar/navBar.php'; ?>

<nav class="navbar">
    <a href="blog.php?category=all">All Blogs</a>
    <a href="blog.php?category=Health">Health</a>
    <a href="blog.php?category=Entertainment">Entertainment</a>
    <a href="blog.php?category=Events">Events</a>
    <a href="blog.php?category=Wisdom">Wisdom</a>
    <a href="blog.php?category=Experience">Experience</a>



    <!-- Search form -->
    <form method="GET" action="blog.php" class="search-form">
        <input type="text" name="search" placeholder="Search blogs..." value="<?php echo htmlspecialchars($search_term); ?>">
        <input type="hidden" name="category" value="<?php echo htmlspecialchars($category); ?>">
        <button type="submit">Search</button>
    </form>
</nav>


<div class="hii">
<button onclick="document.getElementById('createBlog').style.display='block'; document.getElementById('overlay').style.display='block';" class="createblog">Create Blog</button>

    <button onclick="window.location.href='edit_blog.php';" class="myblogs">My Blogs</button>
</div>



<div class="blogs-container">
    <?php if ($result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="blog-card">

            <span class="username"><?php echo htmlspecialchars($row['user_name']); ?></span>
                <div class="blog-card-header">
                    
                    <div class="blog-photo">
                        <?php if ($row['photo']) : ?>
                            <img src="data:image/jpeg;base64,<?php echo base64_encode($row['photo']); ?>" alt="Blog Photo">
                        <?php else : ?>
                            <p>No Image</p>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="blog-card-details">
                    <span class="blog-date"><?php echo date('F j, Y', strtotime($row['created_at'])); ?></span>
                    <span class="blog-time"><?php echo date('g:i A', strtotime($row['created_at'])); ?></span>
                    <span class="blog-category">Category: <?php echo htmlspecialchars($row['category']); ?></span>
                </div>
                <div class="blog-card-content">
                    <h3 class="blog-title"><?php echo htmlspecialchars($row['title']); ?></h3>
                    <p class="blog-excerpt"><?php echo htmlspecialchars(substr($row['content'], 0, 100)) . '...'; ?></p>
                    <button class="read-more" onclick="openModal(
                        `<?php echo htmlspecialchars(addslashes($row['title'])); ?>`, 
                        `<?php echo htmlspecialchars(addslashes($row['content'])); ?>`, 
                        `<?php echo htmlspecialchars(addslashes($row['user_name'])); ?>`, 
                        `<?php echo date('F j, Y', strtotime($row['created_at'])); ?>`, 
                        `<?php echo $row['photo'] ? base64_encode($row['photo']) : ''; ?>`
                    )">Read more</button>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>No blogs found for in this category.</p>
    <?php endif; ?>
</div>

<div class="overlay" id="overlay" onclick="closePopup()"></div>

<div class="my-blogs-popup" id="createBlog">
    <form method="POST" enctype="multipart/form-data">
        <input type="text" name="title" placeholder="Title" required>
        <textarea name="content" placeholder="Content" required></textarea>
        <select name="category" required>
            <option value="">Select Category</option>
            <option value="Health">Health</option>
            <option value="Entertainment">Entertainment</option>
            <option value="Events">Events</option>
            <option value="Wisdom">Wisdom</option>
            <option value="Experience">Experience</option>
        </select>
        <input type="file" name="photo" accept="image/*">
        <button type="submit" name="create_blog">Create Blog</button>
    </form>
    <button onclick="closePopup()">Close</button>
</div>

<div class="modal" id="blogModal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h2 id="modalTitle"></h2>
        <img id="modalPhoto" src="" alt="Blog Photo" style="width:100%; max-height:300px; object-fit:cover;">
        <p id="modalContent"></p>
        <span class="modal-author"></span>
        <span class="modal-date"></span>
    </div>
</div>

<script>
function openModal(title, content, author, date, photo) {
    document.getElementById('modalTitle').innerText = title;
    document.getElementById('modalContent').innerText = content;
    document.getElementById('modalContent').style.whiteSpace = 'pre-wrap'; 
    document.querySelector('.modal-author').innerText = "By: " + author;
    document.querySelector('.modal-date').innerText = "Date: " + date;

    const modalPhoto = document.getElementById('modalPhoto');
    if (photo) {
        modalPhoto.src = "data:image/jpeg;base64," + photo;
        modalPhoto.style.display = 'block';
    } else {
        modalPhoto.style.display = 'none';
    }

    document.getElementById('blogModal').style.display = 'block';
}




function closeModal() {
    document.getElementById('blogModal').style.display = 'none';
}

function closePopup() {
    document.getElementById('createBlog').style.display = 'none';
    document.getElementById('overlay').style.display = 'none';
}
</script>
</body>
</html>


