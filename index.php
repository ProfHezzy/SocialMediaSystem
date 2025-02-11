<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Social Media App</title>
    <link rel="stylesheet" href="css/index.css">
    <script defer src="script.js"></script>
   
</head>
<body>
    <header>
        <nav>
            <div class="logo">MySocial</div>
            <ul>
                <li><a href="#">Home</a></li>
                <li><a href="login.php">Login</a></li>
                <li><a href="#">Sign Up</a></li>
            </ul>
        </nav>
    </header>
    
    <section class="hero">
        <h1>Welcome to MySocial</h1>
        <p>Connect with friends and the world around you.</p>
        <a href="#" class="btn">Get Started</a>
    </section>
    
    <section class="auth-forms">
        <div class="signup-form">
            <h2>Sign Up</h2>
            <form action="registration.php" method="POST" enctype="multipart/form-data">
                <input type="text" name="full_name" placeholder="Full Name" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <input type="tel" name="phoneumber" placeholder="Phone Number" required>
                <input type="file" name="profile_picture" required>
                <button type="submit" name="register">Sign Up</button>
            </form>
        </div>

    </section>
</body>
</html>
