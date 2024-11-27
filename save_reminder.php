<?php
session_start();
include('db.php'); 



// Get user input
$user_id = $_SESSION['user_id'];
$pet_name = $_POST['pet_name'];
$email = $_POST['email'];
$vaccination_date = $_POST['vaccination_date'];
$note = $_POST['note'];

// Check if user is premium
$sql = "SELECT is_premium FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($user['is_premium'] == 1) {
    // Insert reminder into the database
    $sql = "INSERT INTO vaccination_reminders (user_id, pet_name, email, vaccination_date, note) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issss", $user_id, $pet_name, $email, $vaccination_date, $note);

    if ($stmt->execute()) {
        echo "Reminder saved successfully!";
    } else {
        echo "Error saving reminder: " . $stmt->error;
    }

    $stmt->close();
} else {
    echo "This feature is only available for premium users.";
}

$conn->close();
?>
