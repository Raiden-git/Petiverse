<?php
// Include your database connection
include('../db.php');
include('session_check.php');

// Check if the feedback ID is passed in the URL
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Delete the feedback entry from the database
    $sql = "DELETE FROM feedback WHERE id = $id";
    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('Feedback deleted successfully'); window.location.href='view_feedback.php';</script>";
    } else {
        echo "<script>alert('Error: Could not delete feedback'); window.location.href='view_feedback.php';</script>";
    }
}

// Close the database connection
mysqli_close($conn);
?>
