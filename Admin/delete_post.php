<?php
include('../db.php');
include('session_check.php');

// Get the post ID from the URL
$post_id = $_GET['post_id'] ?? 0;
$post_id = intval($post_id);

// Begin a transaction to ensure both deletions succeed or fail together
$conn->begin_transaction();

try {
    // Delete associated comments
    $delete_comments_sql = "DELETE FROM comments WHERE post_id = ?";
    $delete_comments_stmt = $conn->prepare($delete_comments_sql);
    $delete_comments_stmt->bind_param("i", $post_id);
    $delete_comments_stmt->execute();
    $delete_comments_stmt->close();

    // Delete the post
    $delete_post_sql = "DELETE FROM posts WHERE id = ?";
    $delete_post_stmt = $conn->prepare($delete_post_sql);
    $delete_post_stmt->bind_param("i", $post_id);
    $delete_post_stmt->execute();
    $delete_post_stmt->close();

    // Commit the transaction
    $conn->commit();

    // Redirect to the posts section
    header("Location: community_controls.php#posts-section");
    exit();
} catch (mysqli_sql_exception $e) {
    // Roll back the transaction on error
    $conn->rollback();
    echo "Error deleting post and its comments: " . $e->getMessage();
}

$conn->close();
?>
