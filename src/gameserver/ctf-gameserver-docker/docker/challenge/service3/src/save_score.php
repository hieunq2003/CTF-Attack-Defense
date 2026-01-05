<?php
// save_score.php
require __DIR__ . '/db.php';

header('Content-Type: application/json; charset=utf-8');

// Chỉ cho phép POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
    exit;
}

// Đọc body (JSON)
$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

// Nếu JSON lỗi, thử fallback qua $_POST (phòng trường hợp gửi form)
if (!is_array($data)) {
    if (!empty($_POST)) {
        $data = $_POST;
    } else {
        http_response_code(400);
        echo json_encode([
            'ok'    => false,
            'error' => 'Invalid JSON body',
            'raw'   => $raw, // debug cho dễ nhìn trong tab Network
        ]);
        exit;
    }
}

// Lấy field
$name  = trim($data['name']  ?? '');
$score = isset($data['score']) ? (int)$data['score'] : 0;
$level = isset($data['level']) ? (int)$data['level'] : 0;

// Validate đơn giản
if ($name === '' || $score <= 0 || $level <= 0) {
    http_response_code(400);
    echo json_encode([
        'ok'    => false,
        'error' => 'Missing or invalid fields',
        'debug' => [
            'name'  => $name,
            'score' => $score,
            'level' => $level,
        ],
    ]);
    exit;
}

try {
    $stmt = $pdo->prepare('INSERT INTO scores (name, level, score) VALUES (?, ?, ?)');
    $stmt->execute([$name, $level, $score]);

    echo json_encode(['ok' => true]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'ok'    => false,
        'error' => $e->getMessage(),
    ]);
}
