<?php
require_once 'config/db.php';

if (isset($_GET['invoice_id'])) {
    $invoice_id = intval($_GET['invoice_id']);
    
    // ดึงข้อมูลใบแจ้งยอด
    $stmt = $conn->prepare("SELECT * FROM invoices WHERE id = :invoice_id");
    $stmt->bindParam(":invoice_id", $invoice_id);
    $stmt->execute();
    $invoice = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($invoice) {
        // สร้างไฟล์ PDF หรือ HTML สำหรับใบแจ้งยอด
        // ใช้ไลบรารีเช่น FPDF หรือ HTML2PDF สำหรับการสร้าง PDF

        // ตัวอย่างการดาวน์โหลด HTML (สามารถเปลี่ยนเป็น PDF ได้ตามต้องการ)
        header('Content-Type: text/html');
        header('Content-Disposition: attachment; filename="invoice-' . $invoice['order_id'] . '.html"');
        echo '<html><body>';
        echo '<h1>ใบแจ้งยอด #'. $invoice['order_id'] .'</h1>';
        echo '<p>ราคารวม: ' . number_format($invoice['total_price'], 2) . ' บาท</p>';
        echo '<p>วิธีการชำระเงิน: ' . htmlspecialchars($invoice['payment_method']) . '</p>';
        echo '</body></html>';
        exit();
    }
}

header('Location: tracking.php');
exit();
