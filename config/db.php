<?php
/**
 * Database connection (XAMPP defaults: host=localhost, user=root, no password)
 * Edit these values if your MySQL setup is different.
 */

$DB_HOST = 'localhost';
$DB_NAME = 'procurement_db';
$DB_USER = 'root';
$DB_PASS = '';
$DB_CHARSET = 'utf8mb4';

$dsn = "mysql:host={$DB_HOST};dbname={$DB_NAME};charset={$DB_CHARSET}";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $DB_USER, $DB_PASS, $options);
} catch (PDOException $e) {
    die('Database connection failed: ' . htmlspecialchars($e->getMessage()) .
        '<br>Make sure Apache + MySQL are running in XAMPP and that you imported database/schema.sql.');
}
