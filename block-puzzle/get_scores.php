<?php
header('Content-Type: application/json');
require __DIR__ . '/db.php';

$level = isset($_GET['level']) ? (int)$_GET['level'] : 0;

if ($level > 0) {
    $stmt = $pdo->prepare(
        'SELECT name, level, score, created_at
         FROM scores
         WHERE level = :level
         ORDER BY score DESC, created_at ASC
         LIMIT 20'
    );
    $stmt->execute([':level' => $level]);
} else {
    $stmt = $pdo->query(
        'SELECT name, level, score, created_at
         FROM scores
         ORDER BY score DESC, created_at ASC
         LIMIT 20'
    );
}

$rows = $stmt->fetchAll();
echo json_encode(['ok' => true, 'data' => $rows]);
