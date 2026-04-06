<?php
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ==============================
    // RATE LIMITING (IP BASED)
    // ==============================

    $ip = $_SERVER['REMOTE_ADDR'];
    $limit = 5;
    $windowSeconds = 600; // 10 minutes

    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM signup_rate_limits
        WHERE ip_address = ?
        AND attempt_time > (NOW() - INTERVAL ? SECOND)
    ");
    $stmt->execute([$ip, $windowSeconds]);
    $attempts = $stmt->fetchColumn();

    if ($attempts >= $limit) {
        header("Location: index.php?page=auth&error=" . urlencode("Too many signup attempts. Try again later."));
        exit;
    }

    // Log this attempt
    $stmt = $pdo->prepare("
        INSERT INTO signup_rate_limits (ip_address, attempt_time)
        VALUES (?, NOW())
    ");
    $stmt->execute([$ip]);
    
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

    // ==============================
    // VERIFY CLOUDFLARE TURNSTILE
    // ==============================

    $turnstileSecret = "0x4AAAAAACgGUFSEs2CXD3nGFr1BPM3SmPg";
    $turnstileResponse = $_POST['cf-turnstile-response'] ?? '';

    if (!$turnstileResponse) {
        header("Location: index.php?page=auth&error=" . urlencode("Captcha verification failed."));
        exit;
    }

    $ch = curl_init("https://challenges.cloudflare.com/turnstile/v0/siteverify");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query([
            'secret' => $turnstileSecret,
            'response' => $turnstileResponse,
            'remoteip' => $ip
        ])
    ]);

    $verifyResponse = curl_exec($ch);
    curl_close($ch);

    $captchaResult = json_decode($verifyResponse, true);

    if (!$captchaResult || empty($captchaResult['success'])) {
        header("Location: index.php?page=auth&error=" . urlencode("Captcha verification failed."));
        exit;
    }

    // ==============================
    // VALIDATE INPUT
    // ==============================

    $username = trim($_POST['username']);
    $email = strtolower(trim($_POST['email']));
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    if (
        empty($username) ||
        strlen($username) < 3 ||
        !filter_var($email, FILTER_VALIDATE_EMAIL) ||
        strlen($password) < 8
    ) {
        header("Location: index.php?page=auth&error=" . urlencode("Invalid input."));
        exit;
    }

    $allowedDomains = [
        "gmail.com",
        "outlook.com",
        "hotmail.com",
        "yahoo.com"
    ];

    $domain = explode("@", $email)[1] ?? '';

    if (!in_array($domain, $allowedDomains)) {
        header("Location: index.php?page=auth&error=" . urlencode("Only Gmail/Outlook/Yahoo allowed."));
        exit;
    }

    // ==============================
    // CREATE ACCOUNT
    // ==============================

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $token = bin2hex(random_bytes(32));
    $expires = date("Y-m-d H:i:s", time() + 3600);

    try {

        $stmt = $pdo->prepare("
            INSERT INTO users (username, email, password_hash, verification_token, token_expires)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$username, $email, $hash, $token, $expires]);

        $verifyLink = "https://assist.chippytime.com/verify.php?token=" . $token;

        $subject = "Verify your Assist by ChippyTime account";
        $message = "Hi $username,

Please verify your account by clicking the link below:

$verifyLink

This link expires in 1 hour.

If you did not register, ignore this email.";

        $headers = "From: no-reply@assist.chippytime.com\r\n";
        $headers .= "Reply-To: no-reply@assist.chippytime.com\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

        mail($email, $subject, $message, $headers);

        header("Location: index.php?page=auth&success=" . urlencode("Check your email to verify."));
        exit;

    } catch (PDOException $e) {

        if ($e->getCode() == 23000) {
            header("Location: index.php?page=auth&error=" . urlencode("Username or email exists."));
            exit;
        }

        error_log($e->getMessage());
        header("Location: index.php?page=auth&error=" . urlencode("System error."));
        exit;
    }
}
?>