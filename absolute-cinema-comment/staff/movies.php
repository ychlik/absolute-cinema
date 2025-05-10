<?php
require_once '../includes/config.php';
checkRole('staff');

$action = $_GET['action'] ?? 'list';
$movieId = $_GET['id'] ?? 0;
//เริ่มต้นการทำงาน, ตรวจสอบสิทธิ์, และกำหนดตัวแปร action กับ movieId จาก URL


// ตรวจสอบการกระทำ ใช้ switch case เพื่อเรียกฟังก์ชันที่เกี่ยวข้องกับการกระทำ (add, edit, delete, หรือแสดงรายการ)
switch ($action) {
    case 'add':
        addMovie();
        break;
    case 'edit':
        editMovie($movieId);
        break;
    case 'delete':
        deleteMovie($movieId);
        break;
    default:
        listMovies();
}

//เพิ่มภาพยนตร์ ตรวจสอบ POST, เตรียมและ execute SQL INSERT, แสดงผล หรือแสดงฟอร์ม
function addMovie() {
    global $pdo;
    
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO movies (title, synopsis, duration, status, release_date)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $_POST['title'],
                $_POST['synopsis'],
                $_POST['duration'],
                $_POST['status'],
                $_POST['release_date']
            ]);
            
            $_SESSION['success'] = "เพิ่มภาพยนตร์เรียบร้อยแล้ว";
            header("Location: movies.php");
            exit();
        } catch (PDOException $e) {
            die("เกิดข้อผิดพลาด: " . $e->getMessage());
        }
    }
    
    displayMovieForm();
}


//ฟังก์ชันแก้ไขภาพยนตร์
function editMovie($id) {
    global $pdo;
    
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        try {
            $stmt = $pdo->prepare("
                UPDATE movies SET
                    title = ?,
                    synopsis = ?,
                    duration = ?,
                    status = ?,
                    release_date = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $_POST['title'],
                $_POST['synopsis'],
                $_POST['duration'],
                $_POST['status'],
                $_POST['release_date'],
                $id
            ]);
            
            $_SESSION['success'] = "อัปเดตภาพยนตร์เรียบร้อยแล้ว";
            header("Location: movies.php");
            exit();
        } catch (PDOException $e) {
            die("เกิดข้อผิดพลาด: " . $e->getMessage());
        }
    }
    
    $stmt = $pdo->prepare("SELECT * FROM movies WHERE id = ?");
    $stmt->execute([$id]);
    $movie = $stmt->fetch();
    
    if (!$movie) {
        $_SESSION['error'] = "ไม่พบภาพยนตร์นี้";
        header("Location: movies.php");
        exit();
    }
    
    displayMovieForm($movie);
}

//ฟังก์ชันลบภาพยนตร์
function deleteMovie($id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("DELETE FROM movies WHERE id = ?");
        $stmt->execute([$id]);
        
        $_SESSION['success'] = "ลบภาพยนตร์เรียบร้อยแล้ว";
    } catch (PDOException $e) {
        $_SESSION['error'] = "เกิดข้อผิดพลาด: " . $e->getMessage();
    }
    
    header("Location: movies.php");
    exit();
}

//ฟังก์ชันแสดงรายการภาพยนตร์ ดึงข้อมูล,ขึ้นตารางแสดงข้อมูลและปุ่มจัดการ ตามการปรับเปลี่ยน css
function listMovies() {
    global $pdo;
    
    $stmt = $pdo->query("SELECT * FROM movies ORDER BY release_date DESC");
    $movies = $stmt->fetchAll();
    ?>
    <!DOCTYPE html>
    <html lang="th">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>จัดการภาพยนตร์ - Absolute Cinema</title>
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
            .card {
                background: white;
                padding: 20px;
                border-radius: 8px;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
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
            .form-group {
                margin-bottom: 15px;
            }
            .form-group label {
                display: block;
                margin-bottom: 5px;
                font-weight: 600;
            }
            .form-group input, .form-group select, .form-group textarea {
                width: 100%;
                padding: 8px;
                border: 1px solid #ddd;
                border-radius: 4px;
            }
            .success {
                color: #4CAF50;
                padding: 10px;
                margin-bottom: 15px;
                background-color: #e8f5e9;
                border-radius: 4px;
            }
            .error {
                color: #f44336;
                padding: 10px;
                margin-bottom: 15px;
                background-color: #ffebee;
                border-radius: 4px;
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
                <div class="card">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <h2>จัดการภาพยนตร์</h2>
                        <a href="movies.php?action=add" class="btn btn-primary">เพิ่มภาพยนตร์</a>
                    </div>
                    
                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
                    <?php endif; ?>
                    
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
                    <?php endif; ?>
                    
                    <?php if (empty($movies)): ?>
                        <p>ยังไม่มีข้อมูลภาพยนตร์</p>
                    <?php else: ?>
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
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
}

//ฟังก์ชันแสดงฟอร์มเพิ่ม/แก้ไขภาพยนตร์
function displayMovieForm($movie = null) {
    $isEdit = ($movie !== null);
    ?>
    <!DOCTYPE html>
    <html lang="th">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo $isEdit ? 'แก้ไขภาพยนตร์' : 'เพิ่มภาพยนตร์'; ?> - Absolute Cinema</title>
        <link rel="stylesheet" href="../assets/css/style.css">
        <style>
            /* Same styles as in listMovies() */
        </style>
    </head>
    <body>
        <!-- Same Navbar as in listMovies() -->
        
        <div class="dashboard-container">
            <!-- Same Sidebar as in listMovies() -->
            
            <div class="main-content">
                <div class="card">
                    <h2><?php echo $isEdit ? 'แก้ไขภาพยนตร์' : 'เพิ่มภาพยนตร์'; ?></h2>
                    
                    <form method="post" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="title">ชื่อภาพยนตร์</label>
                            <input type="text" id="title" name="title" required 
                                   value="<?php echo $isEdit ? htmlspecialchars($movie['title']) : ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="synopsis">เรื่องย่อ</label>
                            <textarea id="synopsis" name="synopsis" rows="5" required><?php 
                                echo $isEdit ? htmlspecialchars($movie['synopsis']) : ''; 
                            ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="duration">ความยาว (นาที)</label>
                            <input type="number" id="duration" name="duration" required 
                                   value="<?php echo $isEdit ? $movie['duration'] : ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="status">สถานะ</label>
                            <select id="status" name="status" required>
                                <option value="now_showing" <?php echo ($isEdit && $movie['status'] == 'now_showing') ? 'selected' : ''; ?>>กำลังฉาย</option>
                                <option value="coming_soon" <?php echo ($isEdit && $movie['status'] == 'coming_soon') ? 'selected' : ''; ?>>เร็วๆ นี้</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="release_date">วันที่ออกฉาย</label>
                            <input type="date" id="release_date" name="release_date" required 
                                   value="<?php echo $isEdit ? $movie['release_date'] : ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="poster">โปสเตอร์ภาพยนตร์</label>
                            <input type="file" id="poster" name="poster" accept="image/*">
                        </div>
                        
                        <button type="submit" class="btn btn-primary">บันทึก</button>
                        <a href="movies.php" class="btn">ยกเลิก</a>
                    </form>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
}