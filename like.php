<?php
// Database connection
include 'db.php';

$type = $_POST['type'];  // 'post' or 'comment'
$id = intval($_POST['id']); // Post ID or Comment ID

if ($type === 'post') {
    $sql = "UPDATE posts SET likes = likes + 1 WHERE id = ?";
} elseif ($type === 'comment') {
    $sql = "UPDATE comments SET likes = likes + 1 WHERE id = ?";
}

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();

$stmt->close();
$conn->close();
?>
