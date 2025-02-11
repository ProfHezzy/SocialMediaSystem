<?php
session_start(); // Start the session

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); // Redirect to login page if not logged in
    exit;
}

// Include database connection
include('db_connect.php');

// Get the user ID from session
$user_id = $_SESSION['user_id'];

// Fetch user data from the database
$query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id); // Bind the user_id parameter
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// If user doesn't exist, redirect to login page
if (!$user) {
    header('Location: login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f7fa;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .profile-container {
            background-color: #fff;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            width: 300px;
            text-align: center;
        }
        .profile-container h2 {
            margin-bottom: 20px;
        }
        .profile-container p {
            margin: 10px 0;
        }
        .btn {
            width: 100%;
            padding: 10px;
            background-color: #4CAF50;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 20px;
        }
        .btn:hover {
            background-color: #45a049;
        }
        .profile-info {
            list-style: none;
            padding: 0;
        }
        .profile-info li {
            margin: 8px 0;
        }
    </style>
</head>
<body>

<div class="profile-container">
    <h2>Welcome, <?php echo htmlspecialchars($user['full_name']); ?></h2>

    <ul class="profile-info">
        <li><strong>User Name:</strong> <?php echo htmlspecialchars($user['full_name']); ?></li>
        <li><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></li>
    </ul>

    <!-- Log Out Button -->
    <a href="logout.php"><button class="btn">Log Out</button></a>
    <a href="chat.php"><button class="btn">Go to Chat</button></a>

</div>

</body>
</html>
