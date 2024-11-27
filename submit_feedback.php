<?php

include 'db.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect form data and sanitize inputs
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $message = mysqli_real_escape_string($conn, $_POST['message']);

    // Insert data into the feedback table
    $sql = "INSERT INTO feedback (name, email, message) VALUES ('$name', '$email', '$message')";

    if (mysqli_query($conn, $sql)) {
        // On successful insertion, redirect or show a success message
        echo "<script>alert('Feedback submitted successfully!'); window.location.href='index.php';</script>";
    } else {
        // On error, show an error message
        echo "<script>alert('Error: Could not submit feedback. Please try again later.'); window.location.href='index.php';</script>";
    }
}

// Close the database connection
mysqli_close($conn);
?>
