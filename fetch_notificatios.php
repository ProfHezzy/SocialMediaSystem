<?php
session_start();
include('db_connect.php');

$user_id = $_SESSION['user_id'];

// Count pending friend requests
$query = "SELECT COUNT(*) AS count FROM friends WHERE friend_id = ? AND status = 'pending'";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$friend_requests = $result->fetch_assoc()['count'];

// Count unread messages
$query = "SELECT COUNT(*) AS count FROM messages WHERE receiver_id = ? AND is_read = 0";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$unread_chats = $result->fetch_assoc()['count'];

echo json_encode([
    "friend_requests" => $friend_requests,
    "unread_chats" => $unread_chats
]);
?>
