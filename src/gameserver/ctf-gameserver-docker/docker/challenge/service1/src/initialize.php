<?php

/**
 * =======================================================
 *  BASE CONFIG (giữ nguyên logic bản gốc)
 * =======================================================
 */

// Base URL (tự động nhận host hiện tại)
if (!defined('base_url')) {
    define('base_url', 'http://' . $_SERVER['HTTP_HOST'] . '/');
}

// Base app path
if (!defined('base_app')) {
    define('base_app', str_replace('\\', '/', __DIR__) . '/');
}

// Developer login (giữ nguyên của bản gốc)
$dev_data = array(
    'id' => '-1',
    'firstname' => 'Developer',
    'lastname' => '',
    'username' => 'dev_oretnom',
    'password' => '5da283a2d990e8d8512cf967df5bc0d0',
    'last_login' => '',
    'date_updated' => '',
    'date_added' => ''
);

if (!defined('dev_data')) {
    define('dev_data', $dev_data);
}


/**
 * =======================================================
 *  DATABASE CONFIG (cho MySQL trong container)
 * =======================================================
 *
 * Vì team server chạy MySQL nội bộ trong chính container,
 * hostname = 127.0.0.1, user root, password root.
 */

if (!defined('DB_SERVER')) define('DB_SERVER', "127.0.0.1");
if (!defined('DB_USERNAME')) define('DB_USERNAME', "ctf");
if (!defined('DB_PASSWORD')) define('DB_PASSWORD', "ctfpass");
if (!defined('DB_NAME')) define('DB_NAME', "sis_db");


/**
 * =======================================================
 *  PDO CONNECTION
 * =======================================================
 */
try {
    $conn = new PDO(
        "mysql:host=" . DB_SERVER . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USERNAME,
        DB_PASSWORD,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    die("DB Connection failed: " . $e->getMessage());
}

?>
