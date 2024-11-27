<?php
require 'db.php';
require 'vendor/autoload.php';
session_start();

// Verify user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "Unauthorized access";
    exit;
}

$user_id = $_SESSION['user_id'];
$vet_id = intval($_GET['vet_id']);
$appointment_date = $_GET['date'];
$appointment_time = $_GET['time'];
$appointment_type_id = intval($_GET['type_id']);
$appointment_mode = $_GET['mode'];

// For online appointments, verify Stripe payment
if ($appointment_mode === 'online') {
    // Check if session_id is set
    if (!isset($_GET['session_id']) || empty($_GET['session_id'])) {
        echo "Invalid payment session. Please try booking again.";
        exit;
    }

    try {
        // Retrieve Stripe session
        $stripe = new \Stripe\StripeClient('sk_test_51QNWQKG2zxFLmtj9w1HsGLgAkVByklUMkMC59EYOk9A2XNaL5azhcTTlFT2LE5oJMkYPxOysXU4cdJidanITC70n00S49ksdJ4');
        
        // Verify the Stripe session
        $session = $stripe->checkout->sessions->retrieve($_GET['session_id']);
        
        // Check payment status
        if ($session->payment_status !== 'paid') {
            echo "Payment not completed. Please try again.";
            exit;
        }
    } catch (Exception $e) {
        // Log the full error for debugging
        error_log("Stripe verification error: " . $e->getMessage());
        echo "Payment verification failed. Please contact support.";
        exit;
    }
}

// Fetch appointment type details
$type_query = "SELECT type_name, duration, price FROM appointment_types WHERE id = ?";
$type_stmt = $conn->prepare($type_query);
$type_stmt->bind_param('i', $appointment_type_id);
$type_stmt->execute();
$type_result = $type_stmt->get_result()->fetch_assoc();

$duration = intval($type_result['duration']);
$start_time = $appointment_time;
$end_time = date('H:i:s', strtotime($start_time) + $duration * 60);

// Insert appointment into database
$insert_query = "INSERT INTO appointments (vet_id, user_id, appointment_type_id, appointment_date, start_time, end_time, mode, status) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, 'confirmed')";
$insert_stmt = $conn->prepare($insert_query);
$insert_stmt->bind_param('iiissss', $vet_id, $user_id, $appointment_type_id, $appointment_date, $start_time, $end_time, $appointment_mode);

if ($insert_stmt->execute()) {
    // Fetch vet and user details for confirmation email/notification
    $vet_query = "SELECT name, email FROM vets WHERE id = ?";
    $vet_stmt = $conn->prepare($vet_query);
    $vet_stmt->bind_param('i', $vet_id);
    $vet_stmt->execute();
    $vet_result = $vet_stmt->get_result()->fetch_assoc();

    $user_query = "SELECT email FROM users WHERE id = ?";
    $user_stmt = $conn->prepare($user_query);
    $user_stmt->bind_param('i', $user_id);
    $user_stmt->execute();
    $user_result = $user_stmt->get_result()->fetch_assoc();

   
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Appointment Booked Successfully</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            padding: 50px;
        }
        .success-icon {
            color: green;
            font-size: 100px;
        }
    </style>
</head>
<body>
    <div class="success-icon">✓</div>
    <h1>Appointment Booked Successfully!</h1>
    <p>Your <?php echo ucfirst($appointment_mode); ?> appointment with Dr. <?php echo htmlspecialchars($vet_result['name']); ?> 
       on <?php echo date('F d, Y', strtotime($appointment_date)); ?> 
       at <?php echo date('h:i A', strtotime($start_time)); ?> has been confirmed.</p>
    <p>Appointment Type: <?php echo htmlspecialchars($type_result['type_name']); ?></p>
    <a href="index.php">Return to Dashboard</a>
</body>
</html>
<?php
} else {
    echo "Error confirming appointment.";
}
?>