<?php 
$localhost = 'localhost';
$dbname = 'task_manager';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$localhost;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>