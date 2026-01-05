<?php
// upload.php - Upload file (cố tình không filter -> RCE)
// URL: http://10.50.<team>.10:82/upload.php

$uploadDir = __DIR__ . '/uploads';

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ?>
    <!DOCTYPE html>
    <html lang="vi">
    <head>
        <meta charset="utf-8">
        <title>Block Puzzle - Tải replay</title>
        <!-- Nếu muốn dùng luôn font / màu chung, có thể link style.css -->
        <!-- <link rel="stylesheet" href="assets/css/style.css"> -->

        <style>
            :root {
                --bg: #111111;
                --card-bg: #1f1f1f;
                --text-main: #f5f5f5;
                --text-muted: #aaaaaa;
                --accent: #1e88ff;
                --accent-hover: #3ea0ff;
            }

            * {
                box-sizing: border-box;
            }

            body {
                margin: 0;
                padding: 0;
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                background: var(--bg);
                color: var(--text-main);
                font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            }

            .upload-page {
                width: 100%;
                padding: 24px;
                display: flex;
                justify-content: center;
            }

            .upload-card {
                background: var(--card-bg);
                border-radius: 16px;
                padding: 24px 32px;
                width: 100%;
                max-width: 640px;
                box-shadow: 0 18px 40px rgba(0, 0, 0, 0.6);
            }

            .upload-title {
                margin: 0 0 4px;
                font-size: 24px;
                font-weight: 700;
            }

            .upload-subtitle {
                margin: 0 0 20px;
                color: var(--text-muted);
                font-size: 14px;
            }

            .upload-form {
                margin-top: 8px;
                display: flex;
                flex-wrap: wrap;
                gap: 12px;
                align-items: center;
            }

            .upload-form input[type="file"] {
                max-width: 100%;
                color: var(--text-main);
            }

            .upload-form button[type="submit"] {
                padding: 8px 18px;
                border-radius: 8px;
                border: none;
                background: var(--accent);
                color: #ffffff;
                font-weight: 600;
                cursor: pointer;
                font-size: 14px;
            }

            .upload-form button[type="submit"]:hover {
                background: var(--accent-hover);
            }

            .upload-hint {
                margin-top: 10px;
                font-size: 12px;
                color: var(--text-muted);
            }

            .upload-back {
                margin-top: 18px;
                font-size: 14px;
            }

            .upload-back a {
                color: var(--accent);
                text-decoration: none;
            }

            .upload-back a:hover {
                text-decoration: underline;
            }
        </style>
    </head>
    <body>
        <div class="upload-page">
            <div class="upload-card">
                <h2 class="upload-title">Tải lên file replay (beta)</h2>
                <p class="upload-subtitle">
                    Chọn file replay (<code>.bpreplay</code>) để xem lại ván đấu trước.
                </p>

                <form method="post" enctype="multipart/form-data" class="upload-form">
                    <input type="file" name="file" accept=".bpreplay">
                    <button type="submit">Tải lên</button>
                </form>

                <p class="upload-hint">
                    Lưu ý: Chỉ hỗ trợ replay được xuất từ Block Puzzle.
                </p>

                <div class="upload-back">
                    <a href="index.php">&larr; Quay lại game</a>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

header('Content-Type: application/json; charset=utf-8');

if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'No file uploaded']);
    exit;
}

$originalName = $_FILES['file']['name'] ?? 'upload.bin';
$tmpName      = $_FILES['file']['tmp_name'];

$baseName = basename($originalName);

// LỖ HỔNG: không hề filter extension, có thể upload .php
$targetPath = $uploadDir . DIRECTORY_SEPARATOR . $baseName;

if (!move_uploaded_file($tmpName, $targetPath)) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Cannot move uploaded file']);
    exit;
}

// Đường dẫn public cho attacker / checker
$publicPath = '/uploads/' . $baseName;

http_response_code(200);
echo json_encode([
    'status' => 'ok',
    'path'   => $publicPath,
]);
