<?php
session_start();
require 'google-config.php'; // Google Client configuration

$error_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Database connection
    $conn = new mysqli('localhost', 'root', '', 'petiverse');

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $email = $_POST['email'];
    $password = md5($_POST['password']);

    // Check if user exists with email and password
    $stmt = $conn->prepare("SELECT id, full_name FROM users WHERE email = ? AND password = ?");
    $stmt->bind_param("ss", $email, $password);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $name );
        $stmt->fetch();

        if ($is_google) {
            $error_message = "Please log in using Google!";
        } else {
            // Set session variables
            $_SESSION['user_id'] = $id;
            $_SESSION['username'] = $name;
            // Redirect to homepage
            header("Location: index.php");
            exit();
        }
    } else {
        $error_message = "Invalid email or password!";
    }

    $stmt->close();
    $conn->close();
}

$google_login_url = $google_client->createAuthUrl(); // Google login URL

?>