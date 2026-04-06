<?php
session_start();
header("Content-Type: application/json");

if (!isset($_SESSION['logged_in']) || !isset($_SESSION['username'])) {
    echo json_encode([
        "success" => false,
        "error" => "Unauthorized access."
    ]);
    exit;
}

require 'db.php';

$username = $_SESSION['username'];

try {
    $stmt = $pdo->prepare("
        UPDATE chat_history 
        SET is_hidden = 1 
        WHERE username = ?
    ");

    $stmt->execute([$username]);

    echo json_encode([
        "success" => true,
        "message" => "Chat history cleared."
    ]);

} catch (PDOException $e) {
    error_log("Clear chat error: " . $e->getMessage());

    echo json_encode([
        "success" => false,
        "error" => "Database error."
    ]);
}
?>
