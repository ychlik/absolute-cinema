<?php
session_start();
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../functions.php';
$users = listUsers();
?>

<!DOCTYPE html> <!-- สไตล์การตกแต่ง navbar sidebar เหมือนหน้าอื่น ๆ -->
    <html lang="th">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>จัดการผู้ใช้ - Absolute Cinema</title>
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
            font-weight: bold;
        }

        .status-inactive {
            color: #dc3545;
            font-weight: bold;
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar">
    <a href="index.php" class="logo">Absolute Cinema - Admin</a>
    <div class="user-menu">
    <span class="user-icon">👤 <?= isset($_SESSION['email']) ? htmlspecialchars($_SESSION['email']) : 'Guest' ?></span>
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



    <!-- Main Content จัดการบัญชีผู้ใช้ -->
    <div class="main-content">
        <h2 class="mb-4">จัดการบัญชีผู้ใช้</h2>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?> <!-- ถ้ามีข้อความ success ใน session ให้แสดงข้อความนั้นด้วยกล่องแจ้งเตือนสีเขียว และลบออกหลังแสดงแล้ว -->
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>  <!-- ข้อความ error จะใช้กล่องสีแดง -->
  
        <!-- ตารางแสดงผู้ใช้ -->
        <table class="table table-bordered table-hover">
            <thead class="table-light">
                <tr>
                    <th>อีเมล</th>
                    <th>เบอร์โทร</th>
                    <th>สถานะ</th>
                    <th>บทบาท</th>
                    <th>วันที่สร้าง</th>
                    <th>การดำเนินการ</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?> <!-- วนลูปแสดงข้อมูลผู้ใช้จากตัวแปร $users -->
                <tr>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <td><?= htmlspecialchars($user['phone']) ?></td> <!-- แสดงอีเมลและเบอร์โทรของผู้ใช้ (ใช้ htmlspecialchars เพื่อป้องกัน XSS) -->
                    <td class="<?= $user['is_active'] ? 'status-active' : 'status-inactive' ?>">
                        <?= $user['is_active'] ? 'ใช้งาน' : 'ปิดใช้งาน' ?> <!-- แสดงสถานะว่าใช้งานอยู่หรือไม่ พร้อมใส่คลาส CSS ตามสถานะ -->
                    </td>
                    <td><?= ucfirst($user['role']) ?></td>
                    <td><?= date("d/m/Y H:i", strtotime($user['created_at'])) ?></td> <!-- แสดงบทบาท (เช่น user/admin) และวันเวลาที่สร้างบัญชี -->
                    <td>
                        <a href="users.php?action=toggle&id=<?= $user['id'] ?>" class="btn btn-secondary btn-sm" onclick="return confirm('คุณแน่ใจหรือไม่ที่จะเปลี่ยนสถานะผู้ใช้นี้?')">
                            <?= $user['is_active'] ? 'ปิดใช้งาน' : 'เปิดบัญชี' ?> <!-- ปุ่ม toggle เปิด/ปิดสถานะบัญชี พร้อมยืนยันก่อนคลิก -->
                        </a>
                        <?php if ($user['role'] === 'user'): ?>
                            <a href="users.php?action=promote&id=<?= $user['id'] ?>" class="btn btn-success btn-sm" onclick="return confirm('เลื่อนขั้นผู้ใช้นี้เป็นพนักงาน?')">เลื่อนขั้น</a>
                        <?php endif; ?> <!-- ถ้าผู้ใช้ยังเป็น user จะมีปุ่มเลื่อนขั้นเป็น staff -->
                    </td>
                </tr>
                <?php endforeach ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>

<?php
// ดำเนินการตาม action
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'], $_GET['id'])) { ////ตรวจสอบว่า URL มี action และ id มาพร้อม และเป็นการส่งแบบ GET
    $id = intval($_GET['id']); //แปลง id เป็นจำนวนเต็มเพื่อความปลอดภัย
    switch ($_GET['action']) {
        case 'toggle':
            toggleUserActive($id);
            break;
        case 'promote':
            promoteUser($id);
            break;
    } //ตรวจสอบ action ถ้าเป็น toggle จะเรียกฟังก์ชัน toggleUserActive() และถ้าเป็น promote จะเรียก promoteUser()
}
?>
