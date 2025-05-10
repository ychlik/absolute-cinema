<?php
require_once '../includes/config.php';
checkRole('staff'); // staff ไหม?

// ดึงข้อมูลการจองจากตารางใหม่
try {
    $stmt = $pdo->prepare("
        SELECT 
            b.id,
            u.email,
            m.title,
            s.show_date,
            s.show_time,
            st.seat_number,
            b.booking_date
        FROM bookings b
        JOIN users u ON b.user_id = u.id
        JOIN showtimes s ON b.showtime_id = s.id
        JOIN movies m ON s.movie_id = m.id
        JOIN seats st ON b.seat_id = st.id
        JOIN seat_status ss ON ss.seat_id = b.seat_id AND ss.showtime_id = b.showtime_id
        WHERE ss.status = 'reserved'
        ORDER BY b.booking_date DESC
    "); // ใช้ JOIN เชื่อมตารางแล้วกรองให้แสดงเฉพาะที่นั่งที่มีสถานะ 'reserved' โดยเรียงลำดับตามวันที่จองจากล่าสุดไปเก่าสุด
    $stmt->execute();
    $bookings = $stmt->fetchAll();
    //สั่งให้ SQL ทำงาน แล้วดึงผลลัพธ์ทั้งหมดจากการ query และเก็บไว้ในตัวแปร $bookings
} catch (PDOException $e) {
    die("เกิดข้อผิดพลาด: " . $e->getMessage());
} // แสดงข้อผิดพลาดหากมีปัญหาในการดึงข้อมูล

include '../includes/header.php'; // รวมไฟล์ส่วนหัวของหน้าเว็บ
?>
<!-- อิงคำสั่งเหมือนหน้าอื่น ๆ ปรับแต่งรายละเอียดตามสถานะผู้ใช้และฟังก์ชั่น -->
 <!DOCTYPE html>
    <html lang="th">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="../assets/css/style.css">
        <style>
            .dashboard-container {
                display: grid;
                grid-template-columns: 250px 1fr;
                gap: 20px;
                padding: 20px;
            }
            .sidebar {
                background: #f5f5f5;
                padding: 20px;
                border-radius: 8px;
            }
            .main-content {
                display: grid;
                gap: 20px;
            }
            .stats-cards {
                display: grid;
                grid-template-columns: repeat(4, 1fr);
                gap: 15px;
            }
            .card {
                background: white;
                padding: 20px;
                border-radius: 8px;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }
            .section-title {
                margin-bottom: 15px;
                color: #333;
                font-size: 1.2rem;
            }
            table {
                width: 100%;
                border-collapse: collapse;
            }
            th, td {
                padding: 12px 15px;
                text-align: left;
                border-bottom: 1px solid #ddd;
            }
            th {
                background-color: #f2f2f2;
            }
        </style>
    </head>
    <body>
        <!-- Navbar -->
        <nav class="navbar">
            <a href="index.php" class="logo">Absolute Cinema - Staff</a>
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
<div class="dashboard-container">
    <div class="sidebar">
    <h3>เมนูพนักงาน</h3>
        <ul style="list-style: none; padding: 0;">
            <li><a href="index.php" class="btn">แดชบอร์ด</a></li>
            <li style="margin-top: 10px;"><a href="movies.php" class="btn">จัดการภาพยนตร์</a></li>
            <li style="margin-top: 10px;"><a href="bookings.php" class="btn">การจองทั้งหมด</a></li>
            <li style="margin-top: 10px;"><a href="reports.php" class="btn">รายงาน</a></li>
        </ul>
    </div>
    <div class="main-content">
        <div class="card">
            <h2>การจองทั้งหมด</h2>
            
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>ผู้ใช้</th>
                        <th>ภาพยนตร์</th>
                        <th>รอบฉาย</th>
                        <th>ที่นั่ง</th>
                        <th>วันที่จอง</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bookings as $booking): ?>
                    <tr>
                        <td><?php echo $booking['id']; ?></td>
                        <td><?php echo htmlspecialchars($booking['email']); ?></td>
                        <td><?php echo htmlspecialchars($booking['title']); ?></td>
                        <td>
                            <?php 
                            echo date('d/m/Y', strtotime($booking['show_date']));
                            echo ' เวลา ';
                            echo date('H:i', strtotime($booking['show_time']));
                            ?>
                        </td>
                        <td><?php echo htmlspecialchars($booking['seat_number']); ?></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($booking['booking_date'])); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include(__DIR__ . '/../includes/footer.php'); ?> <!-- รวมไฟล์ส่วนท้ายของหน้าเว็บ -->
