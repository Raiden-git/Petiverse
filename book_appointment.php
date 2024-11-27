<?php
session_start();
require 'db.php';

// Check if user is premium
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$premium_query = "SELECT is_premium FROM users WHERE id = ?";
$stmt = $conn->prepare($premium_query);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user['is_premium']) {
    header('Location: upgrade.php');
    exit;
}

// Handle appointment booking
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $vet_id = $_POST['vet_id'];
    $appointment_type_id = $_POST['appointment_type_id'];
    $appointment_date = $_POST['appointment_date'];
    $start_time = $_POST['start_time'];
    
    // Get appointment duration
    $duration_query = "SELECT duration FROM appointment_types WHERE id = ?";
    $stmt = $conn->prepare($duration_query);
    $stmt->bind_param('i', $appointment_type_id);
    $stmt->execute();
    $type = $stmt->get_result()->fetch_assoc();
    
    // Calculate end time
    $end_time = date('H:i:s', strtotime($start_time . ' + ' . $type['duration'] . ' minutes'));
    
    // Check if slot is available
    $check_query = "SELECT * FROM appointments 
                    WHERE vet_id = ? 
                    AND appointment_date = ? 
                    AND ((start_time <= ? AND end_time > ?) 
                    OR (start_time < ? AND end_time >= ?))
                    AND status != 'cancelled'";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param('isssss', $vet_id, $appointment_date, $start_time, $start_time, $end_time, $end_time);
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows === 0) {
        // Slot is available, book it
        $book_query = "INSERT INTO appointments (vet_id, user_id, appointment_type_id, appointment_date, start_time, end_time, notes) 
                       VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($book_query);
        $notes = $_POST['notes'] ?? '';
        $stmt->bind_param('iiissss', $vet_id, $user_id, $appointment_type_id, $appointment_date, $start_time, $end_time, $notes);
        $stmt->execute();
        
        header('Location: appointment_confirmation.php?id=' . $stmt->insert_id);
        exit;
    } else {
        $error = "Selected time slot is not available. Please choose another time.";
    }
}

// Get vet's available appointment types
$vet_id = $_GET['vet_id'] ?? 0;
$types_query = "SELECT * FROM appointment_types WHERE vet_id = ? AND is_available = 1";
$stmt = $conn->prepare($types_query);
$stmt->bind_param('i', $vet_id);
$stmt->execute();
$appointment_types = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get vet's availability
$availability_query = "SELECT * FROM vet_availability WHERE vet_id = ? AND is_available = 1";
$stmt = $conn->prepare($availability_query);
$stmt->bind_param('i', $vet_id);
$stmt->execute();
$availabilities = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Book Appointment</title>
</head>
<body>
    <h1>Book an Appointment</h1>
    
    <?php if (isset($error)): ?>
        <div style="color: red;"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
        <input type="hidden" name="vet_id" value="<?= htmlspecialchars($vet_id) ?>">
        
        <div>
            <label>Appointment Type:</label>
            <select name="appointment_type_id" required>
                <?php foreach ($appointment_types as $type): ?>
                    <option value="<?= htmlspecialchars($type['id']) ?>">
                        <?= htmlspecialchars($type['type_name']) ?> 
                        (<?= htmlspecialchars($type['duration']) ?> min) - 
                        $<?= htmlspecialchars($type['price']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label>Date:</label>
            <input type="date" name="appointment_date" required min="<?= date('Y-m-d') ?>">
        </div>

        <div>
            <label>Available Time Slots:</label>
            <select name="start_time" required>
                <?php foreach ($availabilities as $slot): ?>
                    <option value="<?= htmlspecialchars($slot['start_time']) ?>">
                        <?= date('g:i A', strtotime($slot['start_time'])) ?> - 
                        <?= date('g:i A', strtotime($slot['end_time'])) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label>Notes (optional):</label>
            <textarea name="notes"></textarea>
        </div>

        <button type="submit">Book Appointment</button>
    </form>
</body>
</html>