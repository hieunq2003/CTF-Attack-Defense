<?php
header('Content-Type: application/json');
require __DIR__ . '/db.php';

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!is_array($data)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Invalid JSON']);
    exit;
}

$name  = trim($data['name'] ?? '');
$score = (int)($data['score'] ?? 0);
$level = (int)($data['level'] ?? 1);

if ($name === '' || $score <= 0 || $level <= 0) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Invalid fields']);
    exit;
}

if (mb_strlen($name) > 50) {
    $name = mb_substr($name, 0, 50);
}

$stmt = $pdo->prepare(
    'INSERT INTO scores (name, level, score) VALUES (:name, :level, :score)'
);
$stmt->execute([
    ':name'  => $name,
    ':level' => $level,
    ':score' => $score,
]);

echo json_encode(['ok' => true]);
