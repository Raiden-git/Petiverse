<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Waiting for Admin Approval</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="./assets/css/scrollbar.css">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f4f7f6;
            margin: 0;
            padding: 20px;
            color: #333;
            line-height: 1.6;
        }
        .container {
            max-width: 700px;
            margin: 0 auto;
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        h2 {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 30px;
            border-bottom: 3px solid #3498db;
            padding-bottom: 10px;
        }
        p {
            color: #34495e;
            margin-bottom: 20px;
        }
        ul {
            list-style-type: square;
            padding-left: 20px;
            color: #34495e;
        }
        li {
            margin-bottom: 10px;
        }
        .info-box {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
        }
        button {
            width: 100%;
            padding: 12px;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        button:hover {
            background-color: #2980b9;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Thank You for Registering!</h2>
        <p>Your registration is currently under review by our admin team. You will be notified once your account is approved.</p>

        <div class="info-box">
            <h3>What’s Next?</h3>
            <p>In the meantime, please review the following guidelines for veterinarians on our platform:</p>
            <ul>
                <li>Ensure that your clinic location and contact details are accurate, as clients will rely on this information to book appointments.</li>
                <li>Once approved, you can set your availability and consultation fees through your vet dashboard.</li>
                <li>Appointments can be booked online or physically at your clinic, based on your preference.</li>
                <li>Maintain a professional and timely communication with clients through our in-app chat feature.</li>
                <li>Follow all legal and ethical guidelines in your region while offering your services through our platform.</li>
            </ul>
        </div>

        <button onclick="window.location.href='./vet/index.php';">Back to Login</button>
    </div>
</body>
</html>
