<?php
session_start();
require_once 'config/db.php';

// ✅ ตรวจสอบว่าผู้ใช้ล็อกอินหรือไม่
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "กรุณาเข้าสู่ระบบเพื่อดูใบแจ้งหนี้";
    header("location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

// ✅ ตรวจสอบว่า order นี้เป็นของผู้ใช้คนนี้หรือไม่
$stmt = $conn->prepare("
    SELECT * FROM orders 
    WHERE id = :order_id 
    AND user_id = :user_id
");
$stmt->bindParam(":order_id", $order_id);
$stmt->bindParam(":user_id", $user_id);
$stmt->execute();
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    $_SESSION['error'] = "ไม่พบใบแจ้งหนี้นี้ในระบบ";
    header("location: orders.php");
    exit();
}

// ✅ ดึงข้อมูลสินค้าในออเดอร์
$stmt = $conn->prepare("
    SELECT oi.*, p.name, p.price 
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = :order_id
");
$stmt->bindParam(":order_id", $order_id);
$stmt->execute();
$order_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include 'layouts/header.php'; ?>

<div class="invoice mt-4">
    <h2>ใบแจ้งหนี้ #<?= $order['id']; ?></h2>
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

    <div class="mt-4">
        <form action="process_payment.php" method="POST" id="payment-form">
            <input type="hidden" name="order_id" value="<?= $order_id; ?>">
            <input type="hidden" name="total_price" value="<?= $total; ?>">

            <label>เลือกวิธีการชำระเงิน:</label>
            <select class="form-control" required id="payment-method">
                <option value="">-- เลือกวิธีการชำระเงิน --</option>
                <option value="qr_code">QR Code</option>
                <option value="credit_card">บัตรเครดิต</option>
                <option value="bank_transfer">โอนเงินผ่านธนาคาร</option>
            </select>

            <!-- ✅ QR Code -->
            <div id="qr-code-section" style="display: none; text-align: center; margin-top: 20px;">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=PaymentExample" alt="QR Code">
                <p>สแกนเพื่อชำระเงิน</p>
            </div>

            <!-- ✅ บัตรเครดิต -->
            <div id="credit-card-section" style="display: none;">
                <label>หมายเลขบัตรเครดิต:</label>
                <input type="text" class="form-control" placeholder="xxxx-xxxx-xxxx-xxxx">
                <label>วันหมดอายุ:</label>
                <input type="text" class="form-control" placeholder="MM/YY">
                <label>CVV:</label>
                <input type="text" class="form-control" placeholder="xxx">
            </div>

            <!-- ✅ โอนเงินผ่านธนาคาร -->
            <div id="bank-transfer-section" style="display: none;">
                <p>กรุณาโอนเงินผ่านบัญชีธนาคาร:</p>
                <ul>
                    <li>ธนาคาร: ธนาคารตัวอย่าง</li>
                    <li>เลขบัญชี: 123-456-789</li>
                    <li>ชื่อบัญชี: ร้านขายสัตว์เลี้ยง</li>
                </ul>
                <p>หลังจากชำระเงินแล้ว กรุณาอัปโหลดหลักฐานในหน้า "ยืนยันการชำระเงิน"</p>
            </div>

            <button type="submit" class="btn btn-success w-100 mt-3">ยืนยันการชำระเงิน</button>
        </form>

    </div>
</div>

<script>
    document.getElementById('payment-method').addEventListener('change', function() {
        var method = this.value;
        document.getElementById('qr-code-section').style.display = method === 'qr_code' ? 'block' : 'none';
        document.getElementById('credit-card-section').style.display = method === 'credit_card' ? 'block' : 'none';
        document.getElementById('bank-transfer-section').style.display = method === 'bank_transfer' ? 'block' : 'none';
    });
</script>



<?php include 'layouts/footer.php'; ?>