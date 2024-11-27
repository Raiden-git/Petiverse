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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f3f4f6;
            color: #1f2937;
            font-family: system-ui, -apple-system, sans-serif;
        }

        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        .card {
            background: white;
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        select, input[type="time"], input[type="number"] {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            margin-bottom: 1rem;
        }

        button {
            background-color: #3b82f6;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            border: none;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        button:hover {
            background-color: #2563eb;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }

        th, td {
            padding: 0.75rem;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }

        th {
            background-color: #f9fafb;
            font-weight: 600;
        }

        tr:hover {
            background-color: #f9fafb;
        }

        .status-available {
            color: #059669;
            font-weight: 500;
        }

        .status-unavailable {
            color: #dc2626;
            font-weight: 500;
        }

        .checkbox-wrapper {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin: 1rem 0;
        }

        @media (max-width: 768px) {
            .dashboard-container {
                padding: 1rem;
            }

            table {
                display: block;
                overflow-x: auto;
            }
        }
    </style>
</head>
<body>
<?php include 'nav.php'; ?>
    <div class="dashboard-container">
        <h1 class="text-3xl font-bold mb-8">Manage Your Availability</h1>
        
        <div class="card">
            <h2 class="text-xl font-semibold mb-4">Set Weekly Availability</h2>
            <form method="POST">
                <input type="hidden" name="update_availability">
                <div class="grid md:grid-cols-3 gap-4">
                    <div class="form-group">
                        <label class="block text-sm font-medium mb-2">Day of Week</label>
                        <select name="day_of_week" required>
                            <option value="Monday">Monday</option>
                            <option value="Tuesday">Tuesday</option>
                            <option value="Wednesday">Wednesday</option>
                            <option value="Thursday">Thursday</option>
                            <option value="Friday">Friday</option>
                            <option value="Saturday">Saturday</option>
                            <option value="Sunday">Sunday</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="block text-sm font-medium mb-2">Start Time</label>
                        <input type="time" name="start_time" required>
                    </div>
                    <div class="form-group">
                        <label class="block text-sm font-medium mb-2">End Time</label>
                        <input type="time" name="end_time" required>
                    </div>
                </div>
                <div class="checkbox-wrapper">
                    <input type="checkbox" name="is_available" checked>
                    <label>Available</label>
                </div>
                <button type="submit">Save Availability</button>
            </form>
        </div>

        <div class="card">
            <h2 class="text-xl font-semibold mb-4">Current Availability</h2>
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
                    <td class="<?= $slot['is_available'] ? 'status-available' : 'status-unavailable' ?>">
                        <?= $slot['is_available'] ? 'Available' : 'Not Available' ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>

        <div class="card">
            <h2 class="text-xl font-semibold mb-4">Manage Appointment Types</h2>
            <form method="POST">
                <input type="hidden" name="update_appointment_type">
                <div class="grid md:grid-cols-3 gap-4">
                    <div class="form-group">
                        <label class="block text-sm font-medium mb-2">Appointment Type</label>
                        <select name="type_name" required>
                            <option value="online">Online Consultation</option>
                            <option value="physical">Physical Visit</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="block text-sm font-medium mb-2">Duration (minutes)</label>
                        <input type="number" name="duration" placeholder="Duration (minutes)" required>
                    </div>
                    <div class="form-group">
                        <label class="block text-sm font-medium mb-2">Price (LKR)</label>
                        <input type="number" name="price" step="0.01" placeholder="Price" required>
                    </div>
                </div>
                <div class="checkbox-wrapper">
                    <input type="checkbox" name="type_available" checked>
                    <label>Available</label>
                </div>
                <button type="submit">Save Appointment Type</button>
            </form>
        </div>

        <div class="card">
            <h2 class="text-xl font-semibold mb-4">Current Appointment Types</h2>
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
                    <td>LKR <?= htmlspecialchars($type['price']) ?></td>
                    <td class="<?= $type['is_available'] ? 'status-available' : 'status-unavailable' ?>">
                        <?= $type['is_available'] ? 'Available' : 'Not Available' ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>
</body>
</html>