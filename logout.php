<?php
// 1. Initialize the session
session_start();

// 2. Unset all of the session variables
$_SESSION = array();

// 3. If it's desired to kill the session, also delete the session cookie.
// This is crucial for security!
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 4. Finally, destroy the session on the server
session_destroy();

// 5. Redirect to the auth page with a success message
header("Location: /auth?success=" . urlencode("Logged out successfully."));

?>