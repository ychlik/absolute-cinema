<?php
require_once '../includes/config.php';
checkRole('staff'); // ตรวจสอบสิทธิ์เฉพาะ staff

// ตั้งค่าหัวเรื่องหน้า
$pageTitle = "Absolute Cinema - Staff Dashboard";

// ดึงข้อมูลการจองล่าสุดจากตารางใหม่ ดึงข้อมูลการจองล่าสุด 10 รายการจากฐานข้อมูล
try {
    $bookingsStmt = $pdo->prepare("
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
        LIMIT 10
    ");
    $bookingsStmt->execute();
    $recentBookings = $bookingsStmt->fetchAll(); 
    //ดึงข้อมูลทั้งหมดมาเก็บในตัวแปร $recentBookings

    // ดึงข้อมูลภาพยนตร์ทั้งหมด
    $moviesStmt = $pdo->query("SELECT * FROM movies ORDER BY release_date DESC");
    $movies = $moviesStmt->fetchAll();

    // ดึงข้อมูลสถิติ (จำนวนภาพยนตร์ทั้งหมด, การจองวันนี้, ผู้ใช้ทั้งหมด)
    $statsStmt = $pdo->query("
        SELECT 
            COUNT(*) as total_movies,
            (SELECT COUNT(*) FROM bookings WHERE DATE(booking_date) = CURDATE()) as today_bookings,
            (SELECT COUNT(*) FROM users WHERE role = 'user') as total_users
        FROM movies
    ");
    $stats = $statsStmt->fetch();

} catch (PDOException $e) {
    die("เกิดข้อผิดพลาดในการดึงข้อมูล: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
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
            grid-template-columns: repeat(3, 1fr);
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
        .btn {
            padding: 8px 16px;
            background-color: #000;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .btn-primary {
            background-color: #000;
        }
        .btn-danger {
            background-color: #dc3545;
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
        <!-- Sidebar -->
        <div class="sidebar">
            <h3>เมนูพนักงาน</h3>
            <ul style="list-style: none; padding: 0;">
                <li><a href="index.php" class="btn">แดชบอร์ด</a></li>
                <li style="margin-top: 10px;"><a href="movies.php" class="btn">จัดการภาพยนตร์</a></li>
                <li style="margin-top: 10px;"><a href="bookings.php" class="btn">การจองทั้งหมด</a></li>
                <li style="margin-top: 10px;"><a href="reports.php" class="btn">รายงาน</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Stats Cards -->
            <div class="stats-cards">
                <div class="card">
                    <h4>ภาพยนตร์ทั้งหมด</h4>
                    <p style="font-size: 2rem;"><?php echo $stats['total_movies']; ?></p>
                </div>
                <div class="card">
                    <h4>การจองวันนี้</h4>
                    <p style="font-size: 2rem;"><?php echo $stats['today_bookings']; ?></p>
                </div>
                <div class="card">
                    <h4>ผู้ใช้ทั้งหมด</h4>
                    <p style="font-size: 2rem;"><?php echo $stats['total_users']; ?></p>
                </div>
            </div>

            <!-- Recent Bookings -->
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

            <!-- Movies List -->
            <div class="card">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <h3 class="section-title">ภาพยนตร์ล่าสุด</h3>
                    <a href="movies.php?action=add" class="btn btn-primary">เพิ่มภาพยนตร์</a>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>ชื่อภาพยนตร์</th>
                            <th>สถานะ</th>
                            <th>วันที่ออกฉาย</th>
                            <th>การกระทำ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($movies as $movie): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($movie['title']); ?></td>
                            <td><?php echo $movie['status'] == 'now_showing' ? 'กำลังฉาย' : 'เร็วๆ นี้'; ?></td>
                            <td><?php echo date('d/m/Y', strtotime($movie['release_date'])); ?></td>
                            <td>
                                <a href="movies.php?action=edit&id=<?php echo $movie['id']; ?>" class="btn">แก้ไข</a>
                                <a href="movies.php?action=delete&id=<?php echo $movie['id']; ?>" class="btn btn-danger" onclick="return confirm('ยืนยันการลบภาพยนตร์นี้?')">ลบ</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>