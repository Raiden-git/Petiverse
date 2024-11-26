<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require 'db.php';

$user_id = $_SESSION['user_id'];

// Handle blog update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_blog'])) {
    $blog_id = $_POST['blog_id'];
    $title = $_POST['title'];
    $content = $_POST['content'];
    $category = $_POST['category'];

    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $photoData = file_get_contents($_FILES['photo']['tmp_name']);
        $stmt = $conn->prepare("UPDATE user_blogs SET title = ?, content = ?, category = ?, photo = ? WHERE id = ? AND user_id = ?");
        $null = NULL;
        $stmt->bind_param("sssibi", $title, $content, $category, $null, $blog_id, $user_id);
        $stmt->send_long_data(3, $photoData);
    } else {
        $stmt = $conn->prepare("UPDATE user_blogs SET title = ?, content = ?, category = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param("sssii", $title, $content, $category, $blog_id, $user_id);
    }

    if ($stmt->execute()) {
        header("Location: blog.php");
        exit();
    } else {
        echo "<p>Error: " . $stmt->error . "</p>";
    }

    $stmt->close();
}

// Handle blog deletion
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_blog_id'])) {
    $delete_blog_id = $_POST['delete_blog_id'];
    $delete_stmt = $conn->prepare("DELETE FROM user_blogs WHERE id = ? AND user_id = ?");
    $delete_stmt->bind_param("ii", $delete_blog_id, $user_id);
    $delete_stmt->execute();
    $delete_stmt->close();
}

// Retrieve the blogs for the current user
$stmt = $conn->prepare("SELECT * FROM user_blogs WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$blogs = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- <link rel="stylesheet" href="./assets/css/edit_blog.css"> -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <title>My Blogs - Petiverse</title>
    <style>
        
    :root {
        --primary-color: #2563eb;
        --primary-hover: #1d4ed8;
        --secondary-color: #f8fafc;
        --accent-color: #0f172a;
        --text-color: #334155;
        --border-radius: 16px;
        --box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
        --transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }

    body {
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
        background-color: #f1f5f9;
        color: var(--text-color);
        line-height: 1.6;
        margin: 0;
        padding: 0;
    }

    .your_blog {
        text-align: center;
        color: var(--accent-color);
        font-size: 2rem;
        font-weight: 600;
        margin: 2rem 0;
        padding: 0 1rem;
    }

    /* Blog List Container */
    .blog-list {
        max-width: 1400px;
        margin: 2rem auto;
        padding: 0 2rem;
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 2rem;
    }

    /* Blog Item Card */
    .blog-item {
        background: white;
        border-radius: var(--border-radius);
        overflow: hidden;
        box-shadow: var(--box-shadow);
        transition: var(--transition);
        border: 1px solid #e2e8f0;
        padding: 1.5rem;
    }

    .blog-item:hover {
        transform: translateY(-4px);
        box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
    }

    .blog-item img {
        width: 100%;
        height: 220px;
        object-fit: cover;
        border-radius: calc(var(--border-radius) - 4px);
        margin: 1rem 0;
    }

    .blog-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: var(--accent-color);
        margin-bottom: 1rem;
        line-height: 1.4;
    }

    .blog-content {
        color: #64748b;
        font-size: 0.95rem;
        line-height: 1.6;
        margin: 1rem 0;
        max-height: 150px;
        overflow-y: auto;
    }

    .approval-status {
        display: inline-block;
        padding: 0.5rem 1rem;
        border-radius: 9999px;
        background: var(--secondary-color);
        color: var(--text-color);
        font-size: 0.875rem;
        font-weight: 500;
        margin: 0.5rem 0;
    }

    /* Action Buttons */
    .action-buttons {
        display: flex;
        gap: 1rem;
        margin-top: 1.5rem;
    }

    .edit-btn, .delete-btn {
        padding: 0.75rem 1.5rem;
        border-radius: 9999px;
        font-size: 0.95rem;
        font-weight: 500;
        cursor: pointer;
        transition: var(--transition);
        flex: 1;
        border: none;
    }

    .edit-btn {
        background: var(--primary-color);
        color: white;
    }

    .edit-btn:hover {
        background: var(--primary-hover);
        transform: translateY(-2px);
    }

    .delete-btn {
        background: white;
        color: #ef4444;
        border: 1px solid #fecaca;
    }

    .delete-btn:hover {
        background: #fef2f2;
        color: #dc2626;
        transform: translateY(-2px);
    }

    /* Modal Styles */
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
        max-width: 600px;
        margin: 2rem auto;
        padding: 2.5rem;
        border-radius: var(--border-radius);
        position: relative;
        box-shadow: 0 25px 50px -12px rgb(0 0 0 / 0.25);
    }

    .modal-content h2 {
        color: var(--accent-color);
        margin: 0 0 1.5rem 0;
        font-size: 1.5rem;
        font-weight: 600;
    }

    .close-btn {
        position: absolute;
        right: 1.5rem;
        top: 1.5rem;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        cursor: pointer;
        color: #64748b;
        background: var(--secondary-color);
        border-radius: 50%;
        transition: var(--transition);
    }

    .close-btn:hover {
        background: #e2e8f0;
        color: var(--accent-color);
    }

    /* Form Styles */
    #editForm {
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
    }

    #editForm label {
        font-weight: 500;
        color: var(--accent-color);
        font-size: 0.95rem;
    }

    #editForm input[type="text"],
    #editForm textarea,
    #editForm select {
        padding: 0.875rem 1.25rem;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        font-size: 0.95rem;
        transition: var(--transition);
        background: #f8fafc;
    }

    #editForm input[type="text"]:focus,
    #editForm textarea:focus,
    #editForm select:focus {
        outline: none;
        border-color: var(--primary-color);
        background: white;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    }

    #editForm textarea {
        min-height: 200px;
        resize: vertical;
    }

    #editForm input[type="file"] {
        padding: 0.875rem 0;
    }

    #editForm input[type="submit"] {
        background: var(--primary-color);
        color: white;
        border: none;
        padding: 0.875rem;
        border-radius: 12px;
        cursor: pointer;
        transition: var(--transition);
        font-weight: 500;
        font-size: 0.95rem;
        margin-top: 0.5rem;
    }

    #editForm input[type="submit"]:hover {
        background: var(--primary-hover);
        transform: translateY(-2px);
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .blog-list {
            padding: 1rem;
            grid-template-columns: 1fr;
        }
        
        .modal-content {
            width: 95%;
            margin: 1rem auto;
            padding: 1.5rem;
        }
        
        .your_blog {
            font-size: 1.75rem;
            margin: 1.5rem 0;
        }
    }
    </style>
</head>
<body>

<?php include 'Cus-NavBar/navBar.php'; ?>

    <h1 class="your_blog">Your Blogs</h1>



    <div class="blog-list">
        <?php foreach ($blogs as $blog): ?>
            <div class="blog-item" data-blog-id="<?php echo $blog['id']; ?>">
                <div class="blog-title"><?php echo htmlspecialchars($blog['title']); ?></div>
                <img src="data:image/jpeg;base64,<?php echo base64_encode($blog['photo']); ?>" alt="Blog Photo" style="max-width: 100%; height: auto;">
                <div class="blog-content"><?php echo nl2br(htmlspecialchars($blog['content'])); ?></div>
                <p><strong>Category:</strong> <?php echo htmlspecialchars($blog['category']); ?></p>
                <p class="approval-status">Status: <?php echo htmlspecialchars($blog['status']); ?></p>

                <div class="action-buttons">
                    <button class="edit-btn" onclick="openModal(<?php echo $blog['id']; ?>)">Edit</button>
                    <form method="POST" action="" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this blog post?');">
                        <input type="hidden" name="delete_blog_id" value="<?php echo $blog['id']; ?>">
                        <button type="submit" class="delete-btn">Delete</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>




    <!-- Edit Blog Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
        <h2>Edit your blog</h2>
            <span class="close-btn" onclick="closeModal()">&times;</span>
            <form id="editForm" action="" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="update_blog" value="1">
                <input type="hidden" name="blog_id" id="editBlogId">
                
                <label>Title:</label>
                <input type="text" name="title" id="editTitle" required>

                <label>Content:</label>
                <textarea name="content" id="editContent" required></textarea>

                <label>Category:</label>
                <select name="category" id="editCategory" required>
                    <option value="Health">Health</option>
                    <option value="Entertainment">Entertainment</option>
                    <option value="Events">Events</option>
                    <option value="Wisdom">Wisdom</option>
                    <option value="Experience">Experience</option>
                </select>

                <label>Photo (optional):</label>
                <input type="file" name="photo">
                
                <input type="submit" value="Update Blog Post">
            </form>
        </div>
    </div>

    <script>
        // Open modal and populate fields
        function openModal(blogId) {
            const blogItem = document.querySelector(`.blog-item[data-blog-id='${blogId}']`);
            document.getElementById("editBlogId").value = blogId;
            document.getElementById("editTitle").value = blogItem.querySelector('.blog-title').innerText;
            document.getElementById("editContent").value = blogItem.querySelector('.blog-content').innerText;
            const category = blogItem.querySelector('.blog-content + p').innerText.split(": ")[1];
            document.getElementById("editCategory").value = category;
            document.getElementById("editModal").style.display = "block";
        }

        function closeModal() {
            document.getElementById("editModal").style.display = "none";
        }

        window.onclick = function(event) {
            const modal = document.getElementById("editModal");
            if (event.target === modal) {
                closeModal();
            }
        }
    </script>
</body>
</html>

<?php
$conn->close();
?>
