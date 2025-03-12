<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'config/db.php';

// ตรวจสอบสิทธิ์ผู้ดูแลระบบ
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header('Location: login.php');
    exit();
}

// รับหมายเลขคำสั่งซื้อจากแบบฟอร์ม
$order_id = $_POST['order_id'] ?? '';

// คำสั่ง SQL ดึงข้อมูลคำสั่งซื้อ
$query = "SELECT * FROM orders";
if ($order_id) {
    $query .= " WHERE id = :order_id";
}
$query .= " ORDER BY id DESC";

$stmt = $conn->prepare($query);
if ($order_id) {
    $stmt->bindParam(':order_id', $order_id);
}
$stmt->execute();
$orders = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - ติดตามสินค้า</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <script>
        function confirmDelete(order_id) {
            if (confirm("คุณต้องการลบคำสั่งซื้อหมายเลข " + order_id + " หรือไม่?")) {
                window.location.href = "admin_tracking_db.php?action=delete&order_id=" + order_id;
            }
        }
    </script>
</head>
<body>
    <?php include 'layouts/header.php'; ?>
    
    <div class="container mt-4">
        <h2>จัดการคำสั่งซื้อ</h2>

        <!-- ✅ ค้นหาคำสั่งซื้อ -->
        <form method="POST" action="admin_tracking.php" class="mb-3">
            <div class="input-group">
                <input type="text" name="order_id" class="form-control" placeholder="กรุณากรอกหมายเลขคำสั่งซื้อ" value="<?= htmlspecialchars($order_id); ?>" required>
                <button type="submit" class="btn btn-primary">ค้นหา</button>
            </div>
        </form>

        <?php if ($orders): ?>
            <?php foreach ($orders as $order): ?>
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5>หมายเลขคำสั่งซื้อ: <span class="text-primary">#<?= $order['id']; ?></span></h5>
                            <p>ราคารวม: <strong><?= number_format($order['total_price'], 2); ?> บาท</strong></p>
                        </div>

                        <!-- ✅ Timeline Progress -->
                        <ul class="timeline">
                            <li class="timeline-item <?= ($order['status'] == 'paid') ? 'active' : (($order['status'] == 'processing' || $order['status'] == 'shipped' || $order['status'] == 'delivered') ? 'completed' : ''); ?>">
                                <div class="circle"></div>
                                <span>ชำระเงินแล้ว</span>
                            </li>
                            <li class="timeline-item <?= ($order['status'] == 'processing') ? 'active' : (($order['status'] == 'shipped' || $order['status'] == 'delivered') ? 'completed' : ''); ?>">
                                <div class="circle"></div>
                                <span>กำลังดำเนินการ</span>
                            </li>
                            <li class="timeline-item <?= ($order['status'] == 'shipped') ? 'active' : (($order['status'] == 'delivered') ? 'completed' : ''); ?>">
                                <div class="circle"></div>
                                <span>จัดส่งแล้ว</span>
                            </li>
                            <li class="timeline-item <?= ($order['status'] == 'delivered') ? 'active' : ''; ?>">
                                <div class="circle"></div>
                                <span>จัดส่งสำเร็จ</span>
                            </li>
                        </ul>

                        <!-- ✅ ฟอร์มอัปเดตสถานะคำสั่งซื้อ -->
                        <form method="POST" action="admin_tracking_db.php" class="mb-3">
                            <input type="hidden" name="id" value="<?= $order['id']; ?>">
                            <div class="mb-3">
                                <label for="status" class="form-label">เลือกสถานะ</label>
                                <select name="status" class="form-select" required>
                                    <option value="paid" <?= $order['status'] == 'paid' ? 'selected' : ''; ?>>ชำระเงินแล้ว</option>
                                    <option value="processing" <?= $order['status'] == 'processing' ? 'selected' : ''; ?>>กำลังดำเนินการ</option>
                                    <option value="shipped" <?= $order['status'] == 'shipped' ? 'selected' : ''; ?>>จัดส่งแล้ว</option>
                                    <option value="delivered" <?= $order['status'] == 'delivered' ? 'selected' : ''; ?>>จัดส่งสำเร็จ</option>
                                </select>
                            </div>

                            <div class="d-flex justify-content-start">
                                <button type="submit" name="action" value="update" class="btn btn-warning btn-sm me-2">อัปเดต</button>
                                <button type="button" class="btn btn-danger btn-sm" onclick="confirmDelete(<?= $order['id']; ?>)">ลบ</button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-center">ไม่พบข้อมูลคำสั่งซื้อ</p>
        <?php endif; ?>
    </div>

    <?php include 'layouts/footer.php'; ?>
</body>
</html>
