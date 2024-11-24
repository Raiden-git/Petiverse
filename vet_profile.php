<?php
require 'db.php';
session_start();

// Check if a vet ID is passed in the URL
if (!isset($_GET['id'])) {
    echo "No vet selected!";
    exit;
}

// Get the vet ID from the URL
$vet_id = intval($_GET['id']);

// Fetch the vet's details from the database
$query = "SELECT * FROM vets WHERE id = ? AND is_approved = 1";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $vet_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "No vet found!";
    exit;
}

$vet = $result->fetch_assoc();

// Check user's premium status
$user_id = $_SESSION['user_id'] ?? null;
if ($user_id) {
    $user_query = "SELECT is_premium FROM users WHERE id = ?";
    $user_stmt = $conn->prepare($user_query);
    $user_stmt->bind_param('i', $user_id);
    $user_stmt->execute();
    $user_result = $user_stmt->get_result();
    $user = $user_result->fetch_assoc();
    $is_premium = $user['is_premium'];
} else {
    $is_premium = false;
}

// Get vet's schedule for the next 7 days
function getVetSchedule($conn, $vet_id, $start_date, $end_date) {
    // Get vet's availability
    $availability_query = "SELECT * FROM vet_availability 
                          WHERE vet_id = ? 
                          AND day_of_week = DAYOFWEEK(?) - 1 
                          AND is_available = 1";
    $stmt = $conn->prepare($availability_query);
    
    // Get booked appointments
    $appointments_query = "SELECT appointment_date, start_time, end_time 
                          FROM appointments 
                          WHERE vet_id = ? 
                          AND appointment_date BETWEEN ? AND ?
                          AND status != 'cancelled'";
    $appointments_stmt = $conn->prepare($appointments_query);
    $appointments_stmt->bind_param('iss', $vet_id, $start_date, $end_date);
    $appointments_stmt->execute();
    $booked_slots = $appointments_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    $schedule = [];
    $current_date = new DateTime($start_date);
    $end = new DateTime($end_date);
    
    while ($current_date <= $end) {
        $date_string = $current_date->format('Y-m-d');
        $stmt->bind_param('is', $vet_id, $date_string);
        $stmt->execute();
        $day_slots = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        $schedule[$date_string] = [
            'dayName' => $current_date->format('l'),
            'date' => $date_string,
            'slots' => array_map(function($slot) use ($booked_slots, $date_string) {
                $is_booked = false;
                foreach ($booked_slots as $booked) {
                    if ($booked['appointment_date'] == $date_string &&
                        $booked['start_time'] <= $slot['start_time'] &&
                        $booked['end_time'] > $slot['start_time']) {
                        $is_booked = true;
                        break;
                    }
                }
                return [
                    'start_time' => $slot['start_time'],
                    'end_time' => $slot['end_time'],
                    'is_booked' => $is_booked
                ];
            }, $day_slots)
        ];
        
        $current_date->modify('+1 day');
    }
    
    return $schedule;
}

// Handle appointment booking for premium users
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $is_premium) {
    $appointment_type_id = intval($_POST['appointment_type']);
    $appointment_date = $_POST['appointment_date'];
    $appointment_time = $_POST['appointment_time'];

    // Convert the selected time to a start and end time based on appointment duration
    $type_query = "SELECT duration FROM appointment_types WHERE id = ?";
    $type_stmt = $conn->prepare($type_query);
    $type_stmt->bind_param('i', $appointment_type_id);
    $type_stmt->execute();
    $type_result = $type_stmt->get_result()->fetch_assoc();
    $duration = intval($type_result['duration']);

    $start_time = $appointment_time;
    $end_time = date('H:i:s', strtotime($start_time) + $duration * 60); // End time is calculated by adding the duration

    // Check if the selected time is available
    $check_query = "SELECT * FROM appointments 
                    WHERE vet_id = ? AND appointment_date = ? 
                    AND (start_time < ? AND end_time > ?) AND status != 'cancelled'";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->bind_param('isss', $vet_id, $appointment_date, $end_time, $start_time);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows === 0) {
        // Book the appointment
        $insert_query = "INSERT INTO appointments (vet_id, user_id, appointment_type_id, appointment_date, start_time, end_time) 
                         VALUES (?, ?, ?, ?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_query);
        $insert_stmt->bind_param('iiisss', $vet_id, $user_id, $appointment_type_id, $appointment_date, $start_time, $end_time);
        if ($insert_stmt->execute()) {
            echo "Appointment booked successfully!";
        } else {
            echo "Error booking appointment.";
        }
    } else {
        echo "The selected time slot is already booked.";
    }
}

// Get schedule for next 7 days
$start_date = date('Y-m-d');
$end_date = date('Y-m-d', strtotime('+6 days'));
$schedule = getVetSchedule($conn, $vet_id, $start_date, $end_date);

// Get active tab
$active_tab = $_GET['tab'] ?? 'profile';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($vet['name']); ?> - Veterinarian Profile</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --gradient-primary: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            --gradient-secondary: linear-gradient(135deg, #ff6b6b 0%, #feca57 100%);
            --text-dark: #2c3e50;
            --background-light: #f7f9fc;
            --white: #ffffff;
            --shadow-soft: 0 15px 35px rgba(0,0,0,0.08);
            --shadow-medium: 0 15px 45px rgba(0,0,0,0.12);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--background-light);
            color: var(--text-dark);
            line-height: 1.6;
        }

        .pro-container {
            max-width: 1000px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .profile-card {
            /* background: var(--white); */
            border-radius: 20px;
            box-shadow: var(--shadow-soft);
            overflow: hidden;
            transition: all 0.3s ease;
            margin: 2rem;
            padding: 1rem;
        }

        .profile-header {
            display: flex;
            align-items: center;
            padding: 2.5rem;
            background: var(--gradient-primary);
            color: var(--white);
            position: relative;
            border-radius: 20px;
        }

        .profile-avatar {
            width: 200px;
            height: 200px;
            border-radius: 50%;
            object-fit: cover;
            border: 6px solid var(--white);
            box-shadow: var(--shadow-medium);
            margin-right: 2.5rem;
            transition: transform 0.4s ease;
        }

        .profile-avatar:hover {
            transform: scale(1.05) rotate(3deg);
        }

        .profile-details {
            flex-grow: 1;
        }

        .profile-name {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .profile-specialization {
            font-size: 1.2rem;
            opacity: 0.9;
            margin-bottom: 1rem;
        }

        .profile-stats {
            display: flex;
            justify-content: space-between;
            background: rgba(255,255,255,0.1);
            padding: 1rem;
            border-radius: 10px;
        }

        .stat-item {
            text-align: center;
        }

        .stat-value {
            font-weight: 700;
            font-size: 1.3rem;
        }

        .stat-label {
            font-size: 0.9rem;
            opacity: 0.7;
        }

        .section {
            padding: 2rem;
            background: var(--white);
            margin-top: 1.5rem;
            border-radius: 15px;
            box-shadow: var(--shadow-soft);
        }

        .section-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #2575fc;
            margin-bottom: 1.5rem;
            position: relative;
            padding-bottom: 0.5rem;
            border-bottom: 3px solid var(--gradient-secondary);
        }

        .btn-appointment {
            display: inline-block;
            background: var(--gradient-secondary);
            color: var(--white);
            padding: 1rem 2rem;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            margin-top: 1rem;
        }

        .btn-appointment:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(255,107,107,0.3);
        }


        .schedule-section {
    padding: 2rem;
    background: #f8f9fa;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.schedule-section h2 {
    color: #2c3e50;
    margin-bottom: 1.5rem;
    font-size: 1.8rem;
}

.schedule-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.day-schedule {
    background: white;
    padding: 1.25rem;
    border-radius: 6px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.day-schedule h3 {
    color: #34495e;
    margin: 0 0 0.5rem 0;
    font-size: 1.2rem;
}

.date {
    color: #7f8c8d;
    font-size: 0.9rem;
    margin-bottom: 1rem;
}

.slots {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.time-slot {
    padding: 0.75rem;
    background: #e8f5e9;
    border-radius: 4px;
    font-size: 0.9rem;
    transition: all 0.3s ease;
    cursor: pointer;
}

.time-slot:hover {
    background: #c8e6c9;
}

.time-slot.booked {
    background: #ffebee;
    cursor: not-allowed;
}

.booked-text {
    color: #e57373;
    font-size: 0.8rem;
    font-weight: 500;
}

/* Booking Form Styles */
.booking-form {
    max-width: 500px;
    margin: 2rem auto;
    padding: 2rem;
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.booking-form h2 {
    color: #2c3e50;
    margin-bottom: 1.5rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

label {
    display: block;
    margin-bottom: 0.5rem;
    color: #34495e;
    font-weight: 500;
}

select, input[type="date"] {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #dfe6e9;
    border-radius: 4px;
    font-size: 1rem;
    margin-bottom: 1rem;
    background: white;
}

select:focus, input[type="date"]:focus {
    outline: none;
    border-color: #3498db;
    box-shadow: 0 0 0 2px rgba(52,152,219,0.2);
}

input[type="submit"] {
    width: 100%;
    padding: 0.75rem;
    background: #2ecc71;
    color: white;
    border: none;
    border-radius: 4px;
    font-size: 1rem;
    font-weight: 500;
    cursor: pointer;
    transition: background 0.3s ease;
}

input[type="submit"]:hover {
    background: #27ae60;
}

/* Premium Feature Box */
.premium-feature {
    max-width: 500px;
    margin: 2rem auto;
    padding: 2rem;
    background: #f8f9fa;
    border-radius: 8px;
    text-align: center;
    border: 2px dashed #bdc3c7;
}

.premium-feature h2 {
    color: #2c3e50;
    margin-bottom: 1rem;
}

.premium-feature p {
    color: #7f8c8d;
    margin-bottom: 1rem;
}

.premium-feature a {
    color: #3498db;
    text-decoration: none;
    font-weight: 500;
}

.premium-feature a:hover {
    text-decoration: underline;
}


.tab-buttons {
    display: flex;
    justify-content: center;
    gap: 0;
    padding: 1rem;
    margin-bottom: 2rem;
    position: relative;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    overflow: hidden;
    width: fit-content;
    margin-left: auto;
    margin-right: auto;
}

.tab-buttons button {
    position: relative;
    padding: 0.75rem 2rem;
    font-size: 1rem;
    font-weight: 500;
    color: #64748b;
    background: transparent;
    border: none;
    cursor: pointer;
    transition: all 0.3s ease;
    min-width: 120px;
    border-right: 2px solid #e2e8f0;
}

.tab-buttons button:last-child {
    border-right: none;
}

.tab-buttons button.active {
    color: #fff;
    background: #3b82f6;
    border-right: none;
}

.tab-buttons button:first-child.active {
    border-radius: 10px 0 0 10px;
}

.tab-buttons button:last-child.active {
    border-radius: 0 10px 10px 0;
}

.tab-buttons button:hover {
    color: #3b82f6;
}

.tab-buttons button.active:hover {
    color: #fff;
}


        @media (max-width: 768px) {
            .profile-header {
                flex-direction: column;
                text-align: center;
            }

            .profile-avatar {
                margin-right: 0;
                margin-bottom: 1.5rem;
            }
        }

        /* For smaller screens */
        @media (max-width: 480px) {
            .tab-buttons {
                padding: 1rem;
            }

            .tab-buttons button {
                padding: 0.5rem 1rem;
                font-size: 0.9rem;
                min-width: 100px;
            }
        }

        .tab-buttons { margin-bottom: 20px; }
        .tab-buttons button { padding: 10px 20px; margin-right: 10px; }
        .tab-buttons button.active { background-color: #2575fc; color: white; }
        .time-slot { padding: 5px 10px; margin: 5px; display: inline-block; }
        .time-slot.booked { background-color: #ffebee; color: #c62828; }
        .schedule-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; }
    </style>
</head>
<body>
    <?php include 'Cus-NavBar/navBar.php'; ?>
    
    <div class="pro-container">
        <!-- Tab Buttons -->
<div class="tab-buttons">
    <button onclick="location.href='?id=<?php echo $vet_id; ?>&tab=profile'" 
            <?php echo $active_tab === 'profile' ? 'class="active"' : ''; ?>>
        Profile
    </button>
    <button onclick="location.href='?id=<?php echo $vet_id; ?>&tab=schedule'" 
            <?php echo $active_tab === 'schedule' ? 'class="active"' : ''; ?>>
        Schedule
    </button>
</div>

        <?php if ($active_tab === 'profile'): ?>
            <!-- Profile Content -->
            <div class="profile-card">
            <div class="profile-header">
                <?php 
                $profilePicture = !empty($vet['profile_picture']) 
                    ? 'data:image/jpeg;base64,'.base64_encode($vet['profile_picture']) 
                    : 'default-profile.jpg'; 
                ?>
                <img src="<?php echo $profilePicture; ?>" alt="Dr. <?php echo htmlspecialchars($vet['name']); ?>" class="profile-avatar">
                
                <div class="profile-details">
                    <h1 class="profile-name">Dr. <?php echo htmlspecialchars($vet['name']); ?></h1>
                    <div class="profile-specialization">
                        <?php echo htmlspecialchars($vet['specialization']); ?> Specialist
                    </div>

                    <div class="profile-stats">
                        <div class="stat-item">
                            <div class="stat-value"><?php echo intval($vet['experience']); ?></div>
                            <div class="stat-label">Years Experience</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value">$<?php echo number_format($vet['consultation_fee'], 2); ?></div>
                            <div class="stat-label">Consultation Fee</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value"><?php echo number_format($vet['rating'], 1); ?> <i class="fas fa-star" style="color: #feca57;"></i></div>
                            <div class="stat-label">Rating</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="section">
                <h2 class="section-title">About Me</h2>
                <p>Dr. <?php echo htmlspecialchars($vet['name']); ?> brings <?php echo intval($vet['experience']); ?> years of dedicated veterinary expertise 
                to <?php echo htmlspecialchars($vet['clinic_name']); ?>. Specializing in <?php echo htmlspecialchars($vet['specialization']); ?>, 
                I'm committed to providing compassionate and comprehensive care for your beloved pets.</p>
            </div>

                
            </div>

        <?php else: ?>
            <div class="schedule-section">
    <h2>Weekly Availability</h2>
    <div class="schedule-grid">
        <?php foreach ($schedule as $date => $day): ?>
            <div class="day-schedule">
                <h3><?php echo htmlspecialchars($day['dayName']); ?></h3>
                <div class="date"><?php echo date('M d, Y', strtotime($date)); ?></div>
                <div class="slots">
                    <?php foreach ($day['slots'] as $slot): ?>
                        <div class="time-slot <?php echo $slot['is_booked'] ? 'booked' : ''; ?>">
                            <?php 
                            echo date('g:i A', strtotime($slot['start_time'])) . ' - ' . 
                                 date('g:i A', strtotime($slot['end_time']));
                            ?>
                            <?php if ($slot['is_booked']): ?>
                                <br><span class="booked-text">Booked</span>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php if ($is_premium): ?>
    <div class="booking-form">
        <form action="vet_profile.php?id=<?php echo $vet_id; ?>&tab=schedule" method="POST">
            <h2>Book an Appointment</h2>
            <div class="form-group">
                <label for="appointment_type">Appointment Type:</label>
                <select name="appointment_type" id="appointment_type" required>
                    <?php
                    $types_query = "SELECT id, type_name, duration, price FROM appointment_types WHERE vet_id = ? AND is_available = 1";
                    $types_stmt = $conn->prepare($types_query);
                    $types_stmt->bind_param('i', $vet_id);
                    $types_stmt->execute();
                    $types_result = $types_stmt->get_result();

                    while ($type = $types_result->fetch_assoc()) {
                        echo "<option value='{$type['id']}'>" . ucfirst($type['type_name']) . " ({$type['duration']} mins) - $ {$type['price']}</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="form-group">
                <label for="appointment_date">Select Date:</label>
                <input type="date" name="appointment_date" id="appointment_date" required>
            </div>

            <div class="form-group">
                <label for="appointment_time">Select Time Slot:</label>
                <select name="appointment_time" id="appointment_time" required>
                    <!-- Time slots will be dynamically populated with available times using JS -->
                </select>
            </div>

            <input type="submit" value="Book Appointment">
        </form>
    </div>
<?php else: ?>
    <div class="premium-feature">
        <h2>Premium Feature</h2>
        <p>Booking an appointment is available for premium members only. <a href="upgrade.php">Upgrade to premium</a> to access this feature and more!</p>
    </div>
<?php endif; ?>
        <?php endif; ?>
        
    </div>

    <?php include 'footer.php'; ?>

    <script>
document.getElementById('appointment_date').addEventListener('change', function() {
    var date = this.value;
    var vetId = <?php echo $vet_id; ?>;
    var timeSelect = document.getElementById('appointment_time');

    // Clear previous options
    timeSelect.innerHTML = '';

    // Fetch available time slots from server
    fetch('fetch_time_slots.php?vet_id=' + vetId + '&date=' + date)
        .then(response => response.json())
        .then(data => {
            if (data.slots.length > 0) {
                data.slots.forEach(function(slot) {
                    var option = document.createElement('option');
                    option.value = slot.start_time;
                    option.textContent = slot.start_time + ' - ' + slot.end_time;
                    timeSelect.appendChild(option);
                });
            } else {
                var option = document.createElement('option');
                option.textContent = 'No available slots';
                option.disabled = true;
                timeSelect.appendChild(option);
            }
        });
});
</script>
</body>
</html>