<?php
include('../db.php');
include('session_check.php');

// Database connection
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Approve or reject pet submission
if (isset($_GET['approve']) && isset($_GET['pet_id'])) {
    $pet_id = $_GET['pet_id'];
    $sql = "UPDATE lost_and_found_pets SET approved = 1 WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $pet_id);
    $stmt->execute();
    $stmt->close();
}

if (isset($_GET['reject']) && isset($_GET['pet_id'])) {
    $pet_id = $_GET['pet_id'];
    $sql = "UPDATE lost_and_found_pets SET approved = -1 WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $pet_id);
    $stmt->execute();
    $stmt->close();
}

// Fetch all pets (pending, approved, rejected) with user info
$sql = "SELECT p.*, u.first_name, u.last_name, u.email 
        FROM lost_and_found_pets p 
        LEFT JOIN users u ON p.user_id = u.id";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Petiverse - Lost & Found Pets Management</title>
    <link rel="stylesheet" href="../Moderator/moderator_sidebar.css">
    <link rel="stylesheet" href="assets/css/admin.css"> 
    <script src="logout_js.js"></script>
</head>
<body>

<header>
    <div class="header-content">
        <h1>Lost & Found Pets Management</h1>
    </div>
</header>

<nav>
    <ul>
        <li><a href="moderator_dashboard.php">Home</a></li>
        <li><a href="Moderator_shop_management.php">Shop Management</a></li>
        <li><a href="community_controls.php">Community Controls</a></li>
        <li><a href="Moderator_add_blog.php">Blog Management</a></li>
        <li><a href="lost_found_pets.php" class="active">Lost & Found Pets</a></li>
        <li><a href="special_events.php">Special Events</a></li>
        <li><a href="vet_management.php">Vet Management</a></li>
        <li><a href="logout.php" onclick="return confirmLogout();">Logout</a></li>
    </ul>
</nav>

<main>
    <h2>Manage Lost & Found Pets</h2>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Pet Name</th>
                    <th>Pet Type</th>
                    <th>Description</th>
                    <th>Location</th>
                    <th>User</th> <!-- Add this column for displaying the user's name -->
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['pet_name']) ?></td>
                        <td><?= htmlspecialchars($row['pet_type']) ?></td>
                        <td><?= htmlspecialchars($row['description']) ?></td>
                        <td><?= htmlspecialchars($row['location']) ?></td>
                        <td><?= htmlspecialchars($row['first_name']) . " " . htmlspecialchars($row['last_name']) ?></td> <!-- Display user's full name -->
                        <td>
                            <?php 
                            if ($row['approved'] == 0) {
                                echo "<span class='status pending'>Pending Approval</span>";
                            } elseif ($row['approved'] == 1) {
                                echo "<span class='status approved'>Approved</span>";
                            } else {
                                echo "<span class='status rejected'>Rejected</span>";
                            }
                            ?>
                        </td>
                        <td>
                            <?php if ($row['approved'] == 0): ?>
                                <a href="?approve=true&pet_id=<?= $row['id'] ?>" class="btn approve">Approve</a>
                                <a href="?reject=true&pet_id=<?= $row['id'] ?>" class="btn reject">Reject</a>
                            <?php elseif ($row['approved'] == 1): ?>
                                <span class="btn disabled">Already Approved</span>
                            <?php else: ?>
                                <span class="btn disabled">Already Rejected</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</main>

</body>
</html>

<?php
$conn->close();
?>
