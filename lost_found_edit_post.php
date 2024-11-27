<?php
include './db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if the user is not logged in
    header('Location: login.php');
    exit();
}

$userId = $_SESSION['user_id']; 

// Fetch the post details for editing
if (isset($_GET['post_id'])) {
    $postId = $_GET['post_id'];

    // Get the post details from the database
    $sql = "SELECT id, pet_name, pet_type, description, location, status, image FROM lost_and_found_pets WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ii', $postId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo "Post not found or you don't have permission to edit this post.";
        exit();
    }

    $post = $result->fetch_assoc();
} else {
    echo "No post ID provided.";
    exit();
}

// Handle post update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $petName = $_POST['pet_name'];
    $petType = $_POST['pet_type'];
    $description = $_POST['description'];
    $location = $_POST['location'];
    $status = $_POST['status'];

    // Update the post without modifying the image
    $updateSql = "UPDATE lost_and_found_pets SET pet_name = ?, pet_type = ?, description = ?, location = ?, status = ? WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($updateSql);
    $stmt->bind_param('ssssssi', $petName, $petType, $description, $location, $status, $postId, $userId);

    if ($stmt->execute()) {
        header("Location: lost_found_myposts.php");
        exit();
    } else {
        echo "Failed to update the post.";
    }

}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Post - Lost and Found</title>
    <link rel="stylesheet" href="assets/css/styles.css">

    <style>
body {
    font-family: Arial, sans-serif;
    background-color: #f9f9f9;
    margin: 0; 
    padding: 0;
    display: flex;
    flex-direction: column;
    min-height: 100vh;
}


/* Main section styling */
main {
    flex: 1;
    margin-top: 80px;
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 1rem;
}

section {
    background: #fff;
    padding: 2rem;
    border-radius: 10px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    width: 100%;
    max-width: 600px;
}

h2 {
    text-align: center;
    margin-bottom: 1.5rem;
    color: #333;
}

form {
    display: flex;
    flex-direction: column;
}

label {
    font-weight: bold;
    margin-bottom: 0.5rem;
    color: #555;
}

input[type="text"],
textarea,
select {
    padding: 0.9rem; 
    border: 1px solid #ddd;
    border-radius: 5px;
    margin-bottom: 1rem;
    font-size: 1rem; 
    width: 100%; 
    max-width: 100%; 
    background: #f7f7f7;
    box-sizing: border-box; 
}

input[type="text"]:focus,
textarea:focus,
select:focus {
    outline: none;
    border-color: #007bff;
    background: #fff;
    box-shadow: 0 0 4px rgba(0, 123, 255, 0.3);
}

textarea {
    resize: none;
    height: 120px;
}

button {
    padding: 0.75rem;
    background: #8B4513; 
    color: #fff;
    border: none;
    border-radius: 5px;
    font-size: 1rem;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

button:hover {
    background: #5a2e0f; 
}

button:active {
    transform: scale(0.98);
}

    </style>
</head>
<body>

<!-- Include Navbar -->

<?php include './Cus-NavBar/navBar.php'; ?>

<!-- Main content -->
<main>
    <section>
        <h2>Edit Post</h2>

        <form method="POST">
            <label for="pet_name">Pet Name:</label>
            <input type="text" id="pet_name" name="pet_name" value="<?php echo htmlspecialchars($post['pet_name']); ?>" required>

            <label for="pet_type">Pet Type:</label>
            <input type="text" id="pet_type" name="pet_type" value="<?php echo htmlspecialchars($post['pet_type']); ?>" required>

            <label for="description">Description:</label>
            <textarea id="description" name="description" required><?php echo htmlspecialchars($post['description']); ?></textarea>

            <label for="location">Location:</label>
            <input type="text" id="location" name="location" value="<?php echo htmlspecialchars($post['location']); ?>" required>

            <label for="status">Status:</label>
            <select id="status" name="status">
                <option value="lost" <?php echo $post['status'] == 'lost' ? 'selected' : ''; ?>>Lost</option>
                <option value="found" <?php echo $post['status'] == 'found' ? 'selected' : ''; ?>>Found</option>
            </select>

            <button type="submit">Update Post</button>
        </form>
    </section>
</main>

</body>
</html>

