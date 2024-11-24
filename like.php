<?php
// Database connection
include 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo "Not logged in";
    exit();
}

$user_id = $_SESSION['user_id'];
$type = $_POST['type']; 
$id = intval($_POST['id']); 
$action = $_POST['action']; 

// Determine the table based on the type
$table = ($type === 'post') ? 'posts' : 'comments';

// Fetch current likes and user_likes for the specific post or comment
$sql = "SELECT likes, user_likes FROM $table WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stmt->close();

if (!$row) {
    echo "Invalid $type ID.";
    exit();
}

// Decode user_likes JSON or initialize as an empty array
$user_likes = json_decode($row['user_likes'], true) ?? [];

if ($action === 'like') {
    if (in_array($user_id, $user_likes)) {
        echo "already liked";
    } else {
        // Add user ID to the list and increment the like count
        $user_likes[] = $user_id;
        $likes = $row['likes'] + 1;

        // Update the post or comment with the new like count and user_likes JSON
        $update_sql = "UPDATE $table SET likes = ?, user_likes = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $user_likes_json = json_encode($user_likes);
        $update_stmt->bind_param("isi", $likes, $user_likes_json, $id);
        $update_stmt->execute();
        $update_stmt->close();

        echo "liked";
    }
} elseif ($action === 'dislike') {
    if (!in_array($user_id, $user_likes)) {
        echo "not liked yet";
    } else {
        // Remove user ID from the list and decrement the like count
        $user_likes = array_diff($user_likes, [$user_id]);
        $likes = $row['likes'] - 1;

        // Update the post or comment with the new like count and user_likes JSON
        $update_sql = "UPDATE $table SET likes = ?, user_likes = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $user_likes_json = json_encode($user_likes);
        $update_stmt->bind_param("isi", $likes, $user_likes_json, $id);
        $update_stmt->execute();
        $update_stmt->close();

        echo "disliked";
    }
}

$conn->close();
?>
