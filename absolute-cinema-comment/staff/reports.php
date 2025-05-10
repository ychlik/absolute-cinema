<?php
require_once '../includes/config.php';
checkRole('staff'); //ใช่ staff ไหม?

// ดึงข้อมูลสถิติ
try //เริ่มบล็อกที่จะลองรันโค้ดที่อาจมีข้อผิดพลาด
{
    //คำสั่ง SQL เพื่อเลือกชื่อภาพยนตร์ (title), นับจำนวนการจอง (booking_count), และหาการจองล่าสุด (last_booking) โดยมีการ JOIN หลายตาราง
    $statsStmt = $pdo->query("
    SELECT 
        m.title,
        COUNT(b.id) as booking_count,
        MAX(b.booking_date) as last_booking
    FROM movies m
    JOIN showtimes s ON m.id = s.movie_id
    LEFT JOIN bookings b ON s.id = b.showtime_id
    GROUP BY m.id
    ORDER BY booking_count DESC
    ");
    $movieStats = $statsStmt->fetchAll(); //ดึงผลลัพธ์ทั้งหมดมาเก็บไว้ในตัวแปร $movieStats
    
    //ดึงสถิติการจองรายวันล่าสุดจากฐานข้อมูล
    $dailyStmt = $pdo->query("
        SELECT 
            DATE(booking_date) as booking_day,
            COUNT(*) as bookings_count
        FROM bookings
        GROUP BY DATE(booking_date)
        ORDER BY booking_day DESC
        LIMIT 7
    ");
    $dailyStats = $dailyStmt->fetchAll();

    //จัดการข้อผิดพลาด
} catch (PDOException $e) {
    die("เกิดข้อผิดพลาด: " . $e->getMessage());
}

//include_once เพื่อรวมไฟล์ header.php เข้ามา เนื่องจากตอนแรกทำใน mac แล้วไม่ขึ้น error เมื่อย้ายมาทำใน window ต้องสร้างไฟล์ header.php footer.php แยก
include_once __DIR__ . '/../includes/header.php';
?>

<!-- ปรับแต่งการตกแต่งที่เซ็ตตามค่าเริ่มต้น -->
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
<div class="dashboard-container">
    <div class="sidebar">
        
        <!-- Same sidebar as index.php -->
    </div>
    <div class="main-content">
        <div class="card">
            <h2>รายงานภาพยนตร์ยอดนิยม</h2>
            
            <table>
                <thead>
                    <tr>
                        <th>ภาพยนตร์</th>
                        <th>จำนวนการจอง</th>
                        <th>การจองล่าสุด</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($movieStats as $stat): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($stat['title']); ?></td>
                        <td><?php echo $stat['booking_count']; ?></td>
                        <td><?php echo $stat['last_booking'] ? date('d/m/Y H:i', strtotime($stat['last_booking'])) : '-'; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <div class="card">
            <h2>สถิติการจอง 7 วันล่าสุด</h2>
            <table> 
                <thead>
                    <tr>
                        <th>วันที่</th>
                        <th>จำนวนการจอง</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($dailyStats as $stat): ?>
                    <tr>
                        <td><?php echo date('d/m/Y', strtotime($stat['booking_day'])); ?></td>
                        <td><?php echo $stat['bookings_count']; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php
include_once __DIR__ . '/../includes/footer.php';