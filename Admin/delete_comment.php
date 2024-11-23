<?php
include('../db.php');
include('session_check.php');

// Get the comment ID from the URL
$comment_id = $_GET['comment_id'] ?? 0;
$comment_id = intval($comment_id);

// Prepare and execute the delete query
$sql = "DELETE FROM comments WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $comment_id);

if ($stmt->execute()) {
    // Redirect back to the admin dashboard or comments section
    header("Location: community_controls.php#comments-section");
    exit();
} else {
    echo "Error deleting comment.";
}

$stmt->close();
$conn->close();
?>
