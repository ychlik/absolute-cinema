<?php //ใส่รหัสเชื่อม database และ web hosting infinityfree
$host = 'sql301.infinityfree.com';
$db   = 'if0_38936001_absolute_cinema';
$user = 'if0_38936001';
$pass = 'GEWKY3HzDC1s';
$charset = 'utf8mb4';

$dsn = 'mysql:host=sql301.infinityfree.com;port=3306;dbname=if0_38936001_absolute_cinema;charset=utf8mb4';

try {
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("การเชื่อมต่อฐานข้อมูลล้มเหลว: " . $e->getMessage());
}
?>
