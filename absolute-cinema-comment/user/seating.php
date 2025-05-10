<?php
require_once '../includes/config.php';
checkRole('user'); // user ไหม?

if (!isset($_GET['showtime_id'])) {
    header('Location: index.php');
    exit();
} //ถ้าไม่มี showtime_id ใน URL (ไม่ระบุรอบฉาย) จะกลับไปที่หน้าแรก

$showtimeId = $_GET['showtime_id']; //ดึง showtime_id จาก URL มาเก็บไว้ในตัวแปร

try {
    $showtimeStmt = $pdo->prepare("SELECT s.*, m.title FROM showtimes s JOIN movies m ON s.movie_id = m.id WHERE s.id = ?");
    $showtimeStmt->execute([$showtimeId]);
    $showtime = $showtimeStmt->fetch();
    //สร้างคำสั่ง SQL ดึงข้อมูลรอบฉาย (showtimes) พร้อมชื่อหนัง (movies.title) ส่ง $showtimeId เข้าไปในคำสั่ง execute ดึงผลลัพธ์มาเก็บไว้ใน $showtime

    if (!$showtime) {
        header('Location: index.php');
        exit();
    } //ถ้าหาไม่เจอรอบฉายที่ตรงกับ ID จะ redirect กลับหน้าแรก

    // ดึงข้อมูลที่นั่งจาก seat_status ร่วมกับ seats เพื่อเอา seat_number
    $seatStmt = $pdo->prepare("SELECT ss.seat_id, ss.status, s.seat_number FROM seat_status ss JOIN seats s ON ss.seat_id = s.id WHERE ss.showtime_id = ?");
    $seatStmt->execute([$showtimeId]);
    $seats = $seatStmt->fetchAll();


    //แปลงข้อมูลที่นั่งให้เป็น array ซ้อน ($seatMap) โดยแบ่งเป็นแถว (A, B, ฯลฯ) และคอลัมน์ (1, 2, ...)
    $seatMap = [];
    foreach ($seats as $seat) {
        $row = substr($seat['seat_number'], 0, 1);
        $col = substr($seat['seat_number'], 1);
        $seatMap[$row][$col] = $seat;
    }
} catch (PDOException $e) {
    die("เกิดข้อผิดพลาดในการดึงข้อมูลที่นั่ง: " . $e->getMessage());
}

$pageTitle = "Absolute Cinema - เลือกที่นั่ง";
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <a href="index.php" class="logo">Absolute Cinema</a>
        <div class="user-menu">
            <span class="user-icon">👤 <?php echo htmlspecialchars($_SESSION['email']); ?></span>
            <div class="dropdown-menu">
                <a href="profile.php">โปรไฟล์</a>
                <form action="../includes/logout.php" method="post" style="display: inline;">
                    <button type="submit" style="background: none; border: none; color: #333; padding: 12px 16px; width: 100%; text-align: left; cursor: pointer;">ออกจากระบบ</button>
                </form>
            </div>
        </div>
    </nav>

    <div class="seating-container">
        <h1><?php echo htmlspecialchars($showtime['title']); ?></h1>
        <!-- แสดงวันและเวลาในรูปแบบอ่านง่าย -->
        <p>วันที่: <?php echo date('d/m/Y', strtotime($showtime['show_date'])); ?> เวลา: <?php echo date('H:i', strtotime($showtime['show_time'])); ?></p>
 
        <div class="screen">หน้าจอ</div>

        <div class="seat-map">
            <!-- วนลูปแถวและคอลัมน์ของที่นั่งทั้งหมด -->
            <?php foreach ($seatMap as $row => $cols): ?>
                <?php foreach ($cols as $col => $seat): ?>
                     <!-- สร้างกล่องสำหรับแต่ละที่นั่ง บอกสถานะ หมายเลขไอดี คลิกเพื่อเลือก -->
                    <div class="seat <?php echo $seat['status']; ?>" 
                         data-seat-id="<?php echo $seat['seat_id']; ?>"
                         onclick="selectSeat(this, '<?php echo $seat['status']; ?>')">
                        <?php echo $seat['seat_number']; ?>
                    </div>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </div>

        <!-- สำหรับอธิบายสีที่นั่งให้ user เข้าใจ -->
        <div class="seat-legend">
            <div><span class="seat-available"></span> สีเขียว: ว่าง</div>
            <div><span class="seat-reserved"></span> สีแดง: จองแล้ว</div>
            <div><span class="seat-selected"></span> สีฟ้า: เลือกแล้ว</div>
        </div>

        <!-- ปุ่มกดเพื่อจ่ายเงิน (เริ่มต้นถูก disable ไว้) -->
        <button id="payment-btn" class="payment-btn" disabled onclick="processPayment()">ชำระเงิน</button>
    </div>

    <script>
        let selectedSeats = []; //เก็บ ID ที่นั่งที่ถูกเลือกไว้ใน array

        function selectSeat(element, status) {
            if (status === 'reserved' || status === 'selected') return;//ถ้าที่นั่งถูกจองหรือเลือกไปแล้วจะไม่ให้เลือกซ้ำ

            const seatId = element.getAttribute('data-seat-id');

            if (element.classList.contains('selected')) {
                element.classList.remove('selected');
                selectedSeats = selectedSeats.filter(id => id !== seatId);
            } else {
                element.classList.add('selected');
                selectedSeats.push(seatId);
            }

            document.getElementById('payment-btn').disabled = selectedSeats.length === 0;
        } //ถ้าคลิกซ้ำจะเอาออกจาก selectedSeats ถ้าเลือกใหม่ค่อยใส่เข้าไปใน selectedSeats และเปิดปุ่ม "ชำระเงิน" ถ้ามีที่นั่งถูกเลือก


        function processPayment() {
            if (selectedSeats.length === 0) return; //ถ้ายังไม่มีที่นั่งถูกเลือกจะไม่มีอะไรเกิดขึ้น

            const btn = document.getElementById('payment-btn');
            btn.disabled = true;
            btn.textContent = 'กำลังประมวลผล...'; //ปิดปุ่มชั่วคราวขณะกำลังประมวล

            fetch('process_booking.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    showtime_id: <?php echo (int)$showtimeId; ?>,
                    seats: selectedSeats
                })
            }) //ส่งข้อมูลแบบ JSON ไปที่ process_booking.php เพื่อจองที่นั่ง


            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('ชำระเงินสำเร็จ! ที่นั่งของคุณถูกจองแล้ว');
                    window.location.href = 'index.php';
                } else {
                    alert('เกิดข้อผิดพลาด: ' + data.message);
                    btn.disabled = false;
                    btn.textContent = 'ชำระเงิน';
                }
            }) //ถ้าสำเร็จจะแสดงข้อความและ redirect แต่ถ้า error จะแจ้งเตือนและเปิดปุ่มให้ทำรายการใหม่

            .catch(error => {
                alert('เกิดข้อผิดพลาดในการเชื่อมต่อ: ' + error.message);
                btn.disabled = false;
                btn.textContent = 'ชำระเงิน';
            }); // ถ้า error จะขึ้นเตือนและเปิดปุ่มให้กดชำระเงิน
        }
    </script>
</body>
</html>
