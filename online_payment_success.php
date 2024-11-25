



<?php
// Retrieve the order_id
$order_id = isset($_GET['order_id']) ? htmlspecialchars($_GET['order_id']) : 'N/A';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Success</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
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
        <p>Thank you for your purchase.</p>
        <p>You will be redirected to the homepage shortly.</p>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const popup = document.getElementById('successPopup');
            popup.style.display = 'block';
            setTimeout(function () {
                window.location.href = 'index.php';
            }, 5000);
        });
    </script>
</body>
</html>
