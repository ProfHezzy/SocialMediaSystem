<?php
session_start();
include('db_connect.php');

if (!isset($_SESSION['user_id'])) {
    exit('User not logged in');
}

$user_id = $_SESSION['user_id'];
$friend_id = isset($_POST['friend_id']) ? $_POST['friend_id'] : null;

if ($friend_id) {
    // Insert a new friend request with status 'pending'
    $query = "INSERT INTO friends (user_id, friend_id, status) VALUES (?, ?, 'pending')";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $user_id, $friend_id);
    $stmt->execute();
}
?>
