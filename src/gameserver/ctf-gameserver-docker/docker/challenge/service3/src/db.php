<?php
// DB config cho service3
if (!defined('DB_SERVER'))   define('DB_SERVER', '127.0.0.1');
if (!defined('DB_USERNAME')) define('DB_USERNAME', 'ctf');
if (!defined('DB_PASSWORD')) define('DB_PASSWORD', 'ctfpass');
if (!defined('DB_NAME'))     define('DB_NAME', 'block_puzzle');

try {
    $pdo = new PDO(
        "mysql:host=" . DB_SERVER . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USERNAME,
        DB_PASSWORD,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
         PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );
} catch (PDOException $e) {
    http_response_code(500);
    echo 'Database connection error';
    exit;
}
