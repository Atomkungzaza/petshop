<!-- โค้ดในไฟล์ checkout.php -->

<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'config/db.php';
require_once 'checkout_db.php'; // ✅ นำเข้าไฟล์ประมวลผล
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
    <h2>รายละเอียดการชำระเงิน</h2>
    <p>ยอดรวมทั้งหมด: <strong><?= number_format($total_price, 2); ?> บาท</strong></p>

    <!-- ✅ แสดงรายการสินค้า -->
    <ul>
        <?php foreach ($order_items as $item): ?>
            <li><?= htmlspecialchars($item['name']); ?> x <?= $item['quantity']; ?> ชิ้น</li>
        <?php endforeach; ?>
    </ul>

    <?php if ($order_status == 'paid'): ?>
        <!-- ✅ แสดงข้อความหากชำระเงินแล้ว -->
        <div class="alert alert-success">✅ คำสั่งซื้อนี้ชำระเงินเรียบร้อยแล้ว</div>
        <a href="orders.php" class="btn btn-primary w-100">ดูคำสั่งซื้อของฉัน</a>
    <?php else: ?>
        <!-- ✅ แสดงฟอร์มเลือกวิธีการชำระเงินหากยังไม่ได้จ่าย -->
        <form action="process_payment.php" method="POST">
            <input type="hidden" name="order_id" value="<?= $order_id; ?>">
            <input type="hidden" name="total_price" value="<?= $total_price; ?>">

            <label>เลือกวิธีการชำระเงิน:</label>
            <select name="payment_method" class="form-control" required>
                <option value="">-- เลือกวิธีการชำระเงิน --</option>
                <option value="qr_code">QR Code</option>
                <option value="credit_card">บัตรเครดิต</option>
                <option value="bank_transfer">โอนเงินผ่านธนาคาร</option>
            </select>

            <button type="submit" class="btn btn-success w-100 mt-3">ยืนยันการชำระเงิน</button>
        </form>
    <?php endif; ?>
</div>

<?php include 'layouts/footer.php'; ?>