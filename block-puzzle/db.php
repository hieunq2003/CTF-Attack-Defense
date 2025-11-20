<?php
$host = 'localhost';
$db   = 'block_puzzle';

$user = 'root';
$pass = '';

$dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    http_response_code(500);
    echo 'Database connection error';
    // Nếu muốn debug thêm thì tạm thời bật:
    // echo $e->getMessage();
    exit;
}
