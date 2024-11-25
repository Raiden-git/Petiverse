<?php
require 'db.php'; // Include your database connection
session_start();

if (!isset($_SESSION['user_id']) || !isset($_GET['session_id']) || !isset($_GET['plan'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$plan = $_GET['plan'];
$start_date = date('Y-m-d');
$end_date = $plan === 'monthly' ? date('Y-m-d', strtotime('+1 month')) : date('Y-m-d', strtotime('+1 year'));

// Update user's subscription in the database
$query = "UPDATE users SET is_premium = 1, premium_start_date = '$start_date', premium_end_date = '$end_date', subscription_plan = '$plan' WHERE id = $user_id";
$conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Success</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .popup {
            display: none;
            background-color: #fff;
            border-radius: 10px;
            padding: 40px;
            box-shadow: 0px 4px 20px rgba(0, 0, 0, 0.2);
            text-align: center;
            max-width: 400px;
            animation: popupAnimation 0.5s ease-out forwards;
        }

        .popup h2 {
            color: #28a745;
            font-size: 24px;
            margin-bottom: 10px;
        }

        .popup p {
            font-size: 18px;
            color: #555;
            margin-bottom: 20px;
        }

        .popup i {
            font-size: 50px;
            color: #28a745;
            margin-bottom: 20px;
        }

        @keyframes popupAnimation {
            from {
                transform: scale(0);
                opacity: 0;
            }
            to {
                transform: scale(1);
                opacity: 1;
            }
        }
    </style>
</head>
<body>
    <div class="popup" id="successPopup">
        <i class="fas fa-check-circle"></i>
        <h2>Payment Successful!</h2>
        <p>Thank you for subscribing to our premium plan.</p>
    </div>

    <script>
        // Show the popup after the page loads
        document.addEventListener('DOMContentLoaded', function () {
            const popup = document.getElementById('successPopup');
            popup.style.display = 'block';
            
            // Redirect to index.php after 3 seconds
            setTimeout(function () {
                window.location.href = 'index.php';
            }, 5000); // 3-second delay
        });
    </script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/js/all.min.js"></script>
</body>
</html>
