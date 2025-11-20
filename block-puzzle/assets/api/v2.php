<?php
require __DIR__ . '/../../db.php';

$name = $_GET['name'] ?? '';
if ($name === '') {
    http_response_code(400);
    echo 'Missing name';
    exit;
}

// Check điểm bình thường, KHÔNG hidden param
$stmt = $pdo->prepare('SELECT MAX(score) FROM scores WHERE name = ?');
$stmt->execute([$name]);
$bestScore = (int)$stmt->fetchColumn();

if ($bestScore < 30000) {
    http_response_code(403);
    echo 'New target is 30000 point -.-';
    exit;
}

// BUG: cho phép client điều khiển stage – KHÔNG CAST INT
$stageParam = $_GET['stage'] ?? '3';

// LỖ HỔNG SQLi: nối thẳng $stageParam vào query
$sql = "SELECT kind, content FROM crypto_hints WHERE stage = $stageParam";

$res  = $pdo->query($sql);
$rows = $res->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: text/plain; charset=utf-8');

if (!$rows) {
    echo "No hints for this stage.\n";
    exit;
}

foreach ($rows as $row) {
    echo strtoupper($row['kind']) . ":\n";
    echo $row['content'] . "\n\n";
}
