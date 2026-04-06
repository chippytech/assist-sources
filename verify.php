<?php
require 'db.php';

$token = $_GET['token'] ?? '';

if (empty($token)) {
    die("Invalid verification link.");
}

$stmt = $pdo->prepare("
    SELECT id, token_expires 
    FROM users 
    WHERE verification_token = ?
");
$stmt->execute([$token]);
$user = $stmt->fetch();

if (!$user) {
    die("Invalid or already used token.");
}

if (strtotime($user['token_expires']) < time()) {
    die("Verification link expired.");
}

// Mark as verified
$stmt = $pdo->prepare("
    UPDATE users
    SET is_verified = 1,
        verification_token = NULL,
        token_expires = NULL
    WHERE id = ?
");
$stmt->execute([$user['id']]);

echo "Email verified successfully. You may now log in.";
?>