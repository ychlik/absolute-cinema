<?php
require_once 'config.php';
//โหลดไฟล์ config.php เพื่อให้ใช้การเชื่อมต่อฐานข้อมูลหรือการตั้งค่าอื่น ๆ ได้

// เริ่มต้นเซสชันเพื่อให้สามารถเข้าถึงตัวแปร $_SESSION ได้
session_start();

// ล้างค่าทุกตัวในตัวแปร $_SESSION (เหมือนรีเซ็ต session)
$_SESSION = array();

// ทำลายเซสชันในฝั่งเซิร์ฟเวอร์
session_destroy();

// ตรวจสอบว่า PHP ใช้ cookie สำหรับ session หรือไม่
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params(); //ดึงค่าพารามิเตอร์ของ cookie ที่ใช้กับ session
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    ); //ลบ cookie ที่เก็บ session ID โดยตั้งค่าให้หมดอายุไปแล้ว (time() - 42000 = เวลาย้อนหลัง)
}

// ส่งผู้ใช้กลับไปยังหน้าเข้าสู่ระบบ
$_SESSION['logout_success'] = "คุณได้ออกจากระบบเรียบร้อยแล้ว";
header("Location: ../login.php");
exit();
?>