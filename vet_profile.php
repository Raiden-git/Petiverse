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
    <link rel="stylesheet" href="./assets/css/scrollbar.css">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%);
            --secondary-gradient: linear-gradient(135deg, #06b6d4 0%, #3b82f6 100%);
            --accent-gradient: linear-gradient(135deg, #ec4899 0%, #8b5cf6 100%);
            --surface-color: #ffffff;
            --background-color: #f8fafc;
            --text-primary: #1e293b;
            --text-secondary: #64748b;
            --border-color: #e2e8f0;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.1);
            --shadow-md: 0 4px 6px -1px rgba(0,0,0,0.1);
            --shadow-lg: 0 10px 15px -3px rgba(0,0,0,0.1);
            --rounded-sm: 0.5rem;
            --rounded-md: 1rem;
            --rounded-lg: 1.5rem;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--background-color);
            color: var(--text-primary);
            line-height: 1.6;
        }

        .pro-container {
            max-width: 1200px;
            margin: 3rem auto;
            padding: 0 1.5rem;
        }

        .profile-card {
            background: var(--surface-color);
            border-radius: var(--rounded-lg);
            box-shadow: var(--shadow-lg);
            overflow: hidden;
            margin-bottom: 2rem;
        }

        .profile-header {
            display: flex;
            align-items: center;
            padding: 3rem;
            background: var(--primary-gradient);
            color: var(--surface-color);
            position: relative;
            overflow: hidden;
        }

        .profile-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('/api/placeholder/1200/400') center/cover;
            opacity: 0.1;
            pointer-events: none;
        }

        .profile-avatar {
            width: 180px;
            height: 180px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid var(--surface-color);
            box-shadow: var(--shadow-lg);
            margin-right: 3rem;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            z-index: 1;
        }

        .profile-avatar:hover {
            transform: scale(1.05) rotate(2deg);
        }

        .profile-details {
            flex-grow: 1;
            position: relative;
            z-index: 1;
        }

        .profile-name {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            letter-spacing: -0.025em;
        }

        .profile-specialization {
            font-size: 1.25rem;
            opacity: 0.9;
            margin-bottom: 1.5rem;
            font-weight: 500;
        }

        .profile-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.5rem;
            background: rgba(255,255,255,0.1);
            padding: 1.5rem;
            border-radius: var(--rounded-md);
            backdrop-filter: blur(10px);
        }

        .stat-item {
            text-align: center;
            padding: 1rem;
            border-radius: var(--rounded-sm);
            transition: transform 0.3s ease;
        }

        .stat-item:hover {
            transform: translateY(-2px);
        }

        .stat-value {
            font-weight: 700;
            font-size: 1.5rem;
            margin-bottom: 0.25rem;
        }

        .stat-label {
            font-size: 0.875rem;
            opacity: 0.8;
            font-weight: 500;
        }

        .section {
            padding: 2.5rem;
            background: var(--surface-color);
            margin-top: 1.5rem;
            border-radius: var(--rounded-md);
            box-shadow: var(--shadow-md);
        }

        .section-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .section-title i {
            color: #4f46e5;
        }

        .tab-buttons {
            display: flex;
            justify-content: center;
            background: var(--surface-color);
            padding: 0.5rem;
            border-radius: var(--rounded-lg);
            box-shadow: var(--shadow-md);
            margin-bottom: 2rem;
            gap: 0.5rem;
        }

        .tab-buttons button {
            padding: 1rem 2rem;
            font-size: 1rem;
            font-weight: 600;
            color: var(--text-secondary);
            background: transparent;
            border: none;
            border-radius: var(--rounded-md);
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .tab-buttons button.active {
            background: var(--secondary-gradient);
            color: white;
        }

        .schedule-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin: 2rem 0;
        }

        .day-schedule {
            background: var(--surface-color);
            padding: 1.5rem;
            border-radius: var(--rounded-md);
            box-shadow: var(--shadow-md);
            transition: transform 0.3s ease;
        }

        .day-schedule:hover {
            transform: translateY(-2px);
        }

        .day-schedule h3 {
            color: var(--text-primary);
            font-size: 1.25rem;
            margin-bottom: 0.5rem;
        }

        .date {
            color: var(--text-secondary);
            font-size: 0.875rem;
            margin-bottom: 1.25rem;
            font-weight: 500;
        }

        .time-slot {
            padding: 0.75rem 1rem;
            background: #f1f5f9;
            border-radius: var(--rounded-sm);
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .time-slot:not(.booked):hover {
            background: #e2e8f0;
            transform: translateX(4px);
        }

        .time-slot.booked {
            background: #fee2e2;
            color: #991b1b;
        }

        .booking-form {
            background: var(--surface-color);
            padding: 2.5rem;
            border-radius: var(--rounded-lg);
            box-shadow: var(--shadow-lg);
            max-width: 600px;
            margin: 2rem auto;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--text-primary);
        }

        .form-group select,
        .form-group input {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid var(--border-color);
            border-radius: var(--rounded-sm);
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-group select:focus,
        .form-group input:focus {
            outline: none;
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        .booking-form input[type="submit"] {
            width: 100%;
            padding: 1rem;
            background: var(--accent-gradient);
            color: white;
            border: none;
            border-radius: var(--rounded-md);
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .booking-form input[type="submit"]:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .premium-feature {
            background: var(--surface-color);
            padding: 2.5rem;
            border-radius: var(--rounded-lg);
            text-align: center;
            border: 2px dashed #e2e8f0;
            margin: 2rem auto;
            max-width: 600px;
        }

        .premium-feature h2 {
            color: var(--text-primary);
            margin-bottom: 1rem;
            font-size: 1.5rem;
        }

        .premium-feature p {
            color: var(--text-secondary);
            margin-bottom: 1.5rem;
        }

        .premium-feature a {
            color: #4f46e5;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .premium-feature a:hover {
            color: #4338ca;
        }

        .chat-section {
        background: linear-gradient(135deg, #ffffff, #f8fafc);
        border-radius: 20px;
        padding: 40px;
        text-align: center;
        max-width: 500px;
        margin: 30px auto;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05),
                    0 1px 8px rgba(0, 0, 0, 0.03);
        border: 1px solid rgba(231, 235, 241, 0.8);
        position: relative;
        overflow: hidden;
    }

    .chat-section::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #3b82f6, #60a5fa);
    }

    .chat-section h2 {
        color: #1e293b;
        font-size: 1.75rem;
        font-weight: 600;
        margin-bottom: 25px;
        line-height: 1.3;
    }

    .chat-section p {
        color: #64748b;
        font-size: 1rem;
        margin-bottom: 30px;
        line-height: 1.6;
    }

    .chat-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        background: #3b82f6;
        color: white;
        padding: 14px 32px;
        border-radius: 12px;
        text-decoration: none;
        font-weight: 500;
        font-size: 1.1rem;
        transition: all 0.3s ease;
        border: 2px solid transparent;
    }

    .chat-button:hover {
        transform: translateY(-2px);
        background: #2563eb;
        box-shadow: 0 8px 20px rgba(59, 130, 246, 0.2);
    }

    .chat-button:active {
        transform: translateY(0);
    }

    .chat-button i {
        font-size: 1.2rem;
        transition: transform 0.3s ease;
    }

    .chat-button:hover i {
        transform: translateX(4px);
    }

    .chat-icon-bg {
        position: absolute;
        right: -20px;
        bottom: -20px;
        width: 120px;
        height: 120px;
        opacity: 0.05;
        transform: rotate(-10deg);
        pointer-events: none;
    }

    /* Optional animation */
    @keyframes float {
        0% { transform: translateY(0px) rotate(-10deg); }
        50% { transform: translateY(-10px) rotate(-8deg); }
        100% { transform: translateY(0px) rotate(-10deg); }
    }

    .chat-icon-bg {
        animation: float 6s ease-in-out infinite;
    }

    @media (max-width: 640px) {
        .chat-section {
            padding: 30px 20px;
            margin: 20px;
        }

        .chat-section h2 {
            font-size: 1.5rem;
        }

        .chat-button {
            padding: 12px 24px;
            font-size: 1rem;
        }
    }

        @media (max-width: 768px) {
            .profile-header {
                flex-direction: column;
                text-align: center;
                padding: 2rem 1.5rem;
            }

            .profile-avatar {
                margin: 0 0 1.5rem 0;
                width: 150px;
                height: 150px;
            }

            .profile-stats {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .tab-buttons {
                flex-direction: column;
            }

            .tab-buttons button {
                width: 100%;
            }
        }
    </style>
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
                            <div class="stat-value">LKR <?php echo number_format($vet['consultation_fee'], 2); ?></div>
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

            <?php if ($is_premium): // Only allow chat if the user is premium ?>
                <div class="chat-section">
    <!-- Background decorative icon -->
    <svg class="chat-icon-bg" viewBox="0 0 24 24" fill="currentColor">
        <path d="M12 2C6.48 2 2 6.48 2 12c0 5.52 4.48 10 10 10s10-4.48 10-10c0-5.52-4.48-10-10-10zM8 14l3-3v7h2v-7l3 3h2l-5-5-5 5h2z"/>
    </svg>

    <h2>Want to chat with Dr. <?php echo htmlspecialchars($vet['name']); ?>?</h2>
    <p>Start a conversation with our experienced veterinarian for professional advice and pet care guidance.</p>
    
    <a href="user_chat.php?vet_id=<?php echo $vet_id; ?>" class="chat-button">
        Start Chat
        <i class="fas fa-arrow-right"></i>
    </a>
</div>
<?php else: ?>
    <div class="premium-feature">
        <h2>Premium Feature</h2>
        <p>Chatting with veterinarians is available for premium members only. <a href="subscription.php">Upgrade to premium</a> to access this feature and more!</p>
    </div>
<?php endif; ?>

                
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
        <p>Booking an appointment is available for premium members only. <a href="subscription.php">Upgrade to premium</a> to access this feature and more!</p>
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