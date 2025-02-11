<?php
session_start();  // Start the session

// Check if the user is already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: profile.php');  // Redirect to profile page if already logged in
    exit;
}

$error = '';  // Error message initialization

// Include the database connection file
include('db_connect.php');

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if the email and password are set
    if (isset($_POST['email']) && isset($_POST['password'])) {
        // Retrieve form data
        $email = $_POST['email'];
        $password = $_POST['password'];

        // Prepare SQL query to fetch user data based on email
        $query = "SELECT * FROM users WHERE email = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $email);  // Bind the email to the query
        $stmt->execute();
        $result = $stmt->get_result();

        // Check if the user exists
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();  // Fetch user data

            // Verify the entered password with the stored hashed password
            if (password_verify($password, $user['password'])) {
                // Password is correct, store user information in session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['full_name'];

                // Redirect to the profile page
                header('Location: profile.php');
                exit;
            } else {
                $error = "Invalid password.";
            }
        } else {
            $error = "No user found with that email.";
        }
    } else {
        $error = "Please fill in both email and password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - MySocial</title>
    <link rel="stylesheet" href="css/index.css">
</head>
<body>
    <header>
        <nav>
            <div class="logo">MySocial</div>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="login.php">Login</a></li>
                <li><a href="signup.php">Sign Up</a></li>
            </ul>
        </nav>
    </header>

    <section class="auth-forms">
        <div class="login-form">
            <h2>Login</h2>

            <!-- Show error message if login fails -->
            <?php if ($error != ''): ?>
                <p style="color: red;"><?php echo $error; ?></p>
            <?php endif; ?>

            <form method="POST" action="login.php">
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit">Login</button>
            </form>
        </div>
    </section>
</body>
</html>
