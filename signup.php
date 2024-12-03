<?php
require 'google-config.php'; 

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

$google_signup_url = $google_client->createAuthUrl(); 
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
            background: linear-gradient(to bottom right, #6A82FB, #FC5C7D);
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
            background-color: #6A82FB;
            color: white;
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

        .signup-container {
            width: 40%;
            background-color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 40px;
            animation: slideInRight 1s ease-out;
            overflow-y: auto;
        }

        .signup-form {
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

        input[type="text"], 
        input[type="email"], 
        input[type="password"] {
            width: 100%;
            padding: 12px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            transition: all 0.3s ease;
        }

        input[type="text"]:focus, 
        input[type="email"]:focus, 
        input[type="password"]:focus {
            border-color: #FC5C7D;
            box-shadow: 0 0 10px rgba(252, 92, 125, 0.2);
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
            transition: background-color 0.3s ease, transform 0.1s;
            margin-top: 20px;
        }

        input[type="submit"]:hover {
            background-color: #FC5C7D;
            transform: scale(1.02);
        }

        .google-signup-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 10px 20px;
            background-color: #4285f4;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: bold;
            transition: background-color 0.3s ease, transform 0.1s;
            margin-top: 10px;
        }

        .google-signup-btn:hover {
            background-color: #357ae8;
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

            .description-section, .signup-container {
                width: 100%;
                padding: 20px;
            }

            .left-column, .right-column {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="page-container">
        <div class="description-section">
            <h1>Join Petiverse</h1>
            <p>Create your account and unlock a world of comprehensive pet care. Petiverse offers a seamless platform to manage your pet's health, connect with pet lovers, and access personalized pet care resources.</p>
            <img src="./src/img/pet-log.png" alt="Petiverse Signup Illustration" class="description-image">
        </div>
        <div class="signup-container">
            <div class="signup-form">
                <h2>Signup to Petiverse</h2>
                <?php if (!empty($error_message)): ?>
                    <div class="error-message">
                        <?php echo $error_message; ?>
                    </div>
                <?php endif; ?>
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
                <p style="text-align: center; margin-top: 15px;">Already registered? <a href="login.php" style="color: #6A82FB; text-decoration: none;">Login here</a></p>
            </div>
        </div>
    </div>
</body>
</html>