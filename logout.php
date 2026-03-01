<?php
// logout.php

// 1. Start session (must come before ANY output)
session_start();

// 2. Clear all session data
$_SESSION = [];

// 3. Delete the session cookie if it exists
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,                  // expire in the past
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// 4. Completely destroy the session
session_destroy();

// 5. Redirect to home page
// Choose ONE of the following lines depending on where logout.php is located

// If logout.php is in the same folder as index.php (root folder)
header("Location: index.php");

// If logout.php is inside /pages/ folder
// header("Location: ../index.php");

// If you want to be very explicit (good for localhost)
// header("Location: /grokcamping/index.php");

// 6. Stop script execution immediately after redirect
exit();
?>