<?php
require '../db.php';
session_start();

if (!isset($_SESSION['vet_id'])) {
    header('Location: vet_login.php');
    exit;
}

$vet_id = $_SESSION['vet_id'];


$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

$user_query = "SELECT full_name, profile_pic FROM users WHERE id = ?";
$user_stmt = $conn->prepare($user_query);
$user_stmt->bind_param('i', $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user = $user_result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $message = trim($_POST['message']);
    
    if (!empty($message)) {
        $insert_query = "INSERT INTO messages (sender_type, sender_id, receiver_type, receiver_id, message) 
                        VALUES ('vet', ?, 'user', ?, ?)";
        $insert_stmt = $conn->prepare($insert_query);
        $insert_stmt->bind_param('iis', $vet_id, $user_id, $message);
        $insert_stmt->execute();

        header("Location: " . $_SERVER['PHP_SELF'] . "?user_id=" . $user_id);
        exit;
    }
}

$chat_list_query = "SELECT DISTINCT 
                    u.id as user_id,
                    u.full_name,
                    u.profile_pic,
                    MAX(m.created_at) as last_message_time,
                    (SELECT message FROM messages 
                     WHERE (sender_id = u.id AND receiver_id = ? AND sender_type = 'user')
                     OR (sender_id = ? AND receiver_id = u.id AND sender_type = 'vet')
                     ORDER BY created_at DESC LIMIT 1) as last_message,
                    COUNT(CASE WHEN m.read_status = 0 AND m.sender_type = 'user' AND m.receiver_id = ? THEN 1 END) as unread_count
                    FROM messages m
                    JOIN users u ON (m.sender_type = 'user' AND m.sender_id = u.id)
                    WHERE m.sender_id = u.id AND m.receiver_id = ?
                    OR m.sender_id = ? AND m.receiver_id = u.id
                    GROUP BY u.id
                    ORDER BY last_message_time DESC";

$chat_list_stmt = $conn->prepare($chat_list_query);
$chat_list_stmt->bind_param('iiiii', $vet_id, $vet_id, $vet_id, $vet_id, $vet_id);
$chat_list_stmt->execute();
$chat_list = $chat_list_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

if ($user_id) {
    $chat_query = "SELECT m.*, 
                   CASE 
                       WHEN m.sender_type = 'user' THEN u.full_name 
                       WHEN m.sender_type = 'vet' THEN v.name 
                   END as sender_name
                   FROM messages m 
                   LEFT JOIN users u ON m.sender_type = 'user' AND m.sender_id = u.id 
                   LEFT JOIN vets v ON m.sender_type = 'vet' AND m.sender_id = v.id 
                   WHERE (m.sender_type = 'user' AND m.sender_id = ? AND m.receiver_id = ? AND m.deleted_by_receiver = 0)
                   OR (m.sender_type = 'vet' AND m.sender_id = ? AND m.receiver_id = ? AND m.deleted_by_sender = 0)
                   ORDER BY m.created_at ASC";

    $chat_stmt = $conn->prepare($chat_query);
    $chat_stmt->bind_param('iiii', $user_id, $vet_id, $vet_id, $user_id);
    $chat_stmt->execute();
    $messages = $chat_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    $update_query = "UPDATE messages 
                    SET read_status = 1 
                    WHERE sender_type = 'user' 
                    AND sender_id = ? 
                    AND receiver_id = ? 
                    AND read_status = 0";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param('ii', $user_id, $vet_id);
    $update_stmt->execute();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vet Chat Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }

        .chat-dashboard {
            display: flex;
            max-width: 1200px;
            margin: 20px auto;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            height: calc(100vh - 100px);
        }

        .chat-list {
            width: 300px;
            border-right: 1px solid #eee;
            overflow-y: auto;
        }

        .chat-item {
            padding: 15px;
            border-bottom: 1px solid #eee;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .chat-item:hover {
            background: #f8f9fa;
        }

        .chat-item.active {
            background: #e9ecef;
        }

        .chat-item-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }

        .chat-item-details {
            flex: 1;
        }

        .chat-item-name {
            font-weight: 600;
            margin-bottom: 5px;
        }

        .chat-item-last-message {
            font-size: 12px;
            color: #6c757d;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .unread-badge {
            background: #007bff;
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 12px;
        }

        .chat-main {
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .chat-header {
            padding: 15px;
            border-bottom: 1px solid #eee;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
            background: #f8f9fa;
        }

        .message {
            margin: 10px 0;
            padding: 10px;
            border-radius: 10px;
            max-width: 70%;
        }

        .message.sent {
            background: #007bff;
            color: white;
            margin-left: auto;
        }

        .message.received {
            background: #e9ecef;
            color: #212529;
        }

        .message-time {
            font-size: 12px;
            color: #6c757d;
            margin-top: 5px;
        }

        .chat-input {
            padding: 15px;
            border-top: 1px solid #eee;
        }

        .message-form {
            display: flex;
            gap: 10px;
        }

        .message-input {
            flex: 1;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }

        .send-button {
            padding: 10px 20px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .send-button:hover {
            background: #0056b3;
        }

        .no-chat-selected {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 100%;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <?php include 'nav.php'; ?>

    <div class="chat-dashboard">
        <div class="chat-list">
            <?php foreach ($chat_list as $chat): ?>

                <div class="chat-item <?php echo $chat['user_id'] == $user_id ? 'active' : ''; ?>" 
                     onclick="window.location.href='?user_id=<?php echo $chat['user_id']; ?>'">

                    <?php 
                    $profilePic = !empty($chat['profile_pic']) 
                        ? 'data:image/jpeg;base64,'.base64_encode($chat['profile_pic']) 
                        : 'default-profile.jpg'; 
                    ?>

                    <img src="<?php echo $profilePic; ?>" alt="" class="chat-item-avatar">

                    <div class="chat-item-details">
                        <div class="chat-item-name"><?php echo htmlspecialchars($chat['full_name']); ?></div>
                        <div class="chat-item-last-message"><?php echo htmlspecialchars($chat['last_message']); ?></div>
                    </div>

                    <?php if ($chat['unread_count'] > 0): ?>
                        <span class="unread-badge"><?php echo $chat['unread_count']; ?></span>
                    <?php endif; ?>
                </div>

            <?php endforeach; ?>

        </div>

        <div class="chat-main">
            <?php if ($user_id && $user): ?>
                <div class="chat-header">

                    <?php 
                    $userProfilePic = !empty($user['profile_pic']) 
                        ? 'data:image/jpeg;base64,'.base64_encode($user['profile_pic']) 
                        : 'default-profile.jpg'; 
                    ?>

                    <img src="<?php echo $userProfilePic; ?>" alt="" class="chat-item-avatar">
                    <h2><?php echo htmlspecialchars($user['full_name']); ?></h2>

                </div>

                <div class="chat-messages" id="chatMessages">

                    <?php foreach ($messages as $message): ?>
                        <div class="message <?php echo $message['sender_type'] === 'vet' ? 'sent' : 'received'; ?>">
                            <?php echo htmlspecialchars($message['message']); ?>
                            <div class="message-time">
                                <?php echo date('M d, Y H:i', strtotime($message['created_at'])); ?>
                            </div>
                        </div>

                    <?php endforeach; ?>
                </div>

                <div class="chat-input">
                    <form class="message-form" method="POST">
                        <input type="text" name="message" class="message-input" placeholder="Type your message..." required>
                        <button type="submit" class="send-button">
                            <i class="fas fa-paper-plane"></i> Send
                        </button>
                    </form>
                </div>
            <?php else: ?>
                <div class="no-chat-selected">
                    <i class="fas fa-comments" style="font-size: 48px; margin-bottom: 20px;"></i>
                    <h2>Select a conversation</h2>
                    <p>Choose a chat from the list to start messaging</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        
        function scrollToBottom() {
            const chatMessages = document.getElementById('chatMessages');
            if (chatMessages) {
                chatMessages.scrollTop = chatMessages.scrollHeight;
            }
        }

        scrollToBottom();

        <?php if ($user_id): ?>
        setInterval(function() {
            fetch('fetch_vet_messages.php?user_id=<?php echo $user_id; ?>')
                .then(response => response.json())
                .then(data => {
                    const chatMessages = document.getElementById('chatMessages');
                    chatMessages.innerHTML = '';
                    
                    data.forEach(message => {
                        const messageDiv = document.createElement('div');
                        messageDiv.className = `message ${message.sender_type === 'vet' ? 'sent' : 'received'}`;
                        
                        const messageContent = document.createElement('div');
                        messageContent.textContent = message.message;
                        
                        const messageTime = document.createElement('div');
                        messageTime.className = 'message-time';
                        messageTime.textContent = new Date(message.created_at).toLocaleString();
                        
                        messageDiv.appendChild(messageContent);
                        messageDiv.appendChild(messageTime);
                        chatMessages.appendChild(messageDiv);
                    });
                    
                    scrollToBottom();
                });
        }, 5000);
        <?php endif; ?>
    </script>
</body>
</html>