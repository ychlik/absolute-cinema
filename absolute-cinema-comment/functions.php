<?php
// ฟังก์ชัน listUsers ใช้ในการดึงรายการผู้ใช้ทั้งหมดจากตาราง users
function listUsers() {
    global $pdo; //เรียกใช้ตัวแปร $pdo ที่ประกาศไว้ภายนอก (global scope) เพื่อใช้เชื่อมต่อฐานข้อมูลภายในฟังก์ชัน

    $stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC");
    //ส่งคำสั่ง SQL ไปยังฐานข้อมูล ดึงข้อมูลทุกคอลัมน์ของผู้ใช้ทุกคนและเรียงลำดับจากผู้ใช้ที่สร้างล่าสุดไปหาเก่าสุด
    $users = $stmt->fetchAll(); //แสดงออกมาเป็นแถวหลาย ๆ แถว

    return $users; // คืนค่าผลลัพธ์ออกไป
}
?>
