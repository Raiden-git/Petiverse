<?php
require 'db.php';
session_start();

// Check if user is logged in and is premium
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Check premium status
$premium_query = "SELECT is_premium FROM users WHERE id = ?";
$premium_stmt = $conn->prepare($premium_query);
$premium_stmt->bind_param('i', $user_id);
$premium_stmt->execute();
$premium_result = $premium_stmt->get_result();
$user_premium = $premium_result->fetch_assoc();

if (!$user_premium['is_premium']) {
    header('Location: subscription.php');
    exit;
}

// Get vet ID from URL
$vet_id = isset($_GET['vet_id']) ? intval($_GET['vet_id']) : 0;

// Fetch vet details
$vet_query = "SELECT name, profile_picture FROM vets WHERE id = ?";
$vet_stmt = $conn->prepare($vet_query);
$vet_stmt->bind_param('i', $vet_id);
$vet_stmt->execute();
$vet_result = $vet_stmt->get_result();
$vet = $vet_result->fetch_assoc();

// Handle message sending
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $message = trim($_POST['message']);
    
    if (!empty($message)) {
        $insert_query = "INSERT INTO messages (sender_type, sender_id, receiver_type, receiver_id, message) 
                        VALUES ('user', ?, 'vet', ?, ?)";
        $insert_stmt = $conn->prepare($insert_query);
        $insert_stmt->bind_param('iis', $user_id, $vet_id, $message);
        $insert_stmt->execute();

        // After successful insertion, redirect to avoid resubmitting the form
        header("Location: " . $_SERVER['PHP_SELF'] . "?vet_id=" . $vet_id);
        exit;
    }
}

// Fetch chat history
$chat_query = "SELECT m.*, 
               CASE 
                   WHEN m.sender_type = 'user' THEN u.full_name 
                   WHEN m.sender_type = 'vet' THEN v.name 
               END as sender_name,
               CASE 
                   WHEN m.sender_type = 'user' THEN u.profile_pic 
                   WHEN m.sender_type = 'vet' THEN v.profile_picture 
               END as sender_pic
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat with Dr. <?php echo htmlspecialchars($vet['name']); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }

        body {
            background-color: #f3f4f6;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .back-button {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 16px;
            background: #ffffff;
            color: #374151;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.2s ease;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        }

        .back-button:hover {
            background: #f9fafb;
            color: #111827;
        }

        .chat-container {
            max-width: 900px;
            margin: 20px auto;
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -1px rgba(0,0,0,0.06);
            overflow: hidden;
        }

        .chat-header {
            display: flex;
            align-items: center;
            padding: 20px;
            background: #ffffff;
            border-bottom: 1px solid #e5e7eb;
        }

        .vet-avatar {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 16px;
            border: 2px solid #e5e7eb;
        }

        .vet-info h2 {
            color: #111827;
            font-size: 1.125rem;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .online-status {
            font-size: 0.875rem;
            color: #10b981;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .chat-messages {
            height: 500px;
            overflow-y: auto;
            padding: 24px;
            background: #ffffff;
            scroll-behavior: smooth;
        }

        .message {
            display: flex;
            flex-direction: column;
            margin-bottom: 20px;
            max-width: 80%;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .message.sent {
            margin-left: auto;
        }

        .message-content {
            padding: 12px 16px;
            border-radius: 16px;
            font-size: 0.9375rem;
            line-height: 1.5;
        }

        .message.sent .message-content {
            background: #3b82f6;
            color: #ffffff;
            border-bottom-right-radius: 4px;
        }

        .message.received .message-content {
            background: #f3f4f6;
            color: #1f2937;
            border-bottom-left-radius: 4px;
        }

        .message-time {
            font-size: 0.75rem;
            margin-top: 6px;
            color: #6b7280;
        }

        .message.sent .message-time {
            text-align: right;
        }

        .message-form {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 20px;
            background: #ffffff;
            border-top: 1px solid #e5e7eb;
        }

        .message-input {
            flex: 1;
            padding: 12px 16px;
            border: 1px solid #e5e7eb;
            border-radius: 9999px;
            font-size: 0.9375rem;
            background: #f9fafb;
            transition: all 0.2s ease;
        }

        .message-input:focus {
            outline: none;
            border-color: #3b82f6;
            background: #ffffff;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .send-button {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 44px;
            height: 44px;
            background: #3b82f6;
            color: white;
            border: none;
            border-radius: 50%;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .send-button:hover {
            background: #2563eb;
            transform: scale(1.05);
        }

        .send-button i {
            font-size: 1.125rem;
        }

        /* Custom scrollbar */
        .chat-messages::-webkit-scrollbar {
            width: 6px;
        }

        .chat-messages::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        .chat-messages::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }

        .chat-messages::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>
</head>
<body>
    <?php include 'Cus-NavBar/navBar.php'; ?>

    <div class="container">
        <a href="vet_profile.php?id=<?php echo $vet_id; ?>" class="back-button">
            <i class="fas fa-arrow-left"></i> Back to Profile
        </a>

        <div class="chat-container">
            <div class="chat-header">
                <?php 
                $profilePicture = !empty($vet['profile_picture']) 
                    ? 'data:image/jpeg;base64,'.base64_encode($vet['profile_picture']) 
                    : 'default-profile.jpg'; 
                ?>
                <img src="<?php echo $profilePicture; ?>" alt="Dr. <?php echo htmlspecialchars($vet['name']); ?>" class="vet-avatar">
                <div class="vet-info">
                    <h2>Dr. <?php echo htmlspecialchars($vet['name']); ?></h2>
                    <div class="online-status">
                        <span class="status-dot" style="display: inline-block; width: 8px; height: 8px; background: #10b981; border-radius: 50%;"></span>
                        Online
                    </div>
                </div>
            </div>

            <div class="chat-messages" id="chatMessages">
                <?php foreach ($messages as $message): ?>
                    <div class="message <?php echo $message['sender_type'] === 'user' ? 'sent' : 'received'; ?>">
                        <div class="message-content">
                            <?php echo htmlspecialchars($message['message']); ?>
                        </div>
                        <div class="message-time">
                            <?php echo date('M d, Y H:i', strtotime($message['created_at'])); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <form class="message-form" method="POST">
                <input type="text" name="message" class="message-input" placeholder="Type your message..." required>
                <button type="submit" class="send-button">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </form>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script>
        // Auto-scroll to bottom of chat
        function scrollToBottom() {
            const chatMessages = document.getElementById('chatMessages');
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        // Scroll to bottom on page load
        scrollToBottom();

        // Check for new messages every 5 seconds
        setInterval(function() {
            fetch('fetch_messages.php?vet_id=<?php echo $vet_id; ?>')
                .then(response => response.json())
                .then(data => {
                    const chatMessages = document.getElementById('chatMessages');
                    chatMessages.innerHTML = '';
                    
                    data.forEach(message => {
                        const messageDiv = document.createElement('div');
                        messageDiv.className = `message ${message.sender_type === 'user' ? 'sent' : 'received'}`;
                        
                        const messageContent = document.createElement('div');
                        messageContent.className = 'message-content';
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
    </script>
</body>
</html>