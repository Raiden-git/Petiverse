<?php
session_start();

// Check if vet is logged in
if (!isset($_SESSION['vet_id'])) {
    header("Location: vet_login.php"); // Redirect to login if not logged in
    exit();
}

include '../db.php'; // Include the database connection file

// Fetch vet details
$vet_id = $_SESSION['vet_id'];
$sql = "SELECT * FROM vets WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $vet_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$vet = mysqli_fetch_assoc($result);

// Close database connection
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vet Dashboard - Petiverse</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f8ff;
            margin: 0;
            padding: 0;
        }
        .dashboard-container {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        h2 {
            text-align: center;
            color: #333;
        }
        .vet-info {
            margin-bottom: 30px;
        }
        .vet-info p {
            font-size: 18px;
            margin: 5px 0;
        }
        .actions {
            display: flex;
            justify-content: space-around;
        }
        .actions a {
            display: inline-block;
            padding: 10px 20px;
            background-color: #28a745;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        .actions a:hover {
            background-color: #218838;
        }
        .logout {
            text-align: center;
            margin-top: 30px;
        }
        .logout a {
            color: #dc3545;
            text-decoration: none;
            font-size: 18px;
        }
        .logout a:hover {
            color: #c82333;
        }
    </style>
</head>
<body>

    <div class="dashboard-container">
        <h2>Welcome, Dr. <?php echo $vet['name']; ?></h2>
        
        <div class="vet-info">
            <p><strong>Clinic Name:</strong> <?php echo $vet['clinic_name']; ?></p>
            <p><strong>Specialization:</strong> <?php echo $vet['specialization']; ?></p>
            <p><strong>Experience:</strong> <?php echo $vet['experience']; ?> years</p>
            <p><strong>Consultation Fee:</strong> $<?php echo $vet['consultation_fee']; ?></p>
        </div>

        <div class="actions">
            <a href="manage_appointments.php">Manage Appointments</a>
            <a href="edit_profile.php">Update Profile</a>
            <a href="vet_chat.php">Chat with Clients</a>
        </div>

        <div class="logout">
            <a href="vet_logout.php">Logout</a>
        </div>
    </div>

</body>
</html>
