<?php
// Set SMTP configuration for Gmail
ini_set("SMTP", "smtp.gmail.com");
ini_set("smtp_port", "587");
ini_set("sendmail_from", "osurapathirana@gmail.com");

// Email setup
$to = "osurapathirana@gmail.com";
$subject = "Feedback from Petiverse";
$message = $_POST['message'];
$headers = "From: osurapathirana@gmail.com";

// Send email
if(mail($to, $subject, $message, $headers)) {
    echo "Thank you for your feedback!";
} else {
    echo "Sorry, something went wrong. Please try again later.";
}
?>