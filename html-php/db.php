<?php

$host = 'localhost';
$db   = 'fitness_db';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Aruncă erori pe care le putem prinde
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Returnează datele ca array asociativ
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Securitate mai bună
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);

} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
?>