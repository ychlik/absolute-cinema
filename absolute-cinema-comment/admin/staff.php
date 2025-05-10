<?php
require_once '../includes/config.php';
checkRole('admin'); //ตรวจสอบว่า user ปัจจุบันมีสิทธิ์เป็นแอดมินหรือไม่

$action = $_GET['action'] ?? 'list'; //รับค่า action จาก query string (add, edit, delete, toggle_active, หรือ list)
$staffId = $_GET['id'] ?? 0;

// ฟังก์ชันจัดการการกระทำต่างๆ เรียกฟังก์ชันตาม action ที่กำหนด
switch ($action) {
    case 'add':
        addStaff();
        break;
    case 'edit':
        editStaff($staffId);
        break;
    case 'delete':
        deleteStaff($staffId);
        break;
    case 'toggle_active':
        toggleStaffActive($staffId);
        break;
    default:
        listStaff();
}

function addStaff() { // เพิ่มพนักงานใหม่
    global $pdo;
    
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        try {
            $hashedPassword = password_hash($_POST['password'], PASSWORD_DEFAULT);
            //เข้ารหัสรหัสผ่าน
            $stmt = $pdo->prepare("
                INSERT INTO users (email, password, role, is_active, phone, created_at)
                VALUES (?, ?, 'staff', 1, ?, NOW())
            "); 
            $stmt->execute([
                $_POST['email'],
                $hashedPassword,
                $_POST['phone']
            ]); // เตรียมและรันคำสั่ง SQL เพื่อเพิ่ม user ใหม่ที่มี role เป็น staff
            
            //แสดงข้อความสำเร็จและกลับไปหน้าหลัก ถ้า error ให้แจ้งว่าเกิดข้อผิดพลาด
            $_SESSION['success'] = "เพิ่มพนักงานเรียบร้อยแล้ว";
            header("Location: staff.php");
            exit();
        } catch (PDOException $e) {
            $_SESSION['error'] = "เกิดข้อผิดพลาด: " . $e->getMessage();
            header("Location: staff.php");
            exit();
        }
    }
    
    displayStaffForm();
}

function editStaff($id) { //แก้ไขข้อมูลพนักงาน
    global $pdo;
    
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        try {
            $sql = "UPDATE users SET email = ?, phone = ?, is_active = ? WHERE id = ?"; //เตรียมคำสั่ง SQL สำหรับอัปเดตข้อมูล
            $params = [
                $_POST['email'],
                $_POST['phone'],
                isset($_POST['is_active']) ? 1 : 0,
                $id
            ];
            
            // ถ้ามีการเปลี่ยนรหัสผ่าน
            if (!empty($_POST['password'])) {
                $sql = "UPDATE users SET email = ?, password = ?, phone = ?, is_active = ? WHERE id = ?";
                $hashedPassword = password_hash($_POST['password'], PASSWORD_DEFAULT); //// เพิ่ม password ใน SQL
                $params = [
                    $_POST['email'],
                    $hashedPassword,
                    $_POST['phone'],
                    isset($_POST['is_active']) ? 1 : 0,
                    $id
                ];
            }
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            
            $_SESSION['success'] = "อัปเดตข้อมูลพนักงานเรียบร้อยแล้ว";
            header("Location: staff.php");
            exit();
        } catch (PDOException $e) {
            $_SESSION['error'] = "เกิดข้อผิดพลาด: " . $e->getMessage();
            header("Location: staff.php");
            exit();
        }
    }
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND role = 'staff'");
    $stmt->execute([$id]);
    $staff = $stmt->fetch();
    
    if (!$staff) {
        $_SESSION['error'] = "ไม่พบพนักงานนี้";
        header("Location: staff.php");
        exit();
    }
    
    displayStaffForm($staff); //ดึงข้อมูลพนักงานจากฐานข้อมูล ถ้าพบข้อมูล เรียก displayStaffForm($staff) ถ้า error ไม่พบพนักงานนี้
}

function deleteStaff($id) { //ลบพนักงาน
    global $pdo;
    
    try {
        // ตรวจสอบว่าไม่ใช่ admin คนสุดท้าย กันเผลอลบ
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE role = 'admin'");
        $stmt->execute();
        $adminCount = $stmt->fetchColumn();
        
        if ($adminCount <= 1) {
            $_SESSION['error'] = "ไม่สามารถลบผู้ดูแลระบบคนสุดท้ายได้";
            header("Location: staff.php");
            exit();
        }
        
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]); //ลบพนักงานออกจากฐานข้อมูล
        
        $_SESSION['success'] = "ลบพนักงานเรียบร้อยแล้ว";
    } catch (PDOException $e) {
        $_SESSION['error'] = "เกิดข้อผิดพลาด: " . $e->getMessage();
    }
    
    header("Location: staff.php");
    exit();
}

function toggleStaffActive($id) { //สลับสถานะการใช้งาน
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("UPDATE users SET is_active = NOT is_active WHERE id = ?");
        $stmt->execute([$id]); //เปลี่ยนสถานะ is_active จาก 1 เป็น 0 หรือกลับกัน
        
        $_SESSION['success'] = "เปลี่ยนสถานะพนักงานเรียบร้อยแล้ว";
    } catch (PDOException $e) {
        $_SESSION['error'] = "เกิดข้อผิดพลาด: " . $e->getMessage();
    }
    
    header("Location: staff.php");
    exit();
}

function listStaff() { // แสดงรายชื่อพนักงาน
    global $pdo;
    
    try {
        $stmt = $pdo->query("SELECT * FROM users WHERE role = 'staff' ORDER BY created_at DESC");
        $staffMembers = $stmt->fetchAll(); //ดึงรายชื่อพนักงานทั้งหมดจากฐานข้อมูล
    } catch (PDOException $e) {
        die("เกิดข้อผิดพลาดในการดึงข้อมูลพนักงาน: " . $e->getMessage());
    }
    ?>
    <!DOCTYPE html>
    <html lang="th"> <!-- สไตล์การตกแต่ง navbar sidebar เหมือนหน้าอื่น ๆ -->
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>จัดการพนักงาน - Absolute Cinema</title>
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
            .btn-sm {
                padding: 4px 8px;
                font-size: 0.8rem;
            }
            .btn-primary {
                background-color: #000;
            }
            .btn-success {
                background-color: #28a745;
            }
            .btn-danger {
                background-color: #dc3545;
            }
            .btn-warning {
                background-color: #ffc107;
                color: #212529;
            }
            .status-active {
                color: #28a745;
            }
            .status-inactive {
                color: #dc3545;
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

            <!-- Main Content -->
            <div class="main-content">
                <div class="card">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <h2>จัดการพนักงาน</h2>
                        <a href="staff.php?action=add" class="btn btn-primary">เพิ่มพนักงาน</a>
                    </div>
                    
                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
                    <?php endif; ?>
                    
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
                    <?php endif; ?>
                    
                    <?php if (empty($staffMembers)): ?>
                        <p>ยังไม่มีข้อมูลพนักงาน</p>
                    <?php else: ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>อีเมล</th>
                                    <th>โทรศัพท์</th>
                                    <th>วันที่สมัคร</th>
                                    <th>สถานะ</th>
                                    <th>การกระทำ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($staffMembers as $staff): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($staff['email']); ?></td>
                                    <td><?php echo htmlspecialchars($staff['phone'] ?? '-'); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($staff['created_at'])); ?></td>
                                    <td class="<?php echo $staff['is_active'] ? 'status-active' : 'status-inactive'; ?>">
                                        <?php echo $staff['is_active'] ? 'ใช้งาน' : 'ปิดใช้งาน'; ?>
                                    </td>
                                    <td>
                                        <a href="staff.php?action=edit&id=<?php echo $staff['id']; ?>" class="btn btn-sm">แก้ไข</a>
                                        <a href="staff.php?action=toggle_active&id=<?php echo $staff['id']; ?>" class="btn btn-sm <?php echo $staff['is_active'] ? 'btn-warning' : 'btn-success'; ?>">
                                            <?php echo $staff['is_active'] ? 'ปิดใช้งาน' : 'เปิดใช้งาน'; ?>
                                        </a>
                                        <a href="staff.php?action=delete&id=<?php echo $staff['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('ยืนยันการลบพนักงานนี้?')">ลบ</a>
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

function displayStaffForm($staff = null) {
    $isEdit = ($staff !== null);
    ?>
    <!DOCTYPE html>
    <html lang="th">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo $isEdit ? 'แก้ไขพนักงาน' : 'เพิ่มพนักงาน'; ?> - Absolute Cinema</title>
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
            .btn-sm {
                padding: 4px 8px;
                font-size: 0.8rem;
            }
            .btn-primary {
                background-color: #000;
            }
            .btn-success {
                background-color: #28a745;
            }
            .btn-danger {
                background-color: #dc3545;
            }
            .btn-warning {
                background-color: #ffc107;
                color: #212529;
            }
            .status-active {
                color: #28a745;
            }
            .status-inactive {
                color: #dc3545;
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
                    <li style="margin-top: 10px;"><a href="staff.php" class="btn">จัดการพนักงาน</a></li>
                    <li style="margin-top: 10px;"><a href="reports.php" class="btn">รายงานและสถิติ</a></li>
                </ul>
            </div>
            
            <div class="main-content">
                <div class="card">
                    <h2><?php echo $isEdit ? 'แก้ไขพนักงาน' : 'เพิ่มพนักงาน'; ?></h2>
                    
                    <form method="post">
                        <div class="form-group">
                            <label for="email">อีเมล</label>
                            <input type="email" id="email" name="email" required 
                                   value="<?php echo $isEdit ? htmlspecialchars($staff['email']) : ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="phone">โทรศัพท์</label>
                            <input type="text" id="phone" name="phone" 
                                   value="<?php echo $isEdit ? htmlspecialchars($staff['phone']) : ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="password"><?php echo $isEdit ? 'รหัสผ่านใหม่ (เว้นว่างหากไม่ต้องการเปลี่ยน)' : 'รหัสผ่าน'; ?></label>
                            <input type="password" id="password" name="password" <?php echo $isEdit ? '' : 'required'; ?>>
                        </div>
                        
                        <?php if ($isEdit): ?>
                        <div class="form-group">
                            <label>
                                <input type="checkbox" name="is_active" <?php echo $staff['is_active'] ? 'checked' : ''; ?>>
                                เปิดใช้งานบัญชี
                            </label>
                        </div>
                        <?php endif; ?>
                        
                        <button type="submit" class="btn btn-primary">บันทึก</button>
                        <a href="staff.php" class="btn">ยกเลิก</a>
                    </form>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
}
