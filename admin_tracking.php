<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'config/db.php';

// ตรวจสอบสิทธิ์แอดมิน
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// ตรวจสอบค่าที่กรอกจากฟอร์ม
$order_id = isset($_POST['order_id']) ? $_POST['order_id'] : '';

// ถ้ามีการกรอก order_id ให้ดึงเฉพาะข้อมูลที่เกี่ยวข้อง
if ($order_id) {
    $query = "SELECT t.id, t.order_id, t.status, t.user_id, u.username, t.created_at 
              FROM tracking t 
              JOIN users u ON t.user_id = u.id
              WHERE t.order_id = :order_id
              ORDER BY t.created_at DESC";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':order_id', $order_id);
} else {
    // ถ้าไม่ได้กรอก order_id ให้ดึงข้อมูลทั้งหมด
    $query = "SELECT t.id, t.order_id, t.status, t.user_id, u.username, t.created_at 
              FROM tracking t 
              JOIN users u ON t.user_id = u.id
              ORDER BY t.created_at DESC";
    $stmt = $conn->prepare($query);
}

$stmt->execute();
$trackings = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ติดตามสินค้า (แอดมิน)</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include 'layouts/header.php'; ?>

    <div class="container">
        <h2>ติดตามสถานะสินค้า (แอดมิน)</h2>

        <!-- ฟอร์มกรอก order_id -->
        <form method="POST" action="admin_tracking.php" class="mb-3">
            <div class="input-group">
                <input type="text" name="order_id" class="form-control" placeholder="กรุณากรอกหมายเลขคำสั่งซื้อ" value="<?= htmlspecialchars($order_id); ?>" required>
                <button type="submit" class="btn btn-primary">ค้นหา</button>
            </div>
        </form>

        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>หมายเลขคำสั่งซื้อ</th>
                    <th>สถานะ</th>
                    <th>ผู้ใช้</th>
                    <th>วันที่</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($trackings): ?>
                    <?php foreach ($trackings as $tracking): ?>
                        <tr>
                            <td><?= htmlspecialchars($tracking['id']); ?></td>
                            <td><?= htmlspecialchars($tracking['order_id']); ?></td>
                            <td><?= htmlspecialchars($tracking['status']); ?></td>
                            <td><?= htmlspecialchars($tracking['username']); ?></td>
                            <td><?= htmlspecialchars($tracking['created_at']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="5" class="text-center">ไม่พบข้อมูลการติดตามสำหรับหมายเลขคำสั่งซื้อนี้</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php include 'layouts/footer.php'; ?>
</body>
</html>
