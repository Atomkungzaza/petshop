<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'config/db.php';


if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id']; // ใช้ user_id แทน username

// เช็คว่ามีการกรอก order_id หรือไม่
$order_id = isset($_POST['order_id']) ? $_POST['order_id'] : '';

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

// คำสั่ง SQL เพื่อดึงข้อมูลจาก orders และ tracking
$query = "SELECT o.id AS order_id, o.status, t.created_at 
          FROM orders o 
          LEFT JOIN tracking t ON o.id = t.order_id 
          WHERE o.user_id = :user_id ";

if ($order_id) {
    $query .= "AND o.id = :order_id ";
}

$query .= "ORDER BY t.created_at DESC";

$stmt = $conn->prepare($query);
$stmt->bindParam(':user_id', $user_id);
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
    <title>ติดตามสินค้า</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include 'layouts/header.php'; ?>
    
    <div class="container">
        <h2>ติดตามสถานะสินค้า</h2>
        
        <!-- ฟอร์มกรอก order_id -->
        <form method="POST" action="tracking.php" class="mb-3">
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
                    <th>วันที่อัปเดต</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($orders): ?>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?= $order['order_id']; ?></td>
                            <td><?= $order['order_id']; ?></td>
                            <td><?= translateStatus($order['status']); ?></td>
                            <td><?= $order['created_at'] ? $order['created_at'] : '-'; ?></td>
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
