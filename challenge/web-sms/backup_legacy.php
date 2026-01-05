<?php
// backup_legacy.php – Version 1 CŨ, CÓ LỖ HỔNG PATH TRAVERSAL
require_once 'config.php';

$baseDir = BACKUP_STORAGE_DIR;         // C:\xampp\htdocs\sms\backup\
$file    = $_GET['file'] ?? 'initial_backup.sql';

/*
 * LỖ HỔNG:
 * - Tham số "file" được nối thẳng vào đường dẫn
 * - Không kiểm tra ".." hay truy cập thư mục ẩn
 * -> Kẻ tấn công có thể đọc bất kỳ file nào bên trong thư mục backup, kể cả .flags
 */
$fullPath = $baseDir . $file;

if (!file_exists($fullPath) || !is_file($fullPath)) {
    die("File not found.");
}

header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . basename($fullPath) . '"');
readfile($fullPath);
