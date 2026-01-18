<?php
// config/database.php

// Use environment variables for production, fallback to localhost for development
$host = getenv('DB_HOST') ?: '127.0.0.1';
$port = getenv('DB_PORT') ?: '3306';
$db   = getenv('DB_NAME') ?: 'teacher_management';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') ?: '';
$charset = 'utf8mb4';

// Critical for TiDB Cloud: Path to CA Certificate
$ca_cert = getenv('DB_SSL_CA') ?: null; 

$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

// Add SSL options if a certificate is provided (required for most TiDB Cloud clusters)
if ($ca_cert) {
    $options[PDO::MYSQL_ATTR_SSL_CA] = $ca_cert;
}

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
?>