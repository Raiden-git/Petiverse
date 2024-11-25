<?php
include('db.php'); // Include your database connection

// Get tomorrow's date
$tomorrow = date('Y-m-d', strtotime('+1 day'));

// Fetch reminders for tomorrow from premium users
$sql = "SELECT u.email AS user_email, r.pet_name, r.vaccination_date, r.note 
        FROM vaccination_reminders r
        JOIN users u ON r.user_id = u.id
        WHERE r.vaccination_date = ? AND u.is_premium = 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $tomorrow);
$stmt->execute();
$result = $stmt->get_result();

while ($reminder = $result->fetch_assoc()) {
    $to = $reminder['user_email'];
    $subject = "Vaccination Reminder for " . $reminder['pet_name'];
    $message = "Dear Pet Owner,\n\nThis is a friendly reminder that your pet, " . $reminder['pet_name'] . ", is due for vaccination on " . $reminder['vaccination_date'] . ".\n\nNote: " . $reminder['note'] . "\n\nBest regards,\nPetiverse";
    $headers = "From: noreply@petiverse.com";

    // Send email
    mail($to, $subject, $message, $headers);
}

$stmt->close();
$conn->close();
?>
