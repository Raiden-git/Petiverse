<?php
// Include the database connection file
include 'db.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve email and password from the form
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    // Check if the user exists in the database
    $sql = "SELECT * FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);

        // Verify the password
        if (password_verify($password, $user['password'])) {
            // Password is correct, start the session and redirect to the customer page
            $_SESSION['user_id'] = $user['id'];  // Store user ID in session
            $_SESSION['email'] = $user['email']; // Store user email in session

            // Redirect to the customer page (Cus-index.html)
            header("Location: ../Customer/Cus-index.php");
            exit;
        } else {
            // Incorrect password
            echo "<script>alert('Incorrect password. Please try again.'); window.location.href='login.html';</script>";
        }
    } else {
        // User doesn't exist
        echo "<script>alert('User does not exist. Please register first.'); window.location.href='signup.html';</script>";
    }
}

// Close the connection
mysqli_close($conn);
?>



<!DOCTYPE html>
<html>
<head>
    <title>Petiverse Login & Signup</title>
    <link rel="stylesheet" type="text/css" href="assets/css/login.css">
    <link href="https://fonts.googleapis.com/css2?family=Jost:wght@500&display=swap" rel="stylesheet">
</head>
<body>
<div class="container" id="container">
    <div class="form-container sign-up-container">
        <!-- Signup form -->
        <form action="signup.php" method="POST">
            <h1>Create Account</h1>
            <span>Use your details for registration</span>
            <input type="text" name="name" placeholder="Name" required />
            <input type="email" name="email" placeholder="Email" required />
            <input type="password" name="password" placeholder="Password" required />
            <input type="text" name="address" placeholder="Address" />
            <input type="text" name="mobile_number" placeholder="Mobile Number" />
            <button type="submit">Sign Up</button>
        </form>
    </div>
    <div class="form-container sign-in-container">
        <!-- Login form -->
        <form action="login.php" method="POST">
            <h1>Sign in</h1>
            <span>Use your email and password</span>
            <input type="email" name="email" placeholder="Email" required />
            <input type="password" name="password" placeholder="Password" required />
            <a href="#">Forgot your password?</a>
            <button type="submit">Sign In</button>
        </form>
    </div>
    <div class="overlay-container">
        <div class="overlay">
            <div class="overlay-panel overlay-left">
                <h1>Welcome Back!</h1>
                <p>To keep connected with us, please log in with your personal info</p>
                <button class="ghost" id="signIn">Sign In</button>
            </div>
            <div class="overlay-panel overlay-right">
                <h1>Hello, Pet Lovers...!</h1>
                <p>Enter your personal details and start your journey with <span>Petiverse</span></p>
                <button class="ghost" id="signUp">Sign Up</button>
            </div>
        </div>
    </div>
</div>

<footer>
</footer>

<script src="assets/js/login.js"></script>
</body>
</html>
