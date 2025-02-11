<?php
session_start();
include('db_connect.php');

if (!isset($_SESSION['user_id'])) {
    exit('User not logged in');
}

$user_id = $_SESSION['user_id'];

if (isset($_GET['search'])) {
    $search = $_GET['search'];
    // Search for users who are not already friends
    $query = "SELECT * FROM users WHERE name LIKE ? AND id != ?";
    $stmt = $conn->prepare($query);
    $search_term = "%{$search}%";
    $stmt->bind_param("si", $search_term, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($user = $result->fetch_assoc()) {
        echo "<div class='user-result'>";
        echo "<p>" . htmlspecialchars($user['name']) . "</p>";
        echo "<button onclick='addFriend(" . $user['id'] . ")'>Add Friend</button>";
        echo "</div>";
    }
}
?>
