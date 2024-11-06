<?php
require 'google-config.php'; // Google Client configuration

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Database connection
    $conn = new mysqli('localhost', 'root', '', 'petiverse');

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Collecting form data
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = md5($_POST['password']);
    $confirm_password = md5($_POST['confirm_password']);
    $address = $_POST['address'];
    $mobile_number = $_POST['mobile_number'];

    // Check if passwords match
    if ($_POST['password'] != $_POST['confirm_password']) {
        echo "<p class='error'>Passwords do not match!</p>";
    } else {
        // Check if the email already exists
        $email_check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $email_check->bind_param("s", $email);
        $email_check->execute();
        $email_check->store_result();
        
        if ($email_check->num_rows > 0) {
            echo "<p class='error'>Email already exists!</p>";
        } else {
            // Insert user into database
            $stmt = $conn->prepare("INSERT INTO users (full_name, email, password, address, contact_number) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $name, $email, $password, $address, $mobile_number);

            if ($stmt->execute()) {
                echo "<script>
                          alert('Signup successful!');
                          window.location.href = 'login.php';
                      </script>";
                exit;
            } else {
                echo "<p class='error'>Error: " . $stmt->error . "</p>";
            }
            $stmt->close();
        }
        $email_check->close();
    }

    $conn->close();
}

$google_signup_url = $google_client->createAuthUrl(); // Google signup URL
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup - Petiverse</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: transparent;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .signup-container {
            position: relative;
            background-color: #ffffff;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0px 10px 20px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 500px;
            animation: slideIn 0.8s ease;
            left: 25%;
        }

        h2 {
            text-align: center;
            margin-bottom: 30px;
            font-weight: 600;
            color: #FC5C7D;
        }

        form {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
        }

        .left-column, .right-column {
            width: 48%;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }

        input[type="text"], input[type="email"], input[type="password"] {
            width: 100%;
            padding: 12px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            transition: border-color 0.3s ease;
        }

        input[type="text"]:focus, input[type="email"]:focus, input[type="password"]:focus {
            border-color: #FC5C7D;
        }

        .full-width {
            width: 100%;
        }

        input[type="submit"] {
            width: 100%;
            padding: 12px;
            background-color: #6A82FB;
            border: none;
            border-radius: 5px;
            color: #fff;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s ease;
            margin-top: 20px;
        }

        input[type="submit"]:hover {
            background-color: #FC5C7D;
        }

        .google-signup-btn {
            display: block;
            width: 100%;
            padding: 12px;
            background-color: #4285F4;
            color: #fff;
            font-size: 16px;
            text-align: center;
            border-radius: 5px;
            margin-top: 10px;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }

        .google-signup-btn:hover {
            background-color: #357ae8;
        }

        a {
            color: #6A82FB;
            text-decoration: none;
        }

        a:hover {
            text-decoration: none;
        }

        p {
            text-align: center;
            margin-top: 20px;
        }

        .centered-or {
            text-align: center;
            /* margin-top: -10px; */
            margin-bottom: 5px;
            position: relative;
            left: 47%;
        }

        .error {
            color: #FF0000;
            text-align: center;
            margin: 10px 0;
        }

        .success {
            color: #28a745;
            text-align: center;
            margin: 10px 0;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

    </style>
</head>
<body>
    <div class="signup-container">
        <h2>Signup to Petiverse</h2>
        <form action="signup.php" method="POST">
            <div class="left-column">
                <label>Email:</label>
                <input type="email" name="email" placeholder="Enter your email" required>

                <label>Password:</label>
                <input type="password" name="password" placeholder="Enter your password" required>

                <label>Confirm Password:</label>
                <input type="password" name="confirm_password" placeholder="Confirm your password" required>
            </div>
            <div class="right-column">
                <label>Name:</label>
                <input type="text" name="name" placeholder="Enter your name" required>

                <label>Address:</label>
                <input type="text" name="address" placeholder="Enter your address" required>

                <label>Mobile Number:</label>
                <input type="text" name="mobile_number" placeholder="Enter your mobile number" required>
            </div>
            <input type="submit" value="Signup" class="full-width">
        </form>
        <p>Already registered? <a href="login.php">Login here</a></p>
    </div>
</body>
</html>
