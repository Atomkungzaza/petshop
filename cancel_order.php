<!-- โค้ดในไฟล์ cancel_order.php -->

<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'config/db.php';

// ✅ ตรวจสอบว่ามี `order_id` หรือไม่
if (!isset($_GET['order_id'])) {
    $_SESSION['error'] = "ไม่พบคำสั่งซื้อที่ต้องการยกเลิก";
    header("location: orders.php");
    exit();
}

$order_id = intval($_GET['order_id']);

// ✅ ตรวจสอบว่า order นี้เป็นของผู้ใช้ที่ล็อกอินหรือไม่
$user_id = $_SESSION['user_id'];
$check_order = $conn->prepare("SELECT * FROM orders WHERE id = :order_id AND user_id = :user_id");
$check_order->bindParam(":order_id", $order_id);
$check_order->bindParam(":user_id", $user_id);
$check_order->execute();

if ($check_order->rowCount() == 0) {
    $_SESSION['error'] = "คุณไม่มีสิทธิ์ยกเลิกคำสั่งซื้อนี้";
    header("location: orders.php");
    exit();
}

// ✅ ลบ `order_items` ก่อน
$delete_items = $conn->prepare("DELETE FROM order_items WHERE order_id = :order_id");
$delete_items->bindParam(":order_id", $order_id);
$delete_items->execute();

// ✅ ลบ `orders`
$delete_order = $conn->prepare("DELETE FROM orders WHERE id = :order_id");
$delete_order->bindParam(":order_id", $order_id);
$delete_order->execute();

// ✅ แจ้งเตือนว่ายกเลิกสำเร็จ
$_SESSION['success'] = "ยกเลิกคำสั่งซื้อเรียบร้อย!";
header("location: orders.php");
exit();
?>
