<?php
// ====================================================
// Secure User Details API
// ====================================================

session_start();
require 'db.php'; // must define $pdo

header("Content-Type: application/json");

// ----------------------------------------------------
// Helper function
// ----------------------------------------------------
function respond(array $data, int $code = 200): void {
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_SLASHES);
    exit;
}

// ----------------------------------------------------
// 1️⃣ Require authentication
// ----------------------------------------------------
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    respond(['error' => 'Unauthorized'], 401);
}

if ($_SESSION['username'] !== "chippytech") {
    die("You must be the site owner to resolve");
}
// ----------------------------------------------------
// 2️⃣ Validate request method
// ----------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(['error' => 'Invalid request method'], 405);
}

// ----------------------------------------------------
// 3️⃣ Validate input
// ----------------------------------------------------
$username = $_GET['username']

if (strlen($username) < 3 || strlen($username) > 50) {
    respond(['error' => 'Invalid username'], 400);
}

// Optional: Only allow user to fetch their own info

// ----------------------------------------------------
// 4️⃣ Fetch user basic info
// ----------------------------------------------------
try {

    // Get email & created_at from users table
    $stmt = $pdo->prepare("
        SELECT username, email, created_at
        FROM users
        WHERE username = ?
        LIMIT 1
    ");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        respond(['error' => 'User not found'], 404);
    }

    // ------------------------------------------------
    // 5️⃣ Get last IP from chat_history
    // ------------------------------------------------
    $stmt = $pdo->prepare("
        SELECT ip
        FROM chat_history
        WHERE username = ?
        ORDER BY created_at DESC
        LIMIT 1
    ");
    $stmt->execute([$username]);
    $lastChat = $stmt->fetch(PDO::FETCH_ASSOC);

    $lastLoginIp = $lastChat['ip'] ?? null;

    // ------------------------------------------------
    // 6️⃣ Return safe data
    // ------------------------------------------------
    respond([
        'username'      => $user['username'],
        'email'         => $user['email'],
        'created_at'    => $user['created_at'],
        'last_login_ip' => $lastLoginIp
    ]);

} catch (PDOException $e) {
    error_log("User Details API Error: " . $e->getMessage());
    respond(['error' => 'Server error'], 500);
}