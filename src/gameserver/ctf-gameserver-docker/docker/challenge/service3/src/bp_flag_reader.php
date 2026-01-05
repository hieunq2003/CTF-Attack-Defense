<?php
// bp_flag_reader.php - endpoint nội bộ cho CHECKER đọc flag
// KHÔNG dùng cho player. Checker sẽ gọi với token bí mật.

// PHẢI bí mật, chỉ để trong checker:
const READER_TOKEN = 'BP_READER_SECRET_32B'; // tự generate chuỗi random 32 ký tự

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo "Method not allowed\n";
    exit;
}

$token = $_GET['token'] ?? '';
$tick  = $_GET['tick']  ?? '';

if (!hash_equals(READER_TOKEN, $token)) {
    http_response_code(403);
    echo "Forbidden\n";
    exit;
}

if (!ctype_digit((string)$tick)) {
    http_response_code(400);
    echo "Invalid tick\n";
    exit;
}

$tickInt = (int)$tick;

// CHÚ Ý: Phải trùng với chỗ bp_flag_agent.php ghi flag
// hiện tại: $flagDir = __DIR__ . '/flags';
$flagFile = __DIR__ . '/flags/flag_' . $tickInt . '.txt';

if (!is_readable($flagFile)) {
    http_response_code(404);
    echo "Not found\n";
    exit;
}

// Trả đúng nội dung flag (kèm \n nếu file có)
readfile($flagFile);
