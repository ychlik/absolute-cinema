<?php
require_once '../includes/config.php';
checkRole('staff');
// เหมือน profile.php ในโฟลเดอร์ user

// ดึงข้อมูลผู้ใช้ปัจจุบัน
try {
    $userStmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $userStmt->execute([$_SESSION['user_id']]);
    $user = $userStmt->fetch();
    
    if (!$user) {
        header('Location: ../includes/auth.php?action=logout');
        exit();
    }
} catch (PDOException $e) {
    die("เกิดข้อผิดพลาดในการดึงข้อมูลผู้ใช้: " . $e->getMessage());
}

// ตรวจสอบว่ามีการส่งฟอร์มอัปเดตหรือไม่
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $password = trim($_POST['password']);
    $phone = trim($_POST['phone']);
    
    try {
        // ถ้ามีการเปลี่ยนรหัสผ่าน
        if (!empty($password)) {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $updateStmt = $pdo->prepare("UPDATE users SET password = ?, phone = ? WHERE id = ?");
            $updateStmt->execute([$hashedPassword, $phone, $_SESSION['user_id']]);
        } else {
            $updateStmt = $pdo->prepare("UPDATE users SET phone = ? WHERE id = ?");
            $updateStmt->execute([$phone, $_SESSION['user_id']]);
        }
        
        $success = "อัปเดตโปรไฟล์สำเร็จ";
    } catch (PDOException $e) {
        $error = "เกิดข้อผิดพลาดในการอัปเดตโปรไฟล์: " . $e->getMessage();
    }
}

$pageTitle = "Absolute Cinema - โปรไฟล์ผู้ใช้";
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

    <!-- Profile Container -->
    <div class="profile-container">
        <div class="profile-header">
            <h1>โปรไฟล์ผู้ใช้</h1>
            <?php if (isset($success)): ?>
                <div class="success"><?php echo $success; ?></div>
            <?php endif; ?>
            <?php if (isset($error)): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
        </div>
        
        <form class="profile-form" method="post">
            <div class="form-group">
                <label for="email">อีเมล:</label>
                <input type="email" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
            </div>
            
            <div class="form-group">
                <label for="password">รหัสผ่านใหม่ (เว้นว่างหากไม่ต้องการเปลี่ยน):</label>
                <input type="password" id="password" name="password">
            </div>
            
            <div class="form-group">
                <label for="phone">โทรศัพท์:</label>
                <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
            </div>
            
            <div class="form-group">
                <label>วันที่สร้างบัญชี:</label>
                <input type="text" value="<?php echo date('d/m/Y H:i', strtotime($user['created_at'])); ?>" readonly>
            </div>
            
            <div class="form-actions">
                <a href="index.php" class="btn">ย้อนกลับ</a>
                <button type="submit" class="btn">บันทึกการเปลี่ยนแปลง</button>
            </div>
        </form>
    </div>
</body>
</html>