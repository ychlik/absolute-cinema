<?php
// ไฟล์สำหรับการเชื่อมต่อฐานข้อมูล
session_start(); // เริ่ม session เพื่อเก็บข้อมูลผู้ใช้

// ข้อมูลสำหรับเชื่อมต่อฐานข้อมูล
$host = 'localhost';
$dbname = 'absolute_cinema';
$username = 'root'; // ชื่อผู้ใช้ฐานข้อมูล (ค่าเริ่มต้นของ XAMPP)
$password = ''; // รหัสผ่านฐานข้อมูล (ค่าเริ่มต้นของ XAMPP)

try {
    // สร้างการเชื่อมต่อ PDO
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    
    // ตั้งค่า PDO ให้แสดงข้อผิดพลาดในรูปแบบ exceptions
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // ตั้งค่า charset เป็น UTF-8
    $pdo->exec("SET NAMES utf8");
} catch (PDOException $e) {
    // แสดงข้อความผิดพลาดหากเชื่อมต่อไม่สำเร็จ
    die("การเชื่อมต่อฐานข้อมูลล้มเหลว: " . $e->getMessage());
}

// ฟังก์ชันสำหรับตรวจสอบว่าผู้ใช้เข้าสู่ระบบหรือไม่
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// ฟังก์ชันสำหรับตรวจสอบบทบาทผู้ใช้
function checkRole($requiredRole) {
    if (!isLoggedIn() || $_SESSION['role'] != $requiredRole) {
        header('Location: ../login.php');
        exit();
    }
}
?>