<?php
session_start();  // Start the session

// Unset all session variables to log out the user
session_unset();

// Destroy the session
session_destroy();

// Redirect to the home page or login page
header('Location: index.php');  // Change this to your desired page
exit;
?>
