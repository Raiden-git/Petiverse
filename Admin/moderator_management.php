<?php
session_start();
include('../db.php');

// Initialize session message if not set
if (!isset($_SESSION['message'])) {
    $_SESSION['message'] = '';
}

// Add Moderator Functionality
if (isset($_POST['add_moderator'])) {
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $contact_number = $_POST['contact_number'];
    $address = $_POST['address'];

    $sql = "INSERT INTO moderator (email, password, first_name, last_name, contact_number, address)
            VALUES ('$email', '$password', '$first_name', '$last_name', '$contact_number', '$address')";

    if ($conn->query($sql) === TRUE) {
        $message = "Moderator added successfully.";
    } else {
        $message = "Error adding moderator: " . $conn->error;
    }
}

// Delete Moderator Functionality
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $sql = "DELETE FROM moderator WHERE id = $delete_id";

    if ($conn->query($sql) === TRUE) {
        $message = "Moderator deleted successfully.";
    } else {
        $message = "Error deleting moderator: " . $conn->error;
    }
}

// Edit Moderator Functionality
if (isset($_POST['edit_moderator'])) {
    $id = $_POST['id'];
    $email = $_POST['email'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $contact_number = $_POST['contact_number'];
    $address = $_POST['address'];

    $sql = "UPDATE moderator SET 
            email = '$email', 
            first_name = '$first_name', 
            last_name = '$last_name', 
            contact_number = '$contact_number', 
            address = '$address' 
            WHERE id = $id";

    if ($conn->query($sql) === TRUE) {
        $message = "Moderator updated successfully.";
    } else {
        $message = "Error updating moderator: " . $conn->error;
    }
}

// Fetch all moderators for display
$moderators = $conn->query("SELECT * FROM moderator");
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Moderator Management</title>
  <link rel="stylesheet" href="admin_sidebar.css">
  <style>

/* admin_sidebar.css */







/* Main Content */
main {
    margin-left: 280px;
    padding: 20px;
}

h2, h3 {
    color: #333;
    margin-bottom: 10px;
}

/* Form Styling */
form label {
    display: inline-block;
    width: 150px;
    font-weight: bold;
}

form input[type="email"],
form input[type="password"],
form input[type="text"] {
    padding: 8px;
    margin-bottom: 10px;
    width: calc(100% - 160px);
    border: 1px solid #ddd;
    border-radius: 4px;
}

form button {
    padding: 8px 15px;
    background-color: #333;
    color: #fff;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-weight: bold;
    margin-bottom: 60px;
    margin-top: 10px;
}

form button:hover {
    background-color: #555;
}



/* Table Styling */
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

table, th, td {
    border: 1px solid #ddd;
}

th, td {
    padding: 10px;
    text-align: left;
}

th {
    background-color: #333;
    color: #fff;
}

/* Modal Styling */
.modal {
    display: none;
    position: fixed;
    z-index: 1;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0, 0, 0, 0.4);
    padding-top: 60px;
}

.modal-content {
    background-color: #fff;
    margin: 5% auto;
    padding: 20px;
    border: 1px solid #888;
    width: 50%;
    border-radius: 8px;
}

.close {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
}

.close:hover,
.close:focus {
    color: #000;
    text-decoration: none;
}

/* Action Links */
a {
    color: #333;
    text-decoration: none;
    padding: 5px;
    font-weight: bold;
}

a:hover {
    color: #555;
}

/* Responsive Adjustments */
@media (max-width: 768px) {
    nav {
        width: 100%;
        height: auto;
        position: relative;
    }

    nav ul {
        display: flex;
        flex-direction: column;
        padding: 10px;
    }

    main {
        margin: 0;
        padding: 10px;
    }

    .modal-content {
        width: 90%;
    }
}



  </style>
</head>
<body>
<header>
    <h1>Moderator Management</h1>
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
<h2>Moderator Management</h2>
    <?php if (isset($message)) { echo "<p>$message</p>"; } ?>

    <!-- Add Moderator Form -->
    <h3>Add Moderator</h3>
    <form action="moderator_management.php" method="POST">
        <label>Email:</label>
        <input type="email" name="email" required><br>

        <label>Password:</label>
        <input type="password" name="password" required><br>

        <label>First Name:</label>
        <input type="text" name="first_name" required><br>

        <label>Last Name:</label>
        <input type="text" name="last_name" required><br>

        <label>Contact Number:</label>
        <input type="text" name="contact_number"><br>

        <label>Address:</label>
        <input type="text" name="address"><br>

        <button type="submit" name="add_moderator">Add Moderator</button>
    </form>

    <!-- Display Moderators Table -->
    <h3>Moderator List</h3>
    <table border="1">
        <tr>
            <th>Email</th>
            <th>First Name</th>
            <th>Last Name</th>
            <th>Contact Number</th>
            <th>Address</th>
            <th>Actions</th>
        </tr>
        <?php while ($row = $moderators->fetch_assoc()) { ?>
        <tr>
            <td><?= $row['email'] ?></td>
            <td><?= $row['first_name'] ?></td>
            <td><?= $row['last_name'] ?></td>
            <td><?= $row['contact_number'] ?></td>
            <td><?= $row['address'] ?></td>
            <td>
                <a href="javascript:void(0)" onclick="openModal(<?= $row['id'] ?>)">Edit</a>
                <a href="moderator_management.php?delete_id=<?= $row['id'] ?>" onclick="return confirm('Are you sure?')">Delete</a>
            </td>
        </tr>
        <?php } ?>
    </table>

    <!-- Edit Moderator Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h3>Edit Moderator</h3>
            <form id="editForm" action="moderator_management.php" method="POST">
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

                <button type="submit" name="edit_moderator">Update Moderator</button>
            </form>
        </div>
    </div>
</main>

<script>
    function openModal(id) {
        document.getElementById('editModal').style.display = "block";

        // Fetch moderator data from the server (AJAX request)
        fetch(`get_moderator.php?id=${id}`)
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

</body>
</html>





























