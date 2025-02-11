<?php
session_start();
include('db_connect.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch pending friend requests
$query = "SELECT f.user_id, u.full_name 
          FROM friends f 
          INNER JOIN users u ON f.user_id = u.id 
          WHERE f.friend_id = ? AND f.status = 'pending'";

$stmt = $conn->prepare($query);

if (!$stmt) {
    die("SQL Error: " . $conn->error);
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$requests_result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Friend Requests</title>
    <link rel="stylesheet" href="css/chat-style.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f0f2f5;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .request-container {
            background: #ffffff;
            padding: 20px;
            width: 400px;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        }

        h3 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }

        .request-list {
            list-style-type: none;
            padding: 0;
        }

        .request-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #f9f9f9;
            padding: 10px 15px;
            border-radius: 8px;
            margin-bottom: 10px;
            transition: 0.3s;
        }

        .request-item:hover {
            background: #ececec;
        }

        .request-name {
            font-size: 16px;
            font-weight: 600;
            color: #333;
        }

        .button-group {
            display: flex;
            gap: 8px;
        }

        button {
            border: none;
            padding: 8px 12px;
            border-radius: 5px;
            font-size: 14px;
            cursor: pointer;
            transition: 0.3s;
        }

        .accept-btn {
            background-color: #4CAF50;
            color: white;
        }

        .reject-btn {
            background-color: #f44336;
            color: white;
        }

        button:hover {
            opacity: 0.8;
        }

        .no-requests {
            text-align: center;
            font-size: 14px;
            color: #666;
        }
    </style>
</head>
<body>

<div class="request-container">
    <h3>Friend Requests</h3>
    
    <ul class="request-list">
        <?php while ($request = $requests_result->fetch_assoc()) { ?>
            <li class="request-item">
                <span class="request-name"><?php echo htmlspecialchars($request['full_name']); ?></span>
                <div class="button-group">
                    <button class="accept-btn" onclick="handleRequest(<?php echo $request['user_id']; ?>, 'accept')">Accept</button>
                    <button class="reject-btn" onclick="handleRequest(<?php echo $request['user_id']; ?>, 'reject')">Reject</button>
                </div>
            </li>
        <?php } ?>

        <?php if ($requests_result->num_rows === 0) { ?>
            <p class="no-requests">No pending friend requests.</p>
        <?php } ?>
    </ul>
</div>

<script>
function handleRequest(userId, action) {
    console.log("Sending request:", userId, action);

    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'handle_request.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    
    xhr.onload = function() {
        console.log("Response received:", xhr.responseText);
        if (xhr.status === 200) {
            try {
                let response = JSON.parse(xhr.responseText);
                alert(response.message);
                if (response.success) location.reload();
            } catch (e) {
                console.error("Invalid JSON response:", xhr.responseText);
                alert("Unexpected server response.");
            }
        }
    };

    const requestData = `user_id=${encodeURIComponent(userId)}&action=${encodeURIComponent(action)}`;
    console.log("Request Data:", requestData);

    xhr.send(requestData);
}
</script>

</body>
</html>
