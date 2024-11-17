<?php
include('../db.php');

?>

<!DOCTYPE html>
<html>
<head>
    <title>Moderator Panel</title>
    <link rel="stylesheet" href="moderator_sidebar.css">
    <style>
        /* General Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Arial', sans-serif;
        }

        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background: #f4f6f9;
            color: #333;
        }

        header {
            margin-left: 250px;
            background-color: #333;
            color: white;
            padding: 20px 0;
            text-align: center;
        }

        header h1 {
            font-size: 2.5rem;
            letter-spacing: 2px;
        }

        main {
            margin-left: 260px; /* Space for the sidebar */
            padding: 40px;
            flex: 1;
        }

        main h2 {
            font-size: 2rem;
            color: #333;
        }

        main p {
            margin-top: 10px;
            font-size: 1.1rem;
            line-height: 1.6;
        }

        /* Responsive Styles */
        @media (max-width: 768px) {

            main {
                margin-left: 0;
                padding: 20px;
            }
        }

    </style>
    <script>
        // JavaScript function to display confirmation
        function confirmLogout() {
            return confirm("Do you really want to log out?");
        }
    </script>
</head>
<body>
<header>
    <h1>Moderator Panel</h1>
</header>

<nav>
    <ul>
        <li><a href="moderator_dashboard.php">Home</a></li>
        <li><a href="moderator_shop_management.php">Shop Management</a></li>
        <li><a href="community_controls.php">Community Controls</a></li>
        <li><a href="blog_management.php">Blog Management</a></li>
        <li><a href="lost_found_pets.php">Lost & Found Pets</a></li>
        <li><a href="special_events.php">Special Events</a></li>
        <li><a href="vet_management.php">Vet Management</a></li>
        <li><a href="logout.php" onclick="return confirmLogout();">Logout</a></li>
    </ul>
</nav>

<main>

</main>
</body>
</html>