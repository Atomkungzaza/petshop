<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'config/db.php';

// ตรวจสอบสิทธิ์ผู้ใช้ (เฉพาะแอดมินเท่านั้น)
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header('Location: login.php');
    exit();
}

if (isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action == 'add' && isset($_POST['total_price'], $_POST['status'])) {
        // ✅ เพิ่มคำสั่งซื้อใหม่
        $total_price = $_POST['total_price'];
        $status = $_POST['status'];

        // บันทึกคำสั่งซื้อใหม่ในฐานข้อมูล
        $query = "INSERT INTO orders (total_price, status, created_at) VALUES (:total_price, :status, NOW())";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':total_price', $total_price);
        $stmt->bindParam(':status', $status);
        $stmt->execute();

        header('Location: admin_tracking.php');
        exit();
    }

    if ($action == 'update' && isset($_POST['id'], $_POST['status'])) {
        // ✅ อัปเดตสถานะคำสั่งซื้อ
        $order_id = $_POST['id'];
        $status = $_POST['status'];

        // อัปเดตข้อมูลในตาราง orders
        $query = "UPDATE orders SET status = :status WHERE id = :order_id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':order_id', $order_id);
        $stmt->execute();

        header('Location: admin_tracking.php');
        exit();
    }

    if ($action == 'delete' && isset($_GET['order_id'])) {
        // ✅ ลบคำสั่งซื้อและรายการสินค้าที่เกี่ยวข้อง
        $order_id = $_GET['order_id'];

        // ลบคำสั่งซื้อจากตาราง orders
        $query = "DELETE FROM orders WHERE id = :order_id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':order_id', $order_id);
        $stmt->execute();

        // ลบรายการสินค้าใน order_items ที่เกี่ยวข้อง
        $query2 = "DELETE FROM order_items WHERE order_id = :order_id";
        $stmt2 = $conn->prepare($query2);
        $stmt2->bindParam(':order_id', $order_id);
        $stmt2->execute();

        header('Location: admin_tracking.php');
        exit();
    }
}
?>
