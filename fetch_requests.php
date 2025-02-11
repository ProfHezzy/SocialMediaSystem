<?php
session_start();
include('db_connect.php');

$user_id = $_SESSION['user_id'];

// Fetch pending friend requests
$query = "SELECT users.id, users.full_name FROM friends 
          JOIN users ON friends.user_id = users.id 
          WHERE friends.friend_id = ? AND friends.status = 'pending'";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$requests = [];
while ($row = $result->fetch_assoc()) {
    $requests[] = $row;
}

echo json_encode($requests);
?>
