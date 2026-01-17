<?php
// config/database.php

// 1. Get credentials from Environment Variables (set in Render dashboard)
$host = getenv('DB_HOST');
$port = getenv('DB_PORT') ?: '4000';
$dbname = getenv('DB_NAME'); // usually 'test' or the one you created
$username = getenv('DB_USER');
$password = getenv('DB_PASS');

try {
    // 2. Add SSL Options for TiDB Cloud Security
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_SSL_CA => true,
        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false
    ];

    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $username, $password, $options);

} catch (PDOException $e) {
    // Show a generic error message in production
    die("Database Connection Failed. Check credentials.");
}
?>