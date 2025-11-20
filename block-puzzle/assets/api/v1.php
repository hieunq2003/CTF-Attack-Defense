<?php
require __DIR__ . '/../../db.php';

$name = $_GET['name'] ?? '';
if ($name === '') {
    http_response_code(400);
    echo 'Missing name';
    exit;
}

// HIDDEN PARAMETER – LỖ HỔNG
$scoreOverride = isset($_GET['score_override'])
    ? (int)$_GET['score_override']
    : null;

if ($scoreOverride !== null) {
    // BUG: CHO PHÉP CLIENT TỰ GHI ĐÈ SCORE
    $bestScore = $scoreOverride;
} else {
    // Logic chuẩn: lấy best score trong DB
    $stmt = $pdo->prepare('SELECT MAX(score) FROM scores WHERE name = ?');
    $stmt->execute([$name]);
    $bestScore = (int)$stmt->fetchColumn();
}

if ($bestScore < 1500) {
    http_response_code(403);
    echo 'Try to get 1500 point :))';
    exit;
}

// Lấy fake key từ bảng crypto_hints
$stmt = $pdo->query(
    "SELECT content FROM crypto_hints 
     WHERE stage = 2 AND kind = 'fake_key' 
     LIMIT 1"
);
$fakeKey = $stmt->fetchColumn() ?: 'NO_FAKE_KEY_CONFIGURED';

header('Content-Type: text/plain; charset=utf-8');
echo "Stage 2 – Fake key:\n";
echo $fakeKey . "\n";
