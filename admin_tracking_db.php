<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'config/db.php';

// ตรวจสอบสิทธิ์ผู้ใช้ (กรณีแอดมิน)
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header('Location: login.php');
    exit();
}

if (isset($_POST['action'])) {
    $action = $_POST['action'];
    $order_id = $_POST['order_id'];

    if ($action == 'update' && isset($_POST['status'])) {
        // อัปเดตสถานะคำสั่งซื้อ
        $status = $_POST['status'];

        // อัปเดตสถานะในตาราง orders
        $query = "UPDATE orders SET status = :status WHERE id = :order_id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':order_id', $order_id);
        $stmt->execute();

        header('Location: admin_tracking.php');
        exit();
    }

    if ($action == 'delete') {
        // ลบคำสั่งซื้อ
        $query = "DELETE FROM orders WHERE id = :order_id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':order_id', $order_id);
        $stmt->execute();

        // ลบรายการสินค้าที่เกี่ยวข้องใน order_items
        $query2 = "DELETE FROM order_items WHERE order_id = :order_id";
        $stmt2 = $conn->prepare($query2);
        $stmt2->bindParam(':order_id', $order_id);
        $stmt2->execute();

        header('Location: admin_tracking.php');
        exit();
    }
}
?>
