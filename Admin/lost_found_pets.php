<?php
include('../db.php'); // Include the database connection
include('session_check.php'); // Include session check for admin

// Approve or Reject Pet Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && isset($_POST['pet_id'])) {
        $pet_id = intval($_POST['pet_id']); // Ensure it's an integer
        $action = $_POST['action']; // Action can be 'approve' or 'reject'

        if ($action === 'approve') {
            $sql = "UPDATE lost_and_found_pets SET approved = 1 WHERE id = ?";
        } elseif ($action === 'reject') {
            $sql = "UPDATE lost_and_found_pets SET approved = -1 WHERE id = ?";
        } else {
            echo "Invalid action!";
            exit;
        }

        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("i", $pet_id);
            $stmt->execute();
            $stmt->close();
        } else {
            echo "Failed to execute action: " . $conn->error;
            exit;
        }
    }
}

// Fetch all pets with user information
$sql = "SELECT p.*, u.first_name, u.last_name, u.email 
        FROM lost_and_found_pets p 
        LEFT JOIN users u ON p.user_id = u.id";
$result = $conn->query($sql);

// Separate approved and pending pets
$approved_pets = [];
$pending_pets = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        if ($row['approved'] == 1) {
            $approved_pets[] = $row;
        } elseif ($row['approved'] == 0) {
            $pending_pets[] = $row;
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Petiverse - Lost & Found Pets Management</title>
    <link rel="stylesheet" href="admin_sidebar.css">
    <link rel="stylesheet" href="assets/css/admin.css"> 
    <script src="logout_js.js"></script>

    <style>
    body {
        font-family: Arial, sans-serif;
        margin: 20px;
        background-color: #f9f9f9;
    }

    h2 {
        color: #333;
    }

    .table-container {
        margin-top: 20px;
        overflow-x: auto;
    }

    table {
        width: 100%;
        border: 1px solid #ccc;
        border-collapse: collapse;
        margin-bottom: 20px;
    }

    th, td {
        padding: 10px;
        border: 1px solid #ddd;
        text-align: left;
    }

    th {
        background-color: #f2f2f2;
    }

    tr:nth-child(even) {
        background-color: #fafafa;
    }

    .status {
        padding: 5px 10px;
        border-radius: 3px;
    }

    .status.pending {
        background-color: #ffcc00;
        color: white;
    }

    .status.approved {
        background-color: #4caf50;
        color: white;
    }

    .btn {
        padding: 5px 10px;
        text-decoration: none;
        border-radius: 3px;
        color: white;
    }

    .btn.approve {
        background-color: #4caf50;
    }

    .btn.reject {
        background-color: #f44336;
    }

    .btn.edit {
        background-color: #2196F3;
    }

    .btn.delete {
        background-color: #f44336;
    }

    .btn:hover {
        opacity: 0.8;
    }

    form {
        display: inline;
    }
</style>
</head>
<body>

<header>
    <div class="header-content">
        <h1>Lost & Found Pets Management</h1>
    </div>
</header>

<nav>
    <ul>
        <li><a href="dashboard.php">Dashboard</a></li>
        <li><a href="user_management.php">User Management</a></li>
        <li><a href="shop_management.php">Shop Management</a></li>
        <li><a href="community_controls.php">Community Controls</a></li>
        <li><a href="blog_management.php">Blog Management</a></li>
        <li><a href="lost_found_pets.php" class="active">Lost & Found Pets</a></li>
        <li><a href="special_events.php">Special Events</a></li>
        <li><a href="vet_management.php">Vet Management</a></li>
        <li><a href="moderator_management.php">Moderator Management</a></li>
        <li><a href="petselling.php">Pet selling</a></li>
        <li><a href="view_feedback.php">Feedbacks</a></li>
        <li><a href="logout.php" onclick="return confirmLogout();">Logout</a></li>
    </ul>
</nav>

<main>
    <h2>Manage Lost & Found Pets</h2>

    <!-- Pending Pets Table -->
    <h3>Pending Approval</h3>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Pet Name</th>
                    <th>Pet Type</th>
                    <th>Description</th>
                    <th>Location</th>
<<<<<<< HEAD
                    <th>User</th>
=======
                    <th>User</th> 
>>>>>>> 8e174373cfe696749201fd38eb04ef54c15e6dfb
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pending_pets as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['pet_name']) ?></td>
                        <td><?= htmlspecialchars($row['pet_type']) ?></td>
                        <td><?= htmlspecialchars($row['description']) ?></td>
                        <td><?= htmlspecialchars($row['location']) ?></td>
<<<<<<< HEAD
                        <td><?= htmlspecialchars($row['first_name']) . " " . htmlspecialchars($row['last_name']) ?></td>
                        <td><span class="status pending">Pending Approval</span></td>
=======
                        <td><?= htmlspecialchars($row['first_name']) . " " . htmlspecialchars($row['last_name']) ?></td> 
>>>>>>> 8e174373cfe696749201fd38eb04ef54c15e6dfb
                        <td>
                            <a href="?approve=true&pet_id=<?= $row['id'] ?>" class="btn approve">Approve</a>
                            <a href="?reject=true&pet_id=<?= $row['id'] ?>" class="btn reject">Reject</a>
                            <a href="edit_ad.php?pet_id=<?= $row['id'] ?>" class="btn edit">Edit</a>
                            <form method="POST" action="delete_ad.php">
                            <input type="hidden" name="pet_id" value="<?= htmlspecialchars($row['id']) ?>">
                            <button type="submit" class="btn delete" onclick="return confirm('Are you sure you want to delete this pet?');">Delete</button>
                            </form>

                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Approved Pets Table -->
    <h3>Approved Pets</h3>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Pet Name</th>
                    <th>Pet Type</th>
                    <th>Description</th>
                    <th>Location</th>
                    <th>User</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($approved_pets as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['pet_name']) ?></td>
                        <td><?= htmlspecialchars($row['pet_type']) ?></td>
                        <td><?= htmlspecialchars($row['description']) ?></td>
                        <td><?= htmlspecialchars($row['location']) ?></td>
                        <td><?= htmlspecialchars($row['first_name']) . " " . htmlspecialchars($row['last_name']) ?></td>
                        <td><span class="status approved">Approved</span></td>
                        <td>
                            <a href="edit_ad.php?id=<?= $row['id'] ?>" class="btn edit">Edit</a>
                        <form method="POST" action="delete_ad.php">
                            <!-- Replace $row['id'] with the actual variable holding the ID -->
                            <input type="hidden" name="pet_id" value="<?= htmlspecialchars($row['id']) ?>">
                            <button type="submit" class="btn delete" onclick="return confirm('Are you sure you want to delete this pet?');">Delete</button>
                        </form>

                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</main>

</body>
</html>

<?php
$conn->close();
?>
