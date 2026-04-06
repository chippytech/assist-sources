<?php
session_start();
require 'db.php';
    
if (!isset($_POST['auth_token']) || !isset($_SESSION['auth_token'])) {
    die("Auth token missing.");
}

$session_token = $_SESSION['auth_token'];
$post_token = $_POST['auth_token'];

// Validate token securely
if (!hash_equals($session_token, $post_token)) {
    die("Invalid auth token.");
}


// Optional: regenerate token after validation
unset($_SESSION['auth_token']);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ==============================
    // VERIFY CLOUDFLARE TURNSTILE
    // ==============================

    $turnstileSecret = "0x4AAAAAACgGUFSEs2CXD3nGFr1BPM3SmPg";
    $turnstileResponse = $_POST['cf-turnstile-response'] ?? '';

    if (!$turnstileResponse) {
        header("Location: /auth?error=" . urlencode("Captcha verification failed."));
        exit;
    }

    $ch = curl_init("https://challenges.cloudflare.com/turnstile/v0/siteverify");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query([
            'secret' => $turnstileSecret,
            'response' => $turnstileResponse,
            'remoteip' => $_SERVER['REMOTE_ADDR']
        ])
    ]);

    $verifyResponse = curl_exec($ch);
    curl_close($ch);

    $captchaResult = json_decode($verifyResponse, true);

    if (!$captchaResult || empty($captchaResult['success'])) {
        header("Location: /auth?error=" . urlencode("Captcha verification failed."));
        exit;
    }

    // ==============================
    // LOGIN LOGIC
    // ==============================

    $username = trim($_POST['username']);
    $password = $_POST['password'];
if ($username == "lee") {
    die("Sorry ifastnet user are not allow to login.");
};
    $stmt = $pdo->prepare("SELECT id, email, password_hash, is_verified FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {

        // If NOT verified → resend verification email
        if ((int)$user['is_verified'] !== 1) {

            $token = bin2hex(random_bytes(32));
            $expires = date("Y-m-d H:i:s", strtotime("+1 hour"));

            $update = $pdo->prepare("UPDATE users SET verification_token = ?, token_expires = ? WHERE id = ?");
            $update->execute([$token, $expires, $user['id']]);

            $verifyLink = "https://assist.chippytime.com/verify.php?token=" . $token;

            $headers = "From: Assist by ChippyTime <no-reply@assist.chippytime.com>\r\n";
            $headers .= "Reply-To: no-reply@assist.chippytime.com\r\n";
            $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

            $message = "Hello,\n\nPlease verify your email by clicking the link below:\n\n$verifyLink\n\nThis link expires in 1 hour.";

            mail($user['email'], "Verify Your Assist Account", $message, $headers);

            header("Location: /auth?success=" . urlencode("Verification email resent. Please check your inbox."));
            exit;
        }

        // Verified → allow login
        session_regenerate_id(true);

        $_SESSION['username'] = $username;
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['logged_in'] = true;

        header("Location: /");
        exit;

    } else {
        header("Location: /auth?error=" . urlencode("Invalid username or password."));
        exit;
    }
}
?>