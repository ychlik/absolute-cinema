<?php
require_once '../includes/config.php';
checkRole('admin'); //ตรวจสอบว่า user ปัจจุบันมีสิทธิ์เป็นแอดมินหรือไม่

$startDate = $_GET['start_date'] ?? date('Y-m-01');
$endDate = $_GET['end_date'] ?? date('Y-m-t');
//รับค่าช่วงวันที่สำหรับกรองรายงาน

try {
    // สถิติภาพยนตร์ยอดนิยม (นับจากการจองใน bookings) นับจำนวนการจอง (total_bookings) และผู้จองไม่ซ้ำ (unique_users) โดยใช้ JOIN ตาราง movies, showtimes, และ bookings
    $popularMovies = $pdo->prepare(" 
        SELECT 
            m.id, m.title, 
            COUNT(b.id) as total_bookings,
            COUNT(DISTINCT b.user_id) as unique_users
        FROM movies m
        JOIN showtimes s ON m.id = s.movie_id
        JOIN bookings b ON s.id = b.showtime_id
        WHERE b.booking_date BETWEEN ? AND ?
        GROUP BY m.id
        ORDER BY total_bookings DESC
        LIMIT 5
    "); // แสดง 5 อันดับภาพยนตร์ที่มีการจองสูงสุดในช่วงเวลาที่เลือก
    $popularMovies->execute([$startDate, $endDate]);
    $popularMovies = $popularMovies->fetchAll();

    // รายได้ (สมมุติว่าราคาตั๋วคงที่ เช่น 150 บาท/ที่นั่ง)
    $revenueStats = $pdo->prepare("
        SELECT 
            DATE(b.booking_date) AS booking_day,
            COUNT(*) AS total_bookings,
            COUNT(*) * 150 AS total_revenue
        FROM bookings b
        WHERE b.booking_date BETWEEN ? AND ?
        GROUP BY booking_day
        ORDER BY booking_day
    ");
    $revenueStats->execute([$startDate, $endDate]);
    $revenueStats = $revenueStats->fetchAll();

    // สถิติผู้ใช้ นับจำนวนผู้ใช้แบ่งตามบทบาท (admin, staff, user) แสดงจำนวนผู้ที่จองตั๋ว และจำนวนการจองรวม
    $userStats = $pdo->prepare("
        SELECT 
            u.role,
            COUNT(*) AS user_count,
            COUNT(DISTINCT b.user_id) AS booking_users,
            COUNT(b.id) AS total_bookings
        FROM users u
        LEFT JOIN bookings b ON u.id = b.user_id AND b.booking_date BETWEEN ? AND ?
        GROUP BY u.role
    ");
    $userStats->execute([$startDate, $endDate]);
    $userStats = $userStats->fetchAll();

} catch (PDOException $e) {
    die("เกิดข้อผิดพลาดในการดึงข้อมูลรายงาน: " . $e->getMessage());
} //หากมีข้อผิดพลาด SQL หรือการเชื่อมต่อฐานข้อมูล จะหยุดรันพร้อมแสดงข้อความ
?>


<!--  มี <input type="date"> ให้กรอกวันที่เริ่มและสิ้นสุด
  ปุ่ม "แสดงรายงาน" สำหรับโหลดข้อมูลใหม่
    ปุ่ม "เดือนปัจจุบัน" เพื่อ reset ค่า -->

<!-- สไตล์การตกแต่ง navbar sidebar เหมือนหน้าอื่น ๆ -->
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายงานและสถิติ - Absolute Cinema</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .report-period {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .chart-container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 20px;
        }
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
     <!-- Navbar -->
     <nav class="navbar">
            <a href="index.php" class="logo">Absolute Cinema - Admin</a>
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
                <h3>เมนูผู้ดูแลระบบ</h3>
                <ul style="list-style: none; padding: 0;">
                    <li><a href="index.php" class="btn">แดชบอร์ด</a></li>
                    <li style="margin-top: 10px;"><a href="users.php" class="btn">จัดการผู้ใช้</a></li>
                    <li style="margin-top: 10px;"><a href="staff.php" class="btn">จัดการพนักงาน</a></li>
                    <li style="margin-top: 10px;"><a href="reports.php" class="btn">รายงานและสถิติ</a></li>
                </ul>
            </div>
        
        <div class="main-content">
            <div class="card">
                <h2>รายงานและสถิติ</h2>
                
                <!-- ฟอร์มเลือกช่วงเวลา -->
                <div class="report-period">
                    <form method="get" action="reports.php">
                        <div style="display: flex; gap: 10px; align-items: center;">
                            <div>
                                <label for="start_date">ตั้งแต่</label>
                                <input type="date" id="start_date" name="start_date" 
                                       value="<?php echo htmlspecialchars($startDate); ?>" required>
                            </div>
                            <div>
                                <label for="end_date">ถึง</label>
                                <input type="date" id="end_date" name="end_date" 
                                       value="<?php echo htmlspecialchars($endDate); ?>" required>
                            </div>
                            <button type="submit" class="btn">แสดงรายงาน</button>
                            <a href="reports.php" class="btn">เดือนปัจจุบัน</a>
                        </div>
                    </form>
                </div>
                
                <!-- สถิติสรุป -->
                <div class="stats-grid">
                    <?php 
                    $totalRevenue = array_sum(array_column($revenueStats, 'total_revenue'));
                    $totalBookings = array_sum(array_column($revenueStats, 'total_bookings'));
                    ?>
                    <div class="stat-card">
                        <h3>รายได้รวม</h3>
                        <p style="font-size: 2rem; color: #28a745;">
                            ฿<?php echo number_format($totalRevenue, 2); ?>
                        </p>
                    </div>
                    <div class="stat-card">
                        <h3>การจองทั้งหมด</h3>
                        <p style="font-size: 2rem;">
                            <?php echo number_format($totalBookings); ?>
                        </p>
                    </div>
                    <div class="stat-card">
                        <h3>ภาพยนตร์ยอดนิยม</h3>
                        <p style="font-size: 2rem;">
                            <?php echo $popularMovies[0]['title'] ?? '-'; ?>
                        </p>
                    </div>
                </div>
                
                <!-- กราฟรายได้รายวัน -->
                <div class="chart-container">
                    <h3>รายได้รายวัน</h3>
                    <canvas id="revenueChart" height="300"></canvas>
                    <script>
                        const revenueCtx = document.getElementById('revenueChart').getContext('2d');
                        const revenueChart = new Chart(revenueCtx, {
                            type: 'bar',
                            data: {
                                labels: [<?php echo implode(',', array_map(function($item) { 
                                    return "'" . date('d/m', strtotime($item['booking_day'])) . "'"; 
                                }, $revenueStats)); ?>],
                                datasets: [{
                                    label: 'รายได้ (บาท)',
                                    data: [<?php echo implode(',', array_column($revenueStats, 'total_revenue')); ?>],
                                    backgroundColor: 'rgba(40, 167, 69, 0.7)',
                                    borderColor: 'rgba(40, 167, 69, 1)',
                                    borderWidth: 1
                                }]
                            },
                            options: {
                                responsive: true,
                                scales: {
                                    y: {
                                        beginAtZero: true
                                    }
                                }
                            }
                        });
                    </script>
                </div>
                
                <!-- ตารางภาพยนตร์ยอดนิยม -->
                <div class="card">
                    <h3>5 ภาพยนตร์ยอดนิยม</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>อันดับ</th>
                                <th>ภาพยนตร์</th>
                                <th>จำนวนการจอง</th>
                                <th>จำนวนผู้จอง</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($popularMovies as $index => $movie): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td><?php echo htmlspecialchars($movie['title']); ?></td>
                                <td><?php echo $movie['total_bookings']; ?></td>
                                <td><?php echo $movie['unique_users']; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- สถิติผู้ใช้ -->
                <div class="card" style="margin-top: 20px;">
                    <h3>สถิติผู้ใช้</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>ประเภทผู้ใช้</th>
                                <th>จำนวนผู้ใช้</th>
                                <th>ผู้ใช้ที่จองตั๋ว</th>
                                <th>จำนวนการจอง</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($userStats as $stat): ?>
                            <tr>
                                <td>
                                    <?php 
                                    switch($stat['role']) {
                                        case 'admin': echo 'ผู้ดูแลระบบ'; break;
                                        case 'staff': echo 'พนักงาน'; break;
                                        default: echo 'ผู้ใช้ทั่วไป';
                                    }
                                    ?>
                                </td>
                                <td><?php echo $stat['user_count']; ?></td>
                                <td><?php echo $stat['booking_users']; ?></td>
                                <td><?php echo $stat['total_bookings']; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>