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

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Petiverse</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(to bottom right, #6A82FB, #FC5C7D);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .login-container {
            background-color: #ffffff;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0px 10px 20px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 400px;
            animation: slideIn 0.8s ease;
        }

        h2 {
            text-align: center;
            margin-bottom: 30px;
            font-weight: 600;
            color: #FC5C7D;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }

        input[type="email"], input[type="password"] {
            width: 100%;
            padding: 12px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            transition: border-color 0.3s ease;
        }

        input[type="email"]:focus, input[type="password"]:focus {
            border-color: #FC5C7D;
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
        }

        input[type="submit"]:hover {
            background-color: #FC5C7D;
        }

        .google-login-btn {
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

        .google-login-btn:hover {
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

        .forgot-password {
            display: block;
            text-align: right;
            margin-top: -15px;
            margin-bottom: 20px;
        }

        .checkbox-label {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }

        .checkbox-label input[type="checkbox"] {
            margin-right: 10px;
        }

        .error-message {
            color: #fff;
            background-color: #FF5A5A;
            padding: 10px;
            margin-bottom: 20px;
            text-align: center;
            border-radius: 5px;
            font-size: 14px;
            animation: fadeIn 0.5s ease-in-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Login to Petiverse</h2>
        <?php if (!empty($error_message)): ?>
            <div class="error-message">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>
        <form action="login.php" method="POST">
            <label>Email:</label>
            <input type="email" name="email" placeholder="Enter your email" required>

            <label>Password:</label>
            <input type="password" name="password" placeholder="Enter your password" required>

            <div class="checkbox-label">
                <input type="checkbox" name="remember">
                <label>Remember me</label>
            </div>

            <a href="forgot-password.php" class="forgot-password">Forgot Password?</a>

            <input type="submit" value="Login">

            <p>OR</p>
            <a href="<?php echo $google_login_url; ?>" class="google-login-btn">Login with Google</a>
        </form>
        <p>Not registered? <a href="signup.php">Sign up here</a></p>
    </div>
</body>
</html>
