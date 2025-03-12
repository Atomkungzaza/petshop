<?php
session_start();
require_once 'config/db.php';

// ตรวจสอบว่าผู้ใช้ล็อกอิน
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "กรุณาเข้าสู่ระบบเพื่อดูใบแจ้งหนี้";
    header("location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

// ตรวจสอบคำสั่งซื้อนี้เป็นของผู้ใช้หรือไม่
$stmt = $conn->prepare("SELECT * FROM orders WHERE id = :order_id AND user_id = :user_id");
$stmt->bindParam(":order_id", $order_id);
$stmt->bindParam(":user_id", $user_id);
$stmt->execute();
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    $_SESSION['error'] = "ไม่พบใบแจ้งหนี้นี้ในระบบ";
    header("location: orders.php");
    exit();
}

// ดึงข้อมูลสินค้าในออเดอร์
$stmt = $conn->prepare("SELECT oi.*, p.name, p.price FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = :order_id");
$stmt->bindParam(":order_id", $order_id);
$stmt->execute();
$order_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// บันทึกข้อมูลใบแจ้งหนี้ลงในฐานข้อมูล
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payment_method = $_POST['payment_method'];
    $total_price = $_POST['total_price'];

    $stmt = $conn->prepare("INSERT INTO invoice (order_id, user_id, total_price, payment_method, payment_status) VALUES (:order_id, :user_id, :total_price, :payment_method, 'pending')");
    $stmt->bindParam(":order_id", $order_id);
    $stmt->bindParam(":user_id", $user_id);
    $stmt->bindParam(":total_price", $total_price);
    $stmt->bindParam(":payment_method", $payment_method);
    $stmt->execute();

    $_SESSION['success'] = "ใบแจ้งหนี้ถูกสร้างแล้ว";
    header("location: order_tracking.php");  // ย้ายไปหน้าติดตามคำสั่งซื้อ
    exit();
}
?>

<?php include 'layouts/header.php'; ?>

<div class="invoice mt-4">
    <h2>ใบแจ้งยอดชำระ #<?= $order['id']; ?></h2>
    <p>วันที่สั่งซื้อ: <?= $order['created_at']; ?></p>
    <p>สถานะ: <strong><?= $order['status']; ?></strong></p>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>สินค้า</th>
                <th>จำนวน</th>
                <th>ราคาต่อหน่วย</th>
                <th>ราคารวม</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $total = 0;
            foreach ($order_items as $item):
                $subtotal = $item['price'] * $item['quantity'];
                $total += $subtotal;
            ?>
                <tr>
                    <td><?= htmlspecialchars($item['name']); ?></td>
                    <td><?= $item['quantity']; ?></td>
                    <td><?= number_format($item['price'], 2); ?> บาท</td>
                    <td><?= number_format($subtotal, 2); ?> บาท</td>
                </tr>
            <?php endforeach; ?>
            <tr>
                <td colspan="3" class="text-end"><strong>ราคารวมทั้งหมด</strong></td>
                <td><strong class="text-success"><?= number_format($total, 2); ?> บาท</strong></td>
            </tr>
        </tbody>
    </table>

    <form action="invoice.php" method="POST">
        <input type="hidden" name="order_id" value="<?= $order_id; ?>">
        <input type="hidden" name="total_price" value="<?= $total; ?>">

        <label>เลือกวิธีการชำระเงิน:</label>
        <select class="form-control" required name="payment_method">
            <option value="qr_code">QR Code</option>
            <option value="credit_card">บัตรเครดิต</option>
            <option value="bank_transfer">โอนเงินผ่านธนาคาร</option>
        </select>

        <button type="submit" class="btn btn-success w-100 mt-3">ยืนยันการชำระเงิน</button>
    </form>
</div>

<?php include 'layouts/footer.php'; ?>
