<?php
session_start();
include './db.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $ad_id = intval($_POST['delete_id']);
    $user_id = $_SESSION['user_id']; // Ensure the ad belongs to the logged-in user

    // Check if the ad belongs to the user
    $check_query = "SELECT * FROM pet_selling_ads WHERE ad_id = ? AND user_id = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("ii", $ad_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Delete the ad
        $delete_query = "DELETE FROM pet_selling_ads WHERE ad_id = ?";
        $delete_stmt = $conn->prepare($delete_query);
        $delete_stmt->bind_param("i", $ad_id);
        if ($delete_stmt->execute()) {
            echo "<script>alert('Ad deleted successfully.'); window.location.href = 'my_ads.php';</script>";
        } else {
            echo "<script>alert('Failed to delete the ad.'); window.location.href = 'my_ads.php';</script>";
        }
    } else {
        echo "<script>alert('Unauthorized action.'); window.location.href = 'my_ads.php';</script>";
    }
}
?>
