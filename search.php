<?php
session_start();
include('db_connect.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); // Redirect to login page if not logged in
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Users</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <header>
        <nav>
            <div class="logo">MySocial</div>
            <ul>
                <li><a href="chat.php">Chat</a></li>
                <li><a href="logout.php">Log Out</a></li>
            </ul>
        </nav>
    </header>

    <section class="search">
        <h2>Search Users</h2>
        <form method="GET" action="search.php">
            <input type="text" name="search" placeholder="Search for users..." required>
            <button type="submit">Search</button>
        </form>

        <div id="searchResults">
            <?php
            if (isset($_GET['search'])) {
                $search = $_GET['search'];
                // Search for users
                $query = "SELECT * FROM users WHERE name LIKE ? AND id != ?";
                $stmt = $conn->prepare($query);
                $search_term = "%{$search}%";
                $stmt->bind_param("si", $search_term, $_SESSION['user_id']);
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
        </div>
    </section>
</body>
</html>
