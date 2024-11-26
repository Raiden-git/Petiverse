<?php
require 'db.php';
session_start();

if (!isset($_SESSION['user_id']) || !isset($_GET['vet_id'])) {
    http_response_code(400);
    exit;
}

$user_id = $_SESSION['user_id'];
$vet_id = intval($_GET['vet_id']);

// Fetch messages
$chat_query = "SELECT m.*, 
               CASE 
                   WHEN m.sender_type = 'user' THEN u.full_name 
                   WHEN m.sender_type = 'vet' THEN v.name 
               END as sender_name
               FROM messages m 
               LEFT JOIN users u ON m.sender_type = 'user' AND m.sender_id = u.id 
               LEFT JOIN vets v ON m.sender_type = 'vet' AND m.sender_id = v.id 
               WHERE (m.sender_type = 'user' AND m.sender_id = ? AND m.receiver_id = ? AND m.deleted_by_sender = 0)
               OR (m.sender_type = 'vet' AND m.sender_id = ? AND m.receiver_id = ? AND m.deleted_by_receiver = 0)
               ORDER BY m.created_at ASC";

$chat_stmt = $conn->prepare($chat_query);
$chat_stmt->bind_param('iiii', $user_id, $vet_id, $vet_id, $user_id);
$chat_stmt->execute();
$messages = $chat_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Mark messages as read
$update_query = "UPDATE messages 
                SET read_status = 1 
                WHERE sender_type = 'vet' 
                AND sender_id = ? 
                AND receiver_id = ? 
                AND read_status = 0";
$update_stmt = $conn->prepare($update_query);
$update_stmt->bind_param('ii', $vet_id, $user_id);
$update_stmt->execute();

header('Content-Type: application/json');
echo json_encode($messages);