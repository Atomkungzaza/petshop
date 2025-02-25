<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'config/db.php';

// ตรวจสอบว่าผู้ใช้ล็อกอินหรือไม่
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "กรุณาเข้าสู่ระบบก่อนทำการสั่งซื้อ";
    header("location: login.php");
    exit();
}

// ตรวจสอบข้อมูลที่ส่งมาจากฟอร์มชำระเงิน
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['order_id'], $_POST['total_price'], $_POST['payment_method'])) {
    $order_id = intval($_POST['order_id']);
    $user_id = $_SESSION['user_id'];
    $amount = floatval($_POST['total_price']);
    $payment_method = trim($_POST['payment_method']);

    // กำหนดสถานะการชำระเงิน (อิงกับ ENUM ที่ฐานข้อมูลรองรับ)
    $status = 'paid';

    try {
        // เริ่ม Transaction
        $conn->beginTransaction();

        // บันทึกการชำระเงินในตาราง payments
        $stmt = $conn->prepare("INSERT INTO payments (order_id, user_id, amount, payment_method, status, created_at) 
                                VALUES (:order_id, :user_id, :amount, :payment_method, :status, NOW())");
        $stmt->bindParam(":order_id", $order_id);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->bindParam(":amount", $amount);
        $stmt->bindParam(":payment_method", $payment_method);
        $stmt->bindParam(":status", $status);
        $stmt->execute();

        // อัปเดตสถานะคำสั่งซื้อเป็น 'paid'
        $stmt = $conn->prepare("UPDATE orders SET status = :status WHERE id = :order_id");
        $stmt->bindParam(":order_id", $order_id);
        $stmt->bindParam(":status", $status);
        $stmt->execute();

        // ดึงรายการสินค้าในคำสั่งซื้อนี้
        $stmt = $conn->prepare("SELECT product_id, quantity FROM order_items WHERE order_id = :order_id");
        $stmt->bindParam(":order_id", $order_id);
        $stmt->execute();
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // ลดจำนวนสินค้าในสต็อก (คอลัมน์ stock_quantity)
        foreach ($items as $item) {
            $stmt = $conn->prepare("UPDATE products SET stock_quantity = stock_quantity - :quantity WHERE id = :product_id");
            $stmt->bindParam(":quantity", $item['quantity']);
            $stmt->bindParam(":product_id", $item['product_id']);
            $stmt->execute();
        }

        // ยืนยัน Transaction
        $conn->commit();

        $_SESSION['success'] = "ชำระเงินสำเร็จ! ขอบคุณที่ใช้บริการ";
        header("location: orders.php");
        exit();

    } catch (PDOException $e) {
        // ยกเลิก Transaction หากเกิดข้อผิดพลาด
        $conn->rollBack();
        $_SESSION['error'] = "เกิดข้อผิดพลาดในการชำระเงิน: " . $e->getMessage();
        header("location: checkout.php");
        exit();
    }

} else {
    $_SESSION['error'] = "ข้อมูลไม่ถูกต้อง";
    header("location: checkout.php");
    exit();
}
?>
