<?php
session_start();
require 'google-config.php'; 

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

$google_login_url = $google_client->createAuthUrl();

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
            background-image: url('src/img/background-petiverse.png');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            height: 100vh;
            display: flex;
            overflow: hidden;
        }

        .page-container {
            display: flex;
            width: 100%;
            height: 100%;
            box-shadow: 0 0 50px rgba(0,0,0,0.1);
        }

        .description-section {
            width: 60%;
            background-color: #FFF8E7;
            color: #333;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 40px;
            text-align: center;
            animation: slideInLeft 1s ease-out;
        }

        .description-section h1 {
            font-size: 2.5rem;
            margin-bottom: 20px;
            animation: fadeIn 1.5s ease-out;
        }

        .description-section p {
            font-size: 1rem;
            line-height: 1.6;
            max-width: 600px;
            margin-bottom: 30px;
            animation: fadeIn 2s ease-out;
        }

        .description-image {
            max-width: 400px;
            width: 100%;
            height: auto;
            animation: bounce 2s infinite alternate;
        }

        .login-container {
            width: 40%;
            background-color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 40px;
            animation: slideInRight 1s ease-out;
        }

        .login-form {
            max-width: 350px;
            width: 100%;
            margin: 0 auto;
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
            transition: all 0.3s ease;
        }

        input[type="email"]:focus, input[type="password"]:focus {
            border-color: #FC5C7D;
            box-shadow: 0 0 10px rgba(252, 92, 125, 0.2);
        }

        input[type="submit"] {
            width: 100%;
            padding: 12px;
            margin-top: 20px;
            background-color: #DA8359;
            border: none;
            border-radius: 5px;
            color: #fff;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.1s;
        }

        input[type="submit"]:hover {
            background-color: #9c5f41;
            transform: scale(1.02);
        }

        .google-login-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 10px 20px;
            background-color: white;
            color: black;
            text-decoration: none;
            border: 2px solid #DA8359;
            border-radius: 5px;
            font-size: 16px;
            font-weight: bold;
            transition: background-color 0.3s ease, transform 0.1s;
        }

        .google-login-btn:hover {
            background-color: #fff;
            transform: scale(1.02);
        }

        .error-message {
            color: #fff;
            background-color: #FF5A5A;
            padding: 10px;
            margin-bottom: 20px;
            text-align: center;
            border-radius: 5px;
            animation: shake 0.5s;
        }

        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-100%);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(100%);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        @keyframes bounce {
            from {
                transform: translateY(-10px);
            }
            to {
                transform: translateY(10px);
            }
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-10px); }
            20%, 40%, 60%, 80% { transform: translateX(10px); }
        }

        @media (max-width: 768px) {
            .page-container {
                flex-direction: column;
            }

            .description-section, .login-container {
                width: 100%;
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="page-container">
        <div class="description-section">
            <h1>Welcome to Petiverse</h1>
            <p>Petiverse is your all-in-one pet care web application designed to simplify and enhance your pet ownership experience. From health tracking and veterinary records to pet-friendly location recommendations and community connections, Petiverse is the ultimate companion for pet lovers.</p>
            <img src="./src/img/pet-log.png" alt="Petiverse Illustration" class="description-image">
        </div>
        <div class="login-container">
            <div class="login-form">
                <h2>Login to Petiverse</h2>
                <?php if (!empty($error_message)): ?>
                    <div class="error-message">
                        <?php echo $error_message; ?>
                    </div>
                <?php endif; ?>
                <form action="./login.php" method="POST">
                    <label>Email:</label>
                    <input type="email" name="email" placeholder="Enter your email" required>

                    <label>Password:</label>
                    <input type="password" name="password" placeholder="Enter your password" required>

                   

                    <input type="submit" value="Login">

                    <p style="text-align: center; margin: 15px 0;">OR</p>

                    <a href="<?php echo $google_login_url; ?>" class="google-login-btn">
                    <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" id="google" viewBox="-380.2 274.7 65.7 65.8" width="25" height="25">
                    <circle cx="-347.3" cy="307.6" r="32.9" style="fill:#e0e0e0"></circle>
                    <circle cx="-347.3" cy="307.1" r="32.4" style="fill:#fff"></circle>
                    <g>
                        <defs>
                            <path id="SVGID_1_" d="M-326.3 303.3h-20.5v8.5h11.8c-1.1 5.4-5.7 8.5-11.8 8.5-7.2 0-13-5.8-13-13s5.8-13 13-13c3.1 0 5.9 1.1 8.1 2.9l6.4-6.4c-3.9-3.4-8.9-5.5-14.5-5.5-12.2 0-22 9.8-22 22s9.8 22 22 22c11 0 21-8 21-22 0-1.3-.2-2.7-.5-4z"></path>
                        </defs>
                        <clipPath id="SVGID_2_">
                            <use xlink:href="#SVGID_1_" overflow="visible"></use>
                        </clipPath>
                        <path d="M-370.8 320.3v-26l17 13z" style="clip-path:url(#SVGID_2_);fill:#fbbc05"></path>
                        <defs>
                            <path id="SVGID_3_" d="M-326.3 303.3h-20.5v8.5h11.8c-1.1 5.4-5.7 8.5-11.8 8.5-7.2 0-13-5.8-13-13s5.8-13 13-13c3.1 0 5.9 1.1 8.1 2.9l6.4-6.4c-3.9-3.4-8.9-5.5-14.5-5.5-12.2 0-22 9.8-22 22s9.8 22 22 22c11 0 21-8 21-22 0-1.3-.2-2.7-.5-4z"></path>
                        </defs>
                        <clipPath id="SVGID_4_">
                            <use xlink:href="#SVGID_3_" overflow="visible"></use>
                        </clipPath>
                        <path d="M-370.8 294.3l17 13 7-6.1 24-3.9v-14h-48z" style="clip-path:url(#SVGID_4_);fill:#ea4335"></path>
                        <g>
                            <defs>
                                <path id="SVGID_5_" d="M-326.3 303.3h-20.5v8.5h11.8c-1.1 5.4-5.7 8.5-11.8 8.5-7.2 0-13-5.8-13-13s5.8-13 13-13c3.1 0 5.9 1.1 8.1 2.9l6.4-6.4c-3.9-3.4-8.9-5.5-14.5-5.5-12.2 0-22 9.8-22 22s9.8 22 22 22c11 0 21-8 21-22 0-1.3-.2-2.7-.5-4z"></path>
                            </defs>
                            <clipPath id="SVGID_6_">
                                <use xlink:href="#SVGID_5_" overflow="visible"></use>
                            </clipPath>
                            <path d="M-370.8 320.3l30-23 7.9 1 10.1-15v48h-48z" style="clip-path:url(#SVGID_6_);fill:#34a853"></path>
                        </g>
                        <g>
                            <defs>
                                <path id="SVGID_7_" d="M-326.3 303.3h-20.5v8.5h11.8c-1.1 5.4-5.7 8.5-11.8 8.5-7.2 0-13-5.8-13-13s5.8-13 13-13c3.1 0 5.9 1.1 8.1 2.9l6.4-6.4c-3.9-3.4-8.9-5.5-14.5-5.5-12.2 0-22 9.8-22 22s9.8 22 22 22c11 0 21-8 21-22 0-1.3-.2-2.7-.5-4z"></path>
                            </defs>
                            <clipPath id="SVGID_8_">
                                <use xlink:href="#SVGID_7_" overflow="visible"></use>
                            </clipPath>
                            <path d="M-322.8 331.3l-31-24-4-3 35-10z" style="clip-path:url(#SVGID_8_);fill:#4285f4"></path>
                        </g>
                    </g>
                </svg> Continue with Google
                    </a>
                </form>
                <p style="text-align: center; margin-top: 15px;">Not registered? <a href="signup.php" style="color: #6A82FB; text-decoration: none;" >Sign up here</a></p>
            </div>
        </div>
    </div>
</body>
</html>
