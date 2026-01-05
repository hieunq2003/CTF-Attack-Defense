<?php
require __DIR__ . '/../../db.php';

$name = $_GET['name'] ?? '';

// BACKDOOR DEBUG – LỖ HỔNG:
// Nếu ?debug=1 thì bỏ qua check điểm.
$skipCheck = (isset($_GET['debug']) && $_GET['debug'] === '1');

if (!$skipCheck) {
    if ($name === '') {
        http_response_code(400);
        echo 'Missing name';
        exit;
    }

    $stmt = $pdo->prepare('SELECT MAX(score) FROM scores WHERE name = ?');
    $stmt->execute([$name]);
    $bestScore = (int)$stmt->fetchColumn();

    if ($bestScore < 2000000) {
        http_response_code(403);
        echo '2000000 points is coming...';
        exit;
    }
}

// Lấy key & cipher từ DB
$stmt = $pdo->prepare(
    "SELECT hint_type, content
     FROM puzzle_hints
     WHERE stage = :stage"
);
$stmt->execute(['stage' => 6]);

// FETCH_KEY_PAIR => key = hint_type, value = content
$rows = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

// Các trường do checker ghi vào: real_key / cipher / (optional) iv
$realKey = $rows['real_key'] ?? 'MISSING_REAL_KEY';
$cipher  = $rows['cipher']   ?? 'MISSING_CIPHER';

// Có thể cho IV vào DB (hint_type='iv'), nếu chưa thì dùng fallback
$ivHex   = $rows['iv'] ?? 'AABBCCDDEEFF00112233445566778899';

header('Content-Type: text/plain; charset=utf-8');

echo "Stage 6 – Final Crypto Config\n";
echo "-----------------------------\n";
echo "Algorithm : AES-128-CBC\n";
echo "Key (hex) : {$realKey}\n";
echo "IV  (hex) : {$ivHex}\n";
echo "Cipher    : {$cipher}\n";

// DEBUG LEAK – in thêm hint nếu debug=1
if ($skipCheck) {
    echo "\n[DEBUG] Flag plaintext demo might be here...\n";
}
