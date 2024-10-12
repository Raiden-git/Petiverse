<?php
session_start();

// If user is not logged in, redirect to login page
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Database connection
$conn = new mysqli('localhost', 'root', '', 'petiverse');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch logged-in user details
$user_id = $_SESSION['user_id'];
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // If the form is submitted, update the user's data
    $name = $_POST['name'];
    $email = $_POST['email'];
    $address = $_POST['address'];
    $mobile_number = $_POST['mobile_number'];
    
    // Update query
    $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, address = ?, mobile_number = ? WHERE id = ?");
    $stmt->bind_param("ssssi", $name, $email, $address, $mobile_number, $user_id);
    
    if ($stmt->execute()) {
        // Set a success message and redirect
        $_SESSION['success_message'] = "Profile updated successfully!";
        header("Location: profile.php");  // Redirect to the same page to prevent form resubmission
        exit();  // Make sure no more code is executed after redirect
    } else {
        $_SESSION['error_message'] = "Error updating profile: " . $stmt->error;
    }
    
    $stmt->close();
}

// Fetch the current data for display in the form
$sql = "SELECT name, email, address, mobile_number FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($name, $email, $address, $mobile_number);
$stmt->fetch();
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Petiverse</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>

        /* Profile Container */
.profile-container {
    max-width: 600px;
    margin: 40px auto;
    background-color: #f4f4f9;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
}

.profile-container h2 {
    text-align: center;
    font-size: 26px;
    color: #34495e;
    margin-bottom: 25px;
}

/* Form Styles */
form {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

label {
    font-size: 14px;
    color: #2c3e50;
    font-weight: bold;
}

input[type="text"],
input[type="email"] {
    width: 100%;
    padding: 12px;
    border: 1px solid #bdc3c7;
    border-radius: 6px;
    font-size: 16px;
    color: #2c3e50;
    background-color: #fff;
    transition: border-color 0.3s ease;
}

input[type="text"]:focus,
input[type="email"]:focus {
    border-color: #3498db;
    outline: none;
}

input[type="submit"] {
    background-color: #3498db;
    color: white;
    padding: 12px;
    border: none;
    border-radius: 6px;
    font-size: 16px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

input[type="submit"]:hover {
    background-color: #2980b9;
}

/* Profile Actions */
.profile-actions {
    display: flex;
    justify-content: center;
    margin-top: 20px;
}

.logout-btn {
    background-color: #e74c3c;
    color: white;
    padding: 10px 20px;
    border-radius: 6px;
    text-decoration: none;
    font-weight: bold;
    transition: background-color 0.3s ease;
}

.logout-btn:hover {
    background-color: #c0392b;
}


        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            width: 400px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .modal-content p {
            font-size: 18px;
            color: #2c3e50;
        }

        .close-btn {
            background-color: #3498db;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 10px;
        }

        .close-btn:hover {
            background-color: #2980b9;
        }

    </style>
</head>
<body>

<?php include 'Cus-NavBar/navBar.php'; ?>

<div class="profile-container">
    <h2>Welcome, <?php echo $name; ?>!</h2>
    
    <form action="profile.php" method="POST">
        <label>Name:</label>
        <input type="text" name="name" value="<?php echo $name; ?>" required><br>
        
        <label>Email:</label>
        <input type="email" name="email" value="<?php echo $email; ?>" required><br>
        
        <label>Address:</label>
        <input type="text" name="address" value="<?php echo $address; ?>" required><br>
        
        <label>Mobile Number:</label>
        <input type="text" name="mobile_number" value="<?php echo $mobile_number; ?>" required><br><br>
        
        <input type="submit" value="Update Profile">
    </form>

    <div class="profile-actions">
        <a href="logout.php" class="logout-btn">Logout</a>
    </div>
</div>

<!-- Modal structure -->
<div id="successModal" class="modal">
    <div class="modal-content">
        <p><?php echo isset($_SESSION['success_message']) ? $_SESSION['success_message'] : ''; ?></p>
        <button class="close-btn" onclick="closeModal()">Close</button>
    </div>
</div>

<script>
// JavaScript to handle modal display
function closeModal() {
    document.getElementById('successModal').style.display = 'none';
}

// Check if there's a success message and show the modal
<?php if (isset($_SESSION['success_message'])): ?>
    document.getElementById('successModal').style.display = 'flex';
    <?php unset($_SESSION['success_message']); // Remove the message after displaying ?>
<?php endif; ?>
</script>

</body>
</html>
