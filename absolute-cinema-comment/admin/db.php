<?php
$dsn = 'mysql:host=localhost;dbname=absolute_cinema;charset=utf8mb4'; // สร้างตัวแปร $dsn (Data Source Name) ซึ่งเก็บข้อมูลสำหรับการเชื่อมต่อฐานข้อมูล
$username = 'root'; // กำหนดชื่อผู้ใช้ในการเข้าสู่ฐานข้อมูล
$password = ''; // ถ้ามีรหัสผ่านให้ใส่ที่นี่

try // ทดลองรันคำสั่งที่อาจเกิดข้อผิดพลาด เพราะก่อนหน้านี้ error เนื่องจากเปลี่ยนจาก Mac มาทำต่อใน window
{
    $pdo = new PDO($dsn, $username, $password); ///สร้างอ็อบเจ็กต์ PDO โดยส่ง $dsn, $username, และ $password เข้าไปหากการเชื่อมต่อสำเร็จ ตัวแปร $pdo จะเก็บการเชื่อมต่อนั้นไว้ใช้ในภายหลัง
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); //ตั้งค่าให้ PDO แจ้งเตือนข้อผิดพลาดในรูปแบบของ Exception ช่วยให้จัดการกับ error
} catch (PDOException $e) { //หากมีข้อผิดพลาดเกิดขึ้นในการเชื่อมต่อจะเก็บเป็น $e
    die("เชื่อมต่อฐานข้อมูลไม่ได้: " . $e->getMessage()); //แสดงข้อความข้อผิดพลาดและหยุดการทำงานของสคริปต์ทันทีด้วย die() และ getMessage() จะคืนข้อความแสดงข้อผิดพลาดที่เกิดขึ้น
}
?>
