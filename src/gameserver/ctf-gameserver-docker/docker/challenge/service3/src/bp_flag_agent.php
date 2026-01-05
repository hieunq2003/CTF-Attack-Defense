<?php
// bp_flag_agent.php - endpoint nội bộ cho checker service3
// NHIỆM VỤ: ghi flag từng tick vào file /var/www/service3/flags/flag_<tick>.txt
//           để chỉ có webshell (RCE) mới đọc được.

// KHÔNG dùng JSON, trả plain text cho đơn giản.

// PHẢI KHỚP với AGENT_TOKEN trong bp_checker.py
const AGENT_TOKEN = 'BP_UPLOAD_RCE_TOKEN_32B';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo "Method not allowed\n";
    exit;
}

$token = $_POST['token'] ?? '';
$tick  = $_POST['tick']  ?? '';
$flag  = $_POST['flag']  ?? '';

if (!hash_equals(AGENT_TOKEN, $token)) {
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

// Thư mục chứa file flag nằm NGOÀI docroot:
// docroot = /var/www/service3/src -> dirname(__DIR__) = /var/www/service3
$flagDir = __DIR__ . '/flags';

if (!is_dir($flagDir)) {
    if (!mkdir($flagDir, 0770, true) && !is_dir($flagDir)) {
        http_response_code(500);
        echo "Cannot create flag dir\n";
        exit;
    }
}

// File flag tương ứng tick
$filename = $flagDir . '/flag_' . $tickInt . '.txt';

if (@file_put_contents($filename, $flag . PHP_EOL) === false) {
    http_response_code(500);
    echo "Cannot write flag file\n";
    exit;
}

// Option: cho phép group đọc/ghi
@chmod($filename, 0660);

// OK
http_response_code(200);
echo "OK $tickInt\n";
