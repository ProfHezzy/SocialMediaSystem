<?php
session_start();
include('db_connect.php');

// Debugging: Check if required fields are received
if (!isset($_SESSION['user_id'])) {
    die("Error: User not logged in.");
}

if (!isset($_POST['user_id']) || !isset($_POST['action'])) {
    die("Error: Missing parameters.");
}

$user_id = $_SESSION['user_id'];
$requester_id = $_POST['user_id'];
$action = $_POST['action'];

// Debugging: Print received data
error_log("Received request: user_id = $user_id, requester_id = $requester_id, action = $action");

if ($action === 'accept') {
    $query = "UPDATE friends SET status = 'accepted' WHERE user_id = ? AND friend_id = ?";
} elseif ($action === 'reject') {
    $query = "DELETE FROM friends WHERE user_id = ? AND friend_id = ?";
} else {
    die("Error: Invalid action.");
}

// Prepare SQL statement
$stmt = $conn->prepare($query);
if (!$stmt) {
    die("SQL Error: " . $conn->error);
}

$stmt->bind_param("ii", $requester_id, $user_id);
$stmt->execute();

// Debugging: Check if the query was successful
if ($stmt->affected_rows > 0) {
    echo "Friend request " . ($action === 'accept' ? "accepted" : "rejected");
} else {
    echo "Error: No rows affected.";
}
?>
