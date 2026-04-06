<?php
// ====================================================
//  SESSION & AUTH CHECK
// ====================================================
session_start();

header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');

// ---------------------------------------------------------------------
// Helper: send an SSE‑formatted JSON error and stop execution
// ---------------------------------------------------------------------
function sse_error(string $msg, int $httpCode = 200): void
{
    http_response_code($httpCode);
    echo "data: " . json_encode(['error' => $msg], JSON_UNESCAPED_SLASHES) . "\n\n";
    flush();
    exit;
}

// ---------------------------------------------------------------------
// 1️⃣  Authentication
// ---------------------------------------------------------------------
if (!isset($_SESSION['logged_in']) || !isset($_SESSION['username'])) {
    sse_error('Unauthorized access. Please login.', 401);
}

// ---------------------------------------------------------------------
// 2️⃣  DB connection (must expose $pdo)
// ---------------------------------------------------------------------
require 'db.php';           // $pdo = new PDO(...);
$username = $_SESSION['username'];
$SECRET   = 'ASSIST_HASH_9956';

// ---------------------------------------------------------------------
// 3️⃣  Limits / configuration
// ---------------------------------------------------------------------
$MAX_INPUT_BYTES          = 50000;
$MAX_MESSAGES             = 100;
$MAX_REQUESTS_PER_MIN_USER = 8;
$MAX_REQUESTS_PER_MIN_IP   = 15;
$MAX_DAILY_REQUESTS        = 200;   // per user

$ALLOWED_MODELS = [
    'gpt-4.1','gpt-5.2','gpt-4o','gpt-3.5-turbo','gpt-4-turbo',
    'gpt-5-mini','gpt-oss-120b','gpt-4o-mini','gpt-oss-20b', "o3-mini", "moonshotai/kimi-k2.5", "openai/gpt-oss-120b", "z-ai/glm-5", "deepseek/deepseek-v3.2", "meta-llama/llama-4-maverick"
];
$DEFAULT_MODEL = 'gpt-4.1';

$MODEL_MAX_TOKENS = [
    'o3-mini'       => 32768,

    'gpt-5.2'       => 32768,
    'gpt-4.1'       => 32768,
    'gpt-4o'        => 16384,
    'gpt-4o-mini'   => 8192,
    'gpt-4-turbo'   => 16384,
    'gpt-3.5-turbo' => 8192,
    'gpt-5-mini'    => 16384,
    'gpt-5-nano'    => 48096,

    'gpt-oss-120b'  => 8192,
    'gpt-oss-20b'   => 4096
];

// ---------------------------------------------------------------------
// 4️⃣  Error handling (log only)
// ---------------------------------------------------------------------
ini_set('display_errors', 0);
error_reporting(E_ALL);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// ---------------------------------------------------------------------
// 5️⃣  Disable output buffering – SSE needs raw flushes
// ---------------------------------------------------------------------
@ini_set('zlib.output_compression', 0);
@ini_set('output_buffering', 'off');
@ini_set('implicit_flush', 1);
while (ob_get_level()) { ob_end_flush(); }
ob_implicit_flush(true);

// ---------------------------------------------------------------------
// 6️⃣  Load OpenAI API key
// ---------------------------------------------------------------------
$keyPath = '/home/chippyti/assist.chippytime.com/cgi-bin/secret-api-tokens/openrouter.txt';
$OPENAI_API_KEY = file_exists($keyPath) ? trim(file_get_contents($keyPath)) : '';

if ($OPENAI_API_KEY === '') {
    sse_error('API Configuration Error', 500);
}

// ---------------------------------------------------------------------
// 7️⃣  Input validation
// ---------------------------------------------------------------------
$inputRaw = file_get_contents('php://input');

if (strlen($inputRaw) > $MAX_INPUT_BYTES) {
    sse_error('Input too large.', 413);
}
$inputJson = json_decode($inputRaw, true);
if (json_last_error() !== JSON_ERROR_NONE || !isset($inputJson['messages'])) {
    sse_error('Invalid input.', 400);
}
if (count($inputJson['messages']) > $MAX_MESSAGES) {
    sse_error('Conversation too long.', 400);
}

// ---------------------------------------------------------------------
// 8️⃣  Model validation
// ---------------------------------------------------------------------
$requestedModel = $inputJson['model'] ?? $DEFAULT_MODEL;
if (!in_array($requestedModel, $ALLOWED_MODELS, true)) {
    sse_error('Invalid model selection.', 400);
}
$model_used        = $requestedModel;
$maxTokensForModel = $MODEL_MAX_TOKENS[$model_used];

// ---------------------------------------------------------------------
// 9️⃣  Rate limiting (user, IP, daily)
// ---------------------------------------------------------------------
$ip = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['REMOTE_ADDR'] ?? 'unknown';

/* Per‑user (last minute) */
$stmt = $pdo->prepare("
    SELECT COUNT(*) FROM chat_history
    WHERE username = ?
      AND created_at > NOW() - INTERVAL 1 MINUTE
");
$stmt->execute([$username]);
if ((int)$stmt->fetchColumn() >= $MAX_REQUESTS_PER_MIN_USER) {
    sse_error('Rate limit exceeded. Please wait.', 429);
}

/* Per‑IP (last minute) */
$stmt = $pdo->prepare("
    SELECT COUNT(*) FROM chat_history
    WHERE ip = ?
      AND created_at > NOW() - INTERVAL 1 MINUTE
");
$stmt->execute([$ip]);
if ((int)$stmt->fetchColumn() >= $MAX_REQUESTS_PER_MIN_IP) {
    sse_error('IP rate limit exceeded.', 429);
}

/* Daily cap (per user) */
$stmt = $pdo->prepare("
    SELECT COUNT(*) FROM chat_history
    WHERE username = ?
      AND created_at > CURDATE()
");
$stmt->execute([$username]);
if ((int)$stmt->fetchColumn() >= $MAX_DAILY_REQUESTS) {
    sse_error('Daily limit reached.', 429);
}

// ---------------------------------------------------------------------
// 🔟  Build payload for OpenAI (stream = true)
// ---------------------------------------------------------------------

// Standardizes the input to lowercase before checking
$model_check = strtolower($model_used);
$model_used = strtolower($model_used);

// if ($model_check === "gpt-3.5-turbo" || $model_check === "gpt-4-turbo") {
//    die("Legacy models are no longer available in Assist.");
// }

// Detect provider
$is_prefixed_openai = str_starts_with($model_used, 'openai/');
$is_unprefixed_openai = !str_contains($model_used, '/'); // e.g. "gpt-4.1"

if ($is_prefixed_openai || $is_unprefixed_openai) {
    // Normalize to OpenRouter format
    if ($is_unprefixed_openai) {
        $model_used = 'openai/' . $model_used;
    }

    $maxTokensForModel = $MODEL_MAX_TOKENS[$requestedModel] ?? 16384;

} elseif (str_contains($model_used, '/')) {
    // Non-OpenAI models (mistral/*, meta-llama/*, etc.)
    $maxTokensForModel = 8192;

} else {
    sse_error('Invalid model format.', 400);
}

$apiPayload = [
    'model'                => $model_used,
    'messages'             => $inputJson['messages'],
    'max_completion_tokens'=> $maxTokensForModel,
    'user'                 => hash('sha256', $username . $SECRET),
    'stream'               => true
];
// Grab the last user message – this is what we store as the prompt
$lastMessage  = end($inputJson['messages']);
$user_message = $lastMessage['content'] ?? '';

// Variables to keep track of the stream
$full_ai_response = ''; // Final extracted text
$stream_buffer    = ''; // Buffer for handling incomplete packets

// ---------------------------------------------------------------------
// 1️⃣1️⃣  Keep script alive even if client disconnects
// ---------------------------------------------------------------------
ignore_user_abort(true);   // continue after browser closes

// ---------------------------------------------------------------------
// 1️⃣2️⃣  CURL request (streaming)
// ---------------------------------------------------------------------
$ch = curl_init('https://openrouter.ai/api/v1/chat/completions');
curl_setopt_array($ch, [
    CURLOPT_POST            => true,
    CURLOPT_POSTFIELDS      => json_encode($apiPayload),
    CURLOPT_HTTPHEADER      => [
        'Content-Type: application/json',
        "Authorization: Bearer {$OPENAI_API_KEY}",
        'HTTP-Referer: https://assist.chippytime.com',
        'X-OpenRouter-Title: Assist by ChippyTime'   // optional
    ],
    CURLOPT_RETURNTRANSFER  => false,
    CURLOPT_TIMEOUT         => 0,    // no total timeout
    CURLOPT_CONNECTTIMEOUT  => 10,
    CURLOPT_WRITEFUNCTION   => function ($curl, $chunk) use (
        &$full_ai_response,
        &$stream_buffer,
        $ip,
        $username,
        $model_used,
        $user_message
    ) {
        // 1. Forward raw chunk to client immediately so there's zero delay
        echo $chunk;
        flush();

        // 2. Append the chunk to our buffer
        $stream_buffer .= $chunk;

        // 3. Process complete lines ending with "\n"
        while (($pos = strpos($stream_buffer, "\n")) !== false) {
            $line = substr($stream_buffer, 0, $pos);
            $stream_buffer = substr($stream_buffer, $pos + 1);

            $line = trim($line);
            if (strpos($line, 'data: ') === 0) {
                $data = trim(substr($line, 6)); // strip "data: "

                if ($data !== '[DONE]') {
                    $json = json_decode($data, true);
                    if (json_last_error() === JSON_ERROR_NONE &&
                        isset($json['choices'][0]['delta']['content'])) {
                        $full_ai_response .= $json['choices'][0]['delta']['content'];
                    }
                }
            }
        }

        return strlen($chunk);
    }
]);

curl_exec($ch);

if (curl_errno($ch)) {
    error_log('cURL Error: ' . curl_error($ch));
    sse_error('Upstream service error.', 502);
}
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
if ($httpCode !== 200) {
    error_log("OpenAI API returned HTTP {$httpCode}");
    sse_error('Upstream service returned an error.', 502);
}
curl_close($ch);

// ---------------------------------------------------------------------
// 1️⃣3️⃣  DEBUG – log what we captured (helps you see if it’s non‑empty)
// ---------------------------------------------------------------------
error_log('OpenAI response captured: ' . strlen($full_ai_response) . ' bytes');

// ---------------------------------------------------------------------
// 1️⃣4️⃣  Persist the conversation (prompt + answer)
// ---------------------------------------------------------------------
if (!empty($user_message)) {
    $aiToStore = $full_ai_response !== '' ? $full_ai_response : '[no response]';

    try {
        // If FastCGI is available we can finish the HTTP response first,
        // then keep working in the background.
        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();   // sends everything that has been echoed so far
        }

        $stmt = $pdo->prepare("
            INSERT INTO chat_history
                (username, model, ip, user_query, ai_response, is_hidden, created_at)
            VALUES
                (:username, :model, :ip, :user_query, :ai_response, 0, NOW())
        ");
        $stmt->execute([
            ':username'    => $username,
            ':model'       => $model_used,
            ':ip'          => $ip,
            ':user_query'  => $user_message,
            ':ai_response' => $aiToStore
        ]);
    } catch (PDOException $e) {
        // ALWAYS log DB errors – they’ll appear in your PHP error log.
        error_log('DB Error saving chat: ' . $e->getMessage());
        // Do NOT break the SSE stream; the answer was already sent.
    }
}

// End of script – the client already has the whole SSE stream.
// No further output is needed.
?>