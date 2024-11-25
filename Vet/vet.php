<?php
// vet_dashboard.php - For vets to manage their availability and appointment types
session_start();
require '../db.php';

// Check if logged in as vet
if (!isset($_SESSION['vet_id'])) {
    header('Location: login.php');
    exit;
}

$vet_id = $_SESSION['vet_id'];

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_availability'])) {
        $day = $_POST['day_of_week'];
        $start = $_POST['start_time'];
        $end = $_POST['end_time'];
        $is_available = isset($_POST['is_available']) ? 1 : 0;

        $stmt = $conn->prepare("INSERT INTO vet_availability (vet_id, day_of_week, start_time, end_time, is_available) 
                               VALUES (?, ?, ?, ?, ?) 
                               ON DUPLICATE KEY UPDATE start_time = ?, end_time = ?, is_available = ?");
        $stmt->bind_param('isssisii', $vet_id, $day, $start, $end, $is_available, $start, $end, $is_available);
        $stmt->execute();
    }

    if (isset($_POST['update_appointment_type'])) {
        $type = $_POST['type_name'];
        $duration = $_POST['duration'];
        $price = $_POST['price'];
        $is_available = isset($_POST['type_available']) ? 1 : 0;

        $stmt = $conn->prepare("INSERT INTO appointment_types (vet_id, type_name, duration, price, is_available) 
                               VALUES (?, ?, ?, ?, ?) 
                               ON DUPLICATE KEY UPDATE duration = ?, price = ?, is_available = ?");
        $stmt->bind_param('isidiidi', $vet_id, $type, $duration, $price, $is_available, $duration, $price, $is_available);
        $stmt->execute();
    }
}

// Fetch current availability
$availability_query = "SELECT * FROM vet_availability WHERE vet_id = ?";
$stmt = $conn->prepare($availability_query);
$stmt->bind_param('i', $vet_id);
$stmt->execute();
$availabilities = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Fetch appointment types
$types_query = "SELECT * FROM appointment_types WHERE vet_id = ?";
$stmt = $conn->prepare($types_query);
$stmt->bind_param('i', $vet_id);
$stmt->execute();
$appointment_types = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Vet Dashboard - Manage Availability</title>
</head>
<body>
    <h1>Manage Your Availability</h1>
    
    <h2>Set Weekly Availability</h2>
    <form method="POST">
        <input type="hidden" name="update_availability">
        <select name="day_of_week" required>
            <option value="Monday">Monday</option>
            <option value="Tuesday">Tuesday</option>
            <option value="Wednesday">Wednesday</option>
            <option value="Thursday">Thursday</option>
            <option value="Friday">Friday</option>
            <option value="Saturday">Saturday</option>
            <option value="Sunday">Sunday</option>
        </select>
        <input type="time" name="start_time" required>
        <input type="time" name="end_time" required>
        <label>
            <input type="checkbox" name="is_available" checked>
            Available
        </label>
        <button type="submit">Save Availability</button>
    </form>

    <h2>Current Availability</h2>
    <table>
        <tr>
            <th>Day</th>
            <th>Start Time</th>
            <th>End Time</th>
            <th>Status</th>
        </tr>
        <?php foreach ($availabilities as $slot): ?>
        <tr>
            <td><?= htmlspecialchars($slot['day_of_week']) ?></td>
            <td><?= htmlspecialchars($slot['start_time']) ?></td>
            <td><?= htmlspecialchars($slot['end_time']) ?></td>
            <td><?= $slot['is_available'] ? 'Available' : 'Not Available' ?></td>
        </tr>
        <?php endforeach; ?>
    </table>

    <h2>Manage Appointment Types</h2>
    <form method="POST">
        <input type="hidden" name="update_appointment_type">
        <select name="type_name" required>
            <option value="online">Online Consultation</option>
            <option value="physical">Physical Visit</option>
        </select>
        <input type="number" name="duration" placeholder="Duration (minutes)" required>
        <input type="number" name="price" step="0.01" placeholder="Price" required>
        <label>
            <input type="checkbox" name="type_available" checked>
            Available
        </label>
        <button type="submit">Save Appointment Type</button>
    </form>

    <h2>Current Appointment Types</h2>
    <table>
        <tr>
            <th>Type</th>
            <th>Duration</th>
            <th>Price</th>
            <th>Status</th>
        </tr>
        <?php foreach ($appointment_types as $type): ?>
        <tr>
            <td><?= htmlspecialchars($type['type_name']) ?></td>
            <td><?= htmlspecialchars($type['duration']) ?> minutes</td>
            <td>$<?= htmlspecialchars($type['price']) ?></td>
            <td><?= $type['is_available'] ? 'Available' : 'Not Available' ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>