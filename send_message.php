<?php
// Include database connection
include('db_connect.php');

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];

if (isset($_POST['message']) && isset($_POST['receiver_id'])) {
    $message = trim($_POST['message']);
    $receiver_id = intval($_POST['receiver_id']); // Ensure receiver_id is an integer

    // Validate input
    if (empty($message) || $receiver_id <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid input']);
        exit;
    }

    // Sanitize message
    $message = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');

    // Insert the message into the database
    $query = "INSERT INTO messages (sender_id, receiver_id, message, created_at) VALUES (?, ?, ?, NOW())";
    $stmt = $conn->prepare($query);

    if ($stmt) {
        $stmt->bind_param("iis", $user_id, $receiver_id, $message);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Message sent successfully',
                'data' => [
                    'sender_id' => $user_id,
                    'receiver_id' => $receiver_id,
                    'message' => $message,
                    'created_at' => date('Y-m-d H:i:s')
                ]
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to send message']);
        }
        $stmt->close();
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $conn->error]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Missing parameters']);
}
?>

<script>
    // Send Message Function
function sendMessage(event) {
    event.preventDefault();
    
    const message = document.getElementById('messageText').value.trim();
    const receiver_id = <?php echo isset($_GET['receiver_id']) ? $_GET['receiver_id'] : 0; ?>;

    if (message === "") {
        alert("Message cannot be empty");
        return;
    }

    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'send_message.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    
    xhr.onload = function () {
        if (xhr.status === 200) {
            try {
                const response = JSON.parse(xhr.responseText);
                
                if (response.status === "success") {
                    // Clear input box
                    document.getElementById('messageText').value = '';

                    // Append new message to the chat window dynamically
                    const chatMessages = document.getElementById('chatMessages');
                    const newMessage = document.createElement('div');
                    newMessage.classList.add('message', 'sender');
                    newMessage.innerHTML = `<div class='message-content'>
                        <p><strong>You:</strong> ${response.data.message}</p>
                        <span class='message-time'>${response.data.created_at}</span>
                    </div>`;
                    
                    chatMessages.appendChild(newMessage);
                    chatMessages.scrollTop = chatMessages.scrollHeight;
                } else {
                    alert(response.message);
                }
            } catch (e) {
                console.error("Invalid JSON response:", xhr.responseText);
                alert("An error occurred. Please try again.");
            }
        } else {
            alert("Request failed. Please try again.");
        }
    };

    const requestData = `message=${encodeURIComponent(message)}&receiver_id=${encodeURIComponent(receiver_id)}`;
    xhr.send(requestData);
}

</script>