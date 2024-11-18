<?php
session_start();
include('../db.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Query to check if the user exists
    $sql = "SELECT * FROM system_admin WHERE username='$username'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        // Verify the password using password_verify
        if (password_verify($password, $row['password'])) {
            // Set session variables
            $_SESSION['role'] = 'admin';
            $_SESSION['username'] = $username;
            header("Location: dashboard.php");
            exit();
        } else {
            $error_message = "Invalid password.";
        }
    } else {
        $error_message = "Invalid username.";
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Petiverse - Admin Login</title>
    <style>
        /* Global Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Arial', sans-serif;
        }

        body {
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: linear-gradient(135deg, #74ebd5, #ACB6E5); /* Reintroducing gradient */
        }

        .login-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
            padding: 50px;
            max-width: 400px;
            width: 100%;
            text-align: center;
            transition: all 0.3s ease;
        }

        .login-container:hover {
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.2);
        }

        .login-header {
            margin-bottom: 30px;
        }

        .login-header h1 {
            font-size: 2.5rem;
            color: #333;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        input[type="text"],
        input[type="password"] {
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #ddd;
            font-size: 1rem;
            width: 100%;
            transition: all 0.3s ease;
        }

        input[type="text"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: #74ebd5;
            box-shadow: 0 0 5px rgba(116, 235, 213, 0.5);
        }

        button {
            padding: 15px;
            background-color: #74ebd5;
            border: none;
            border-radius: 8px;
            font-size: 1.2rem;
            color: white;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        button:hover {
            background-color: #66d2d1;
        }

        .error {
            color: red;
            margin-bottom: 20px;
            font-size: 0.9rem;
        }

        @media (max-width: 500px) {
            .login-container {
                padding: 30px;
            }

            .login-header h1 {
                font-size: 2rem;
            }

            input[type="text"],
            input[type="password"] {
                padding: 12px;
            }

            button {
                padding: 12px;
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>

<div class="login-container">
    <div class="login-header">
        <h1>Admin Login</h1>
    </div>
    <form action="index.php" method="POST">
        <?php if (isset($error_message)) { ?>
            <p class="error"><?php echo $error_message; ?></p>
        <?php } ?>
        
        <input type="text" id="username" name="username" placeholder="Username" required>
        <input type="password" id="password" name="password" placeholder="Password" required>
        <button type="submit">Login</button>
    </form>
</div>

</body>
</html>
