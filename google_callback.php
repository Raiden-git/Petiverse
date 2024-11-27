<?php
session_start();
require 'google-config.php'; 

if (isset($_GET['code'])) {
    try {
        // Fetch the token with the authorization code
        $token = $google_client->fetchAccessTokenWithAuthCode($_GET['code']);
        
        // Check if there's an error fetching the access token
        if (isset($token['error'])) {
            throw new Exception("Error fetching access token: " . $token['error']);
        }

        // Set access token in the client
        $google_client->setAccessToken($token['access_token']);

        // Get profile info from Google
        $google_oauth = new Google_Service_Oauth2($google_client);
        $google_account_info = $google_oauth->userinfo->get();
        
        // Extract required user info
        $email = $google_account_info->email;
        $first_name = $google_account_info->givenName;
        $last_name = $google_account_info->familyName;
        $token = $google_account_info->id;
        $picture = $google_account_info->picture;
        $verified_email = $google_account_info->verifiedEmail ? 1 : 0;

        // Database connection
        $conn = new mysqli('localhost', 'root', '', 'petiverse');

        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Check if the user already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            // User already exists, log them in
            $stmt->bind_result($id);
            $stmt->fetch();
            $_SESSION['user_id'] = $id;
            $_SESSION['username'] = $first_name;
            header("Location: index.php");
        } else {
            // New Google user, register them
            $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, token, verifiedEmail, picture) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $first_name, $last_name, $email, $token, $verified_email, $picture);
            
            if ($stmt->execute()) {
                $_SESSION['user_id'] = $conn->insert_id;
                $_SESSION['username'] = $first_name;
                header("Location: index.php");
            } else {
                echo "Error: " . $stmt->error;
            }
        }

        $stmt->close();
        $conn->close();

    } catch (Exception $e) {
        echo 'Error: ' . $e->getMessage();
    }
} else {
    echo "Google sign-in failed.";
}
?>
