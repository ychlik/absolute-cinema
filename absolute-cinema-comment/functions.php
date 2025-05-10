<?php
// ฟังก์ชัน listUsers
function listUsers() {
    global $pdo;

    $stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC");
    $users = $stmt->fetchAll();

    return $users; // คืนค่าผลลัพธ์ออกไป
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
