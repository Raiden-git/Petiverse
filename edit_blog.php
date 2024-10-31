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
    <link rel="stylesheet" href="./assets/css/edit_blog.css">
    <title>My Blogs - Petiverse</title>
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
