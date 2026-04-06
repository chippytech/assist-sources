<?php


// ------------- 1. Auth Check -------------
if (!isset($_SESSION['logged_in']) || !isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

require 'db.php'; // sets $pdo
$username = $_SESSION['username'];

$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 512;
if ($limit <= 0 || $limit > 5000) {
    $limit = 512;
}

try {
    $stmt = $pdo->prepare("
        SELECT id, model, user_query, ai_response, created_at, is_hidden
        FROM chat_history
        WHERE username = ?
        ORDER BY is_hidden DESC, id DESC
        LIMIT ?
    ");
    $stmt->bindValue(1, $username, PDO::PARAM_STR);
    $stmt->bindValue(2, $limit, PDO::PARAM_INT);
    $stmt->execute();
    $history = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error.");
}

// ------------- 2. Compute Statistics -------------
$count        = count($history);
$hiddenCount  = 0;
$visibleCount = 0;
$dates        = array_column($history, 'created_at');
sort($dates);
$firstChat    = $count > 0 ? $dates[0] : null;
$lastChat     = $count > 0 ? $dates[$count - 1] : null;

$modelCounts = [];
$combinedText = "";

foreach ($history as $row) {
    $model = $row['model'] ?: 'Unknown';
    if (!isset($modelCounts[$model])) $modelCounts[$model] = 0;
    $modelCounts[$model]++;
    
    if ((int)$row['is_hidden'] === 1) $hiddenCount++;
    else $visibleCount++;

    // Collect text for word cloud
    $combinedText .= " " . mb_strtolower($row['user_query']);

}
arsort($modelCounts);
$topModel = $count > 0 ? array_keys($modelCounts)[0] : null;

// --- Time Series Logic ---
$timeSeries = [];
$uniqueModels = array_keys($modelCounts);
foreach ($history as $row) {
    $date = date('Y-m-d', strtotime($row['created_at']));
    $model = $row['model'] ?: 'Unknown';
    if (!isset($timeSeries[$date])) {
        $timeSeries[$date] = array_fill_keys($uniqueModels, 0);
    }
    $timeSeries[$date][$model]++;
}
ksort($timeSeries); 

$chartDates = array_keys($timeSeries);
$datasets = [];
$colors = ['#36A2EB', '#FF6384', '#4BC0C0', '#FF9F40', '#9966FF', '#FFCE56'];
$colorIdx = 0;

foreach ($uniqueModels as $model) {
    $dataPoints = [];
    foreach ($chartDates as $date) { $dataPoints[] = $timeSeries[$date][$model]; }
    $datasets[] = [
        'label' => $model,
        'data' => $dataPoints,
        'borderColor' => $colors[$colorIdx % count($colors)],
        'backgroundColor' => $colors[$colorIdx % count($colors)] . '33',
        'fill' => true,
        'tension' => 0.3
    ];
    $colorIdx++;
}

// --- Word Cloud Logic ---
$words = preg_split('/\W+/u', $combinedText, -1, PREG_SPLIT_NO_EMPTY);
$stopWords = ['the', 'and', 'a', 'to', 'of', 'in', 'i', 'is', 'that', 'it', 'on', 'you', 'this', 'for', 'with', 'was', 'as', 'at', 'have', 'what', 'how'];
$wordCounts = array_count_values(array_filter($words, function($w) use ($stopWords) {
    return mb_strlen($w) > 3 && !in_array($w, $stopWords);
}));
arsort($wordCounts);
$wordCloudData = [];
foreach (array_slice($wordCounts, 0, 40) as $word => $cnt) {
    $wordCloudData[] = ['text' => $word, 'size' => $cnt];
}

function h($str) { return htmlspecialchars($str ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Statistics - Assist by ChippyTime</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/d3/7.8.5/d3.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/d3-cloud/1.2.7/d3.layout.cloud.min.js"></script>
    <style>
        .card { border: none; transition: transform 0.2s; }
        .card:hover { transform: translateY(-2px); }
        #word-cloud text { font-family: sans-serif; cursor: default; transition: opacity 0.2s; }
        #word-cloud text:hover { opacity: 0.7; }
    </style>
</head>
<body class="bg-light">
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>My Statistics <small class="text-muted fs-6">by ChippyTime</small></h2>
        <a href="/" class="btn btn-sm btn-outline-primary">Back to Chat</a>
    </div>
    
    <!-- Row 1: Key Metrics -->
    <div class="row mb-4">
        <div class="col-md-2 mb-3">
            <div class="card text-bg-primary shadow-sm h-100 p-3">
                <small>Total Chats</small>
                <div class="fs-2 fw-bold"><?= $count ?></div>
                <small class="opacity-75">Limit: <?= h($limit) ?></small>
            </div>
        </div>
        <div class="col-md-2 mb-3">
            <div class="card text-bg-success shadow-sm h-100 p-3">
                <small>Visible</small>
                <div class="fs-2 fw-bold"><?= $visibleCount ?></div>
            </div>
        </div>
        <div class="col-md-2 mb-3">
            <div class="card text-bg-secondary shadow-sm h-100 p-3">
                <small>Hidden</small>
                <div class="fs-2 fw-bold"><?= $hiddenCount ?></div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card bg-white shadow-sm h-100 p-3 border">
                <small class="text-muted">Activity Period</small>
                <div class="small fw-bold mt-1">Start: <?= $firstChat ? date('M j, Y', strtotime($firstChat)) : 'N/A' ?></div>
                <div class="small fw-bold text-info">End: <?= $lastChat ? date('M j, Y', strtotime($lastChat)) : 'N/A' ?></div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card bg-white shadow-sm h-100 p-3 border">
                <small class="text-muted">Favorite Model</small>
                <div class="fs-5 fw-bold text-truncate" title="<?= h($topModel) ?>"><?= h($topModel) ?: 'N/A' ?></div>
            </div>
        </div>
    </div>

    <!-- Row 2: Charts -->
    <div class="row mb-4">
        <div class="col-md-4 mb-4">
            <div class="card h-100 shadow-sm">
                <div class="card-header bg-white fw-bold">Model Mix</div>
                <div class="card-body">
                    <canvas id="modelChart" height="250"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-8 mb-4">
            <div class="card h-100 shadow-sm">
                <div class="card-header bg-white fw-bold">Usage Frequency</div>
                <div class="card-body">
                    <canvas id="lineChart" height="250"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Row 3: Word Cloud -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-white fw-bold">Topic Analysis (Keywords)</div>
        <div class="card-body text-center bg-white" style="min-height: 320px;">
            <div id="word-cloud"></div>
        </div>
    </div>

    <!-- Row 4: Table -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white fw-bold">Recent History Details</div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Date</th>
                            <th>Model</th>
                            <th>Query Summary</th>
                            <th class="text-end pe-4">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($history as $row): ?>
                        <tr class="<?= (int)$row['is_hidden'] === 1 ? 'table-secondary text-muted' : '' ?>">
                            <td class="ps-4 small"><?= date('y-m-d H:i', strtotime($row['created_at'])) ?></td>
                            <td><span class="badge border text-dark bg-white fw-normal"><?= h($row['model']) ?></span></td>
                            <td class="small"><?= h(mb_strimwidth($row['user_query'], 0, 100, "...")) ?></td>
                            <td class="text-end pe-4">
                                <?= (int)$row['is_hidden'] === 1 ? '<span class="badge bg-secondary">Hidden</span>' : '<span class="badge bg-success">Visible</span>' ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    // --- Chart JS Implementation ---
    const modelLabels = <?= json_encode(array_keys($modelCounts)) ?>;
    const modelData   = <?= json_encode(array_values($modelCounts)) ?>;
    const timeLabels  = <?= json_encode($chartDates) ?>;
    const timeDatasets = <?= json_encode($datasets) ?>;

    if(modelLabels.length > 0) {
        // Doughnut Chart
        new Chart(document.getElementById('modelChart'), {
            type: 'doughnut',
            data: {
                labels: modelLabels,
                datasets: [{ data: modelData, backgroundColor: ['#36A2EB', '#FF6384', '#4BC0C0', '#FF9F40', '#9966FF', '#FFCE56'] }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
        });

        // Line Chart
        new Chart(document.getElementById('lineChart'), {
            type: 'line',
            data: { labels: timeLabels, datasets: timeDatasets },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: { 
                    y: { beginAtZero: true, ticks: { stepSize: 1 } },
                    x: { grid: { display: false } }
                },
                plugins: { tooltip: { mode: 'index', intersect: false } }
            }
        });
    }

    // --- D3 Word Cloud Implementation ---
    const wcWords = <?= json_encode($wordCloudData) ?>;
    if (wcWords.length > 0) {
        const width = document.getElementById('word-cloud').offsetWidth || 800;
        const height = 300;
        const maxVal = Math.max(...wcWords.map(d => d.size));

        const layout = d3.layout.cloud()
            .size([width, height])
            .words(wcWords.map(d => ({ text: d.text, size: 12 + (d.size / maxVal) * 50 })))
            .padding(5)
            .rotate(() => (~~(Math.random() * 2) * 90))
            .fontSize(d => d.size)
            .on("end", (output) => {
                d3.select("#word-cloud").append("svg")
                    .attr("width", width).attr("height", height)
                    .append("g")
                    .attr("transform", `translate(${width/2},${height/2})`)
                    .selectAll("text").data(output).enter().append("text")
                    .style("font-size", d => d.size + "px")
                    .style("fill", () => d3.schemeTableau10[Math.floor(Math.random() * 10)])
                    .attr("text-anchor", "middle")
                    .attr("transform", d => `translate(${d.x},${d.y})rotate(${d.rotate})`)
                    .text(d => d.text);
            });
        layout.start();
    }
</script>
</body>
</html>