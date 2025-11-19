<?php
// backup.php – Version 2 (an toàn)
require_once 'config.php'; // hoặc tên file cấu hình chung

global $conn;

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    // Liệt kê danh sách backup
    $qry = $conn->query("SELECT id, display_name, created_at FROM backups ORDER BY created_at DESC");
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="utf-8">
        <title>Backup Center</title>
    </head>
    <body>
        <h1>Backup Center (v2)</h1>
        <p>Chức năng quản lý và tải về bản sao lưu hệ thống kho hàng.</p>
        <table border="1" cellpadding="5">
            <tr>
                <th>ID</th>
                <th>Tên backup</th>
                <th>Thời gian</th>
                <th>Tải về</th>
            </tr>
            <?php while($row = $qry->fetch_assoc()): ?>
                <tr>
                    <td><?= (int)$row['id'] ?></td>
                    <td><?= htmlspecialchars($row['display_name'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($row['created_at'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><a href="backup.php?id=<?= (int)$row['id'] ?>">Download</a></td>
                </tr>
            <?php endwhile; ?>
        </table>
    </body>
    </html>
    <?php
    exit;
}

// Download 1 file cụ thể (AN TOÀN – dùng id, không truyền path)
$stmt = $conn->prepare("SELECT stored_filename, display_name FROM backups WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->bind_result($stored_filename, $display_name);
if (!$stmt->fetch()) {
    die("Backup not found.");
}
$stmt->close();

$fullPath = BACKUP_STORAGE_DIR . $stored_filename;

if (!is_file($fullPath)) {
    die("Backup file missing.");
}

header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . basename($display_name) . '"');
readfile($fullPath);
