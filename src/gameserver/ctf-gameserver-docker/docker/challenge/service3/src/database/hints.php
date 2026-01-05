<?php
// hints.php - trả về nội dung JSON từ hints.json

header('Content-Type: application/json; charset=utf-8');

$hints_file = __DIR__ . '/hints.json';

if (!file_exists($hints_file)) {
    echo json_encode([
        'stages'  => [],
        'secrets' => []
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

$json = file_get_contents($hints_file);
$data = json_decode($json, true);

if (!is_array($data)) {
    $data = [
        'stages'  => [],
        'secrets' => []
    ];
}

echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
