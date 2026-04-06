<?php
// --------- SESSION & AUTH CHECK ----------
session_start();
header("Content-Type: application/json");

if (!isset($_SESSION['logged_in']) || !isset($_SESSION['username'])) {
    echo json_encode([
        "success" => false,
        "error" => "Unauthorized access. Please login."
    ]);
    exit;
}

require 'db.php'; // Must contain $pdo
$username = $_SESSION['username'];

// --------- OPTIONAL LIMIT ----------
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
if ($limit <= 0 || $limit > 500) {
    $limit = 50; // Safety cap
}

try {
    $stmt = $pdo->prepare("
        SELECT id, model, user_query, ai_response, created_at
        FROM chat_history_visible
        WHERE username = ?
        ORDER BY id DESC
        LIMIT ?
    ");

    $stmt->bindValue(1, $username, PDO::PARAM_STR);
    $stmt->bindValue(2, $limit, PDO::PARAM_INT);
    $stmt->execute();

    $history = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "username" => $username,
        "count" => count($history),
        "history" => $history
    ], JSON_PRETTY_PRINT);

} catch (PDOException $e) {
    error_log("DB Error fetching chat history: " . $e->getMessage());

    echo json_encode([
        "success" => false,
        "error" => "Database error."
    ]);
}
?>