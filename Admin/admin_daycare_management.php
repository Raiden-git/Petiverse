<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="admin_sidebar.css">
    <title>Daycare Management</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .map {
            height: 150px;
            width: 100%;
        }

        .container {
            text-align: center;
            padding: 50px;
        }
        h2 {
            color: #333;
            font-size: 2.5em;
            margin-bottom: 10px;
        }
        h3 {
            margin-top: 0;
        }
        #btn {
            text-decoration: none;
            background-color: #28a745;
            color: white;
            padding: 15px 30px;
            font-size: 1.2em;
            border-radius: 5px;
            display: inline-block;
            margin-top: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
        }
        #btn:hover {
            background-color: #218838;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }
    </style>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDHdLOieN0OIcTyyY6CmJv6gPNx-OX3MwA"></script>
</head>
<body>
<header>
    <h1>Daycare Management</h1>
</header>

<nav>
    <!-- Navigation menu remains the same as in the previous script -->
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
        <li><a href="petselling.php">Pet selling</a></li>
        <li><a href="view_feedback.php">Feedbacks</a></li>
        <li><a href="logout.php" onclick="return confirmLogout();">Logout</a></li>
    </ul>
</nav>

<main>
<div class="container">
    <h2>New Daycare Location</h2>
    <h3><a href="admin_daycare.php" id="btn">Add New Daycare Location</a></h3>
</div>

    <h1>Manage Pet Daycare Locations</h1>

    <!-- Daycare table with map -->
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Address</th>
                <th>Location Map</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            include '../db.php'; 

            // Fetch all daycare locations
            $sql = "SELECT * FROM daycare_locations";
            $result = mysqli_query($conn, $sql);
            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $latitude = $row['latitude'];
                    $longitude = $row['longitude'];
                    echo "<tr>
                        <td>{$row['id']}</td>
                        <td>{$row['name']}</td>
                        <td>{$row['address']}</td>
                        <td>
                            <div id='map-{$row['id']}' class='map'></div>
                            <script>
                                function initMap_{$row['id']}() {
                                    var location = { lat: $latitude, lng: $longitude };
                                    var map = new google.maps.Map(document.getElementById('map-{$row['id']}'), {
                                        zoom: 15,
                                        center: location
                                    });
                                    new google.maps.Marker({
                                        position: location,
                                        map: map
                                    });
                                }
                                initMap_{$row['id']}();
                            </script>
                        </td>
                        <td>
                            <a href='edit_daycare.php?id={$row['id']}'>Edit</a> | 
                            <a href='delete_daycare.php?id={$row['id']}' onclick='return confirm(\"Are you sure you want to delete this daycare?\")'>Delete</a>
                        </td>
                    </tr>";
                }
            } else {
                echo "<tr><td colspan='5'>No daycare locations found.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</main>
</body>
</html>
