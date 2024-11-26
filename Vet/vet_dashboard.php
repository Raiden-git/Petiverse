<?php
session_start();

// Check if vet is logged in
if (!isset($_SESSION['vet_id'])) {
    header("Location: index.php");
    exit();
}

include '../db.php';

// Fetch vet details
$vet_id = $_SESSION['vet_id'];
$sql = "SELECT * FROM vets WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $vet_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$vet = mysqli_fetch_assoc($result);

// Fetch today's appointments
$today = date('Y-m-d');
$sql_appointments = "SELECT COUNT(*) as today_appointments FROM appointments WHERE vet_id = ? AND appointment_date = ?";
$stmt_appointments = mysqli_prepare($conn, $sql_appointments);
mysqli_stmt_bind_param($stmt_appointments, "is", $vet_id, $today);
mysqli_stmt_execute($stmt_appointments);
$result_appointments = mysqli_stmt_get_result($stmt_appointments);
$appointments_count = mysqli_fetch_assoc($result_appointments)['today_appointments'];

// Fetch pending messages - UPDATED QUERY
$sql_messages = "SELECT COUNT(*) as unread_messages 
                FROM messages 
                WHERE receiver_id = ? 
                AND receiver_type = 'vet'
                AND read_status = 0 
                AND deleted_by_receiver = 0";
$stmt_messages = mysqli_prepare($conn, $sql_messages);
mysqli_stmt_bind_param($stmt_messages, "i", $vet_id);
mysqli_stmt_execute($stmt_messages);
$result_messages = mysqli_stmt_get_result($stmt_messages);
$unread_messages = mysqli_fetch_assoc($result_messages)['unread_messages'];

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vet Dashboard - Petiverse</title>
    <style>
        .dashboard-container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 20px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        .stat-card h3 {
            margin: 0;
            color: #2c3e50;
        }
        .stat-card .number {
            font-size: 36px;
            color: #3498db;
            margin: 10px 0;
        }
        .profile-section {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }
        .action-card {
            background-color: #3498db;
            color: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            text-decoration: none;
            transition: transform 0.3s, background-color 0.3s;
        }
        .action-card:hover {
            transform: translateY(-5px);
            background-color: #2980b9;
        }
    </style>
</head>
<body>
    <?php include 'nav.php'; ?>

    <div class="dashboard-container">
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Today's Appointments</h3>
                <div class="number"><?php echo $appointments_count; ?></div>
            </div>
            <div class="stat-card">
                <h3>Unread Messages</h3>
                <div class="number"><?php echo $unread_messages; ?></div>
            </div>
            <div class="stat-card">
                <h3>Total Patients</h3>
                <div class="number"><!--Add PHP logic for total patients--></div>
            </div>
        </div>

        <div class="profile-section">
            <h2>Welcome, Dr. <?php echo htmlspecialchars($vet['name']); ?></h2>
            <p><strong>Clinic:</strong> <?php echo htmlspecialchars($vet['clinic_name']); ?></p>
            <p><strong>Specialization:</strong> <?php echo htmlspecialchars($vet['specialization']); ?></p>
            <p><strong>Experience:</strong> <?php echo htmlspecialchars($vet['experience']); ?> years</p>
            <p><strong>Consultation Fee:</strong> $<?php echo htmlspecialchars($vet['consultation_fee']); ?></p>
        </div>

        <div class="quick-actions">
            <a href="manage_appointments.php" class="action-card">
                <h3>Manage Appointments</h3>
            </a>
            <a href="vet_chat.php" class="action-card">
                <h3>Chat with Clients</h3>
            </a>
            
            </a>
            <a href="edit_profile.php" class="action-card">
                <h3>Update Profile</h3>
            </a>
        </div>
    </div>
</body>
</html>