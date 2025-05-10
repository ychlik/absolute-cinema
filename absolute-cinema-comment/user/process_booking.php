<?php
require_once '../includes/config.php';

// เปิดการแสดงข้อผิดพลาดทั้งหมดเพื่อ debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json'); //กำหนดให้ Response ที่ส่งกลับเป็น JSON (สำหรับ AJAX/Fetch API)

// ตรวจสอบการเข้าสู่ระบบ ถ้าผู้ใช้ยังไม่ได้ล็อกอิน (isLoggedIn() return false) ให้ส่ง JSON กลับไปแจ้งว่า "กรุณาเข้าสู่ระบบ" แล้วหยุดการทำงาน
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'กรุณาเข้าสู่ระบบ']);
    exit();
}

// อ่านข้อมูล JSON จาก request แปลง JSON เป็น array เก็บไว้ในตัวแปร $data
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// ตรวจสอบข้อมูลที่จำเป็น เช็กว่าในข้อมูลมี showtime_id และ seats มาครบไหม (และ seats ต้องไม่ว่างเปล่า)
if (!isset($data['showtime_id']) || !isset($data['seats']) || empty($data['seats'])) {
    echo json_encode(['success' => false, 'message' => 'ข้อมูลไม่ครบถ้วน']);
    exit();
}

//ดึงค่าต่าง ๆ จาก $data และ session มาเก็บไว้ในตัวแปร
$showtimeId = $data['showtime_id'];
$seats = $data['seats']; // array of seat_id (integers)
$userId = $_SESSION['user_id'];



try {
    $pdo->beginTransaction();

    foreach ($seats as $seatId) {
        // ตรวจสอบว่าสถานะที่นั่งยัง available อยู่ในตาราง seat_status
        $checkStmt = $pdo->prepare("
            SELECT status FROM seat_status
            WHERE seat_id = ? AND showtime_id = ?
            FOR UPDATE
        "); //FOR UPDATE ใช้ล็อกแถวในฐานข้อมูล เพื่อป้องกันการจองซ้อนจากผู้ใช้อื่นในเวลาเดียวกัน
        $checkStmt->execute([$seatId, $showtimeId]);
        $seatStatus = $checkStmt->fetchColumn();


        //ถ้าไม่พบข้อมูล หรือสถานะไม่ใช่ available ให้โยน exception (เพื่อให้จับใน catch)
        if (!$seatStatus) {
            throw new Exception("ไม่พบที่นั่งหมายเลข $seatId");
        }

        if ($seatStatus !== 'available') {
            throw new Exception("ที่นั่งหมายเลข $seatId ถูกจองไปแล้ว");
        }

        // อัปเดตสถานะที่นั่งเป็น reserved
        $updateStmt = $pdo->prepare("
            UPDATE seat_status SET status = 'reserved'
            WHERE seat_id = ? AND showtime_id = ?
        ");
        $updateStmt->execute([$seatId, $showtimeId]);

        // บันทึกการจองลงตาราง bookings
        $bookingStmt = $pdo->prepare("
            INSERT INTO bookings (user_id, showtime_id, seat_id)
            VALUES (?, ?, ?)
        ");
        $bookingStmt->execute([$userId, $showtimeId, $seatId]);
    }

    //ถ้าทุกอย่างผ่านทั้งหมด ให้ commit และส่งข้อความสำเร็จกลับไป
    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'จองที่นั่งสำเร็จ']);

    //ถ้ามี exception เกิดขึ้นที่ไหนใน try ให้ rollback (ยกเลิกทั้งหมด) และส่งข้อความผิดพลาดกลับไป
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
}
