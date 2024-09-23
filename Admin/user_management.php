<?php
include('../db.php');
include('session_check.php');

// Handle form submission to add a new user
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $contact_number = $_POST['contact_number'];
    $email = $_POST['email'];
    $gender = $_POST['gender'];
    $role = $_POST['role'];

    // Check if passwords match
    if ($password != $confirm_password) {
        $error_message = "Passwords do not match!";
    } else {
        // Hash the password for security
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        // Insert into the respective table based on role
        if ($role == 'moderator') {
            $sql = "INSERT INTO moderators (name, password, contact_number, email, gender) 
                    VALUES ('$name', '$hashed_password', '$contact_number', '$email', '$gender')";
        } elseif ($role == 'vet') {
            $sql = "INSERT INTO vets (name, password, contact_number, email, gender) 
                    VALUES ('$name', '$hashed_password', '$contact_number', '$email', '$gender')";
        } elseif ($role == 'customer') {
            $sql = "INSERT INTO customers (name, password, contact_number, email, gender) 
                    VALUES ('$name', '$hashed_password', '$contact_number', '$email', '$gender')";
        }

        if ($conn->query($sql) === TRUE) {
            $success_message = "User created successfully!";
        } else {
            $error_message = "Error: " . $conn->error;
        }
    }
}

// Fetch users for each role
$moderators_result = $conn->query("SELECT * FROM moderators");
$vets_result = $conn->query("SELECT * FROM vets");
$customers_result = $conn->query("SELECT * FROM customers");
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Management</title>
    <link rel="stylesheet" href="admin_sidebar.css">
    <style>
        .form-container {
            margin: 20px;
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .form-container input, select {
            margin-bottom: 10px;
            padding: 10px;
            width: 100%;
            border-radius: 5px;
        }

        .user-tables {
            margin: 20px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table, th, td {
            border: 1px solid #ddd;
        }

        th, td {
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f4f4f4;
        }
    </style>
    <script src="logout_js.js"></script>
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
        <li><a href="logout.php">Logout</a></li>
    </ul>
</nav>

<main>
    <h2>Create User</h2>
    <div class="form-container">
        <form action="user_management.php" method="POST">
            <label for="name">Name:</label>
            <input type="text" id="name" name="name" required>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>

            <label for="confirm_password">Confirm Password:</label>
            <input type="password" id="confirm_password" name="confirm_password" required>

            <label for="contact_number">Contact Number:</label>
            <input type="text" id="contact_number" name="contact_number" required>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>

            <label for="gender">Gender:</label>
            <select id="gender" name="gender" required>
                <option value="male">Male</option>
                <option value="female">Female</option>
                <option value="other">Other</option>
            </select>

            <label for="role">Role:</label>
            <select id="role" name="role" required>
                <option value="moderator">Moderator</option>
                <option value="vet">Vet</option>
                <option value="customer">Customer</option>
            </select>

            <button type="submit">Create User</button>
        </form>

        <?php if (isset($success_message)) { echo "<p style='color:green;'>$success_message</p>"; } ?>
        <?php if (isset($error_message)) { echo "<p style='color:red;'>$error_message</p>"; } ?>
    </div>

    <div class="user-tables">
        <h2>Moderators</h2>
        <table>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Contact Number</th>
                <th>Email</th>
                <th>Gender</th>
                <th>Created At</th>
            </tr>
            <?php while ($row = $moderators_result->fetch_assoc()) { ?>
            <tr>
                <td><?php echo $row['id']; ?></td>
                <td><?php echo $row['name']; ?></td>
                <td><?php echo $row['contact_number']; ?></td>
                <td><?php echo $row['email']; ?></td>
                <td><?php echo $row['gender']; ?></td>
                <td><?php echo $row['created_at']; ?></td>
            </tr>
            <?php } ?>
        </table>

        <h2>Vets</h2>
        <table>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Contact Number</th>
                <th>Email</th>
                <th>Gender</th>
                <th>Created At</th>
            </tr>
            <?php while ($row = $vets_result->fetch_assoc()) { ?>
            <tr>
                <td><?php echo $row['id']; ?></td>
                <td><?php echo $row['name']; ?></td>
                <td><?php echo $row['contact_number']; ?></td>
                <td><?php echo $row['email']; ?></td>
                <td><?php echo $row['gender']; ?></td>
                <td><?php echo $row['created_at']; ?></td>
            </tr>
            <?php } ?>
        </table>

        <h2>Customers</h2>
        <table>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Contact Number</th>
                <th>Email</th>
                <th>Gender</th>
                <th>Created At</th>
            </tr>
            <?php while ($row = $customers_result->fetch_assoc()) { ?>
            <tr>
                <td><?php echo $row['id']; ?></td>
                <td><?php echo $row['name']; ?></td>
                <td><?php echo $row['contact_number']; ?></td>
                <td><?php echo $row['email']; ?></td>
                <td><?php echo $row['gender']; ?></td>
                <td><?php echo $row['created_at']; ?></td>
            </tr>
            <?php } ?>
        </table>
    </div>
</main>
</body>
</html>
