<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'config/db.php';


// เชื่อมต่อฐานข้อมูลและตรวจสอบสถานะผู้ใช้
// เพิ่มฟังก์ชันเพื่อดาวน์โหลดใบแจ้งหนี้

$stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = :user_id ORDER BY created_at DESC");
$stmt->bindParam(":user_id", $user_id);
$stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

function downloadInvoice($order_id) {
    // ดึงข้อมูลใบแจ้งหนี้จากฐานข้อมูล
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM invoice WHERE order_id = :order_id");
    $stmt->bindParam(":order_id", $order_id);
    $stmt->execute();
    $invoice = $stmt->fetch(PDO::FETCH_ASSOC);

    // สร้างไฟล์ PDF หรือ HTML ของใบแจ้งหนี้
    if ($invoice) {
        $invoice_data = "ใบแจ้งหนี้ #{$invoice['id']}\n";
        $invoice_data .= "ยอดรวม: {$invoice['total_price']} บาท\n";
        $invoice_data .= "วิธีการชำระเงิน: {$invoice['payment_method']}\n";
        $invoice_data .= "สถานะการชำระเงิน: {$invoice['payment_status']}\n";
        file_put_contents("invoice_{$invoice['id']}.txt", $invoice_data);  // สร้างไฟล์ .txt
        return "invoice_{$invoice['id']}.txt";
    }
    return null;
}

if (isset($_GET['download_invoice'])) {
    $order_id = $_GET['order_id'];
    $file = downloadInvoice($order_id);
    if ($file) {
        header("Content-Type: application/octet-stream");
        header("Content-Disposition: attachment; filename={$file}");
        readfile($file);
        exit();
    }
}

// ✅ ตรวจสอบว่าผู้ใช้ล็อกอินหรือไม่
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "กรุณาเข้าสู่ระบบเพื่อติดตามคำสั่งซื้อ";
    header("location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// ✅ กำหนด Limit สำหรับการแสดงผล
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 5;
$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

// ✅ ตรวจสอบว่ามีการค้นหาด้วย Order ID หรือไม่
if ($order_id > 0) {
    // ✅ ค้นหาด้วย Order ID
    $stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = :user_id AND id = :order_id ORDER BY created_at DESC");
    $stmt->bindParam(":user_id", $user_id);
    $stmt->bindParam(":order_id", $order_id);
} else {
    // ✅ แสดงรายการทั้งหมดตาม Limit
    $stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = :user_id ORDER BY created_at DESC LIMIT :limit");
    $stmt->bindParam(":user_id", $user_id);
    $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
}

$stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include 'layouts/header.php'; ?>

<div class="container mt-4">
    <h2>ติดตามสถานะการสั่งซื้อ</h2>

    <!-- ✅ ฟอร์มค้นหาด้วย Order ID -->
    <form method="GET" action="order_tracking.php" class="mb-4">
        <div class="input-group">
            <input type="text" name="order_id" class="form-control" placeholder="ค้นหาด้วยหมายเลขคำสั่งซื้อ" value="<?= $order_id > 0 ? $order_id : ''; ?>">
            <button class="btn btn-primary" type="submit">ค้นหา</button>
        </div>
    </form>

    <?php if (empty($orders)): ?>
        <p class="text-center">ไม่พบคำสั่งซื้อที่ค้นหา</p>
    <?php else: ?>
        <?php foreach ($orders as $order): ?>
            <div class="card mb-4">
                <div class="card-body">
                    <div class="order-header d-flex justify-content-between align-items-center">
                        <h5 class="text-start m-0">หมายเลขคำสั่งซื้อ: <span class="text-primary">#<?= $order['id']; ?></span></h5>
                        <span class="order-date text-muted"><?= $order['created_at']; ?></span>
                    </div>
                    <p class="text-start">ราคารวม: <strong><?= number_format($order['total_price'], 2); ?> บาท</strong></p>

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

                </div>
            </div>
        <?php endforeach; ?>

        <!-- ✅ ปุ่ม "ดูเพิ่มเติม" -->
        <?php if ($order_id == 0 && $limit <= 10): ?>
            <div class="text-center">
                <a href="javascript:void(0);" id="toggle-orders" class="btn btn-secondary" onclick="toggleOrders()">ดูเพิ่มเติม</a>
            </div>
        <?php endif; ?>

        <!-- Add a section to show the additional orders when 'See More' is clicked -->
        <div id="extra-orders" style="display: none;">
            <?php
            // Fetch additional orders if 'See More' is clicked
            $stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = :user_id ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
            $stmt->bindParam(":user_id", $user_id);
            $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
            $stmt->bindParam(":offset", $limit, PDO::PARAM_INT);
            $stmt->execute();
            $extraOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($extraOrders as $order): ?>
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="order-header d-flex justify-content-between align-items-center">
                            <h5 class="text-start m-0">หมายเลขคำสั่งซื้อ: <span class="text-primary">#<?= $order['id']; ?></span></h5>
                            <span class="order-date text-muted"><?= $order['created_at']; ?></span>
                        </div>
                        <p class="text-start">ราคารวม: <strong><?= number_format($order['total_price'], 2); ?> บาท</strong></p>

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

                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- JavaScript to handle toggle functionality -->
        <script>
            function toggleOrders() {
                var extraOrders = document.getElementById('extra-orders');
                var toggleButton = document.getElementById('toggle-orders');

                if (extraOrders.style.display === 'none') {
                    extraOrders.style.display = 'block';
                    toggleButton.textContent = 'ซ่อน';
                } else {
                    extraOrders.style.display = 'none';
                    toggleButton.textContent = 'ดูเพิ่มเติม';
                }
            }
        </script>

    <?php endif; ?>
</div>

<?php include 'layouts/footer.php'; ?>