<?php
session_start();
include '../db.php';

// Function to safely redirect with error message
function redirectWithError($errorMessage) {
    $_SESSION['login_error'] = $errorMessage;
    header("Location: index.php");
    exit();
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate input
    if (empty($_POST['email']) || empty($_POST['password'])) {
        redirectWithError("Please enter both email and password.");
    }

    // Sanitize and validate email
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        redirectWithError("Invalid email format.");
    }

    // Prepare statement to prevent SQL injection
    $sql = "SELECT * FROM vets WHERE email = ? LIMIT 1";
    $stmt = mysqli_prepare($conn, $sql);
    
    if (!$stmt) {
        redirectWithError("Database connection error. Please try again.");
    }

    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (!$result) {
        redirectWithError("Error processing your request. Please try again.");
    }

    if (mysqli_num_rows($result) === 1) {
        $vet = mysqli_fetch_assoc($result);

        // Check account approval status
        if ($vet['approval_status'] !== 'approved') {
            redirectWithError("Your account is not approved. Please contact administrator.");
        }

        // Verify password
        if (password_verify($_POST['password'], $vet['password'])) {
            // Regenerate session ID to prevent session fixation
            session_regenerate_id(true);
            
            // Set session variables
            $_SESSION['vet_id'] = $vet['id'];
            $_SESSION['vet_name'] = $vet['name'];
            $_SESSION['login_time'] = time();

            // Optional: Log login attempt
            error_log("Successful login by vet: {$vet['email']} at " . date('Y-m-d H:i:s'));

            // Redirect to dashboard
            header("Location: vet_dashboard.php");
            exit();
        } else {
            redirectWithError("Invalid email or password.");
        }
    } else {
        redirectWithError("No account found with this email.");
    }

    // Close statement and connection
    mysqli_stmt_close($stmt);
    mysqli_close($conn);
}
?>