<?php

include '../db.php';

// Start the session
session_start();

// Check if the user is logged in
if (!isset($_SESSION['email'])) {
    echo "Not logged in."; // Debugging line
    header('Location: index.html'); 
    exit();
}

// Get the user's email from the session
$email = $_SESSION['email'];

// Fetch user details from the database
$sql = "SELECT * FROM users WHERE email = '$email'";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) > 0) {
    $user = mysqli_fetch_assoc($result);
} else {
    echo "No user found!"; 
}

// Close the connection
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <link rel="stylesheet" href="../assets/css/Cus-profile.css"> 
</head>
<body>
    <div class="profile-container">
        <h1>User Profile</h1>
        <div class="profile-item">
            <label for="name">Name:</label>
            <p><?php echo htmlspecialchars($user['name']); ?></p>
        </div>
        <div class="profile-item">
            <label for="email">Email:</label>
            <p><?php echo htmlspecialchars($user['email']); ?></p>
        </div>
        <div class="profile-item">
            <label for="address">Address:</label>
            <p><?php echo htmlspecialchars($user['address']); ?></p>
        </div>
        <div class="profile-item">
            <label for="mobile_number">Mobile Number:</label>
            <p><?php echo htmlspecialchars($user['mobile_number']); ?></p>
        </div>
    </div>
</body>
</html>
