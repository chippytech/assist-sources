<?php
session_start();

if (!isset($_SESSION['logged_in']) || !isset($_SESSION['username'])) {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized access."]);
    exit;
}

require 'db.php';
$username = $_SESSION['username'];

header("Content-Type: application/json");

// =======================
// CONFIG
// =======================
$ALLOWED_MODEL = "black-forest-labs/flux.2-max";
$MAX_PROMPT_LENGTH = 500;
$COOLDOWN_SECONDS = 20;
$MAX_PER_MINUTE = 2;
$MAX_PER_HOUR = 10;
$MAX_PER_DAY = 25;
$GLOBAL_DAILY_LIMIT = 200; // circuit breaker

// =======================
// LOAD API KEY
// =======================
$keyPath = "/home/chippyti/assist.chippytime.com/cgi-bin/secret-api-tokens/openrouter.txt";
$OPENROUTER_API_KEY = file_exists($keyPath) ? trim(file_get_contents($keyPath)) : '';

if (empty($OPENROUTER_API_KEY)) {
    echo json_encode(["error" => "API key missing"]);
    exit;
}

// =======================
// INPUT VALIDATION
// =======================
$raw = file_get_contents("php://input");
$data = json_decode($raw, true);

if (!$data || empty($data["prompt"])) {
    echo json_encode(["error" => "Missing prompt"]);
    exit;
}

$prompt = trim($data["prompt"]);

if (strlen($prompt) > $MAX_PROMPT_LENGTH) {
    echo json_encode(["error" => "Prompt too long"]);
    exit;
}

$model = $ALLOWED_MODEL; // hard locked

$ip = $_SERVER["HTTP_CF_CONNECTING_IP"] ?? $_SERVER["REMOTE_ADDR"] ?? "unknown";

// =======================
// RATE LIMITS
// =======================

// Cooldown
$stmt = $pdo->prepare("
    SELECT created_at 
    FROM image_history 
    WHERE username = ?
    ORDER BY created_at DESC 
    LIMIT 1
");
$stmt->execute([$username]);
$last = $stmt->fetchColumn();

if ($last && (time() - strtotime($last)) < $COOLDOWN_SECONDS) {
    echo json_encode(["error" => "Please wait before generating another image."]);
    exit;
}

// Per minute
$stmt = $pdo->prepare("
    SELECT COUNT(*) FROM image_history
    WHERE username = ?
    AND created_at > NOW() - INTERVAL 1 MINUTE
");
$stmt->execute([$username]);
if ($stmt->fetchColumn() >= $MAX_PER_MINUTE) {
    echo json_encode(["error" => "Too many requests."]);
    exit;
}

// Per hour
$stmt = $pdo->prepare("
    SELECT COUNT(*) FROM image_history
    WHERE username = ?
    AND created_at > NOW() - INTERVAL 1 HOUR
");
$stmt->execute([$username]);
if ($stmt->fetchColumn() >= $MAX_PER_HOUR) {
    echo json_encode(["error" => "Hourly limit reached."]);
    exit;
}

// Per day
$stmt = $pdo->prepare("
    SELECT COUNT(*) FROM image_history
    WHERE username = ?
    AND created_at > CURDATE()
");
$stmt->execute([$username]);
if ($stmt->fetchColumn() >= $MAX_PER_DAY) {
    echo json_encode(["error" => "Daily image limit reached."]);
    exit;
}

// Global circuit breaker
$stmt = $pdo->query("
    SELECT COUNT(*) FROM image_history
    WHERE created_at > CURDATE()
");
if ($stmt->fetchColumn() >= $GLOBAL_DAILY_LIMIT) {
    echo json_encode(["error" => "Image service temporarily unavailable."]);
    exit;
}

// Duplicate detection (last 3)
$stmt = $pdo->prepare("
    SELECT prompt 
    FROM image_history
    WHERE username = ?
    ORDER BY created_at DESC
    LIMIT 3
");
$stmt->execute([$username]);
$recent = $stmt->fetchAll(PDO::FETCH_COLUMN);

if (in_array($prompt, $recent)) {
    echo json_encode(["error" => "Duplicate request detected."]);
    exit;
}

// =======================
// BUILD PAYLOAD
// =======================
$payload = [
    "model" => $model,
    "messages" => [
        [
            "role" => "user",
            "content" => $prompt
        ]
    ]
];

// =======================
// CALL OPENROUTER
// =======================
$ch = curl_init("https://openrouter.ai/api/v1/chat/completions");

curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 60,
    CURLOPT_HTTPHEADER => [
        "Content-Type: application/json",
        "Authorization: Bearer " . $OPENROUTER_API_KEY,
        "HTTP-Referer: https://assist.chippytime.com",
        "X-Title: Assist by ChippyTime"
    ]
]);

$response = curl_exec($ch);

if (curl_errno($ch)) {
    echo json_encode(["error" => "Image generation failed"]);
    curl_close($ch);
    exit;
}

curl_close($ch);

// =======================
// SAVE REQUEST
// =======================
try {
    $stmt = $pdo->prepare("
        INSERT INTO image_history (username, ip, model, prompt)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([$username, $ip, $model, $prompt]);
} catch (PDOException $e) {
    error_log("Image DB error: " . $e->getMessage());
}

// =======================
// RETURN RESPONSE
// =======================
echo $response;