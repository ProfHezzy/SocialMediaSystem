<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); // Redirect to login page if not logged in
    exit;
}

// Include database connection
include('db_connect.php');
include('fetch_messages.php');

$user_id = $_SESSION['user_id'];

// Fetch the user data
$query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    header('Location: login.php');
    exit;
}

// Fetch the friends list
$query = "SELECT u.id, u.full_name, u.profile_picture FROM friends f 
          INNER JOIN users u ON f.friend_id = u.id 
          WHERE f.user_id = ? AND f.status = 'accepted'";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$friends_result = $stmt->get_result();

// Handle search query
$search_query = '';
if (isset($_GET['search'])) {
    $search_query = $_GET['search'];
    $search_query = "%$search_query%";
}

// Fetch all users that are not already friends
$query = "SELECT * FROM users WHERE full_name LIKE ? AND id != ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("si", $search_query, $user_id);
$stmt->execute();
$users_result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat</title>
    <link rel="stylesheet" href="css/chat-style.css">
    <script defer src="script.js"></script>
    <style>
        /* Container for the friends list */
        #friendList {
            list-style-type: none;
            padding: 0;
            margin: 0;
        }

        /* Each friend item */
        .friend-item {
            display: flex;
            align-items: center;
            margin: 10px 0;
            padding: 10px;
            border-bottom: 1px solid #ddd;
            transition: background-color 0.3s;
        }

        .friend-item:hover {
            background-color: #f1f1f1;
        }

        /* Profile picture */
        .friend-profile-pic {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 15px;
        }

        /* Friend's name */
        .friend-name {
            font-size: 16px;
            font-weight: 500;
            color: #333;
            text-decoration: none;
            transition: color 0.3s;
        }

        .friend-name:hover {
            color: #007bff; /* Change color on hover */
        }

        /* Ensure the profile pictures and names align */
        .friend-item img {
            flex-shrink: 0; /* Prevents image from shrinking */
        }

        .friend-item a {
            flex-grow: 1; /* Makes the name take up the available space */
            overflow: hidden; /* Ensures long names don't overflow */
            text-overflow: ellipsis; /* Adds "..." if name is too long */
        }
    </style>
</head>
<body>

<!-- Notification Bar -->
<div class="notification-bar">
    <div class="notification" onclick="window.location.href='requests.php'">
        <i class="fas fa-bell"></i> <span id="friendRequestCount">0</span>
    </div>
    <div class="notification" onclick="window.location.href='messages.php'">
        <i class="fas fa-comments"></i> <span id="chatNotificationCount">0</span>
    </div>
</div>

<div class="chat-container">
    <!-- Friend List Section -->
    <div class="friend-list">
        <h3>Friends</h3>
        <ul id="friendList">
            <?php while ($friend = $friends_result->fetch_assoc()) { ?>
                <li class="friend-item">
                    <img src="uploads/profile_pictures/<?php echo isset($friend['profile_picture']) ? htmlspecialchars($friend['profile_picture']) : 'default.png'; ?>" alt="Profile Picture" class="friend-profile-pic">
                    <a href="chat.php?receiver_id=<?php echo $friend['id']; ?>" class="friend-name">
                        <?php echo htmlspecialchars($friend['full_name']); ?>
                    </a>
                </li>
            <?php } ?>
        </ul>

        
        <!-- Search Box -->
        <h4>Search Friends</h4>
        <form action="chat.php" method="get">
            <input type="text" name="search" placeholder="Search users..." value="<?php echo htmlspecialchars($search_query); ?>" required>
            <button type="submit">Search</button>
        </form>

        <!-- Display Search Results -->
        <h4>Search Results</h4>
        <ul>
            <?php while ($user = $users_result->fetch_assoc()) { ?>
                <li>
                    <!-- Fetch user's profile picture -->
                    <img src="<?php echo 'uploads/profile_pictures/' . htmlspecialchars($user['profile_picture']); ?>" alt="Profile Picture" class="friend-profile-pic">
                    <span><?php echo htmlspecialchars($user['full_name']); ?></span>
                    <button onclick="addFriend(<?php echo $user['id']; ?>)">Add Friend</button>
                </li>
            <?php } ?>
        </ul>
    </div>

    <!-- Chat Area Section -->
    <div class="chat-area">
        <div class="chat-header">
            <?php
            if (isset($_GET['receiver_id'])) {
                $receiver_id = $_GET['receiver_id'];
                // Fetch the friend's details from the users table
                $query = "SELECT full_name, profile_picture FROM users WHERE id = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("i", $receiver_id);
                $stmt->execute();
                $friend_result = $stmt->get_result();
                $friend = $friend_result->fetch_assoc();
            ?>
                <!-- Display friend's profile picture in the chat header -->
                <img src="uploads/profile_pictures/<?php echo htmlspecialchars($friend['profile_picture']); ?>" alt="Friend's Profile Picture" class="chat-profile-pic">
                <h3><?php echo htmlspecialchars($friend['full_name']); ?></h3>
            <?php } ?>
        </div>

        <div class="chat-messages" id="chatMessages">
            <!-- Messages will be dynamically loaded here -->
            <?php
            // Ensure $messages is initialized
            if (isset($messages) && is_array($messages)) {
                foreach ($messages as $message) {
                    if ($message['sender_id'] == $user_id) {
                        // Sender's message (left-aligned)
                        echo "<div class='message sender'>
                                <div class='message-content'>
                                    <p><strong>You:</strong> " . htmlspecialchars($message['message']) . "</p>
                                </div>
                                <span class='message-time'>" . htmlspecialchars($message['created_at']) . "</span>
                              </div>";
                    } else {
                        // Receiver's message (right-aligned)
                        echo "<div class='message receiver'>
                                <div class='message-content'>
                                    <p><strong>" . htmlspecialchars($message['full_name']) . ":</strong> " . htmlspecialchars($message['message']) . "</p>
                                </div>
                                <span class='message-time'>" . htmlspecialchars($message['created_at']) . "</span>
                              </div>";
                    }
                }
            } else {
                echo "<p>No messages found</p>";
            }
            ?>
        </div>

        <div class="chat-input">
            <form id="messageForm" onsubmit="sendMessage(event)">
                <textarea id="messageText" placeholder="Type a message..." required></textarea>
                <button type="submit">Send</button>
            </form>
        </div>
    </div>

</div>

<script>
// Add Friend Functionality
function addFriend(friendId) {
    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'add_friend.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function() {
        if (xhr.status === 200) {
            alert('Friend request sent!');
        }
    };
    xhr.send('friend_id=' + friendId);  // Correctly send the friend ID
}

// Function to fetch and display messages
function fetchMessages() {
    const receiver_id = <?php echo isset($_GET['receiver_id']) ? $_GET['receiver_id'] : 0; ?>;

    const xhr = new XMLHttpRequest();
    xhr.open('GET', 'fetch_messages.php?receiver_id=' + receiver_id, true);
    xhr.onload = function() {
        if (xhr.status === 200) {
            const messages = JSON.parse(xhr.responseText);
            let messagesHTML = '';

            messages.forEach(message => {
                if (message.sender_id == <?php echo $user_id; ?>) {
                    // Sender's message (left)
                    messagesHTML += `
                        <div class="message sender">
                            <div class="message-content">
                                <p><strong>You:</strong> ${message.message}</p>
                            </div>
                            <span class="message-time">${message.created_at}</span>
                        </div>
                    `;
                } else {
                    // Receiver's message (right)
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

            document.getElementById('chatMessages').innerHTML = messagesHTML;
        }
    };
    xhr.send();
}

// Fetch messages every 1 second
setInterval(fetchMessages, 1000);

// Send Message Function
function sendMessage(event) {
    event.preventDefault();
    const message = document.getElementById('messageText').value;
    const receiver_id = <?php echo isset($_GET['receiver_id']) ? $_GET['receiver_id'] : 0; ?>;

    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'send_message.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function() {
        if (xhr.status === 200) {
            document.getElementById('messageText').value = '';
            fetchMessages();
        }
    };
    xhr.send('message=' + message + '&receiver_id=' + receiver_id);
}

// Fetch Notification Counts
function fetchNotifications() {
    fetch('fetch_notifications.php')
        .then(response => response.json())
        .then(data => {
            document.getElementById('friendRequestCount').innerText = data.friend_requests;
            document.getElementById('chatNotificationCount').innerText = data.unread_chats;
        });
}

// Fetch notifications every 5 seconds
setInterval(fetchNotifications, 5000);
fetchNotifications();
</script>

</body>
</html>
