


<?php
session_start();

// Set timeout duration (in seconds)
$timeout_duration = 300; // 5 minutes

// Check if the user is logged in
if ($_SESSION['role'] != 'admin') {
    header('Location: dashboard.php');
    exit;
}

// Check if the last activity time is set
if (isset($_SESSION['last_activity'])) {
    // Calculate the time since last activity
    $elapsed_time = time() - $_SESSION['last_activity'];

    // If the elapsed time is greater than the timeout duration, log the user out
    if ($elapsed_time > $timeout_duration) {
        session_unset();
        session_destroy();
        header('Location: index.php');
        exit;
    }
}

// Update last activity time
$_SESSION['last_activity'] = time();
?>

