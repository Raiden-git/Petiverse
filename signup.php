<?php
// Include the database connection file
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the signup form data
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $mobile_number = mysqli_real_escape_string($conn, $_POST['mobile_number']);

    // Hash the password for security
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Check if the email already exists
    $check_email = "SELECT * FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $check_email);

    if (mysqli_num_rows($result) > 0) {
        echo "<script>alert('Email already exists! Please use another email.'); window.location.href='index.html';</script>";
    } else {
        // Insert user data into the database
        $sql = "INSERT INTO users (name, email, password, address, mobile_number) VALUES ('$name', '$email', '$hashed_password', '$address', '$mobile_number')";

        if (mysqli_query($conn, $sql)) {
            // Redirect to login page after successful signup
            echo "<script>alert('Registration successful!'); window.location.href='../Customer/Cus-index.php';</script>";
        } else {
            echo "Error: " . $sql . "<br>" . mysqli_error($conn);
        }
    }
}

// Close the connection
mysqli_close($conn);
?>
