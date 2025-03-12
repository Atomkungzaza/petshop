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

<?php include 'layouts/header.php'; ?> <!-- ใช้ path ที่ถูกต้อง -->
<div class="container mt-4">
    <h2 class="mb-3">จัดการข้อมูลสมาชิก</h2>

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
                <th>รูปภาพ</th>
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
                        <?php if (!empty($user['image'])): ?>
                            <img src="uploads/<?= $user['image']; ?>" alt="Profile Image" class="img-fluid" style="width: 50px; height: 50px;">
                        <?php else: ?>
                            <div class="profile-img-placeholder" style="width: 50px; height: 50px; border: 1px solid #ccc;"></div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="admin_user_edit.php?id=<?= $user['id']; ?>" class="btn btn-warning btn-sm">แก้ไข</a>
                        <a href="admin_user_db.php?action=delete&id=<?= $user['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('คุณต้องการลบสมาชิกนี้หรือไม่?')">ลบ</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include 'layouts/footer.php'; ?> <!-- ใช้ path ที่ถูกต้อง -->