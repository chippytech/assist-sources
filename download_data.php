<?php
session_start();

// ---------------- AUTH ----------------
if (!isset($_SESSION['logged_in']) || !isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

require 'db.php';

$username = $_SESSION['username'];
$format   = isset($_GET['format']) ? strtolower($_GET['format']) : 'json';

if (!in_array($format, ['json', 'csv'])) {
    $format = 'json';
}

// ---------------- FETCH CHAT DATA ----------------
$stmt = $pdo->prepare("
    SELECT id, model, user_query, ai_response, created_at, is_hidden, ip
    FROM chat_history
    WHERE username = ?
    ORDER BY id DESC
");
$stmt->execute([$username]);
$chatData = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ---------------- FETCH IMAGE DATA ----------------
$stmt = $pdo->prepare("
    SELECT id, model, prompt, created_at, ip
    FROM image_history
    WHERE username = ?
    ORDER BY id DESC
");
$stmt->execute([$username]);
$imageData = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ---------------- FILE NAME ----------------
$timestamp = date('Y-m-d_H-i-s');
$fileBase  = "assist_export_{$username}_{$timestamp}";

// =================================================
// JSON EXPORT
// =================================================
if ($format === 'json') {

    $export = [
        "exported_at" => date('c'),
        "username"    => $username,
        "chat_history_count" => count($chatData),
        "image_history_count" => count($imageData),

        "chat_history" => array_map(function ($row) {
            return [
                "id" => (int)$row['id'],
                "model" => $row['model'],
                "user_query" => $row['user_query'],
                "ai_response" => $row['ai_response'],
                "created_at" => $row['created_at'],
                "is_hidden" => (int)$row['is_hidden'],
                "ip" => $row['ip'] // ✅ added
            ];
        }, $chatData),

        "image_history" => array_map(function ($row) {
            return [
                "id" => (int)$row['id'],
                "model" => $row['model'],
                "prompt" => $row['prompt'],
                "created_at" => $row['created_at'],
                "ip" => $row['ip']
            ];
        }, $imageData)
    ];

    header("Content-Type: application/json");
    header("Content-Disposition: attachment; filename={$fileBase}.json");

    echo json_encode($export, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

// =================================================
// CSV EXPORT (ZIP)
// =================================================
$zip = new ZipArchive();
$tmpZip = tempnam(sys_get_temp_dir(), 'assist_') . '.zip';

if ($zip->open($tmpZip, ZipArchive::CREATE) !== TRUE) {
    die("Could not create ZIP file");
}

// ---------- CHAT CSV ----------
$chatCsv = fopen('php://temp', 'r+');

fputcsv($chatCsv, [
    'id',
    'model',
    'user_query',
    'ai_response',
    'created_at',
    'is_hidden',
    'ip' // ✅ added
]);

foreach ($chatData as $row) {
    fputcsv($chatCsv, [
        $row['id'],
        $row['model'],
        $row['user_query'],
        $row['ai_response'],
        $row['created_at'],
        $row['is_hidden'],
        $row['ip']
    ]);
}

rewind($chatCsv);
$zip->addFromString('chat_history.csv', stream_get_contents($chatCsv));
fclose($chatCsv);

// ---------- IMAGE CSV ----------
$imageCsv = fopen('php://temp', 'r+');

fputcsv($imageCsv, [
    'id',
    'model',
    'prompt',
    'created_at',
    'ip'
]);

foreach ($imageData as $row) {
    fputcsv($imageCsv, $row);
}

rewind($imageCsv);
$zip->addFromString('image_history.csv', stream_get_contents($imageCsv));
fclose($imageCsv);

$zip->close();

// Download ZIP
header("Content-Type: application/zip");
header("Content-Disposition: attachment; filename={$fileBase}.zip");
readfile($tmpZip);
unlink($tmpZip);

exit;