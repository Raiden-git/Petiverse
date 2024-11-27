<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vet Login - Petiverse</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f6f8f9 0%, #e5ebee 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            line-height: 1.6;
        }

        .login-container {
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            padding: 40px;
            width: 100%;
            max-width: 400px;
            text-align: center;
            transition: transform 0.3s ease;
        }

        .login-container:hover {
            transform: scale(1.02);
        }

        h2 {
            color: #2c3e50;
            margin-bottom: 30px;
            font-weight: 600;
        }

        form {
            display: flex;
            flex-direction: column;
        }

        input {
            padding: 12px 15px;
            margin-bottom: 20px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
            transition: border-color 0.3s ease;
        }

        input:focus {
            outline: none;
            border-color: #3498db;
        }

        button {
            background-color: #3498db;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }

        button:hover {
            background-color: #2980b9;
            transform: translateY(-2px);
        }

        .error-message {
            color: #e74c3c;
            background-color: #ffeeee;
            border: 1px solid #e74c3c;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 8px;
            animation: shake 0.5s;
            text-align: left;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
            20%, 40%, 60%, 80% { transform: translateX(5px); }
        }

        @media (max-width: 480px) {
            .login-container {
                width: 90%;
                padding: 25px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Vet Login</h2>
        
        <?php 
        session_start();
        if (isset($_SESSION['login_error'])): ?>
            <div class="error-message">
                <?php 
                echo htmlspecialchars($_SESSION['login_error']); 
                unset($_SESSION['login_error']);
                ?>
            </div>
        <?php endif; ?>

        <form action="vet_login.php" method="POST">
            <input type="email" name="email" placeholder="Enter your email" required>
            <input type="password" name="password" placeholder="Enter your password" required>
            <button type="submit">Login</button>
        </form>
    </div>
</body>
</html>