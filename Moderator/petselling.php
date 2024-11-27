<?php
include '../db.php';
include('session_check.php');

// Fetch pending and approved ads
$queryPending = "SELECT * FROM pet_selling_ads WHERE status = 'pending'";
$resultPending = $conn->query($queryPending);

$queryPublished = "SELECT * FROM pet_selling_ads WHERE status = 'approved'";
$resultPublished = $conn->query($queryPublished);

// Handle ad approval/rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['ad_id']) && isset($_POST['action'])) {
        $ad_id = intval($_POST['ad_id']);
        $action = $_POST['action'];

        $status = $action === 'approve' ? 'approved' : 'rejected';
        $stmt = $conn->prepare("UPDATE pet_selling_ads SET status = ? WHERE ad_id = ?");
        $stmt->bind_param("si", $status, $ad_id);

        if ($stmt->execute()) {
            echo "<script>alert('Ad has been $status.'); window.location.href = 'petselling.php';</script>";
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
    }

    // Handle ad deletion
    if (isset($_POST['delete_id'])) {
        $delete_id = intval($_POST['delete_id']);
        $stmt = $conn->prepare("DELETE FROM pet_selling_ads WHERE ad_id = ?");
        $stmt->bind_param("i", $delete_id);

        if ($stmt->execute()) {
            echo "<script>alert('Ad has been deleted.'); window.location.href = 'petselling.php';</script>";
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Pet Selling - Admin Panel</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Petiverse - Lost & Found Pets Management</title>
    <link rel="stylesheet" href="../Moderator/moderator_sidebar.css">
    <script src="logout_js.js"></script>
    
        <style>
    body {
        font-family: Arial, sans-serif;
        margin: 0;
        padding: 0;
        background-color: #f9f9f9;
    }

    header {
        background-color: #333;
        color: white;
        padding: 15px;
        text-align: center;
    }

    
    main {
        padding: 20px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
        background-color: white;
    }

    table th, table td {
        border: 1px solid #ddd;
        padding: 10px;
        text-align: left;
    }

    table th {
        background-color: #f4f4f4;
    }

    table tr:nth-child(even) {
        background-color: #f9f9f9;
    }

    table tr:hover {
        background-color: #f1f1f1;
    }

    .delete-btn {
        background-color: #e74c3c;
        color: white;
        border: none;
        padding: 5px 10px;
        cursor: pointer;
        border-radius: 3px;
    }

    .delete-btn:hover {
        background-color: #c0392b;
    }
</style>

    </style>
</head>
<body>
    <header>
        <h1>Admin Panel - Pet Selling</h1>
    </header>
    
    <nav>
    <ul>
    <li><a href="moderator_dashboard.php">Home</a></li>
        <li><a href="Moderator_shop_management.php">Shop Management</a></li>
        <li><a href="community_controls.php">Community Controls</a></li>
        <li><a href="blog_management.php">Blog Management</a></li>
        <li><a href="admin_daycare_management.php">Daycare Management</a></li>
        <li><a href="lost_found_pets.php">Lost & Found Pets</a></li>
        <li><a href="special_events.php">Special Events</a></li>
        <li><a href="vet_management.php">Vet Management</a></li>
        <li><a href="petselling.php">Pet selling</a><li>
        <li><a href="view_feedback.php">Feedbacks</a></li>
        <li><a href="logout.php" onclick="return confirmLogout();">Logout</a></li>
    </ul>
</nav>

    <main>
        <!-- Pending Ads Section -->
        <h2>Pending Ads</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Pet Name</th>
                    <th>Type</th>
                    <th>Breed</th>
                    <th>Description</th>
                    <th>Price</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($resultPending->num_rows > 0): ?>
                    <?php while ($row = $resultPending->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['ad_id']) ?></td>
                            <td><?= htmlspecialchars($row['pet_name']) ?></td>
                            <td><?= htmlspecialchars($row['pet_type']) ?></td>
                            <td><?= htmlspecialchars($row['pet_breed']) ?></td>
                            <td><?= htmlspecialchars($row['description']) ?></td>
                            <td>$<?= htmlspecialchars($row['price']) ?></td>
                            <td>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="ad_id" value="<?= $row['ad_id'] ?>">
                                    <button type="submit" name="action" value="approve" class="approve-btn">Approve</button>
                                    <button type="submit" name="action" value="reject" class="reject-btn">Reject</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7">No pending ads to review.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Published Ads Section -->
        <h2>Published Ads</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Pet Name</th>
                    <th>Type</th>
                    <th>Breed</th>
                    <th>Description</th>
                    <th>Price</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($resultPublished->num_rows > 0): ?>
                    <?php while ($row = $resultPublished->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['ad_id']) ?></td>
                            <td><?= htmlspecialchars($row['pet_name']) ?></td>
                            <td><?= htmlspecialchars($row['pet_type']) ?></td>
                            <td><?= htmlspecialchars($row['pet_breed']) ?></td>
                            <td><?= htmlspecialchars($row['description']) ?></td>
                            <td>$<?= htmlspecialchars($row['price']) ?></td>
                            <td>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="delete_id" value="<?= $row['ad_id'] ?>">
                                    <button type="submit" onclick="return confirm('Are you sure you want to delete this ad?');" class="delete-btn">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7">No published ads found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </main>
</body>
</html>
