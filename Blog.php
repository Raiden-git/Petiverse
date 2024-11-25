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
    <!-- <link rel="stylesheet" href="./assets/css/blog.css"> -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
     /* Modern Design Variables */
    :root {
        --primary-color: #DA8359;
        --primary-hover: #ac6440;
        --secondary-color: #f8fafc;
        --accent-color: #0f172a;
        --text-color: #334155;
        --border-radius: 16px;
        --box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
        --transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }

    body {
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
        background-color: #f6f3e8;
        color: var(--text-color);
        line-height: 1.6;
        margin: 0;
        padding: 0;
    }

    /* Modern Navigation */
    .navbar {
        background: rgba(255, 255, 255, 0.8);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        padding: 1rem 2rem;
        box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1);
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 1.5rem;
        position: sticky;
        top: 0;
        z-index: 100;
    }

    .navbar a {
        text-decoration: none;
        color: var(--text-color);
        padding: 0.5rem 1rem;
        border-radius: 9999px;
        transition: var(--transition);
        font-weight: 500;
        font-size: 0.95rem;
    }

    .navbar a:hover {
        background: var(--secondary-color);
        color: var(--primary-color);
    }

    .search-form {
        margin-left: auto;
        display: flex;
        gap: 0.5rem;
    }

    .search-form input {
        padding: 0.75rem 1.5rem;
        border: 1px solid #e2e8f0;
        border-radius: 9999px;
        min-width: 240px;
        background: #f8fafc;
        transition: var(--transition);
    }

    .search-form input:focus {
        outline: none;
        border-color: var(--primary-color);
        background: white;
    }

    .search-form button {
        background: var(--primary-color);
        color: white;
        border: none;
        padding: 0.75rem 1.5rem;
        border-radius: 9999px;
        cursor: pointer;
        transition: var(--transition);
        font-weight: 500;
    }

    .search-form button:hover {
        background: var(--primary-hover);
        transform: translateY(-1px);
    }

    /* Blog Controls - Repositioned to Right */
    .hii {
        display: flex;
        justify-content: flex-end;
        gap: 1rem;
        padding: 2rem 2rem;
        max-width: 1400px;
        margin: 0 auto;
    }

    .createblog, .myblogs {
        background: var(--primary-color);
        color: white;
        border: none;
        padding: 0.75rem 1.5rem;
        border-radius: 9999px;
        font-size: 0.95rem;
        cursor: pointer;
        transition: var(--transition);
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .createblog {
        background: var(--primary-color);
    }

    .myblogs {
        background: white;
        color: var(--text-color);
        border: 1px solid #e2e8f0;
    }

    .createblog:hover, .myblogs:hover {
        transform: translateY(-2px);
        box-shadow: var(--box-shadow);
    }

    .myblogs:hover {
        background: var(--secondary-color);
    }

    /* Modern Blog Cards */
    .blogs-container {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 2rem;
        padding: 1rem 2rem 3rem;
        max-width: 1400px;
        margin: 0 auto;
    }

    .blog-card {
        background: white;
        border-radius: var(--border-radius);
        overflow: hidden;
        box-shadow: var(--box-shadow);
        transition: var(--transition);
        border: 1px solid #e2e8f0;
    }

    .blog-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
    }

    .blog-photo {
        height: 220px;
        overflow: hidden;
    }

    .blog-photo img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: var(--transition);
    }

    .username {
        position: absolute;
        top: 1rem;
        left: 1rem;
        background: rgba(255, 255, 255, 0.95);
        padding: 0.5rem 1rem;
        border-radius: 9999px;
        font-weight: 500;
        font-size: 0.9rem;
        z-index: 1;
        box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1);
    }

    .blog-card-details {
        padding: 1.25rem;
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        font-size: 0.875rem;
        color: #64748b;
        border-bottom: 1px solid #f1f5f9;
    }

    .blog-card-content {
        padding: 1.5rem;
    }

    .blog-title {
        margin: 0 0 1rem 0;
        font-size: 1.25rem;
        color: var(--accent-color);
        font-weight: 600;
        line-height: 1.4;
    }

    .blog-excerpt {
        color: #64748b;
        margin-bottom: 1.5rem;
        font-size: 0.95rem;
        line-height: 1.6;
    }

    .read-more {
        background: white;
        color: var(--primary-color);
        border: 1px solid #e2e8f0;
        padding: 0.75rem 1.5rem;
        border-radius: 9999px;
        cursor: pointer;
        transition: var(--transition);
        font-weight: 500;
        font-size: 0.95rem;
    }

    .read-more:hover {
        background: var(--primary-color);
        color: white;
        border-color: var(--primary-color);
    }

    /* Modern Modal Design */
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.3);
        backdrop-filter: blur(4px);
        z-index: 1000;
    }

    .modal-content {
        background: white;
        width: 90%;
        max-width: 800px;
        margin: 2rem auto;
        padding: 2.5rem;
        border-radius: var(--border-radius);
        max-height: 85vh;
        overflow-y: auto;
        position: relative;
        box-shadow: 0 25px 50px -12px rgb(0 0 0 / 0.25);
    }

    .close {
        position: absolute;
        right: 1.5rem;
        top: 1.5rem;
        font-size: 1.5rem;
        cursor: pointer;
        color: #64748b;
        transition: var(--transition);
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
    }

    .close:hover {
        background: #f1f5f9;
        color: var(--accent-color);
    }

    /* Modern Create Blog Form */
    .my-blogs-popup {
        display: none;
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: white;
        padding: 2.5rem;
        border-radius: var(--border-radius);
        width: 90%;
        max-width: 600px;
        z-index: 1001;
        box-shadow: 0 25px 50px -12px rgb(0 0 0 / 0.25);
    }

    .my-blogs-popup form {
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
    }

    .my-blogs-popup input[type="text"],
    .my-blogs-popup textarea,
    .my-blogs-popup select {
        padding: 0.875rem 1.25rem;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        font-size: 0.95rem;
        transition: var(--transition);
        background: #f8fafc;
    }

    .my-blogs-popup input[type="text"]:focus,
    .my-blogs-popup textarea:focus,
    .my-blogs-popup select:focus {
        outline: none;
        border-color: var(--primary-color);
        background: white;
    }

    .my-blogs-popup textarea {
        min-height: 200px;
        resize: vertical;
    }

    .my-blogs-popup button {
        background: var(--primary-color);
        color: white;
        border: none;
        padding: 0.875rem;
        border-radius: 12px;
        cursor: pointer;
        transition: var(--transition);
        font-weight: 500;
        font-size: 0.95rem;
    }

    .my-blogs-popup button:hover {
        background: var(--primary-hover);
    }

    .overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.3);
        backdrop-filter: blur(4px);
        z-index: 1000;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .navbar {
            padding: 1rem;
        }
        
        .search-form {
            width: 100%;
            margin-top: 1rem;
        }
        
        .search-form input {
            flex: 1;
        }
        
        .blogs-container {
            padding: 1rem;
            grid-template-columns: 1fr;
        }
        
        .modal-content {
            width: 95%;
            margin: 1rem auto;
            padding: 1.5rem;
        }
        
        .hii {
            padding: 1rem;
        }
    }
    </style>
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
    <button onclick="window.location.href='edit_blog.php';" class="myblogs">
        <i class="fas fa-user"></i> My Blogs
    </button>
    <button onclick="document.getElementById('createBlog').style.display='block'; document.getElementById('overlay').style.display='block';" class="createblog">
        <i class="fas fa-plus"></i> Create Blog
    </button>
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

<?php include 'footer.php'; ?>

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


