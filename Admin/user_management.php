<?php
include('../db.php');
include('session_check.php');

// Handle user deletion
if (isset($_GET['delete_id'])) {
    $userId = intval($_GET['delete_id']);
    $deleteSql = "DELETE FROM users WHERE id = $userId";

    if ($conn->query($deleteSql) === TRUE) {
        echo "<p style='color: green;'>User deleted successfully.</p>";
    } else {
        echo "<p style='color: red;'>Error deleting user: " . $conn->error . "</p>";
    }
}

// Handle user edit form submission
if (isset($_POST['edit_user'])) {
    $userId = intval($_POST['id']);
    $email = $_POST['email'];
    $firstName = $_POST['first_name'];
    $lastName = $_POST['last_name'];
    $contactNumber = $_POST['contact_number'];
    $address = $_POST['address'];

    $updateSql = "UPDATE users SET 
                    email = '$email', 
                    first_name = '$firstName', 
                    last_name = '$lastName', 
                    contact_number = '$contactNumber', 
                    address = '$address' 
                    WHERE id = $userId";

    if ($conn->query($updateSql) === TRUE) {
        echo "<p style='color: green;'>User updated successfully.</p>";
    } else {
        echo "<p style='color: red;'>Error updating user: " . $conn->error . "</p>";
    }
}

// Fetch users from the `users` table
$sql = "SELECT id, email, first_name, last_name, gender, full_name, contact_number, address, created_at FROM users";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="admin_sidebar.css">
    <script src="logout_js.js"></script>
    <title>Petiverse - User Management</title>

    <style>
        /* Container Styling */
        .container {
            width: 100%;
            max-width: 1200px;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #007bff;
            color: #fff;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .action-btn {
            color: blue;
            text-decoration: none;
            font-weight: bold;
        }
        .delete-btn {
            color: red;
        }










        /* Modal Background */
.modal {
    display: none; 
    position: fixed; 
    z-index: 1; 
    left: 0;
    top: 0;
    width: 100%; 
    height: 100%; 
    overflow: auto; 
    background-color: rgba(0, 0, 0, 0.5); 
    padding-top: 70px;
}

/* Modal Content Box */
.modal-content {
    background-color: #fefefe;
    margin: 5% auto; 
    padding: 20px;
    border: 1px solid #888;
    width: 40%; 
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    animation: fadeIn 0.3s ease;
}

/* Close Button */
.close {
    color: #aaa;
    float: right;
    font-size: 24px;
    font-weight: bold;
    cursor: pointer;
}

.close:hover,
.close:focus {
    color: #000;
    text-decoration: none;
}

/* Form Label and Input Styles */
.modal-content label {
    display: block;
    font-weight: bold;
    margin: 10px 0 5px;
}

.modal-content input[type="text"],
.modal-content input[type="email"],
.modal-content input[type="number"] {
    width: 100%;
    padding: 8px;
    margin-bottom: 10px;
    border: 1px solid #ccc;
    border-radius: 4px;
    box-sizing: border-box;
    font-size: 14px;
}

/* Submit Button */
.modal-content button[type="submit"] {
    background-color: #007bff;
    color: white;
    padding: 10px 15px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 16px;
    width: 100%;
}

.modal-content button[type="submit"]:hover {
    background-color: #0056b3;
}

/* Animation for Modal */
@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

    </style>
</head>
<body>
<header>
    <h1>User Management</h1>
</header>
<nav>
    <ul>
        <li><a href="dashboard.php">Home</a></li>
        <li><a href="user_management.php">User Management</a></li>
        <li><a href="shop_management.php">Shop Management</a></li>
        <li><a href="community_controls.php">Community Controls</a></li>
        <li><a href="blog_management.php">Blog Management</a></li>
        <li><a href="lost_found_pets.php">Lost & Found Pets</a></li>
        <li><a href="special_events.php">Special Events</a></li>
        <li><a href="vet_management.php">Vet Management</a></li>
        <li><a href="moderator_management.php">Moderator Management</a></li>
        <li><a href="logout.php" onclick="return confirmLogout();">Logout</a></li>
    </ul>
</nav>

<main>
<div class="container">
    <h2>User Details</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Email</th>
            <th>First Name</th>
            <th>Last Name</th>
            <th>Gender</th>
            <th>Full Name</th>
            <th>Contact Number</th>
            <th>Address</th>
            <th>Created At</th>
            <th>Action</th>
        </tr>
        <?php if ($result->num_rows > 0) : ?>
            <?php while ($row = $result->fetch_assoc()) : ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= $row['email'] ?></td>
                    <td><?= $row['first_name'] ?></td>
                    <td><?= $row['last_name'] ?></td>
                    <td><?= $row['gender'] ?></td>
                    <td><?= $row['full_name'] ?></td>
                    <td><?= $row['contact_number'] ?></td>
                    <td><?= $row['address'] ?></td>
                    <td><?= $row['created_at'] ?></td>
                    <td>
                        <a href="javascript:void(0)" class="action-btn" onclick="openModal(<?= $row['id'] ?>)">Edit</a> |
                        <a href="?delete_id=<?= $row['id'] ?>" class="delete-btn" onclick="return confirm('Are you sure you want to delete this user?');">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else : ?>
            <tr><td colspan="10">No users found</td></tr>
        <?php endif; ?>
    </table>
</div>

<!-- Edit User Modal -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h3>Edit User</h3>
        <form id="editForm" action="user_management.php" method="POST">
            <input type="hidden" name="id" id="modal_id">
            <label>Email:</label>
            <input type="email" name="email" id="modal_email" required><br>
            <label>First Name:</label>
            <input type="text" name="first_name" id="modal_first_name" required><br>
            <label>Last Name:</label>
            <input type="text" name="last_name" id="modal_last_name" required><br>
            <label>Contact Number:</label>
            <input type="text" name="contact_number" id="modal_contact_number"><br>
            <label>Address:</label>
            <input type="text" name="address" id="modal_address"><br>
            <button type="submit" name="edit_user">Update User</button>
        </form>
    </div>
</div>

<script>
    function openModal(id) {
        document.getElementById('editModal').style.display = "block";
        
        fetch(`get_user.php?id=${id}`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('modal_id').value = data.id;
                document.getElementById('modal_email').value = data.email;
                document.getElementById('modal_first_name').value = data.first_name;
                document.getElementById('modal_last_name').value = data.last_name;
                document.getElementById('modal_contact_number').value = data.contact_number;
                document.getElementById('modal_address').value = data.address;
            });
    }

    function closeModal() {
        document.getElementById('editModal').style.display = "none";
    }
</script>
</main>
</body>
</html>

<?php
$conn->close();
?>
