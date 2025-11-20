<?php
$host = 'localhost';
$db   = 'block_puzzle';

$user = 'bp_user_srv3';
$pass = 'Super_Strong_Pass_123!';

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

/*
// <?php
// $host = getenv('DB_HOST') ?: '127.0.0.1';
// $db   = getenv('DB_NAME') ?: 'block_puzzle';

// $user = getenv('DB_USER') ?: 'bp_user_srv3';
// $pass = getenv('DB_PASS') ?: 'changeme';

// $dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";

// $options = [
//     PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
//     PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
// ];

// try {
//     $pdo = new PDO($dsn, $user, $pass, $options);
// } catch (PDOException $e) {
//     // sau này nhớ tắt / log riêng, giờ để debug
//     die('DB connection failed: ' . $e->getMessage());
// }

 */