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

// ฟังก์ชันแปลงสถานะเป็นภาษาไทย
function translateStatus($status) {
    $statusMap = [
        'pending' => 'รอดำเนินการ',
        'processing' => 'กำลังดำเนินการ',
        'shipped' => 'จัดส่งแล้ว',
        'delivered' => 'จัดส่งสำเร็จ',
        'cancelled' => 'ยกเลิกคำสั่งซื้อ'
    ];
    return $statusMap[$status] ?? 'ไม่ทราบสถานะ';
}

// รับหมายเลขคำสั่งซื้อจากแบบฟอร์ม
$order_id = $_POST['order_id'] ?? '';

// คำสั่ง SQL ดึงข้อมูลคำสั่งซื้อ
$query = "SELECT id, status, total_price FROM orders";
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
    
    <div class="container">
        <h2>จัดการคำสั่งซื้อ</h2>

        <form method="POST" action="admin_tracking.php" class="mb-3">
            <div class="input-group">
                <input type="text" name="order_id" class="form-control" placeholder="กรุณากรอกหมายเลขคำสั่งซื้อ" value="<?= htmlspecialchars($order_id); ?>" required>
                <button type="submit" class="btn btn-primary">ค้นหา</button>
            </div>
        </form>

        <table class="table">
            <thead>
                <tr>
                    <th>หมายเลขคำสั่งซื้อ</th>
                    <th>สถานะคำสั่งซื้อ</th>
                    <th>ราคา</th>
                    <th>การจัดการ</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($orders): ?>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?= $order['id']; ?></td>
                            <td><?= translateStatus($order['status']); ?></td>
                            <td><?= number_format($order['total_price'], 2); ?></td>
                            <td>
                                <form method="POST" action="admin_tracking_db.php" class="d-inline">
                                    <input type="hidden" name="id" value="<?= $order['id']; ?>">
                                    <select name="status" class="form-select" required>
                                        <option value="pending" <?= $order['status'] == 'pending' ? 'selected' : ''; ?>>รอดำเนินการ</option>
                                        <option value="processing" <?= $order['status'] == 'processing' ? 'selected' : ''; ?>>กำลังดำเนินการ</option>
                                        <option value="shipped" <?= $order['status'] == 'shipped' ? 'selected' : ''; ?>>จัดส่งแล้ว</option>
                                        <option value="delivered" <?= $order['status'] == 'delivered' ? 'selected' : ''; ?>>จัดส่งสำเร็จ</option>
                                        <option value="cancelled" <?= $order['status'] == 'cancelled' ? 'selected' : ''; ?>>ยกเลิกคำสั่งซื้อ</option>
                                    </select>
                                    <button type="submit" name="action" value="update" class="btn btn-warning btn-sm">อัปเดต</button>
                                </form>
                                <button type="button" class="btn btn-danger btn-sm" onclick="confirmDelete(<?= $order['id']; ?>)">ลบ</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="4" class="text-center">ไม่พบข้อมูลคำสั่งซื้อ</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php include 'layouts/footer.php'; ?>
</body>
</html>
