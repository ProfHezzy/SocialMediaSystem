<?php
// Include database connection
include('db_connect.php');

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode([]); 
    exit;
}

$user_id = $_SESSION['user_id'];

if (isset($_GET['receiver_id'])) {
    $receiver_id = intval($_GET['receiver_id']); // Ensure receiver_id is an integer

    // Fetch messages between the logged-in user and the receiver
    $query = "
        SELECT m.message, m.created_at, u.full_name, m.sender_id
        FROM messages m
        JOIN users u ON m.sender_id = u.id
        WHERE (m.sender_id = ? AND m.receiver_id = ?) 
           OR (m.sender_id = ? AND m.receiver_id = ?)
        ORDER BY m.created_at ASC
    ";

    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("iiii", $user_id, $receiver_id, $receiver_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $messages = [];
        while ($row = $result->fetch_assoc()) {
            $messages[] = $row;
        }

        echo json_encode($messages);
    } else {
        echo json_encode([]); // Return empty array if query fails
    }
} else {
    echo json_encode([]); // Return empty array if receiver_id is missing
}
?>

<script>
    document.addEventListener("DOMContentLoaded", function () {
    const chatMessages = document.getElementById('chatMessages');
    const messageInput = document.getElementById('messageText');
    const receiver_id = <?php echo isset($_GET['receiver_id']) ? $_GET['receiver_id'] : 0; ?>;
    let lastMessageTime = null; // Track the last message timestamp to avoid unnecessary updates

    // Function to fetch messages
    async function fetchMessages() {
        try {
            const response = await fetch('fetch_messages.php?receiver_id=' + receiver_id);
            if (!response.ok) throw new Error('Network response was not ok');
            
            const messages = await response.json();
            if (messages.length > 0) {
                const latestMessageTime = messages[messages.length - 1].created_at;

                // Update only if new messages are received
                if (latestMessageTime !== lastMessageTime) {
                    lastMessageTime = latestMessageTime; // Update last message time
                    let messagesHTML = '';

                    messages.forEach(message => {
                        if (message.sender_id == <?php echo $_SESSION['user_id']; ?>) {
                            messagesHTML += `
                                <div class="message sender">
                                    <div class="message-content">
                                        <p><strong>You:</strong> ${message.message}</p>
                                    </div>
                                    <span class="message-time">${message.created_at}</span>
                                </div>
                            `;
                        } else {
                            messagesHTML += `
                                <div class="message receiver">
                                    <div class="message-content">
                                        <p><strong>${message.full_name}:</strong> ${message.message}</p>
                                    </div>
                                    <span class="message-time">${message.created_at}</span>
                                </div>
                            `;
                        }
                    });

                    chatMessages.innerHTML = messagesHTML;
                    chatMessages.scrollTop = chatMessages.scrollHeight; // Auto-scroll to the latest message
                }
            }
        } catch (error) {
            console.error('Error fetching messages:', error);
        }
    }

    // Function to send a new message
    async function sendMessage(event) {
        event.preventDefault();
        const message = messageInput.value.trim();

        if (message === '') return; // Prevent sending empty messages

        try {
            const response = await fetch('send_message.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `message=${encodeURIComponent(message)}&receiver_id=${receiver_id}`
            });

            if (!response.ok) throw new Error('Failed to send message');

            messageInput.value = ''; // Clear input field
            fetchMessages(); // Refresh chat immediately after sending
        } catch (error) {
            console.error('Error sending message:', error);
        }
    }

    // Fetch messages every 2 seconds (improved real-time feel)
    setInterval(fetchMessages, 2000);

    // Attach send message event
    document.getElementById('sendMessageForm').addEventListener('submit', sendMessage);
});

</script>