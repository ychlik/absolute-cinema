<?php
require_once '../includes/config.php'; // รวมไฟล์ config
checkRole('user'); // ตรวจสอบบทบาทผู้ใช้

// ตั้งค่าหัวเรื่องหน้า
$pageTitle = "Absolute Cinema - User Dashboard";

// ดึงข้อมูลภาพยนตร์จากฐานข้อมูล
try {
    // ภาพยนตร์ที่กำลังฉาย
    $nowShowingStmt = $pdo->prepare("SELECT * FROM movies WHERE status = 'now_showing' LIMIT 6");
    $nowShowingStmt->execute();
    $nowShowingMovies = $nowShowingStmt->fetchAll();
    
    // ภาพยนตร์ที่กำลังจะมาฉาย
    $comingSoonStmt = $pdo->prepare("SELECT * FROM movies WHERE status = 'coming_soon' LIMIT 3");
    $comingSoonStmt->execute();
    $comingSoonMovies = $comingSoonStmt->fetchAll();
} catch (PDOException $e) {
    die("เกิดข้อผิดพลาดในการดึงข้อมูลภาพยนตร์: " . $e->getMessage());
}
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
    <!-- Navbar -->
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

    <!-- Movie Status Bar -->
    <div class="movie-status-bar">
        <div class="status-tab active" onclick="showMovies('now_showing')">กำลังฉาย</div>
        <div class="status-tab" onclick="showMovies('coming_soon')">เร็วๆ นี้</div>
    </div>

    <!-- Now Showing Movies -->
    <div id="now_showing" class="movie-section">
        <div class="movie-grid">
            <?php foreach ($nowShowingMovies as $movie): ?>
                <a href="movie_details.php?id=<?php echo $movie['id']; ?>" class="movie-poster" 
                style="background-color: <?php echo getRandomColor(); ?>">
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Coming Soon Movies -->
    <div id="coming_soon" class="movie-section" style="display: none;">
        <div class="movie-grid">
            <?php foreach ($comingSoonMovies as $movie): ?>
                <div class="movie-poster" 
                     style="background-color: <?php echo getRandomColor(); ?>">
                    <!-- ภาพโปสเตอร์จะแสดงที่นี่ -->
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script>
        // ฟังก์ชันสำหรับสลับแสดงภาพยนตร์ระหว่างกำลังฉายและเร็วๆ นี้
        function showMovies(status) {
            // ซ่อนทั้งหมดก่อน
            document.querySelectorAll('.movie-section').forEach(section => {
                section.style.display = 'none';
            });
            
            // แสดงส่วนที่เลือก
            document.getElementById(status).style.display = 'block';
            
            // อัปเดตแท็บที่ active
            document.querySelectorAll('.status-tab').forEach(tab => {
                tab.classList.remove('active');
            });
            
            event.target.classList.add('active');
        }
    </script>
</body>
</html>

<?php
// ฟังก์ชันสำหรับสร้างสีสุ่มสำหรับโปสเตอร์ภาพยนตร์ (จำลอง)
function getRandomColor() {
    $colors = ['#FF5733', '#33FF57', '#3357FF', '#F333FF', '#33FFF3', '#FF33F3'];
    return $colors[array_rand($colors)];
}
?>