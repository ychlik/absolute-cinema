<?php
///ใส่รหัสเชื่อม database และ web hosting infinityfree
session_start();
$host = 'sql301.infinityfree.com';
$db   = 'if0_38936001_absolute_cinema';
$user = 'if0_38936001';
$pass = 'GEWKY3HzDC1s';
$charset = 'utf8mb4';

try {
    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("การเชื่อมต่อฐานข้อมูลล้มเหลว: " . $e->getMessage());
}

require_once($_SERVER['DOCUMENT_ROOT'] . '/functions.php'); //เรียกฟังก์ชั่น
?>
