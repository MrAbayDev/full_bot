<?php
$db = '127.0.0.1';
$user = 'currency_bot';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$db;dbname=currency_bot", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    error_log('connection failed: '.$e->getMessage());
    die("Connection failed: ");
}
return $pdo;
?>
