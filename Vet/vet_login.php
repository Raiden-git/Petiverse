<?php
session_start();
include '../db.php'; // Include the database connection file

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    // Check if vet exists
    $sql = "SELECT * FROM vets WHERE email = ? AND approval_status = 'approved' LIMIT 1";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) === 1) {
        $vet = mysqli_fetch_assoc($result);

        // Verify password
        if (password_verify($password, $vet['password'])) {
            // Set session variables
            $_SESSION['vet_id'] = $vet['id'];
            $_SESSION['vet_name'] = $vet['name'];

            // Redirect to vet dashboard (after successful login)
            header("Location: vet_dashboard.php");
            exit();
        } else {
            $error = "Invalid email or password.";
        }
    } else {
        $error = "Your account is either not approved yet or doesn't exist.";
    }

    // Close the database connection
    mysqli_close($conn);
}
?>
