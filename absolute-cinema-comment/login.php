<?php
require_once 'includes/config.php'; // รวมไฟล์ config สำหรับเชื่อมต่อฐานข้อมูล

$error = ''; // ตัวแปรเก็บข้อความผิดพลาด

// ตรวจสอบว่ามีการส่งฟอร์มเข้าสู่ระบบหรือไม่
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']); //รับค่าที่กรอกจาก input name="email" และ name="password" ใช้ trim() เพื่อลบช่องว่างหน้า/หลังออก
    
    try {
        // ค้นหาผู้ใช้ในฐานข้อมูล
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(); //เตรียมคำสั่ง SQL แบบป้องกัน SQL Injection (prepare) ดึงข้อมูลผู้ใช้ที่ตรงกับอีเมล ใช้ fetch() เพื่อดึงข้อมูลผู้ใช้เพียงแถวเดียว
        
        // ตรวจสอบว่ามีผู้ใช้นั้นจริง และเปรียบเทียบรหัสผ่านที่ผู้ใช้กรอกกับรหัสผ่านที่เข้ารหัสในฐานข้อมูล
        if ($user && password_verify($password, $user['password'])) {
            // บันทึกข้อมูลผู้ใช้ใน session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            
            // นำทางผู้ใช้ไปยังหน้าที่เหมาะสมตามบทบาท
            switch ($user['role']) { //ตรวจสอบ role ของผู้ใช้ว่าเป็นใคร
                case 'admin':
                    header('Location: admin/index.php'); //ถ้าเป็นผู้ดูแลระบบ (admin) → ไปหน้า dashboard admin
                    break;
                case 'staff':
                    header('Location: staff/index.php'); //ถ้าเป็นพนักงาน → ไปหน้า staff dashboard
                    break;
                case 'user':
                    header('Location: user/index.php'); //ถ้าเป็นผู้ใช้งานทั่วไป → ไปหน้า user
                    break;
                default:
                    header('Location: index.php'); //ถ้าไม่มีบทบาทตรงตามข้างบน → ไปหน้าแรก (index.php)
            }
            exit();
        } else {
            $error = 'อีเมลหรือรหัสผ่านไม่ถูกต้อง';
        }
    } catch (PDOException $e) {
        $error = 'เกิดข้อผิดพลาดในการเข้าสู่ระบบ: ' . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head> <!-- กำหนด encoding, responsive layout, ชื่อแท็บ, และลิงก์ไปยัง CSS -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Absolute Cinema - เข้าสู่ระบบ</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="login-container">
        <h1>Absolute Cinema</h1>
        <form action="login.php" method="post"> <!-- แบบฟอร์มจะส่งข้อมูลกลับมาที่ login.php ด้วย method POST-->
            <?php if ($error): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
            
<!--ช่องกรอกอีเมล -->
            <div class="form-group">
                <label for="email">อีเมล:</label>
                <input type="email" id="email" name="email" required>
            </div>
            
<!-- ช่องกรอกรหัสผ่าน -->
            <div class="form-group">
                <label for="password">รหัสผ่าน:</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit" class="btn">เข้าสู่ระบบ</button> <!-- ปุ่มกดเพื่อส่งฟอร์ม -->
        </form>
    </div>
</body>
</html>