<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once 'config/db.php';

// ตรวจสอบสิทธิ์การเข้าถึงหน้า Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// แสดงรายชื่อสมาชิกทั้งหมด
$query = "SELECT * FROM users";
$stmt = $conn->prepare($query);
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="th">
<head>

     

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการข้อมูลสมาชิก</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* เพิ่ม CSS สำหรับการเปลี่ยนสีข้อความ "จัดการข้อมูลสมาชิก" */
        .main-header h1 {
            color: black; /* เปลี่ยนสีข้อความเป็นสีดำ */
            font-size: 24px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <header class="main-header">
        <h1 class="topic">จัดการข้อมูลสมาชิก</h1>
    </header>
    
    <div class="container mt-4">
        <a href="admin_user_add.php" class="btn btn-primary mb-3">เพิ่มสมาชิกใหม่</a>
        <table class="table">
            <thead>
                <tr>
                    <th>รหัสผู้ใช้</th>
                    <th>ชื่อผู้ใช้</th>
                    <th>อีเมล</th>
                    <th>ชื่อ-นามสกุล</th>
                    <th>เบอร์โทรศัพท์</th>
                    <th>ที่อยู่</th>
                    <th>บทบาท</th>
                    <th>การกระทำ</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= $user['id']; ?></td>
                    <td><?= $user['username']; ?></td>
                    <td><?= $user['email']; ?></td>
                    <td><?= $user['full_name']; ?></td>
                    <td><?= $user['phone']; ?></td>
                    <td><?= $user['address']; ?></td>
                    <td><?= $user['role']; ?></td>
                    <td>
                        <a href="admin_user_edit.php?id=<?= $user['id']; ?>" class="btn btn-warning btn-sm">แก้ไข</a>
                        <a href="admin_user_db.php?action=delete&id=<?= $user['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('คุณต้องการลบสมาชิกนี้หรือไม่?')">ลบ</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
