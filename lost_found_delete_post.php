<?php
include './db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$userId = $_SESSION['user_id']; 
$postId = $_POST['post_id']; 

// Delete the post
$deleteSql = "DELETE FROM lost_and_found_pets WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($deleteSql);
$stmt->bind_param('ii', $postId, $userId);

if ($stmt->execute()) {
    $message = "Post deleted successfully!";
} else {
    $message = "Failed to delete the post.";
}

header("Location: lost_found_myposts.php"); 
exit();
?>
