<?php
// flag_agent.php - endpoint nội bộ cho checker SMS
// NHIỆM VỤ: ghi flag vào BACKUP_STORAGE_DIR/.flags/tick_<tick>.flag

// Nuốt mọi output legacy từ config.php cho sạch JSON
ob_start();
require_once __DIR__ . '/config.php';
ob_end_clean();

// Token bí mật, PHẢI trùng với AGENT_TOKEN trong sms_checker.py
const AGENT_TOKEN = 'KEY_NAY_LA_GIA_HAY_TIM_KEY_KHAC_NHE';

// Luôn trả JSON
header('Content-Type: application/json; charset=utf-8');

// Chỉ cho phép POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

$token = $_POST['token'] ?? '';
$tick  = $_POST['tick']  ?? '';
$flag  = $_POST['flag']  ?? '';

// Kiểm tra token
if (!hash_equals(AGENT_TOKEN, $token)) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Forbidden']);
    exit;
}

// Validate tick
if (!ctype_digit((string)$tick)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid tick']);
    exit;
}

// Đảm bảo thư mục FLAG_STORAGE_DIR tồn tại
if (!is_dir(FLAG_STORAGE_DIR)) {
    if (!mkdir(FLAG_STORAGE_DIR, 0750, true) && !is_dir(FLAG_STORAGE_DIR)) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Cannot create flag dir']);
        exit;
    }
}

$filename = FLAG_STORAGE_DIR . 'tick_' . $tick . '.flag';

// Ghi flag ra file (dùng @ để nuốt WARNING, tự mình xử lý lỗi)
if (@file_put_contents($filename, $flag . PHP_EOL) === false) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Cannot write flag']);
    exit;
}

// OK
http_response_code(200);
echo json_encode(['status' => 'ok', 'tick' => (int)$tick]);
