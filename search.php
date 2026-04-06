<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

if (!isset($_SESSION['logged_in']) || !isset($_SESSION['username'])) {
    header("Location: login");
    exit;
}

require 'db.php'; 
$username = $_SESSION['username'];

$q           = isset($_GET['q']) ? trim($_GET['q']) : '';
$modelFilter = isset($_GET['model']) ? trim($_GET['model']) : '';
$hideHidden  = isset($_GET['no_hidden']) ? true : false;

$results = [];

// Only run if there is a search term or a model filter
if ($q !== '' || $modelFilter !== '') {
    try {
        // 1. Start the query
        $sql = "SELECT id, model, user_query, ai_response, created_at, is_hidden 
                FROM chat_history 
                WHERE username = ?";
        
        // 2. Start the parameters array with the username
        $params = [$username];

        // 3. Add keyword search if provided
        if ($q !== '') {
            $sql .= " AND (user_query LIKE ? OR ai_response LIKE ?)";
            $params[] = "%$q%"; // First '?' in the OR
            $params[] = "%$q%"; // Second '?' in the OR
        }

        // 4. Add model filter if provided
        if ($modelFilter !== '') {
            $sql .= " AND model = ?";
            $params[] = $modelFilter;
        }

        // 5. Add hidden filter
        if ($hideHidden) {
            $sql .= " AND is_hidden = 0";
        }

        $sql .= " ORDER BY created_at DESC LIMIT 100";
        
        // 6. Execute with positional parameters
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        $error = "Search failed: " . $e->getMessage();
    }
}

// Fetch models for the dropdown
try {
    $modelStmt = $pdo->prepare("SELECT DISTINCT model FROM chat_history WHERE username = ? AND model IS NOT NULL AND model != ''");
    $modelStmt->execute([$username]);
    $availableModels = $modelStmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    $availableModels = [];
}

function h($str) { return htmlspecialchars($str ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Search History - Assist</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .result-card { transition: all 0.2s; border-left: 4px solid transparent; }
        .result-card:hover { border-left-color: #0d6efd; background-color: #f8f9fa; }
        mark { background: #ffeb3b; padding: 0; color: black; }
    </style>
</head>
<body class="bg-light">
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Search History</h2>
        <div>
            <a href="stats" class="btn btn-sm btn-outline-secondary">View Stats</a>
            <a href="index" class="btn btn-sm btn-primary">Back to Chat</a>
        </div>
    </div>

    <!-- Search Form -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <!-- Fixed action to search -->
            <form method="GET" action="search" class="row g-3">
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Keywords</label>
                    <input type="text" name="q" class="form-control" placeholder="Search user questions or AI answers..." value="<?= h($q) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Filter by Model</label>
                    <select name="model" class="form-select">
                        <option value="">All Models</option>
                        <?php foreach($availableModels as $m): ?>
                            <option value="<?= h($m) ?>" <?= $modelFilter === $m ? 'selected' : '' ?>><?= h($m) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="no_hidden" id="no_hidden" <?= $hideHidden ? 'checked' : '' ?>>
                        <label class="form-check-label" for="no_hidden">Exclude Hidden</label>
                    </div>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary px-4">Search History</button>
                    <?php if($q !== '' || $modelFilter !== ''): ?>
                        <a href="search" class="btn btn-link">Clear Search</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Results Section -->
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= h($error) ?></div>
    <?php elseif ($q !== '' || $modelFilter !== ''): ?>
        <p class="text-muted mb-3">Found <?= count($results) ?> results</p>
        
        <?php foreach ($results as $row): ?>
            <div class="card mb-3 shadow-sm result-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="badge bg-light text-dark border"><?= h($row['model']) ?></span>
                        <small class="text-muted"><?= date('M j, Y H:i', strtotime($row['created_at'])) ?></small>
                    </div>
                    
                    <div class="mb-2">
                        <strong>Q:</strong> 
                        <span class="text-secondary">
                        <?php 
                            $text = h($row['user_query']);
                            echo ($q !== '') ? str_ireplace($q, "<mark>$q</mark>", $text) : $text;
                        ?>
                        </span>
                    </div>
                    
                    <div class="collapse" id="resp-<?= $row['id'] ?>">
                        <hr>
                        <strong>A:</strong> 
                        <div class="small text-dark" style="white-space: pre-wrap;">
                            <?php 
                                $resp = h($row['ai_response']);
                                echo ($q !== '') ? str_ireplace($q, "<mark>$q</mark>", $resp) : $resp;
                            ?>
                        </div>
                    </div>
                    
                    <div class="mt-2">
                        <button class="btn btn-sm btn-link p-0 text-decoration-none" type="button" 
                                data-bs-toggle="collapse" data-bs-target="#resp-<?= $row['id'] ?>">
                            View Full Answer
                        </button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <?php if (count($results) === 0): ?>
            <div class="alert alert-warning">No records found matching your criteria for user <strong><?= h($username) ?></strong>.</div>
        <?php endif; ?>
    <?php else: ?>
        <div class="text-center py-5 text-muted border bg-white rounded shadow-sm">
            <p class="mb-0">Enter keywords or select a model to search your chat history.</p>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>