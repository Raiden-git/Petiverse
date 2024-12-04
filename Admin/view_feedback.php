<?php
include('../db.php');
include('session_check.php');

// Query to get all feedback from the feedback table
$sql = "SELECT id, name, email, message, created_at FROM feedback ORDER BY created_at DESC";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Petiverse - Admin Panel</title>
    <link rel="stylesheet" href="admin_sidebar.css">
    
    <!-- Font Awesome for icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

    <script>
        function confirmLogout() {
            return confirm("Do you really want to log out?");
        }
    </script>
      <style>

        .admin-container {
            margin: 20px;
            font-family: Arial, sans-serif;
        }

        h1 {
            font-size: 24px;
            color: #333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table, th, td {
            border: 1px solid #ddd;
        }

        th, td {
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        a {
            color: #007BFF;
            text-decoration: none;
        }

        a:hover {
            text-decoration: none;
        }



    </style>

</head>
<body>
<header>
    <h1>Admin Panel</h1>
</header>

<nav>
    <ul>
        <li><a href="dashboard.php">Home</a></li>
        <li><a href="user_management.php">User Management</a></li>
        <li><a href="shop_management.php">Shop Management</a></li>
        <li><a href="community_controls.php">Community Controls</a></li>
        <li><a href="blog_management.php">Blog Management</a></li>
        <li><a href="admin_daycare_management.php">Daycare Management</a></li>
        <li><a href="lost_found_pets.php">Lost & Found Pets</a></li>
        <li><a href="special_events.php">Special Events</a></li>
        <li><a href="vet_management.php">Vet Management</a></li>
        <li><a href="moderator_management.php">Moderator Management</a></li>
        <li><a href="petselling.php">Pet selling</a><li>        
        <li><a href="view_feedback.php">Feedbacks</a></li>
        <li><a href="logout.php" onclick="return confirmLogout();">Logout</a></li>
    </ul>
</nav>

<main>
<div class="admin-container">
        <h1>Feedback Management</h1>

        <?php
        if (mysqli_num_rows($result) > 0) {
            echo "<table border='1'>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Feedback Message</th>
                        <th>Date Submitted</th>
                        <th>Actions</th>
                    </tr>";
            
            // Fetch each row and display it in the table
            while($row = mysqli_fetch_assoc($result)) {
                echo "<tr>
                        <td>" . $row['id'] . "</td>
                        <td>" . $row['name'] . "</td>
                        <td>" . $row['email'] . "</td>
                        <td>" . $row['message'] . "</td>
                        <td>" . $row['created_at'] . "</td>
                        <td><a href='delete_feedback.php?id=" . $row['id'] . "' onclick=\"return confirm('Are you sure you want to delete this feedback?');\">Delete</a></td>
                    </tr>";
            }

            echo "</table>";
        } else {
            echo "<p>No feedback available.</p>";
        }

        // Close the database connection
        mysqli_close($conn);
        ?>
    </div>

</main>
</body>
</html>
