<?php
if (!isset($_SESSION)) {
    session_start();
}

// Get current page name
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vet Dashboard - Petiverse</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: Inter, sans-serif;
        }



        .nav-container {
            background-color: #2c3e50;
            padding: 15px 0;
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        .nav-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 20px;
        }
        .logo {
            color: white;
            font-size: 24px;
            text-decoration: none;
            font-weight: bold;
        }
        .nav-links {
            display: flex;
            gap: 20px;
        }
        .nav-links a {
            color: white;
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        .nav-links a:hover {
            background-color: #34495e;
        }
        .nav-links a.active {
            background-color: #3498db;
        }
        .user-info {
            color: white;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .logout-btn {
            color: #e74c3c;
            padding: 8px 16px;
            border-radius: 4px;
            text-decoration: none;
            transition: background-color 0.3s;
        }
        .logout-btn:hover {
            background-color: #34495e;
        }
        @media (max-width: 768px) {
            .nav-content {
                flex-direction: column;
                gap: 15px;
            }
            .nav-links {
                flex-wrap: wrap;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="nav-container">
        <div class="nav-content">
            <a href="vet_dashboard.php" class="logo">Petiverse</a>
            <div class="nav-links">
                <a href="vet_dashboard.php" class="<?php echo $current_page == 'vet_dashboard.php' ? 'active' : ''; ?>">Dashboard</a>
                <a href="manage_appointments.php" class="<?php echo $current_page == 'manage_appointments.php' ? 'active' : ''; ?>">Appointments</a>
                <a href="vet_chat.php" class="<?php echo $current_page == 'vet_chat.php' ? 'active' : ''; ?>">Chat</a>
                <a href="medical_records.php" class="<?php echo $current_page == 'medical_records.php' ? 'active' : ''; ?>">Medical Records</a>
                <a href="edit_profile.php" class="<?php echo $current_page == 'edit_profile.php' ? 'active' : ''; ?>">Profile</a>
            </div>
            <div class="user-info">
                <span>Dr. <?php echo isset($_SESSION['vet_name']) ? $_SESSION['vet_name'] : ''; ?></span>
                <a href="vet_logout.php" class="logout-btn">Logout</a>
            </div>
        </div>
    </div>