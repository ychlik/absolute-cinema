<?php
require_once '../includes/config.php';
checkRole('user'); //เช็คสถานะว่าเป็น user ไหม

// ตรวจสอบว่ามี ID ภาพยนตร์ที่ส่งมาหรือไม่ ถ้าไม่มีพารามิเตอร์ id (ID ของหนัง) ให้ redirect กลับหน้า index
if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit();
}

$movieId = $_GET['id']; //ดึง ID ของหนังจากพารามิเตอร์ใน URL

// ดึงข้อมูลภาพยนตร์จากฐานข้อมูล
try {
    $movieStmt = $pdo->prepare("SELECT * FROM movies WHERE id = ?");
    $movieStmt->execute([$movieId]); 
    //เตรียมคำสั่ง SQL เพื่อดึงข้อมูลหนังจาก movies ที่มี id ตรงกับที่ส่งมา
    $movie = $movieStmt->fetch(); //รันคำสั่งแล้วเก็บผลลัพธ์ในตัวแปร $movie
    
    if (!$movie) {
        header('Location: index.php');
        exit();
    } //ถ้าไม่พบหนัง (เช่น id ไม่ถูกต้อง) ให้ redirect กลับไปหน้า index
    

    // ดึงข้อมูลรอบฉาย
    $showtimeStmt = $pdo->prepare("
    SELECT * FROM showtimes 
    WHERE movie_id = ?
    ORDER BY show_date, show_time
    ");
    $showtimeStmt->execute([$movieId]);
    $showtimes = $showtimeStmt->fetchAll();
    
    // จัดกลุ่มรอบฉายตามวัน
    $groupedShowtimes = [];
    foreach ($showtimes as $showtime) {
        $date = $showtime['show_date'];
        if (!isset($groupedShowtimes[$date])) {
            $groupedShowtimes[$date] = [];
        }
        $groupedShowtimes[$date][] = $showtime;
    }
    // ดึงข้อมูล genres 
    // ดึงเฉพาะ genres ของภาพยนตร์นี้
    $movie_genresStmt = $pdo->prepare("
    SELECT 
    GROUP_CONCAT(g.name ORDER BY g.name SEPARATOR ', ') AS genres
    FROM 
    movie_genres mg
    JOIN 
    genres g ON g.id = mg.genre_id
    WHERE 
    mg.movie_id = :movie_id
    "); // ใช้ GROUP_CONCAT เพื่อรวมชื่อ genre เป็นข้อความเดียว
    $movie_genresStmt->execute(['movie_id' => $movieId]);
    $movieGenres = $movie_genresStmt->fetch(PDO::FETCH_ASSOC);



} catch (PDOException $e) {
    die("เกิดข้อผิดพลาดในการดึงข้อมูลภาพยนตร์: " . $e->getMessage());
}

$pageTitle = "Absolute Cinema - " . htmlspecialchars($movie['title']);
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

    <!-- Movie Details -->
    <div class="movie-details">
        <div class="movie-poster-large" style="background-color: <?php echo getRandomColor(); ?>"></div>
        <div class="movie-info">
            <h1><?php echo htmlspecialchars($movie['title']); ?></h1>
            <p>ความยาว: <?php echo floor($movie['duration'] / 60); ?> ชั่วโมง <?php echo $movie['duration'] % 60; ?> นาที</p>
            <h3>เรื่องย่อ</h3>
            <p><?php echo htmlspecialchars($movie['synopsis']); ?></p>
            <h3>Genres</h3>
            <p><?php echo htmlspecialchars($movieGenres['genres'] ?? 'ไม่ระบุ'); ?></p>


            <!-- Showtimes -->
            <div class="showtimes">
                <h2>รอบฉาย</h2>
                <?php if (empty($groupedShowtimes)): ?>
                    <p>ไม่มีรอบฉายในขณะนี้</p>
                <?php else: ?>
                    <?php foreach ($groupedShowtimes as $date => $times): ?>
                        <div class="showtime-day">
                            <h3><?php echo date('d/m/Y', strtotime($date)); ?></h3>
                            <?php foreach ($times as $time): ?>
                                <a href="seating.php?showtime_id=<?php echo $time['id']; ?>" class="showtime-slot">
                                    <?php echo date('H:i', strtotime($time['show_time'])); ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php
    function getRandomColor() {
        $colors = ['#FF5733', '#33FF57', '#3357FF', '#F333FF', '#33FFF3', '#FF33F3'];
        return $colors[array_rand($colors)];
    }
    ?>
</body>
</html>