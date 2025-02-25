<!-- โค้ดในไฟล์ checkout_db.php -->

<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'config/db.php';

// ✅ ตรวจสอบว่าผู้ใช้ล็อกอินหรือไม่
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "กรุณาเข้าสู่ระบบก่อนทำการสั่งซื้อ";
    header("location: login.php");
    exit();
}

// ✅ ดึงข้อมูลคำสั่งซื้อที่ยังไม่ได้ชำระเงิน
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = :user_id ORDER BY created_at DESC LIMIT 1");
$stmt->bindParam(":user_id", $user_id);
$stmt->execute();
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    $_SESSION['error'] = "ไม่พบคำสั่งซื้อ กรุณาทำการสั่งซื้อใหม่";
    header("location: cart.php");
    exit();
}

// ✅ ตรวจสอบสถานะออเดอร์
$order_id = $order['id'];
$total_price = $order['total_price'];
$order_status = $order['status']; // ⬅️ ตรวจสอบสถานะ

// ✅ ดึงข้อมูลสินค้าที่อยู่ในออเดอร์
$stmt = $conn->prepare("
    SELECT oi.*, p.name 
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = :order_id
");
$stmt->bindParam(":order_id", $order_id);
$stmt->execute();
$order_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>