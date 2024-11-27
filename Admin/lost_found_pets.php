<?php
include('../db.php'); 
include('session_check.php'); 

// Approve or Reject Pet Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && isset($_POST['pet_id'])) {
        $pet_id = intval($_POST['pet_id']); 
        $action = $_POST['action']; 

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

   /* General Reset */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: Arial, sans-serif;
}


/* Body Styling */
body {
    margin: 20px;
    background-color: #f9f9f9;
    color: #333;
    line-height: 1.6;
}

/* Headings */
h2 {
    color: #333;
    text-align: center;
    margin-bottom: 20px;
    font-size: 1.8rem;
}

/* Table Container */
.table-container {
    margin-top: 20px;
    overflow-x: auto;
    background: #fff;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

/* Table Styling */
table {
    width: 100%;
    border: 1px solid #ddd;
    border-collapse: collapse;
    margin-bottom: 20px;
}

th, td {
    padding: 12px;
    border: 1px solid #ddd;
    text-align: left;
    font-size: 1rem;
}

th {
    background-color: #007bff;
    color: white;
    font-weight: bold;
}

tr:nth-child(even) {
    background-color: #f9f9f9;
}

tr:hover {
    background-color: #f1f1f1;
}

/* Status Labels */
.status {
    padding: 5px 12px;
    border-radius: 5px;
    font-size: 0.9rem;
    font-weight: bold;
    display: inline-block;
}

.status.pending {
    background-color: #ffcc00;
    color: white;
}

.status.approved {
    background-color: #28a745;
    color: white;
}

/* Buttons */
.btn {
    display: inline-block;
    padding: 8px 15px;
    text-decoration: none;
    border-radius: 5px;
    color: white;
    font-size: 0.9rem;
    font-weight: bold;
    transition: background-color 0.3s ease, transform 0.2s ease;
    text-align: center;
}

.btn.approve {
    background-color: #28a745;
}

.btn.reject {
    background-color: #dc3545;
}

.btn.edit {
    background-color: #007bff;
}

.btn.delete {
    background-color: #6c757d;
}

.btn:hover {
    opacity: 0.9;
    transform: scale(1.02);
}

/* Form Styling (Inline Actions) */
form {
    display: inline;
}

/* Media Query for Mobile Responsiveness */
@media (max-width: 768px) {
    table {
        font-size: 0.9rem;
    }

    .btn {
        padding: 6px 10px;
        font-size: 0.8rem;
    }
}
/* Edit Link Button Styling */
a.btn-edit {
    display: inline-block;
    background-color: #007bff;
    color: white;
    padding: 8px 15px;
    border-radius: 5px;
    text-align: center;
    font-size: 0.9rem;
    font-weight: bold;
    text-decoration: none; 
    transition: background-color 0.3s ease, transform 0.2s ease;
}

a.btn-edit:hover {
    background-color: #0056b3; 
    transform: scale(1.05);
}

a.btn-edit:active {
    background-color: #004085; /* Even darker blue on click */
}

/* Optional: Add a border to match button-like appearance */
a.btn-edit {
    border: 1px solid transparent;
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
        <li><a href="admin_daycare_management.php">Daycare Management</a></li>
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
                    <th>User</th>
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
                        <td><?= htmlspecialchars($row['first_name']) . " " . htmlspecialchars($row['last_name']) ?></td>
                        <td><span class="status pending">Pending Approval</span></td>
                        <td>
                        <form method="POST" action="lost_found_pets.php">
                    <input type="hidden" name="action" value="approve">
                    <input type="hidden" name="pet_id" value="<?= htmlspecialchars($row['id']) ?>">
                    <button type="submit" class="btn approve">Approve</button>
                    </form>
                    <form method="POST" action="lost_found_pets.php">
                    <input type="hidden" name="action" value="reject">
                    <input type="hidden" name="pet_id" value="<?= htmlspecialchars($row['id']) ?>">
                    <button type="submit" class="btn reject">Reject</button>
                    </form>
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
                        <a href="edit_ad.php?pet_id=<?= $row['id'] ?>" class="btn-edit">Edit</a>


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
