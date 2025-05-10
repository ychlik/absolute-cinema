<?php
require_once '../includes/config.php';
checkRole('admin'); //ตรวจสอบว่า user ปัจจุบันมีสิทธิ์เป็นแอดมินหรือไม่

$pageTitle = "Absolute Cinema - Admin Dashboard"; //ใส่ชื่อหน้า

try {
    // สถิติทั่วไป
    // COUNT(*) → นับผู้ใช้ทั้งหมด, staff, นับการจอง, นับจำนวนภาพยนตร์ทั้งหมด
    
    $statsStmt = $pdo->query("
        SELECT 
            COUNT(*) as total_users,
            (SELECT COUNT(*) FROM users WHERE role = 'staff') as total_staff,
            (SELECT COUNT(*) FROM bookings WHERE DATE(booking_date) = CURDATE()) as today_bookings,
            (SELECT COUNT(*) FROM movies) as total_movies
        FROM users
    ");
    $stats = $statsStmt->fetch(); //ดึงข้อมูลที่นับออกมาเป็นแถว

    // ผู้ใช้ล่าสุด
    // ดึงข้อมูลผู้ใช้ 5 คนล่าสุดโดยเรียงจากเวลาที่สร้าง
    $usersStmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC LIMIT 5");
    $recentUsers = $usersStmt->fetchAll();

    // การจองล่าสุด
    //ดึงข้อมูลการจองที่รวมข้อมูลจากตาราง bookings (วันจอง) users (อีเมลผู้ใช้) showtimes (วัน/เวลาเข้าฉาย) movies (ชื่อหนัง) seat_status (เช็คว่า seat ถูกจองจริงหรือไม่) seats (เลขที่นั่ง)
    $bookingsStmt = $pdo->query("
        SELECT 
            b.booking_date,
            u.email,
            m.title,
            s.show_date,
            s.show_time,
            st.seat_number
        FROM bookings b
        JOIN users u ON b.user_id = u.id
        JOIN showtimes s ON b.showtime_id = s.id
        JOIN movies m ON s.movie_id = m.id
        JOIN seat_status ss ON ss.seat_id = b.seat_id AND ss.showtime_id = b.showtime_id
        JOIN seats st ON st.id = b.seat_id
        WHERE ss.status = 'reserved'
        ORDER BY b.booking_date DESC
        LIMIT 5
    ");
    $recentBookings = $bookingsStmt->fetchAll();

    //หากมีข้อผิดพลาดในการเชื่อมต่อหรือคิวรีฐานข้อมูล จะแสดงข้อความและหยุดทำงาน
} catch (PDOException $e) {
    die("เกิดข้อผิดพลาดในการดึงข้อมูล: " . $e->getMessage());
}
?>

<!-- HTML ตกแต่งออกแบบหน้าตาการแสดงผล -->
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title><?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        /* กำหนดขนาด สี ตัวอักษร*/
        .dashboard-container { display: grid; grid-template-columns: 250px 1fr; gap: 20px; padding: 20px; }
        .sidebar { background: #f5f5f5; padding: 20px; border-radius: 8px; }
        .main-content { display: grid; gap: 20px; }
        .stats-cards { display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; }
        .card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .section-title { margin-bottom: 15px; color: #333; font-size: 1.2rem; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #f2f2f2; }
        .btn { padding: 8px 16px; background-color: #000; color: white; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-block; }
        .btn-sm { padding: 4px 8px; font-size: 0.8rem; }
        .status-active { color: #28a745; }
        .status-inactive { color: #dc3545; }
    </style>
</head>
<body>
    <!-- Navbar ข้างบน แสดงโลโก้ และอีเมลของผู้ใช้ที่ล็อกอินอยู่ -->
    <nav class="navbar">
        <a href="index.php" class="logo">Absolute Cinema - Admin</a>
        <div class="user-menu">
            <span class="user-icon">👤 <?php echo htmlspecialchars($_SESSION['email']); ?></span>
            <div class="dropdown-menu">  <!-- dropdown สำหรับไปยังหน้าโปรไฟล์ หรือออกจากระบบ -->
                <a href="profile.php">โปรไฟล์</a>
                <form action="../includes/logout.php" method="post" style="display: inline;">
                    <button type="submit" style="background: none; border: none; color: #333; padding: 12px 16px; width: 100%; text-align: left; cursor: pointer;">ออกจากระบบ</button>
                </form>
            </div>
        </div>
    </nav>

    <div class="dashboard-container">
        <!-- Sidebar สำหรับลิงก์ไปหน้าต่าง ๆ ที่อยู่ในการดูแลของตำแหน่ง admin -->
        <div class="sidebar">
            <h3>เมนูผู้ดูแลระบบ</h3>
            <ul style="list-style: none; padding: 0;">
                <li><a href="index.php" class="btn">แดชบอร์ด</a></li>
                <li style="margin-top: 10px;"><a href="users.php" class="btn">จัดการผู้ใช้</a></li>
                <li style="margin-top: 10px;"><a href="staff.php" class="btn">จัดการพนักงาน</a></li>
                <li style="margin-top: 10px;"><a href="reports.php" class="btn">รายงานและสถิติ</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Stats สถิติผู้ใช้ทั้งหมด พนักงาน การจอง ภาพยนตร์ทั้งหมด-->
            <div class="stats-cards">
                <div class="card"><h4>ผู้ใช้ทั้งหมด</h4><p style="font-size: 2rem;"><?php echo $stats['total_users']; ?></p></div>
                <div class="card"><h4>พนักงาน</h4><p style="font-size: 2rem;"><?php echo $stats['total_staff']; ?></p></div>
                <div class="card"><h4>การจองวันนี้</h4><p style="font-size: 2rem;"><?php echo $stats['today_bookings']; ?></p></div>
                <div class="card"><h4>ภาพยนตร์ทั้งหมด</h4><p style="font-size: 2rem;"><?php echo $stats['total_movies']; ?></p></div>
            </div>

            <!-- ผู้ใช้ล่าสุด วนลูปข้อมูลผู้ใช้ 5 รายล่าสุด -->
            <div class="card">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <h3 class="section-title">ผู้ใช้ล่าสุด</h3>
                    <a href="users.php" class="btn btn-sm">ดูทั้งหมด</a>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>อีเมล</th>
                            <th>บทบาท</th>
                            <th>วันที่สมัคร</th>
                            <th>สถานะ</th>
                            <th>การกระทำ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentUsers as $user): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td>
                                <?php 
                                switch($user['role']) {
                                    case 'admin': echo 'ผู้ดูแลระบบ'; break;
                                    case 'staff': echo 'พนักงาน'; break;
                                    default: echo 'ผู้ใช้ทั่วไป';
                                }
                                ?>
                            </td>
                            <td><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></td>
                            <td class="<?php echo $user['is_active'] ? 'status-active' : 'status-inactive'; ?>">
                                <?php echo $user['is_active'] ? 'ใช้งาน' : 'ปิดใช้งาน'; ?>
                            </td>
                            <td><a href="users.php" class="btn btn-sm">แก้ไข</a></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- การจองล่าสุด วนลูปการจอง 5 รายการล่าสุด -->
            <div class="card">
                <h3 class="section-title">การจองล่าสุด</h3>
                <table>
                    <thead>
                        <tr>
                            <th>ผู้ใช้</th>
                            <th>ภาพยนตร์</th>
                            <th>รอบฉาย</th>
                            <th>ที่นั่ง</th>
                            <th>วันที่จอง</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentBookings as $booking): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($booking['email']); ?></td>
                            <td><?php echo htmlspecialchars($booking['title']); ?></td>
                            <td><?php echo date('d/m/Y', strtotime($booking['show_date'])) . ' เวลา ' . date('H:i', strtotime($booking['show_time'])); ?></td>
                            <td><?php echo htmlspecialchars($booking['seat_number']); ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($booking['booking_date'])); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</body>
</html>
