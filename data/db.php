<?php
// Database connection (change the username and password if they are different on your computer)
$DB_HOST = '127.0.0.1';
$DB_PORT = '8889';
$DB_NAME = 'rihla';
$DB_USER = 'root';
$DB_PASS = 'root';

try {
    $pdo = new PDO('mysql:host=' . $DB_HOST . ';port=' . $DB_PORT . ';dbname=' . $DB_NAME . ';charset=utf8mb4', $DB_USER, $DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
} catch (PDOException $e) {
    die('لا يمكن الاتصال بقاعدة البيانات: ' . $e->getMessage());
}

// Returns the database connection
function db() {
    global $pdo;
    return $pdo;
}
