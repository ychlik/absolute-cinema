<?php
require_once 'config.php'; // รวมไฟล์ config

// ตรวจสอบ action ที่ส่งมา
$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($action == 'logout') {
    // ลบข้อมูล session ทั้งหมด
    $_SESSION = array();
    
    // ลบ cookie session
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    // ทำลาย session
    session_destroy();
    
    // นำทางไปยังหน้าเข้าสู่ระบบ
    header('Location: ../login.php');
    exit();
}

// ตรวจสอบว่ามีการส่งฟอร์มหรือไม่
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $action == 'process_booking') {
    // ตรวจสอบว่าผู้ใช้เข้าสู่ระบบหรือไม่
    if (!isLoggedIn()) {
        echo json_encode(['success' => false, 'message' => 'กรุณาเข้าสู่ระบบ']);
        exit();
    }
    
    // อ่านข้อมูล JSON ที่ส่งมา
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    $showtimeId = $data['showtime_id'];
    $seats = $data['seats'];
    
    try {
        $pdo->beginTransaction();
        
        // จองที่นั่งแต่ละที่นั่ง
        foreach ($seats as $seat) {
            // ตรวจสอบว่าที่นั่งว่างจริงหรือไม่
            $checkStmt = $pdo->prepare("SELECT status FROM seats WHERE showtime_id = ? AND seat_number = ?");
            $checkStmt->execute([$showtimeId, $seat]);
            $seatStatus = $checkStmt->fetchColumn();
            
            if ($seatStatus != 'available') {
                throw new Exception("ที่นั่ง $seat ไม่ว่างแล้ว");
            }
            
            // อัปเดตสถานะที่นั่งเป็น reserved
            $update