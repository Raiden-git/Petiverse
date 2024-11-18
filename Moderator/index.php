<?php
session_start();
include('../db.php');


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM moderator WHERE email = '$email'";
    $result = $conn->query($sql);
    $moderator = $result->fetch_assoc();

    if ($moderator && password_verify($password, $moderator['password'])) {
        $_SESSION['moderator_id'] = $moderator['id'];
        header("Location: moderator_dashboard.php");
        exit();
    } else {
        echo "Invalid login credentials.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Petiverse - Moderator Login</title>
    <style>
        /* Style for the body and background */
body {
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 100vh;
    margin: 0;
    font-family: Arial, sans-serif;
    background-color: #f2f2f2;
}

/* Style for the form container */
form {
    background-color: #ffffff;
    padding: 20px 30px;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    width: 300px;
    text-align: center;
}

/* Header */
h2 {
    margin-bottom: 20px;
    color: #333333;
}

/* Labels */
label {
    display: block;
    margin-top: 10px;
    font-size: 14px;
    color: #666666;
}

/* Input fields */
input[type="email"],
input[type="password"] {
    width: 100%;
    padding: 10px;
    margin-top: 5px;
    border: 1px solid #cccccc;
    border-radius: 4px;
    box-sizing: border-box;
    font-size: 14px;
}

/* Submit button */
button[type="submit"] {
    width: 100%;
    padding: 10px;
    margin-top: 15px;
    background-color: #4CAF50;
    border: none;
    border-radius: 4px;
    color: white;
    font-size: 16px;
    cursor: pointer;
}

button[type="submit"]:hover {
    background-color: #45a049;
}

/* Error message */
.error {
    color: red;
    margin-top: 10px;
    font-size: 14px;
}

    </style>
</head>
<body>
    
    <form action="index.php" method="POST">
    <h2>Login as Moderator</h2>
        <label>Email:</label>
        <input type="email" name="email" required><br>

        <label>Password:</label>
        <input type="password" name="password" required><br>

        <button type="submit">Login</button>
    </form>
</body>
</html>
