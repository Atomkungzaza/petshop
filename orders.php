<!-- โค้ดในไฟล์ order.php -->

<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'config/db.php';
require_once 'orders_db.php'; // ✅ นำเข้าไฟล์ประมวลผล
?>

<?php include 'layouts/header.php'; ?>

<div class="container mt-4">
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= $_SESSION['success'];
                                            unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= $_SESSION['error'];
                                        unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <?php if (!empty($orders)): ?>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>#</th>
                    <th>ราคารวม</th>
                    <th>สถานะ</th>
                    <th>วันที่สั่งซื้อ</th>
                    <th>ตัวดำเนินการ</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td>#<?= $order['id']; ?></td>
                        <td><?= number_format($order['total_price'], 2); ?> บาท</td>
                        <td>
                            <?php if ($order['status'] == 'pending'): ?>
                                <span class="badge bg-warning text-dark">รอชำระเงิน</span>

                            <?php elseif ($order['status'] == 'processing'): ?>
                                <span class="badge bg-info">กำลังตรวจสอบ</span>
                            <?php elseif ($order['status'] == 'paid'): ?>
                                <span class="badge bg-success">ชำระเงินแล้ว</span>
                            <?php else: ?>
                                <span class="badge bg-secondary"><?= htmlspecialchars($order['status']); ?></span>
                            <?php endif; ?>
                        </td>


                        <td><?= $order['created_at']; ?></td>
                        <td>
                            <?php if ($order['status'] == 'pending'): ?>
                                <a href="checkout.php" class="btn btn-sm btn-primary">🔗 ไปชำระเงิน</a>
                            <?php endif; ?>
                            <?php if ($order['status'] != 'paid'): ?>
                                <!-- ปุ่มยกเลิกคำสั่งซื้อ (ใช้งานได้) -->
                                <a href="cancel_order.php?order_id=<?= $order['id']; ?>"
                                    class="btn  btn-sm btn-danger"
                                    onclick="return confirmCancel();">
                                    ❌ ยกเลิกคำสั่งซื้อ
                                </a>

                            <?php else: ?>
                                <!-- ปุ่มยกเลิกคำสั่งซื้อ (ปิดการใช้งาน) -->
                                <a href="#" class="btn  btn-sm  btn-danger disabled" style="pointer-events: none;">
                                    ❌ ยกเลิกคำสั่งซื้อ
                                </a>
                            <?php endif; ?>
                        </td>


                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="text-center">คุณยังไม่มีคำสั่งซื้อ</p>
    <?php endif; ?>
</div>

<script>
    function confirmCancel() {
        return confirm("คุณแน่ใจหรือไม่ว่าต้องการยกเลิกคำสั่งซื้อนี้?");
    }
</script>
<?php include 'layouts/footer.php'; ?>